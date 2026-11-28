<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>行きたいお店リスト</title>
    <!-- ※すでにVite等でTailwindを読み込んでいる場合はこのscriptタグは不要です -->
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

    <!-- 右側メインコンテンツエリア -->
    <main class="flex-1 p-10 md:px-16 md:py-12">
        <div class="max-w-4xl">

            <!-- 上部ステータス切り替えタブ（DBの status カラムに対応） -->
            <div class="inline-flex bg-[#b4c6e7] rounded-sm overflow-hidden shadow-sm mb-10 divide-x divide-[#8fa8d6]">
                <a href="{{ route('stores.index', ['status' => 'want']) }}"
                    class="px-7 py-3 flex items-center gap-2 text-base font-medium text-gray-900 hover:bg-[#a3b8df] transition {{ request('status', 'want') === 'want' ? 'bg-[#9fb5de] font-bold' : '' }}">
                    <span class="inline-block w-4 h-4 rounded-full bg-gray-200 border border-gray-400"></span>
                    <span>行きたいお店</span>
                </a>

                <a href="{{ route('stores.index', ['status' => 'visited']) }}"
                    class="px-7 py-3 flex items-center gap-2 text-base font-medium text-gray-900 hover:bg-[#a3b8df] transition {{ request('status') === 'visited' ? 'bg-[#9fb5de] font-bold' : '' }}">
                    <span>✅</span>
                    <span>行ったお店</span>
                </a>

                <a href="{{ route('stores.index', ['status' => 'best100']) }}"
                    class="px-7 py-3 flex items-center gap-2 text-base font-medium text-gray-900 hover:bg-[#a3b8df] transition {{ request('status') === 'best100' ? 'bg-[#9fb5de] font-bold' : '' }}">
                    <span>💯</span>
                    <span>百名店</span>
                </a>
            </div>

            <!-- 店舗リスト一覧 -->
            <div class="space-y-6">
                @forelse ($stores ?? [] as $store)
                <div class="bg-white flex items-stretch shadow-sm divide-x divide-gray-300 h-28">

                    <!-- 1. 写真エリア（S3の image_path を表示） -->
                    <div class="w-40 shrink-0 flex items-center justify-center overflow-hidden bg-white">
                        @if (!empty($store->image_path))
                        <!-- 非公開のまま画像を表示するため署名付き一時URLを発行 -->
                        <img src="{{ Storage::disk('s3')->temporaryUrl($store->image_path, now()->addMinutes(10)) }}"
                            alt="{{ $store->store_name }}"
                            class="w-full h-full object-cover">
                        @else
                        <span class="text-xl text-gray-800 tracking-wider">写真</span>
                        @endif
                    </div>

                    <!-- 2. 店名エリア（DBの store_name を表示） -->
                    <div class="flex-1 px-8 flex items-center">
                        <span class="text-2xl text-gray-900 font-normal truncate">
                            {{ $store->store_name }}
                        </span>
                    </div>

                    <!-- 3. 変更ボタンエリア -->
                    <div class="w-28 shrink-0 flex items-center justify-center">
                        <button
                            type="button"
                            data-open-status-modal
                            data-store-id="{{ $store->id }}"
                            data-store-name="{{ $store->store_name }}"
                            data-current-status="{{ $store->status ?? 'want' }}"
                            class="bg-[#b4c6e7] hover:bg-[#9fb5de] text-gray-900 text-base font-medium px-5 py-2 rounded-md transition cursor-pointer">
                            変更
                        </button>
                    </div>

                    <!-- 4. 詳細ボタンエリア -->
                    <div class="w-28 shrink-0 flex items-center justify-center">
                        <button type="button"
                            class="bg-[#b4c6e7] hover:bg-[#9fb5de] text-gray-900 text-base font-medium px-5 py-2 rounded-md transition cursor-pointer">
                            詳細
                        </button>
                    </div>

                </div>
                @empty
                <div class="bg-white p-8 text-center text-gray-500 shadow-sm">
                    表示するお店がありません。
                </div>
                @endforelse
            </div>

        </div>
    </main>


    <!-- ステータス変更モーダル -->
    <div id="status-modal"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="status-modal-title">
        <div class="relative w-full max-w-xl bg-gray-200 rounded-md shadow-xl p-8">
            <!-- 閉じる（×）ボタン -->
            <button type="button"
                id="close-status-modal-btn"
                aria-label="閉じる"
                class="absolute top-4 right-4 w-9 h-9 rounded-full bg-slate-700 hover:bg-slate-800 text-white font-bold flex items-center justify-center transition cursor-pointer">
                ✕
            </button>

            <h3 id="status-modal-title" class="text-center text-xl font-medium text-gray-900 mb-2">
                ステータスを変更しますか？
            </h3>
            <p id="status-modal-store-name" class="text-center text-sm text-gray-600 mb-7"></p>

            <form id="status-update-form" method="POST" action="">
                @csrf
                @method('PATCH')

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <button type="submit"
                        name="status"
                        value="want"
                        data-status-option="want"
                        class="bg-white hover:bg-gray-50 text-gray-900 text-sm font-medium py-3.5 px-4 rounded-md shadow-sm border border-transparent transition cursor-pointer">
                        行きたいお店に変更
                    </button>

                    <button type="submit"
                        name="status"
                        value="visited"
                        data-status-option="visited"
                        class="bg-white hover:bg-gray-50 text-gray-900 text-sm font-medium py-3.5 px-4 rounded-md shadow-sm border border-transparent transition cursor-pointer">
                        行ったお店に変更
                    </button>

                    <button type="submit"
                        name="status"
                        value="best100"
                        data-status-option="best100"
                        class="bg-white hover:bg-gray-50 text-gray-900 text-sm font-medium py-3.5 px-4 rounded-md shadow-sm border border-transparent transition cursor-pointer">
                        百名店に変更
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function() {
            const modal = document.getElementById('status-modal');
            const closeBtn = document.getElementById('close-status-modal-btn');
            const form = document.getElementById('status-update-form');
            const storeNameEl = document.getElementById('status-modal-store-name');
            const optionButtons = modal.querySelectorAll('[data-status-option]');
            
            //本来店舗ID必要だが動的にするため仮の文字列として '__STORE_ID__' を渡している
            const actionTemplate = "{{ route('stores.updateStatus', ['store' => '__STORE_ID__']) }}";

            function openModal(button) {
                const storeId = button.dataset.storeId;
                const storeName = button.dataset.storeName || '';
                const currentStatus = button.dataset.currentStatus || 'want';

                form.action = actionTemplate.replace('__STORE_ID__', storeId);
                storeNameEl.textContent = storeName ? `対象：${storeName}` : '';

                optionButtons.forEach((btn) => {
                    const isCurrent = btn.dataset.statusOption === currentStatus;
                    btn.classList.toggle('ring-2', isCurrent);
                    btn.classList.toggle('ring-blue-600', isCurrent);
                    btn.classList.toggle('font-bold', isCurrent);
                });

                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }

            function closeModal() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }

            document.querySelectorAll('[data-open-status-modal]').forEach((button) => {
                button.addEventListener('click', function() {
                    openModal(this);
                });
            });

            closeBtn.addEventListener('click', closeModal);

            modal.addEventListener('click', function(event) {
                if (event.target === modal) {
                    closeModal();
                }
            });

            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                    closeModal();
                }
            });
        })();
    </script>
</body>

</html>