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
    public function store(Request $request){
        // 文字揺れの原因究明
        // \Log::info('store_name check:', ['store_name' => $request->input('store_name')]);

        // 左側の'google_maps_url'はnameの中身
        $request->validate([
            'store_name'      => 'required|string|max:255',
            'temp_path'       => 'nullable|string',
            'memo'            => 'nullable|string', // 追加
            'opening_hours'   => 'nullable|string', // 追加
            'google_maps_url' => 'nullable|string|url',
        ]);

        $tempPath = $request->input('temp_path');
        $finalPath = null;

        if (!empty($tempPath)) {
            if (Storage::disk('s3')->exists($tempPath)) {
                $filename = basename($tempPath);
                $finalPath = 'stores/' . $filename;

                Storage::disk('s3')->move($tempPath, $finalPath);
            } else {
                return back()
                    ->withInput()
                    ->withErrors(['temp_path' => '画像の一時保存データが見つかりませんでした。もう一度やり直してください。']);
            }
        }

        \Log::info('before create:', ['store_name' => $request->input('store_name')]);
        // create() メソッド: 渡された連想配列をもとに、新しいレコード（行）をデータベースに1件追加（INSERT）するメソッド
        Store::create([
            'store_name'      => $request->input('store_name'),
            'image_path'      => $finalPath, // 画像なしの場合は null
            'memo'            => $request->input('memo'),          // 追加
            'opening_hours'   => $request->input('opening_hours'), // 追加
            'google_maps_url' => $request->input('google_maps_url'),
            // image_path などの左側（キー）が、データベース（stores テーブル）のカラム名を表している
        ]);

        return redirect()->route('stores.create')->with('success', '情報を正式に登録しました！');
    }
    
}
