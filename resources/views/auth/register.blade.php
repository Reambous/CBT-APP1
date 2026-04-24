<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - UKAICHAMPION APP</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-100 flex items-center justify-center min-h-screen">

    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md">
        <h2 class="text-3xl font-extrabold text-center text-indigo-600 mb-6">Buat Akun Baru</h2>

        <form action="{{ route('register.process') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-semibold mb-2">Nama Lengkap</label>
                <input type="text" name="name"
                    class="w-full px-4 py-3 rounded-lg border focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition-all"
                    required>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-semibold mb-2">Alamat Email</label>
                <input type="email" name="email"
                    class="w-full px-4 py-3 rounded-lg border focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition-all"
                    required>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-semibold mb-2">Password (Min. 3 Karakter)</label>
                <input type="password" name="password"
                    class="w-full px-4 py-3 rounded-lg border focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition-all"
                    required>
            </div>

            <button type="submit"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg transition-colors duration-300">
                Daftar Akun
            </button>
        </form>

        <p class="text-center text-sm text-gray-500 mt-6">
            Sudah punya akun? <a href="{{ route('login') }}" class="text-indigo-600 hover:underline font-semibold">Login
                di sini</a>
        </p>
    </div>

</body>

</html>
