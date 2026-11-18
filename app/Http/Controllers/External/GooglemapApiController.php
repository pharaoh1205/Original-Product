<?php


namespace App\Http\Controllers\External;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; // ← Httpファサードをインポート
use App\Http\Controllers\Controller; // ← ★ここを追加！

class GooglemapApiController extends Controller
{
    public function search(Request $request)
    {
        // フォームから送られてきた検索ワードを取得（未入力の場合は空文字）
        $query = $request->input('query', '');

        //フォームのワード取得できない場合、無駄なAPI通信をせずに空の検索結果を返す
        if (empty($query)) {
            return response()->json(['places' => []]);
        }

        // 1. エンドポイントのURL
        $url = 'https://places.googleapis.com/v1/places:searchText';

        // 2. ヘッダーの設定 (.envからAPIキーを呼び出す)Google APIへリクエスト
        $response = Http::withHeaders([
            'X-Goog-Api-Key' => env('GOOGLE_PLACES_API_KEY'),
            'X-Goog-FieldMask' => 'places.id,places.displayName,places.formattedAddress', // 必要に応じて住所なども取得
        ])->post($url, [
            'textQuery' => $query, // ユーザーが入力したキーワードをセット
        ]);

        if ($response->successful()) {
            return response()->json($response->json());
        }

        // エラー時の処理
        return response()->json([
            'error' => 'APIの呼び出しに失敗しました。',
            'details' => $response->body()
        ], $response->status());
    }
}
