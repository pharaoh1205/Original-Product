<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Store; // データベースモデルを使う場合
use Illuminate\Support\Facades\Auth; // ログインユーザーを取得する場合  

class StoreController extends Controller
{
    public function index()
    {
        return view('stores.index');
        // stores というフォルダの中にある、index.blade.php (一覧画面)というファイルを表示
    }

    public function showcreate()
    {
        return view('stores.create');
        // stores というフォルダの中にある、create.blade.php (登録画面)というファイルを表示
    }

    public function finalcreate(Request $request)
    {
        $tempPath = $request->query('temp_path');
        $imageUrl = null;

        if ($tempPath && Storage::disk('s3')->exists($tempPath)) {
            // if の中身は、真ん中の &&（かつ） で大きく左右の2つの条件に分かれている
            // $tempPath （左側の条件：パスがちゃんと存在するか？）
            // Storage::disk('s3')->exists($tempPath) （右側の条件：S3の中にそのファイルが本当にあるか？）

            // ★ S3から数分間だけアクセスできる「署名付きURL」を生成する（例: 有効期限20分）
            $imageUrl = Storage::disk('s3')->temporaryUrl(
                $tempPath,
                now()->addMinutes(20)
            );
        }
        // ★ ここで中身を画面に強制表示して止めてみる
        // dd($tempPath, Storage::disk('s3')->exists($tempPath), $imageUrl);

        return view('stores.finalcreate', compact('imageUrl', 'tempPath'));
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
}
