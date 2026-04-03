@extends('admin.layouts.sidebar') {{-- 1. Panggil bingkai utamanya --}}

@section('content')
    {{-- 2. Masukkan isinya ke dalam kotak 'content' --}}
    <div class="w-full">
        <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-indigo-600 mb-6 flex justify-between items-center">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <a href="{{ route('admin.packages.index') }}"
                        class="text-gray-400 hover:text-indigo-600 font-bold transition-colors">⬅️ Kembali</a>
                    <span class="text-gray-300">|</span>
                    <span class="bg-indigo-100 text-indigo-800 text-xs font-bold px-2 py-1 rounded uppercase">
                        {{ $package->examCategory->name }}
                    </span>
                </div>
                <h1 class="text-2xl font-extrabold text-gray-900">{{ $package->title }}</h1>
                <p class="text-sm text-gray-500 mt-1">Durasi: ⏱️ {{ $package->time_limit }} Menit</p>
            </div>

            <a href="{{ route('admin.questions.create', ['package_id' => $package->id]) }}"
                class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-lg shadow-lg transition-colors flex items-center gap-2">
                <span>➕</span> Tambah Soal Baru
            </a>
        </div>

        <livewire:admin.question-index :package="$package" />
    </div>
@endsection
