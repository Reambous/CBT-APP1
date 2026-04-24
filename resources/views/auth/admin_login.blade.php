<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Admin - UKAICHAMPION APP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="bg-gray-900 flex items-center justify-center min-h-screen">

    <div class="bg-white p-8 rounded-xl shadow-2xl w-full max-w-md border-t-8 border-red-600">
        <div class="text-center mb-6">
            <h2 class="text-3xl font-extrabold text-gray-900">Portal Admin</h2>
            <p class="text-gray-500 mt-2 text-sm">Hanya untuk pengelola sistem</p>
        </div>

        @if ($errors->any())
            <div x-data="{ show: true }" x-show="show"
                class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded relative">
                <span class="block sm:inline">{{ $errors->first() }}</span>
                <button @click="show = false" class="absolute top-0 bottom-0 right-0 px-4 py-3">
                    <span class="text-red-500 font-bold">&times;</span>
                </button>
            </div>
        @endif

        <form action="{{ route('admin.login.process') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-800 text-sm font-bold mb-2">Email Admin</label>
                <input type="email" name="email"
                    class="w-full px-4 py-3 rounded-lg border bg-gray-50 focus:border-red-500 focus:ring-2 focus:ring-red-200 outline-none"
                    required>
            </div>

            <div class="mb-6">
                <label class="block text-gray-800 text-sm font-bold mb-2">Kata Sandi</label>
                <input type="password" name="password"
                    class="w-full px-4 py-3 rounded-lg border bg-gray-50 focus:border-red-500 focus:ring-2 focus:ring-red-200 outline-none"
                    required>
            </div>

            <button type="submit"
                class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-lg shadow-lg transition-all duration-300 transform hover:scale-105">
                Otorisasi Masuk
            </button>
        </form>
    </div>

</body>

</html>
