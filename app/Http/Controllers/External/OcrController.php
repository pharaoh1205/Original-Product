<?php


namespace App\Http\Controllers\External;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; // ← Httpファサードをインポート
use App\Http\Controllers\Controller; // ← ★ここを追加！
use Illuminate\Support\Facades\Storage;

class OcrController extends Controller
{

    // OCRで画像から文字抽出
    public function extractTextFromImage(Request $request)
    {
        $request->validate([
            'temp_path' => 'required|string',
        ]);

        $tempPath = $request->input('temp_path');

        //存在チェック（画像がなければここで終了　早期リターン）
        if (!Storage::disk('s3')->exists($tempPath)) {
            return response()->json([
                'success' => false,
                'message' => 'S3上に一時画像が見つかりませんでした。',
            ], 404);
        }

        // ① S3から画像を取得（これが実際の取得処理）
        $imageData = Storage::disk('s3')->get($tempPath);

        // ② Base64に変換
        $base64Image = base64_encode($imageData);

        //Google Cloud Vision APIを呼び出すための「APIキーの取得・チェック」と「送信先URLの組み立て」
        $apiKey = config('services.google.vision_api_key', env('GOOGLE_VISION_API_KEY'));

        // キーが未設定・空っぽの状態を検知
        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'Cloud Vision APIキーが設定されていません。',
            ], 500);
        }

        // どのAPIを使うか＆APIキー
        $visionUrl = 'https://vision.googleapis.com/v1/images:annotate?key=' . urlencode($apiKey);

        // ③ Vision APIに送信するデータを作成
        $data = [
            'requests' => [
                [
                    'image' => [
                        'content' => $base64Image,
                    ],
                    'features' => [
                        [
                            'type' => 'TEXT_DETECTION',
                        ],
                    ],
                ],
            ],
        ];

        // ④ Vision APIにPOST
        $response = Http::timeout(30)->post($visionUrl, $data);

        //通信エラー時はJSONでエラーを返す
        if ($response->failed()) {
            return response()->json([
                'success' => false,
                'message' => 'Vision APIへのリクエストに失敗しました。',
                'detail'  => $response->json(),
            ], $response->status() ?: 500);
        }

        // ⑤ JSONとして結果を取得
        $result = $response->json();

        // ⑥ OCRで読み取った文字列を取得
        //文字が検出されず該当のキーが存在しない場合は、右側の空文字 '' を返し、エラーにならずに処理を続けられるようにしている
        $ocrText = $result['responses'][0]['textAnnotations'][0]['description'] ?? '';

        return response()->json([
            'success'  => true,
            'ocr_text' => $ocrText,
        ]);
    }
}
