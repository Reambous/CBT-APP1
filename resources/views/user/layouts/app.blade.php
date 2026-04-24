<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'UKAICHAMPION')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    {{-- <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script> --}}
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="bg-gray-50 flex flex-col md:flex-row h-screen" x-data="{ sidebarOpen: false }">

    <div class="w-full md:w-64 bg-blue-800 text-white flex flex-col shadow-xl flex-shrink-0 z-50">

        <div class="p-4 md:p-6 flex justify-between items-center border-b border-blue-700">
            <h2 class="text-xl md:text-2xl font-extrabold tracking-wider">UKAICHAMPION</h2>

            <button @click="sidebarOpen = !sidebarOpen"
                class="md:hidden bg-blue-700 hover:bg-blue-600 p-2 rounded-lg focus:outline-none transition-colors">
                <svg x-show="!sidebarOpen" class="w-6 h-6 text-white" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16">
                    </path>
                </svg>
                <svg x-show="sidebarOpen" style="display: none;" class="w-6 h-6 text-white" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>

        <div :class="sidebarOpen ? 'flex' : 'hidden'" class="md:flex flex-col grow bg-blue-800 w-full overflow-y-auto">
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

                <a href="{{ route('user.contact') }}"
                    class="block py-3 px-4 rounded-xl transition duration-200 mb-2 font-medium {{ request()->routeIs('user.contact') ? 'bg-blue-900 shadow-inner' : 'hover:bg-blue-700/50 text-blue-100' }}">
                    🎧 Hubungi Admin
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
    </div>

    <div class="flex-1 flex flex-col overflow-hidden relative z-0">

        <div class="bg-white shadow-sm p-4 flex justify-between items-center border-b border-gray-200 shrink-0">
            <h2 class="text-xl font-bold text-gray-800 hidden sm:block">Ruang Belajar</h2>
            <h2 class="text-lg font-bold text-gray-800 sm:hidden">Halo, {{ strtok(auth()->user()->name, ' ') }}!</h2>

            <div x-data="{ open: false }" class="relative ml-auto">
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

                <div x-show="open" @click.away="open = false" style="display: none;"
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

        <div class="p-4 md:p-8 overflow-y-auto grow">
            @yield('content')
        </div>

    </div>

    @if (session('success'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-x-8"
            x-transition:enter-end="opacity-100 transform translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform translate-x-0"
            x-transition:leave-end="opacity-0 transform translate-x-8"
            class="fixed top-5 right-5 z-[9999] bg-green-600 text-white px-6 py-4 rounded-xl shadow-2xl flex items-center gap-3 border border-green-500 min-w-[300px]"
            style="display: none;">
            <span class="text-2xl">✅</span>
            <div class="flex flex-col">
                <span class="font-bold text-sm">Berhasil!</span>
                <span class="text-green-100 text-xs">{{ session('success') }}</span>
            </div>
            <button @click="show = false" class="ml-auto text-green-200 hover:text-white transition-colors">
                ✕
            </button>
        </div>
    @endif

    @if (session('error'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-x-8"
            x-transition:enter-end="opacity-100 transform translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform translate-x-0"
            x-transition:leave-end="opacity-0 transform translate-x-8"
            class="fixed top-5 right-5 z-[9999] bg-red-600 text-white px-6 py-4 rounded-xl shadow-2xl flex items-center gap-3 border border-red-500 min-w-[300px]"
            style="display: none;">
            <span class="text-2xl">⚠️</span>
            <div class="flex flex-col">
                <span class="font-bold text-sm">Terjadi Kesalahan!</span>
                <span class="text-red-100 text-xs">{{ session('error') }}</span>
            </div>
            <button @click="show = false" class="ml-auto text-red-200 hover:text-white transition-colors">
                ✕
            </button>
        </div>
    @endif

    @livewireScripts
</body>

</html>
