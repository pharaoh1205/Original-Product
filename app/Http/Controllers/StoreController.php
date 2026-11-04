<?php

namespace App\Http\Controllers;

class StoreController extends Controller
{
    public function index()
    {
        return view('stores.index');
        // stores というフォルダの中にある、index.blade.php というファイルを表示
    }

    public function showcreate()
    {
        return view('stores.create');
        // stores というフォルダの中にある、index.blade.php というファイルを表示
    }
}
