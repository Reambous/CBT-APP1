@extends('user.layouts.app')

@section('title', 'Dashboard Peserta - UKAICHAMPION APP')

@section('content')
    @if (session('error'))
        <div
            class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg shadow-sm font-bold flex items-center gap-2">
            <span>⚠️</span> {{ session('error') }}
        </div>
    @endif
    @if (session('success'))
        <div
            class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg shadow-sm font-bold flex items-center gap-2">
            <span>✅</span> {{ session('success') }}
        </div>
    @endif

    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 mb-6 border-l-4 border-l-blue-500">
        <h2 class="text-2xl font-bold text-gray-800">Selamat datang kembali, {{ auth()->user()->name }}!</h2>
        <p class="text-gray-500 mt-1">Semangat meraih gelar apoteker.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <div
            class="bg-white p-6 rounded-xl shadow-sm border border-t-4 {{ $isPremiumActive ? 'border-t-green-500' : 'border-t-gray-400' }}">
            <h3 class="text-gray-500 text-sm font-bold uppercase tracking-wider mb-3">Status Akses</h3>

            <span
                class="px-3 py-1 text-sm font-bold rounded-full {{ $isPremiumActive ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                @if ($isPremiumActive && auth()->user()->premium_tier == 'ultra')
                    🔮 Ultra Member
                @elseif($isPremiumActive && auth()->user()->premium_tier == 'pro')
                    👑 Pro Member
                @elseif($isPremiumActive && auth()->user()->premium_tier == 'plus')
                    ✨ Plus Member
                @else
                    🆓 Akun Reguler
                @endif
            </span>

            <div class="mt-4 pt-4 border-t border-gray-50">
                @if ($isPremiumActive)
                    <p class="text-sm text-gray-600">Berlaku sampai: <br>
                        <span
                            class="font-bold text-gray-800">{{ \Carbon\Carbon::parse(auth()->user()->premium_until)->format('d M Y') }}</span>
                    </p>
                @else
                    <p class="text-sm text-gray-600 mb-3">Akses ujian terbatas. Buka semua fitur sekarang.</p>
                    <a href="{{ route('user.upgrade') }}">
                        <button
                            class="w-full bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 rounded shadow-sm transition">Upgrade
                            Akses</button>
                    </a>
                @endif
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-t-4 border-t-indigo-500">
            <h3 class="text-gray-500 text-sm font-bold uppercase tracking-wider mb-2">Ujian Diselesaikan</h3>
            <p class="text-4xl font-extrabold text-gray-800 mt-2">{{ $completedExamsCount ?? 0 }} <span
                    class="text-lg font-medium text-gray-500">kali</span></p>
        </div>
    </div>

    <div class="flex justify-between items-end mb-4 border-b pb-2">
        <h2 class="text-xl font-bold text-gray-800">✨ 3 Paket Ujian Terbaru</h2>
        <a href="{{ route('user.exams') }}"
            class="text-blue-600 hover:text-blue-800 font-bold text-sm flex items-center gap-1 transition-colors">
            Lihat Semua Katalog ➡️
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        @forelse($packages as $package)
            <div
                class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow duration-300 flex flex-col">
                <div class="p-6 grow">
                    <div class="flex justify-between items-start mb-4">
                        <span
                            class="bg-blue-50 text-blue-600 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">
                            {{ $package->examCategory?->name ?? 'Tanpa Kategori' }}
                        </span>

                        @if ($package->minimum_tier == 'ultra')
                            <span
                                class="bg-purple-100 text-purple-800 text-xs font-bold px-3 py-1 rounded-full border border-purple-200 shadow-sm ml-2 whitespace-nowrap">🔮
                                Ultra</span>
                        @elseif ($package->minimum_tier == 'pro')
                            <span
                                class="bg-yellow-100 text-yellow-800 text-xs font-bold px-3 py-1 rounded-full border border-yellow-200 shadow-sm ml-2 whitespace-nowrap">👑
                                Pro</span>
                        @elseif ($package->minimum_tier == 'plus')
                            <span
                                class="bg-blue-100 text-blue-800 text-xs font-bold px-3 py-1 rounded-full border border-blue-200 shadow-sm ml-2 whitespace-nowrap">✨
                                Plus</span>
                        @else
                            <span
                                class="bg-gray-100 text-gray-600 text-xs font-bold px-3 py-1 rounded-full border border-gray-200 shadow-sm ml-2 whitespace-nowrap">🆓
                                Gratis</span>
                        @endif

                    </div>

                    <h3 class="text-xl font-bold text-gray-900 mb-2 line-clamp-2">{{ $package->title }}</h3>
                    <div class="flex items-center gap-2 text-sm text-gray-500 mt-4">
                        <span class="flex items-center gap-1">⏱️ {{ $package->time_limit }} Menit</span>
                        <span class="text-gray-300">|</span>
                        <span>📝 {{ $package->questions_count }} Soal</span>
                    </div>
                </div>

                <div class="p-4 border-t border-gray-100 bg-gray-50">
                    {{-- 
                        PENGECEKAN STATUS PENGERJAAN & AKSES 
                        Kita cek nilai user khusus untuk paket yang sedang di-looping ini 
                    --}}
                    @php
                        $isLocked = !auth()->user()->canAccessTier($package->minimum_tier);
                        $hasFinished = \App\Models\UserResult::where('user_id', auth()->id())
                            ->where('exam_package_id', $package->id)
                            ->whereNotNull('finished_at')
                            ->exists();
                    @endphp

                    @if ($package->questions_count == 0)
                        <button disabled
                            class="block w-full text-center bg-gray-300 text-gray-500 font-bold py-2 px-4 rounded-lg cursor-not-allowed">
                            Soal Belum Tersedia
                        </button>
                    @elseif ($hasFinished)
                        <button type="button" disabled
                            class="block w-full text-center bg-gray-200 text-gray-500 border border-transparent font-bold py-2 px-4 rounded-lg cursor-not-allowed shadow-sm">
                            ✅ Selesai
                        </button>
                    @elseif ($isLocked)
                        <a href="{{ route('user.upgrade') }}"
                            class="block w-full text-center bg-gray-100 text-gray-500 hover:bg-yellow-50 hover:text-yellow-700 hover:border-yellow-300 border border-transparent font-bold py-2 px-4 rounded-lg transition-colors shadow-sm">
                            🔒 Terkunci (Upgrade)
                        </a>
                    @else
                        <form action="{{ route('exam.start', $package->id) }}" method="POST" class="m-0 p-0">
                            @csrf
                            <button type="submit"
                                class="w-full text-center bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors shadow-sm">
                                🚀 Mulai Kerjakan
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white p-8 rounded-xl border text-center shadow-sm">
                <span class="text-4xl mb-3 block">📭</span>
                <h3 class="text-lg font-bold text-gray-800">Belum ada paket ujian</h3>
            </div>
        @endforelse
    </div>
@endsection
