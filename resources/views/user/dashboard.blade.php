<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Peserta - CBT APP</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 flex min-h-screen">

    <div class="w-64 bg-blue-800 text-white flex flex-col shadow-xl">
        <div class="p-6 text-center border-b border-blue-700">
            <h2 class="text-2xl font-extrabold tracking-wider">CBT PESERTA</h2>
        </div>
        <div class="grow p-4">
            <a href="#"
                class="block py-3 px-4 rounded transition duration-200 bg-blue-900 hover:bg-blue-700 mb-2 font-medium">🏠
                Dashboard Utama</a>
            <a href="#"
                class="block py-3 px-4 rounded transition duration-200 hover:bg-blue-700 mb-2 font-medium">📝 Daftar
                Ujian</a>
            <a href="#"
                class="block py-3 px-4 rounded transition duration-200 hover:bg-blue-700 mb-2 font-medium">📊 Riwayat
                Nilai</a>
            <a href="#"
                class="block py-3 px-4 rounded transition duration-200 hover:bg-blue-700 mb-2 font-medium">💳 Upgrade
                Premium</a>
        </div>
        <div class="p-4 border-t border-blue-700">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit"
                    class="w-full bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded transition-colors shadow">
                    🚪 Keluar
                </button>
            </form>
        </div>
    </div>

    <div class="flex-1 flex flex-col">

        <div class="bg-white shadow-sm p-4 flex justify-between items-center border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-800">Ruang Belajar</h2>
            <div class="flex items-center gap-3">
                <span class="text-gray-600 font-medium">{{ $user->name }}</span>
                <div
                    class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-lg">
                    {{ substr($user->name, 0, 1) }}
                </div>
            </div>
        </div>

        <div class="p-8">
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 mb-6 border-l-4 border-l-blue-500">
                <h2 class="text-2xl font-bold text-gray-800">Selamat datang kembali, {{ $user->name }}!</h2>
                <p class="text-gray-500 mt-1">Mari persiapkan ujianmu dengan maksimal hari ini.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div
                    class="bg-white p-6 rounded-xl shadow-sm border border-t-4 {{ $user->is_premium ? 'border-t-green-500' : 'border-t-yellow-500' }}">
                    <h3 class="text-gray-500 text-sm font-bold uppercase tracking-wider mb-3">Status Akses</h3>

                    <span
                        class="px-3 py-1 text-sm font-bold rounded-full {{ $user->is_premium ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                        {{ $user->is_premium ? '👑 Akun Premium' : '🔒 Akun Gratis' }}
                    </span>

                    <div class="mt-4 pt-4 border-t border-gray-50">
                        @if ($user->is_premium)
                            <p class="text-sm text-gray-600">Berlaku sampai: <br><span
                                    class="font-bold text-gray-800">{{ $user->premium_until ? $user->premium_until->format('d M Y') : '-' }}</span>
                            </p>
                        @else
                            <p class="text-sm text-gray-600 mb-3">Akses ujian terbatas. Buka semua fitur sekarang.</p>
                            <button
                                class="w-full bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 rounded shadow-sm transition">
                                Upgrade Akses
                            </button>
                        @endif
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm border border-t-4 border-t-indigo-500">
                    <h3 class="text-gray-500 text-sm font-bold uppercase tracking-wider mb-2">Ujian Diselesaikan</h3>
                    <p class="text-4xl font-extrabold text-gray-800 mt-2">0 <span
                            class="text-lg font-medium text-gray-500">kali</span></p>
                </div>
            </div>
        </div>

    </div>

</body>

</html>
