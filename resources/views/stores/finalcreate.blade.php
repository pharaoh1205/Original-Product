<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登録画面③</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-blue-50 min-h-screen flex">

    <!-- 左側サイドバー -->
    <aside class="w-64 bg-blue-100/80 p-6 flex flex-col justify-between border-r border-blue-200">
        <div>
            <!-- プラスアイコン -->
            <div class="w-16 h-16 bg-teal-500 rounded-2xl flex items-center justify-center text-white text-3xl font-bold shadow-md mb-12 cursor-pointer hover:bg-teal-600 transition">
                +
            </div>
            <!-- メニュー項目 -->
            <nav class="space-y-6">
                <a href="#" class="flex items-center text-lg text-gray-700 font-medium hover:text-teal-600">
                    <span class="mr-3">📄</span>一覧
                </a>
                <a href="#" class="flex items-center text-lg text-gray-700 font-medium hover:text-teal-600">
                    <span class="mr-3">🗺️</span>マップ
                </a>
                <a href="#" class="flex items-center text-lg text-gray-700 font-medium hover:text-teal-600">
                    <span class="mr-3">📅</span>百名店
                </a>
            </nav>
        </div>
        <div>
            <a href="#" class="flex items-center text-lg text-gray-700 font-medium hover:text-teal-600">
                <span class="mr-3">👤</span>アカウント
            </a>
        </div>
    </aside>

    <!-- 右側メインコンテンツ -->
    <main class="flex-1 p-10 flex flex-col justify-center items-center">
        <form action="{{ route('stores.store') }}" method="POST" class="max-w-xl w-full">
            @csrf


            {{-- ▼ ここに一時パスを保持する隠しフィールドを追加 --}}
            <input type="hidden" name="temp_path" value="{{ $tempPath ?? '' }}">

            <!-- 写真プレビューエリア -->
            <div class="relative w-full h-64 bg-gray-200 border-2 border-dashed border-gray-400 rounded-lg flex items-center justify-center cursor-pointer overflow-hidden mb-8 shadow-sm" id="drop-zone">

                {{-- 修正: $imageUrl が空でない場合のみ画像を出し、hiddenクラスを外す --}}
                <img id="preview-image" src="{{ $imageUrl ?? '#' }}" alt="プレビュー" class="absolute inset-0 w-full h-full object-cover {{ empty($imageUrl) ? 'hidden' : '' }}">

                {{-- 修正: $imageUrl があればプレースホルダーを隠す --}}
                <div id="placeholder-content" class="flex flex-col items-center text-gray-600 {{ !empty($imageUrl) ? 'hidden' : '' }}">
                    <span class="text-xl font-medium">アップしたスクショ写真</span>
                </div>

            </div>

            <!-- 基本情報セクション -->
            <div class="mb-8">
                <div class="flex items-center mb-6">
                    <h3 class="text-xl font-bold text-gray-800 mr-4">基本情報</h3>
                    <div class="flex-1 h-0.5 bg-blue-200"></div>
                </div>

                <!-- 店名（※コントローラーのバリデーションに合わせて name="store_name" に変更しました） -->
                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-500 mb-1">店名</label>
                    <input type="text" name="store_name" required class="w-full bg-blue-100/60 border border-blue-300 rounded-md p-2.5 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>

                <!-- 経路 -->
                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-500 mb-1">経路</label>
                    <input type="text" name="route" placeholder="Place IDを使ってGoogleマップのURLを自動生成" class="w-full bg-blue-100/60 border border-blue-300 rounded-md p-2.5 text-red-500 placeholder-red-500 focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>

                <!-- 営業時間 -->
                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-500 mb-1">営業時間</label>
                    <input type="text" name="opening_hours" class="w-full bg-blue-100/60 border border-blue-300 rounded-md p-2.5 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>

                <!-- URL（修正: name="url" に変更） -->
                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-500 mb-1">URL</label>
                    <input type="url" name="url" placeholder="https://..." class="w-full bg-blue-100/60 border border-blue-300 rounded-md p-2.5 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>

                <!-- メモ（修正: name="memo" に変更し、複数行書けるよう textarea に） -->
                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-500 mb-1">メモ</label>
                    <textarea name="memo" rows="3" class="w-full bg-blue-100/60 border border-blue-300 rounded-md p-2.5 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400"></textarea>
                </div>
            </div>

            <!-- 右下の「保存する」ボタン -->
            <div class="flex justify-end">
                <button type="submit" class="bg-black text-white px-8 py-3 rounded-lg font-bold shadow-md hover:bg-gray-800 transition">
                    保存する
                </button>
            </div>
        </form>
    </main>

    <!-- 写真選択時のプレビュー表示用JavaScript（この画面で新しく画像を選び直したくなった用） -->
    <script>
        const imageFileInput = document.getElementById('store_image_file');
        if (imageFileInput) {
            imageFileInput.addEventListener('change', function(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const preview = document.getElementById('preview-image');
                        const placeholder = document.getElementById('placeholder-content');
                        preview.src = e.target.result;
                        preview.classList.remove('hidden');
                        placeholder.classList.add('hidden');
                    }
                    reader.readAsDataURL(file);
                }
            });
        }
    </script>
</body>

</html>