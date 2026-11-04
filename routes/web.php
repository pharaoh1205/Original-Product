<?php

use App\Http\Controllers\MapController;
use App\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/stores', [StoreController::class, 'index']);

Route::get('/stores/new', [StoreController::class, 'index']);



Route::get('/map', [MapController::class, 'index']);