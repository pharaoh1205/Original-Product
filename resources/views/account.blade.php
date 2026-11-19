<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>アカウント</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-100 text-gray-800">
    <div class="min-h-screen flex">
        <!-- 左側サイドバー -->
        <aside class="w-64 bg-blue-100/80 p-6 flex flex-col justify-between border-r border-blue-200">
            <div>
                <!-- プラスアイコン -->
                <a href="{{ route('stores.create') }}" class="w-16 h-16 bg-teal-500 rounded-2xl flex items-center justify-center text-white text-3xl font-bold shadow-md mb-12 cursor-pointer hover:bg-teal-600 transition">
                    +
                </a>
                <!-- メニュー項目 -->
                <nav class="space-y-6">
                    <a href="{{ route('stores.index') }}" class="flex items-center text-lg text-gray-700 font-medium hover:text-teal-600">
                        <span class="mr-3">📄</span>一覧
                    </a>
                    <a href="{{ route('map.index') }}" class="flex items-center text-lg text-gray-700 font-medium hover:text-teal-600">
                        <span class="mr-3">🗺️</span>マップ
                    </a>
                    <a href="{{ route('show.best100') }}" class="flex items-center text-lg text-gray-700 font-medium hover:text-teal-600">
                        <span class="mr-3">📅</span>百名店
                    </a>
                    <a href="{{ route('show.account') }}" class="flex items-center text-lg text-gray-700 font-medium hover:text-teal-600">
                        <span class="mr-3">👤</span>アカウント
                    </a>
                </nav>
            </div>
        </aside>

        <!-- 右側メインエリア -->
        <main class="flex-1 p-10 flex flex-col justify-between">
            <div>
                <!-- ページタイトル
                <h1 class="inline-block bg-blue-200/80 text-gray-900 text-2xl font-medium px-6 py-3 rounded-lg mb-10">
                    アカウント
                </h1> -->

                <!-- アカウント情報カード -->
                <section class="bg-blue-200/80 rounded-lg p-8 max-w-4xl">
                    <h2 class="text-xl font-medium text-gray-900 mb-6">
                        アカウント情報
                    </h2>

                    <div class="flex items-center gap-6">
                        <label for="username" class="text-lg text-gray-900 shrink-0">
                            ユーザーネーム
                        </label>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            value="{{ old('username', auth()->user()->name ?? '') }}"
                            class="w-full max-w-lg h-12 px-4 bg-white rounded-md border border-transparent focus:outline-none focus:ring-2 focus:ring-teal-500 text-gray-800">
                    </div>
                </section>
            </div>
            {{-- <!-- 右下ログアウトボタン まだ昨日作ってないので一旦コメントアウト-->
            <!-- <div class="flex justify-end mt-10">
                <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button
                type="submit"
                class="bg-blue-200/80 hover:bg-blue-300/80 text-gray-900 text-xl font-medium px-8 py-4 rounded-lg transition cursor-pointer">
                ログアウト
            </button>
            </form>
    </div> --> --}}

    </main>
    </div>
</body>

</html>