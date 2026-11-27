<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>お店の登録画面</title>
    <!-- Tailwind CSS（デザイン用 CDN） -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-[#e3eaf5] min-h-screen flex">
    <!-- 左側サイドバー -->
    <aside class="w-64 shrink-0 bg-[#b4c6e7] min-h-screen p-6 flex flex-col justify-between border-r border-[#9fb5de]">
        <div>
            <!-- プラスアイコン -->
            <div class="flex justify-center mb-12">
                <a href="{{ route('stores.create') }}" class="w-20 h-20 bg-teal-500 rounded-2xl flex items-center justify-center text-white text-4xl font-bold shadow-md cursor-pointer hover:bg-teal-600 transition">
                    +
                </a>
            </div>
            <!-- メニュー項目 -->
            <nav class="space-y-6 pr-2">
                <a href="{{ route('stores.index') }}" class="flex items-center justify-end text-xl text-[#1e3a8a] font-medium hover:text-teal-700 transition">
                    <span>一覧</span>
                    <span class="ml-3">📄</span>
                </a>
                <a href="{{ route('map.index') }}" class="flex items-center justify-end text-xl text-[#1e3a8a] font-medium hover:text-teal-700 transition">
                    <span>マップ</span>
                    <span class="ml-3">🗺️</span>
                </a>
                <a href="{{ route('show.best100') }}" class="flex items-center justify-end text-xl text-[#1e3a8a] font-medium hover:text-teal-700 transition">
                    <span>百名店</span>
                    <span class="ml-3">📅</span>
                </a>
                <a href="{{ route('show.account') }}" class="flex items-center justify-end text-xl text-[#1e3a8a] font-medium hover:text-teal-700 transition">
                    <span>アカウント</span>
                    <span class="ml-3">👤</span>
                </a>
            </nav>
        </div>
    </aside>

    <!-- メインコンテンツエリア -->
    <main class="flex-1 min-w-0 px-8 py-8 flex flex-col justify-between">
        <div class="max-w-3xl w-full mx-auto space-y-6">
            <h2 class="text-2xl font-bold text-gray-800">行きたいお店の登録</h2>

            <!-- セッションに保存された「成功メッセージ（success）」が存在する場合、緑色の成功アラートとして画面に表示する処理 -->
            @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
            @endif

            {{-- 店名入力欄 --}}
            <div>
                <label for="store-search-input" class="block text-sm font-bold text-slate-700 mb-1.5">
                    店名
                </label>
                <input
                    type="text"
                    id="store-search-input"
                    placeholder="店名を入力"
                    class="w-full px-4 py-3 bg-white border border-slate-500 rounded-md text-gray-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
            </div>

            {{-- 写真の選択 ＆ プレビューエリア --}}
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">お店の写真</label>
                <div class="relative w-full h-72 bg-white border border-slate-600 flex items-center justify-center cursor-pointer overflow-hidden" id="drop-zone">
                    <img id="preview-image" src="#" alt="プレビュー" class="absolute inset-0 w-full h-full object-cover hidden">
                    <div id="upload-status" class="absolute inset-0 bg-white bg-opacity-80 flex items-center justify-center text-gray-600 font-bold hidden">
                        S3へ一時アップロード中...
                    </div>
                    <div id="placeholder-content" class="flex flex-col items-center">
                        <div class="w-24 h-24 border-[6px] border-black rounded-full flex items-center justify-center text-black text-6xl font-bold leading-none">+</div>
                        <span class="text-sm text-gray-500 mt-3">クリックして写真を選択</span>
                    </div>
                    <input type="file" id="store_image_file" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer">
                </div>
            </div>

            {{-- 検索候補セクション --}}
            <div class="pt-2">
                <div class="flex items-center gap-4 border-b-2 border-[#b4c6e7] pb-2">
                    <h3 class="text-2xl font-medium text-gray-900 shrink-0">検索候補</h3>
                </div>
                <!-- 検索候補（結果）を表示するエリア -->
                <div id="search-results" class="mt-2"></div>
            </div>
        </div>

        {{-- 右下の「検索」「手動で入力」ボタン --}}
        <div class="max-w-3xl w-full mx-auto pt-8 flex flex-col items-end space-y-3">
            <button type="button" id="search-btn" class="w-36 bg-black text-white py-2.5 px-6 rounded-xl font-bold hover:bg-gray-800 transition text-sm text-center">
                検索
            </button>
            <a href="{{ route('stores.finalcreate') }}" id="manual-input-btn" class="w-36 bg-black text-white py-2.5 px-6 rounded-xl font-bold hover:bg-gray-800 transition text-sm text-center">
                手動で入力
            </a>
        </div>
    </main>

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
                        // ③ 「手動で入力」ボタンのリンク先に ?temp_path=xxx を付与する
                        const manualBtn = document.getElementById('manual-input-btn');
                        const baseUrl = "{{ route('stores.finalcreate') }}";
                        manualBtn.href = `${baseUrl}?temp_path=${encodeURIComponent(data.temp_path)}`;

                        // OCR専用エンドポイントを呼び出し（①〜⑥の実行）
                        fetch("{{ route('ocr.extract') }}", {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json', //JSON形式で送りますよ
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}' //Laravelの正規リクエストですよ
                                },
                                body: JSON.stringify({
                                    temp_path: data.temp_path
                                })
                            })
                            .then(res => res.json())
                            .then(ocrData => {
                                if (ocrData.success) {
                                    console.log('OCR結果:', ocrData.ocr_text);
                                    // 必要に応じて検索入力欄へ反映したり、次のGemini抽出処理へ渡したりできます

                                    // OCRで取得した文字列をLLMへ送り、店名を抽出する
                                    return fetch("{{ route('llm.extractStoreName') }}", {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                        },
                                        body: JSON.stringify({
                                            ocr_text: ocrData.ocr_text || ''
                                        })
                                    });
                                } else {
                                    console.error('OCR失敗:', ocrData.message);
                                    return null;
                                }
                            })
                            .then(res => res ? res.json() : null)
                            .then(llmData => {
                                if (!llmData) {
                                    return;
                                }

                                if (llmData.success) {
                                    console.log('抽出店名:', llmData.store_name);

                                    if (llmData.store_name && llmData.store_name.trim() !== '') {
                                        const extractedName = llmData.store_name.trim();

                                        $('#store-search-input').val(extractedName);
                                        runPlaceSearch(extractedName);
                                    }
                                } else {
                                    console.error('店名抽出失敗:', llmData.message);
                                }
                            })
                            .catch(err => {
                                console.error('OCR通信エラー:', err);
                            });
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
        // 店舗検索APIを呼び出して候補一覧を描画する関数
        function runPlaceSearch(keyword) {
            // 空文字チェック
            if (!keyword.trim()) {
                alert('店名を入力してください');
                return;
            }

            // LaravelのAPIへリクエストを送信
            $.get('/api/search-places', {
                query: keyword
            }, function(data) {
                let resultsHtml = '<ul class="bg-white border border-gray-300 rounded-lg mt-2 shadow-md divide-y divide-gray-200">';

                if (data.places && data.places.length > 0) {
                    data.places.forEach(function(place) {
                        let name = place.displayName ? place.displayName.text : '名称不明';
                        let placeId = place.id;

                        resultsHtml += `<li class="p-3 hover:bg-blue-50 cursor-pointer place-item" data-id="${placeId}">${name}</li>`;
                    });
                } else {
                    resultsHtml += '<li class="p-3 text-gray-500">該当する店舗が見つかりませんでした</li>';
                }

                resultsHtml += '</ul>';

                // 検索候補エリアに結果を描画
                $('#search-results').html(resultsHtml);
            }).fail(function() {
                alert('通信に失敗しました。');
            });
        }

        // 「検索」ボタンがクリックされたときに関数を実行する
        $('#search-btn').on('click', function() {
            let keyword = $('#store-search-input').val(); // 入力値を取得
            runPlaceSearch(keyword);
        });


        // 検索候補クリック時に temp_path、place_id、query を付けて finalcreate へ遷移
        $(document).on('click', '.place-item', function() {
            let placeId = $(this).data('id');
            let searchQuery = $('#store-search-input').val().trim();

            let manualBtnHref = $('#manual-input-btn').attr('href');
            let tempPathMatch = manualBtnHref ? manualBtnHref.match(/temp_path=([^&]+)/) : null;
            let tempPath = tempPathMatch ? tempPathMatch[1] : '';

            let targetUrl = "{{ route('stores.finalcreate') }}";
            let params = [];

            if (tempPath) {
                params.push(`temp_path=${tempPath}`);
            }

            if (placeId) {
                params.push(`place_id=${encodeURIComponent(placeId)}`);
            }

            if (searchQuery) {
                params.push(`query=${encodeURIComponent(searchQuery)}`);
            }

            if (params.length > 0) {
                targetUrl += '?' + params.join('&');
            }

            window.location.href = targetUrl;
        });
    </script>
</body>

</html>