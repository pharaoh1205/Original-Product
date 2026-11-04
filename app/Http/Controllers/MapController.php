<?php

namespace App\Http\Controllers;
// 書かないとLaravelがコントローラーを認識できない

class MapController extends Controller
{
    public function index()
    {
        return view('map.index');
    }
}