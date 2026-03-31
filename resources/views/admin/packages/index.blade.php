<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paket Ujian - CBT ADMIN</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @livewireStyles
</head>

<body class="bg-gray-100 flex min-h-screen">

    <div class="w-64 bg-gray-900 text-white flex flex-col shadow-xl">
        <div class="p-6 text-center border-b border-gray-800">
            <h2 class="text-2xl font-extrabold text-red-500">CBT ADMIN</h2>
        </div>
        <div class="grow p-4">
            <a href="{{ route('admin.dashboard') }}"
                class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 mb-2">📊 Dashboard</a>
            <a href="{{ route('admin.categories.index') }}"
                class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 mb-2">📚 Kategori Soal</a>
            <a href="{{ route('admin.packages.index') }}"
                class="block py-2.5 px-4 rounded transition duration-200 bg-gray-800 border-l-4 border-red-500 mb-2 font-bold">📦
                Paket Ujian</a>
            <a href="{{ route('admin.questions.index') }}"
                class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 mb-2">📝 Bank Soal</a>
        </div>
        <div class="p-4 border-t border-gray-800">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit"
                    class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded transition-colors">Logout</button>
            </form>
        </div>
    </div>

    <div class="flex-1 p-8 h-screen flex flex-col">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Manajemen Paket Ujian</h1>
            <a href="{{ route('admin.packages.create') }}"
                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow transition-colors">
                + Tambah Paket Baru
            </a>
        </div>

        <livewire:admin.package-index />

    </div>

    @livewireScripts
</body>

</html>
