<?php

use App\Http\Controllers\MapController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\External\GooglemapApiController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// 一覧画面を表示
Route::get('/stores', [StoreController::class, 'index'])->name('stores.index');

// 登録画面を表示
Route::get('/stores/new', [StoreController::class, 'showcreate'])->name('stores.create');

Route::get('/stores/new/confirm', [StoreController::class, 'finalcreate'])->name('stores.finalcreate');

// 1. 非同期で画像を受け取りS3に保存するルート
Route::post('/stores/upload-image', [StoreController::class, 'uploadImage'])->name('stores.uploadImage');

// 2. 【追加】最終的なデータ（店舗名と画像パスなど）をデータベースに保存するルート
Route::post('/stores', [StoreController::class, 'store'])->name('stores.store');

// MAP画面を表示(MVPに含まれないので一旦後回し)
Route::get('/map', [MapController::class, 'index']);

// Google Place APIで検索しお店の候補を表示
Route::get('/api/search-places', [GooglemapApiController::class, 'search'])->name('google.search');