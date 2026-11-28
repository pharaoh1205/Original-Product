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
                            <button type="button"
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

</body>
</html>