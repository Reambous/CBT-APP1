<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UKAICHAMPION</title>
    <script src="https://cdn.tailwindcss.com"></script>
    {{-- <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script> --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="bg-gray-100 flex min-h-screen">

    <div class="w-64 bg-gray-900 text-white flex flex-col shadow-xl min-h-screen">
        <div class="p-6 text-center border-b border-gray-800">
            <h2 class="text-2xl font-extrabold text-red-500">UKAICHAMPIONS</h2>
        </div>
        <div class="grow p-4">
            <a href="{{ route('admin.dashboard') }}"
                class="block py-2.5 px-4 rounded transition duration-200 mb-2 {{ request()->routeIs('admin.dashboard') ? 'bg-gray-800 border-l-4 border-red-500 font-bold' : 'hover:bg-gray-700' }}">
                📊 Dashboard
            </a>

            <a href="{{ route('admin.categories.index') }}"
                class="block py-2.5 px-4 rounded transition duration-200 mb-2 {{ request()->routeIs('admin.categories.*') ? 'bg-gray-800 border-l-4 border-red-500 font-bold' : 'hover:bg-gray-700' }}">
                📚 Kategori Soal
            </a>

            <a href="{{ route('admin.packages.index') }}"
                class="block py-2.5 px-4 rounded transition duration-200 mb-2 {{ request()->routeIs('admin.packages.*') ? 'bg-gray-800 border-l-4 border-red-500 font-bold' : 'hover:bg-gray-700' }}">
                📦 Paket Ujian
            </a>

            <a href="{{ route('admin.users.index') }}"
                class="block py-2.5 px-4 rounded transition duration-200 mb-2 {{ request()->routeIs('admin.users.*') ? 'bg-gray-800 border-l-4 border-red-500 font-bold' : 'hover:bg-gray-700' }}">
                👥 Manajemen User
            </a>

            <a href="{{ route('admin.transactions') }}"
                class="block py-2.5 px-4 rounded transition duration-200 mb-2 {{ request()->routeIs('admin.transactions') ? 'bg-gray-800 border-l-4 border-red-500 font-bold' : 'hover:bg-gray-700' }}">
                💳 Transaksi Premium
            </a>

        </div>
        <div class="p-4 border-t border-gray-800">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit"
                    class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded transition-colors">Logout</button>
            </form>
        </div>
    </div>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">

        <header class="bg-white shadow-sm p-4 flex justify-between items-center border-b border-gray-200 z-10">
            <h2 class="text-xl font-bold text-gray-800">Control Panel</h2>

            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open"
                    class="flex items-center gap-3 hover:bg-gray-50 p-1 rounded-lg transition">
                    <span class="text-gray-600 font-bold hidden md:block">{{ auth('admin')->user()->name }}</span>

                    @if (auth('admin')->user()->profile_picture)
                        <img src="{{ asset('storage/' . auth('admin')->user()->profile_picture) }}"
                            class="w-10 h-10 rounded-full object-cover border-2 border-red-100">
                    @else
                        <div
                            class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-700 font-bold text-lg uppercase border-2 border-red-200">
                            {{ substr(auth('admin')->user()->name, 0, 1) }}
                        </div>
                    @endif
                </button>

                <div x-show="open" @click.away="open = false"
                    class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-gray-100 py-2 z-50">
                    <div class="px-4 py-2 text-xs text-gray-400 font-black uppercase tracking-widest">Opsi Admin</div>
                    <a href="{{ route('admin.profile') }}"
                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-700 font-medium">
                        ⚙️ Pengaturan Profil
                    </a>
                    <hr class="my-1">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 font-medium">
                            🚪 Keluar
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="p-8 overflow-y-auto">
            @yield('content')
        </main>
        @livewireScripts
    </div>

</body>

</html>
