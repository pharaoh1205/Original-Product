<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Store; // データベースモデルを使う場合
use Illuminate\Support\Facades\Auth; // ログインユーザーを取得する場合  

class AccountController extends Controller
{

    public function showaccount()
    {
        return view('account');
        // アカウントファイルを表示
    }

    
}
