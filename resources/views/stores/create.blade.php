<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>お店の登録画面</title>
    <!-- Tailwind CSS（デザイン用 CDN） -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen py-10">

    <div class="max-w-2xl mx-auto p-6 bg-white rounded-xl shadow-md">
        <h2 class="text-2xl font-bold mb-6 text-gray-800">行きたいお店の登録</h2>

        <!-- セッションに保存された「成功メッセージ（success）」が存在する場合、緑色の成功アラートとして画面に表示する処理 -->
        @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
        @endif

        {{-- 最終的な保存処理（POST）を送るフォーム --}}
        <div class="space-y-6">
            {{-- 写真の選択 ＆ プレビューエリア --}}
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">お店の写真</label>
                <div class="relative w-full h-64 bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center cursor-pointer overflow-hidden" id="drop-zone">
                    <img id="preview-image" src="#" alt="プレビュー" class="absolute inset-0 w-full h-full object-cover hidden">
                    <div id="upload-status" class="absolute inset-0 bg-white bg-opacity-80 flex items-center justify-center text-gray-600 font-bold hidden">
                        S3へ一時アップロード中...
                    </div>
                    <div id="placeholder-content" class="flex flex-col items-center">
                        <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center text-white text-3xl font-bold shadow-md">+</div>
                        <span class="text-sm text-gray-500 mt-2">クリックして写真を選択</span>
                    </div>
                    <input type="file" id="store_image_file" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer">
                </div>
            </div>

            {{-- ▼ 【変更】「登録する」ボタンを消し、右下の「検索」「手動で入力」ボタンのみを配置 --}}
            <div class="flex items-center justify-end space-x-2">
                <a href="#" id="search-btn" class="bg-blue-500 text-white py-3 px-6 rounded-lg font-bold hover:bg-blue-600 transition text-sm text-center">
                    検索
                </a>
                <a href="{{ route('stores.finalcreate') }}" id="manual-input-btn" class="bg-gray-200 text-gray-700 py-3 px-6 rounded-lg font-bold hover:bg-gray-300 transition text-sm text-center">
                    手動で入力
                </a>
            </div>
        </div>

        <script>
            document.getElementById('store_image_file').addEventListener('change', function(event) {
                const file = event.target.files[0];
                if (!file) return;

                // ① 即座にローカルでプレビューを表示する処理
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('preview-image');
                    const placeholder = document.getElementById('placeholder-content');
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                    placeholder.classList.add('hidden');
                }
                reader.readAsDataURL(file);

                // ② FormDataを作成して非同期（Ajax）でサーバーへ送信
                const formData = new FormData();
                formData.append('store_image', file);

                const statusDiv = document.getElementById('upload-status');
                statusDiv.classList.remove('hidden');

                fetch("{{ route('stores.uploadImage') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        statusDiv.classList.add('hidden');
                        if (data.success) {
                            // ▼ 【修正】隠しフィールドへの代入は削除
                            // ③ 「手動で入力」ボタンのリンク先に ?temp_path=xxx を付与する
                            const manualBtn = document.getElementById('manual-input-btn');
                            const baseUrl = "{{ route('stores.finalcreate') }}";
                            manualBtn.href = `${baseUrl}?temp_path=${encodeURIComponent(data.temp_path)}`;
                        } else {
                            alert('一時アップロードに失敗しました: ' + (data.message || ''));
                        }
                    })
                    .catch(error => {
                        statusDiv.classList.add('hidden');
                        console.error('Error:', error);
                        alert('通信エラーが発生しました。');
                    });
            });
        </script>
</body>

</html>