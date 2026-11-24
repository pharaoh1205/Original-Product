<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Store; // データベースモデルを使う場合
use Illuminate\Support\Facades\Auth; // ログインユーザーを取得する場合  
use Illuminate\Support\Facades\Http;

class StoreController extends Controller
{
    public function index()
    {
        return view('stores.index');
        // stores というフォルダの中にある、index.blade.php (一覧画面)というファイルを表示
    }

    public function showbest100()
    {
        return view('best100');
    }
    public function showcreate()
    {
        return view('stores.create');
        // stores というフォルダの中にある、create.blade.php (登録画面)というファイルを表示
    }

    public function finalcreate(Request $request)
    {
        $tempPath = $request->query('temp_path');
        $placeId = $request->query('place_id');
        $searchQuery = $request->query('query');

        $imageUrl = null;
        $mapUrl = null;

        if ($tempPath && Storage::disk('s3')->exists($tempPath)) {
            $imageUrl = Storage::disk('s3')->temporaryUrl(
                $tempPath,
                now()->addMinutes(20)
            );
        }

        // 入力キーワード（query）がある場合を軸にGoogleマップURLを生成
        if ($searchQuery) {
            $mapParams = [
                'api'   => '1',
                'query' => $searchQuery,
            ];

            if ($placeId) {
                $mapParams['query_place_id'] = $placeId;
            }

            $mapUrl = 'https://www.google.com/maps/search/?' . http_build_query($mapParams);
        }

        return view('stores.finalcreate', compact(
            'imageUrl',
            'tempPath',
            'mapUrl',
            'placeId',
            'searchQuery'
        ));
    }




    // ❶【非同期】画像が選択された瞬間にS3の tmp フォルダへ一時保存する
    public function uploadImage(Request $request)
    {
        $request->validate([
            'store_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        if ($request->hasFile('store_image')) {
            // ★ S3の 'tmp' ディレクトリに保存する
            $tempPath = $request->file('store_image')->store('tmp', 's3');

            return response()->json([
                'success' => true,
                'temp_path' => $tempPath, // 例: tmp/xxxxxxx.jpg
                'message' => '一時保存しました'
            ]);
        }

        return response()->json(['success' => false, 'message' => 'アップロードに失敗しました'], 400);
    }





    // ❷【本番保存】文字列と一時パスを受け取り、S3でファイルを移動してDBに保存する
    public function store(Request $request)
    {
        // 1. バリデーションに memo と opening_hours を追加する
        $request->validate([
            'store_name'    => 'required|string|max:255',
            'temp_path'     => 'required|string',
            'memo'          => 'nullable|string', // 追加
            'opening_hours' => 'nullable|string', // 追加
        ]);

        $tempPath = $request->input('temp_path');

        if (Storage::disk('s3')->exists($tempPath)) {
            $filename = basename($tempPath);
            $finalPath = 'stores/' . $filename;

            Storage::disk('s3')->move($tempPath, $finalPath);

            // 2. Store::create に memo と opening_hours を追加する
            Store::create([
                'store_name'    => $request->input('store_name'),
                'image_path'    => $finalPath,
                'memo'          => $request->input('memo'),          // 追加
                'opening_hours' => $request->input('opening_hours'), // 追加
            ]);
        } else {
            return back()->withErrors(['temp_path' => '画像の一時保存データが見つかりませんでした。もう一度やり直してください。']);
        }

        return redirect()->route('stores.create')->with('success', '情報を正式に登録しました！');
    }

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
