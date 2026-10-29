<?php

// Migration:データベースの変更を管理するためのクラス
use Illuminate\Database\Migrations\Migration;
// Blueprint：テーブルやカラムの構造を定義するためのクラス
use Illuminate\Database\Schema\Blueprint;
// Schema：テーブルの作成・変更・削除などを行うための機能
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    // up() は、マイグレーションを実行するときに呼ばれるメソッド
    {
        // Schema::create() は新しいテーブルを作るが、今回はテーブルがすでにあるためSchema::table()
        // Schema::table('stores', ...) は、既存の stores テーブルを変更
        Schema::table('stores', function (Blueprint $table) {
            // ユーザーIDを追加
            // foreignId('user_id'):user_id カラムを作成する
            $table->foreignId('user_id')
                //constrained('users')：users テーブルの id と関連付ける
                ->constrained('users')
                //     cascadeOnDelete()：関連するユーザーが削除されたら、そのユーザーのお店データも削除する
                ->cascadeOnDelete();

            // Google マップ関連の情報を追加する
            $table->string('place_id')->nullable();
            $table->string('store_name');
            $table->text('address')->nullable();

            // decimal(10, 7) は小数点以下7桁まで扱える数値型
            // 緯度・経度を保存するために使っている
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->text('google_maps_url')->nullable();
            $table->json('opening_hours')->nullable();

            $table->string('status', 30)->default('want');
            $table->text('memo')->nullable();

            $table->timestamp('want_at')->nullable();
            $table->timestamp('visited_at')->nullable();
            $table->timestamp('best100_added_at')->nullable();

            // ※user_id と place_id の組み合わせに対して UNIQUE 制約を設定
            // 同じユーザーによる同じお店の重複登録を防ぐ
            $table->unique(
                ['user_id', 'place_id'],
                'stores_user_id_place_id_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropUnique('stores_user_id_place_id_unique');
            $table->dropConstrainedForeignId('user_id');

            $table->dropColumn([
                'place_id',
                'store_name',
                'address',
                'latitude',
                'longitude',
                'google_maps_url',
                'opening_hours',
                'status',
                'memo',
                'want_at',
                'visited_at',
                'best100_added_at',
            ]);
        });
    }
};