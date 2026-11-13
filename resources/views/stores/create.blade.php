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
        <form action="{{ route('stores.store') }}" method="POST">
            @csrf

            {{-- 1. 他の文字列を入力するフィールド（例：お店の名前） --}}
            <div class="mb-4">
                {{-- labelのfor属性も store_name に合わせる --}}
                <label for="store_name" class="block text-sm font-bold text-gray-700 mb-2">お店の名前</label>

                {{-- name属性を store_name に、idも store_name に統一 --}}
                <input type="text" name="store_name" id="store_name" required class="w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="例：美味しいレストラン">
            </div>

            {{-- 2. 非同期でS3のtmpに保存されたパスを保持する隠しフィールド --}}
            <input type="hidden" name="temp_path" id="temp_path">

            {{-- 3. 写真の選択 ＆ プレビューエリア --}}
            <div class="mb-4">
                <label class="block text-sm font-bold text-gray-700 mb-2">お店の写真</label>
                <div class="relative w-full h-64 bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center cursor-pointer overflow-hidden" id="drop-zone">

                    {{-- プレビュー画像（初期は非表示） --}}
                    <img id="preview-image" src="#" alt="プレビュー" class="absolute inset-0 w-full h-full object-cover hidden">

                    {{-- アップロード中のメッセージ --}}
                    <div id="upload-status" class="absolute inset-0 bg-white bg-opacity-80 flex items-center justify-center text-gray-600 font-bold hidden">
                        S3へ一時アップロード中...
                    </div>

                    {{-- プラスボタンとテキスト --}}
                    <div id="placeholder-content" class="flex flex-col items-center">
                        <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center text-white text-3xl font-bold shadow-md">
                            +
                        </div>
                        <span class="text-sm text-gray-500 mt-2">クリックして写真を選択</span>
                    </div>

                    {{-- ファイル選択インプット（透明にしてエリア全体に重ねる） --}}
                    <input type="file" id="store_image_file" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer">
                </div>
            </div>

            @error('temp_path')
            <p class="text-red-500 text-sm mb-4">{{ $message }}</p>
            @enderror

            {{-- 正式な保存ボタンと、右下に配置する「検索」「手動で入力」ボタンエリア --}}
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-6">
                {{-- 正式な保存ボタン（左側またはメイン） --}}
                <button type="submit" class="w-full sm:w-auto flex-1 bg-black text-white py-3 px-6 rounded-lg font-bold hover:bg-gray-800 transition">
                    登録する
                </button>

                {{-- 右下に配置する「検索」と「手動で入力」ボタン --}}
                <div class="flex items-center space-x-2 w-full sm:w-auto justify-end">
                    {{-- 検索ボタン（例: リンクやモーダルを開くボタンとして想定） --}}
                    <a href="#" id="search-btn" class="bg-blue-500 text-white py-3 px-4 rounded-lg font-bold hover:bg-blue-600 transition text-sm text-center">
                        検索
                    </a>

                    {{-- 手動で入力ボタン --}}
                    <a href="{{ route('stores.finalcreate') }}" id="manual-input-btn" class="bg-gray-200 text-gray-700 py-3 px-4 rounded-lg font-bold hover:bg-gray-300 transition text-sm text-center">
                        手動で入力
                    </a>
                </div>
            </div>
        </form>
    </div>

    <script>
        // 写真ファイルが選ばれた瞬間に非同期（Ajax）でS3の tmp へ送信する処理
        document.getElementById('store_image_file').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (!file) return;

            // ① まず即座にローカルでプレビューを表示する
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
            statusDiv.classList.remove('hidden'); // アップロード中表示

            fetch("{{ route('stores.uploadImage') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    statusDiv.classList.add('hidden'); // 表示を消す
                    if (data.success) {
                        // ③ 返ってきた一時保存パス（tmp/xxxx.jpg）を隠しinputにセット
                        document.getElementById('temp_path').value = data.temp_path;
                        // ========================================================
                        // ★【追加】S3への一時保存に成功した瞬間に、「手動で入力」ボタンのリンク先を書き換える
                        // 「手動で入力」ボタンのリンク先に ?temp_path=xxx を自動付与して書き換える(画像保持したまま遷移)
                        // ========================================================
                        const manualBtn = document.getElementById('manual-input-btn');
                        const baseUrl = "{{ route('stores.finalcreate') }}";
                        manualBtn.href = `${baseUrl}?temp_path=${encodeURIComponent(data.temp_path)}`;
                        
                        console.log('一時保存パス:', data.temp_path);
                        console.log('手動入力ボタンのリンク先を更新しました:', manualBtn.href);
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