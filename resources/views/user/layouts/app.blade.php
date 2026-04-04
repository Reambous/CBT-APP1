<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'UKAICHAMPION')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    {{-- <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script> --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="bg-gray-50 flex min-h-screen">

    <div class="w-64 bg-blue-800 text-white flex flex-col shadow-xl">
        <div class="p-6 text-center border-b border-blue-700">
            <h2 class="text-2xl font-extrabold tracking-wider">UKAICHAMPION</h2>
        </div>
        <div class="grow p-4">
            <a href="{{ route('user.dashboard') }}"
                class="block py-3 px-4 rounded transition duration-200 mb-2 font-medium {{ request()->routeIs('user.dashboard') ? 'bg-blue-900 font-bold text-white' : 'hover:bg-blue-700 text-blue-100' }}">
                🏠 Dashboard Utama
            </a>

            <a href="{{ route('user.exams') }}"
                class="block py-3 px-4 rounded transition duration-200 mb-2 font-medium {{ request()->routeIs('user.exams') ? 'bg-blue-900 font-bold text-white' : 'hover:bg-blue-700 text-blue-100' }}">
                📝 Daftar Ujian
            </a>

            <a href="{{ route('user.history') }}"
                class="block py-3 px-4 rounded transition duration-200 mb-2 font-medium {{ request()->routeIs('user.history') ? 'bg-blue-900 font-bold text-white' : 'hover:bg-blue-700 text-blue-100' }}">
                📊 Riwayat Nilai
            </a>



            <a href="{{ route('user.upgrade') }}"
                class="block py-3 px-4 rounded transition duration-200 mb-2 font-medium hover:bg-blue-700 text-blue-100">
                💳 Upgrade Premium
            </a>
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

    <div class="flex-1 flex flex-col h-screen overflow-hidden">

        <div class="bg-white shadow-sm p-4 flex justify-between items-center border-b border-gray-200 z-10">
            <h2 class="text-xl font-bold text-gray-800">Ruang Belajar</h2>

            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open"
                    class="flex items-center gap-3 hover:bg-gray-50 p-1 rounded-lg transition">
                    <span class="text-gray-600 font-medium hidden md:block">{{ auth()->user()->name }}</span>

                    @if (auth()->user()->profile_picture)
                        <img src="{{ asset('storage/' . auth()->user()->profile_picture) }}"
                            class="w-10 h-10 rounded-full object-cover border-2 border-blue-100">
                    @else
                        <div
                            class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-lg uppercase border-2 border-blue-200">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                    @endif
                    <span class="text-gray-400 text-xs">▼</span>
                </button>

                <div x-show="open" @click.away="open = false"
                    class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-gray-100 py-2 z-50">
                    <a href="{{ route('user.profile') }}"
                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 font-medium">
                        ⚙️ Pengaturan Profil
                    </a>
                    <hr class="my-1">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 font-medium">
                            🚪 Keluar Aplikasi
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="p-8 overflow-y-auto">
            @yield('content')
        </div>

    </div>
    @livewireScripts
</body>

</html>
