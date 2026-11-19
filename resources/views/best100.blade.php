<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>百名店</title>
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

        
    </div>
</body>

</html>