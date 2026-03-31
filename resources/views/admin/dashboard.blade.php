<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin CBT</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 flex">

    <div class="w-64 bg-gray-900 min-h-screen text-white flex flex-col">
        <div class="p-6 text-center border-b border-gray-800">
            <h2 class="text-2xl font-extrabold text-red-500">CBT ADMIN</h2>
        </div>
        <div class="grow p-4">
            <a href="#"
                class="block py-2.5 px-4 rounded transition duration-200 bg-gray-800 hover:bg-gray-700 mb-2">📊
                Dashboard</a>
            <a href="{{ route('admin.categories.index') }}"
                class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 mb-2">📚 Kategori Soal</a>

            <a href="{{ route('admin.packages.index') }}"
                class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 mb-2">📦 Paket Ujian</a>
            <a href="#" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 mb-2">💰
                Transaksi</a>
        </div>
        <div class="p-4 border-t border-gray-800">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit"
                    class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded transition-colors">
                    Logout
                </button>
            </form>
        </div>
    </div>

    <div class="flex-1 p-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Overview Sistem</h1>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-lg shadow p-6 border-t-4 border-blue-500">
                <h3 class="text-gray-500 text-sm font-bold uppercase">Total Pengguna</h3>
                <p class="text-3xl font-bold text-gray-800 mt-2">0</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6 border-t-4 border-green-500">
                <h3 class="text-gray-500 text-sm font-bold uppercase">Pendapatan</h3>
                <p class="text-3xl font-bold text-gray-800 mt-2">Rp 0</p>
            </div>
        </div>
    </div>

</body>

</html>
