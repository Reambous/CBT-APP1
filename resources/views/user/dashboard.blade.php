@extends('user.layouts.app')

@section('title', 'Dashboard Peserta - CBT APP')

@section('content')
    @if (session('error'))
        <div
            class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg shadow-sm font-bold flex items-center gap-2">
            <span>⚠️</span> {{ session('error') }}
        </div>
    @endif

    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 mb-6 border-l-4 border-l-blue-500">
        <h2 class="text-2xl font-bold text-gray-800">Selamat datang kembali, {{ auth()->user()->name }}!</h2>
        <p class="text-gray-500 mt-1">Mari persiapkan ujianmu dengan maksimal hari ini.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <div
            class="bg-white p-6 rounded-xl shadow-sm border border-t-4 {{ auth()->user()->is_premium ? 'border-t-green-500' : 'border-t-yellow-500' }}">
            <h3 class="text-gray-500 text-sm font-bold uppercase tracking-wider mb-3">Status Akses</h3>
            <span
                class="px-3 py-1 text-sm font-bold rounded-full {{ auth()->user()->is_premium ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                {{ auth()->user()->is_premium ? '👑 Akun Premium' : '🔒 Akun Gratis' }}
            </span>
            <div class="mt-4 pt-4 border-t border-gray-50">
                @if (auth()->user()->is_premium)
                    <p class="text-sm text-gray-600">Berlaku sampai: <br><span
                            class="font-bold text-gray-800">{{ auth()->user()->premium_until ? \Carbon\Carbon::parse(auth()->user()->premium_until)->format('d M Y') : '-' }}</span>
                    </p>
                @else
                    <p class="text-sm text-gray-600 mb-3">Akses ujian terbatas. Buka semua fitur sekarang.</p>
                    <button
                        class="w-full bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 rounded shadow-sm transition">Upgrade
                        Akses</button>
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
                        <span class="text-gray-400 text-sm font-medium flex items-center gap-1">⏱️
                            {{ $package->time_limit }} Menit</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2 line-clamp-2">{{ $package->title }}</h3>
                    <div class="flex items-center gap-2 text-sm text-gray-500 mt-4">
                        <span>📝 {{ $package->questions_count }} Butir Soal</span>
                    </div>
                </div>
                <div class="p-4 border-t border-gray-100 bg-gray-50">
                    @if ($package->questions_count > 0)
                        <form action="{{ route('exam.start', $package->id) }}" method="POST" class="m-0 p-0">
                            @csrf
                            <button type="submit"
                                class="w-full text-center bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors shadow-sm">
                                🚀 Mulai Kerjakan
                            </button>
                        </form>
                    @else
                        <button disabled
                            class="block w-full text-center bg-gray-300 text-gray-500 font-bold py-2 px-4 rounded-lg cursor-not-allowed">
                            Soal Belum Tersedia
                        </button>
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
