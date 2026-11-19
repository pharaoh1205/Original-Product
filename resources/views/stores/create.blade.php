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

        {{-- 仮で作成した検索文字入力欄 --}}
        <div>
            <label for="store-search-input" class="block text-sm font-bold text-slate-600 mb-1.5">
                店名
            </label>
            <input
                type="text"
                id="store-search-input"
                placeholder="店名を入力"
                class="w-full px-4 py-3 bg-[#b4c6e7]/70 border border-slate-400 rounded-xl text-gray-800 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
        </div>

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

            <!-- 検索候補（結果）を表示するエリア -->
            <div id="search-results" style="margin-top: 10px;"></div>

            {{-- ▼ 【変更】「登録する」ボタンを消し、右下の「検索」「手動で入力」ボタンのみを配置 --}}
            <div class="flex items-center justify-end space-x-2">
                <!-- <a href="#" id="search-btn" class="btn btn-primary bg-blue-500 text-white py-3 px-6 rounded-lg font-bold hover:bg-blue-600 transition text-sm text-center">
                    検索
                </a> -->
                <!-- 検索ボタン -->
                <button type="button" id="search-btn" class="btn btn-primary bg-blue-500 text-white py-3 px-6 rounded-lg font-bold hover:bg-blue-600 transition text-sm text-center">検索</button>
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

        <!-- /JavaScriptの便利な拡張ライブラリである「jQuery（ジェイクエリー）」のプログラム本体を、インターネット上の公開サーバー（CDN）経由で自分のホームページ（HTML）に読み込ませる -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>
            // 「検索」ボタンがクリックされたときに関数を実行する
            $('#search-btn').on('click', function() {
                let keyword = $('#store-search-input').val(); // 入力値を取得

                // 空文字チェック
                if (!keyword.trim()) {
                    alert('店名を入力してください');
                    return;
                }

                // LaravelのAPIへリクエストを送信　
                //コールバック関数→通信が終わったあと（成功時）に呼ばれる
                $.get('/api/search-places', {
                    query: keyword
                }, function(data) {

                    // 【変更】リスト全体にTailwindのスタイルを付与
                    let resultsHtml = '<ul class="bg-white border border-gray-300 rounded-lg mt-2 shadow-md divide-y divide-gray-200">';

                    //コールバック関数→配列の中身を1つ取り出すたびに呼ばれる
                    if (data.places && data.places.length > 0) {
                        data.places.forEach(function(place) {
                            let name = place.displayName ? place.displayName.text : '名称不明';
                            let placeId = place.id; // 【追加】Place IDを変数に取得

                            // 【変更】ホバー・ポインター・識別クラス(.place-item)を付与（store_nameは持ち込まない）
                            resultsHtml += `<li class="p-3 hover:bg-blue-50 cursor-pointer place-item" data-id="${placeId}">${name}</li>`;
                        });
                    } else {
                        // 【変更】空の場合のリストにも余白と文字色を付与
                        resultsHtml += '<li class="p-3 text-gray-500">該当する店舗が見つかりませんでした</li>';
                    }

                    resultsHtml += '</ul>';

                    // 検索候補エリアに結果を描画
                    $('#search-results').html(resultsHtml);

                    // コールバック関数 → 通信が失敗したときに呼ばれる
                }).fail(function() {
                    alert('通信に失敗しました。');
                });
            }); // ← ここで #search-btn のクリック処理を閉じる

            // 【追加】検索候補クリック時に temp_path と place_id を付けて finalcreate へ遷移
            $(document).on('click', '.place-item', function() {
            let placeId = $(this).data('id');

            let manualBtnHref = $('#manual-input-btn').attr('href');
            let tempPathMatch = manualBtnHref ? manualBtnHref.match(/temp_path=([^&]+)/) : null;
            let tempPath = tempPathMatch ? tempPathMatch[1] : '';

            let targetUrl = "{{ route('stores.finalcreate') }}";
            let params = [];

            if (tempPath) {
                params.push(`temp_path=${tempPath}`);
            }

            if (placeId) {
                params.push(`place_id=${placeId}`);
            }

            if (params.length > 0) {
                targetUrl += '?' + params.join('&');
            }

            window.location.href = targetUrl;
            });
        
        </script>
</body>

</html>