<?php


namespace App\Http\Controllers\External;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; // ← Httpファサードをインポート
use App\Http\Controllers\Controller; // ← ★ここを追加！
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;

class LlmController extends Controller
{
    // OCR文字列をGemini APIに渡して店名を抽出
    public function extractStoreName(Request $request): JsonResponse
    {
        $ocrText = $request->input('ocr_text', '');

        // 「店名」だけを抽出する箱
        $storeName = '';

        //$ocrTextに文字が入っていたら起動
        if (trim($ocrText) !== '') {
            $geminiApiKey = config('services.gemini.api_key', env('GEMINI_API_KEY'));

            // APIキーが設定されていない場合
            if (!$geminiApiKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gemini APIキーが設定されていません。',
                ], 500);
            }

            // 第2引数で「デフォルト値」を保証
            $geminiModel = 'gemini-3.8-flash';
            $geminiUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$geminiModel}:generateContent?key=" . urlencode($geminiApiKey);

            $prompt = "次のOCRテキストから飲食店・店舗の「店名」だけを抽出してください。\n"
                . "余計な説明・記号・改行は一切含めず、店名のみを出力してください。\n"
                . "店名が特定できない場合は空文字を返してください。\n\n"
                . "OCRテキスト:\n" . $ocrText;

            $geminiPayload = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ],
            ];

            $geminiResponse = Http::timeout(60)->post($geminiUrl, $geminiPayload);

            if ($geminiResponse->failed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gemini APIへのリクエストに失敗しました。',
                    'detail'  => $geminiResponse->json(),
                ], $geminiResponse->status() ?: 500);
            }

            $geminiResult = $geminiResponse->json();
            $rawStoreName = $geminiResult['candidates'][0]['content']['parts'][0]['text'] ?? '';
            $storeName = trim($rawStoreName);
        }

        return response()->json([
            'success'    => true,
            'store_name' => $storeName,
        ]);
    }
}
