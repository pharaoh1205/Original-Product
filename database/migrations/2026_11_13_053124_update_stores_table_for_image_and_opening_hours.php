<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            // 1. image_url カラムを削除する（もし original_filename も消すなら一緒に記述可能）
            $table->dropColumn('image_url');

            // original_filename 消す
            $table->dropColumn('original_filename');

            // 2. opening_hours の型を string (VARCHAR) や text に変更する
            // 既存のカラムを変更する場合は change() メソッド
            $table->string('opening_hours', 255)->nullable()->change();
            
            // ※もし長い文章を入れたい場合は string の代わりに text() にしてください
            // $table->text('opening_hours')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            // ロールバック（元に戻す）ときの処理
            $table->string('image_url')->nullable();
            $table->json('opening_hours')->nullable(); // 元に戻す場合はjson型など
        });
    }
};
