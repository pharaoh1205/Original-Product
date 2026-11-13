<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            // 画像の保存先URL/パスを追加
            $table->string('image_url')->after('store_name')->nullable();
            // アップロード時の元のファイル名を追加
            $table->string('original_filename')->after('image_url')->nullable();
            // カラム名をnameからstore_nameに変更
           

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['image_url', 'original_filename']);
           
        });
    }
};
