<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;
    
    // DB保存を許可する項目（手動登録で使う４項目＋画像パスなど）
    protected $fillable = [
        'store_name',
        'image_path',
        'original_filename',
        'image_url',
        'user_id',
        'status',
    ];
}
