<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// プログラムから一括登録・更新できる項目を指定
#[Fillable(['name', 'email', 'password'])]

// ユーザー情報をJSONなどに変換するとき、パスワードなどを表示しないようにする設定
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
// extends Authenticatable：Laravelの認証機能を引き継ぐ
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */

    // データを保存・取得するときの形式を指定
    protected function casts(): array
    {
        return [
            //datetime：日付・時刻として扱う
            'email_verified_at' => 'datetime',
            //hashed：パスワードをハッシュ化して保存する
            'password' => 'hashed',
        ];
    }
}
