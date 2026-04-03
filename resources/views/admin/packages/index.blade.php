@extends('admin.layouts.sidebar')

@section('content')
    <div class="flex-1 flex flex-col">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Manajemen Paket Ujian</h1>
                <p class="text-sm text-gray-500 mt-1">Kelola daftar paket soal dan durasi ujian siswa.</p>
            </div>

            <a href="{{ route('admin.packages.create') }}"
                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-5 rounded-lg shadow-md transition-all flex items-center gap-2 transform active:scale-95">
                <span>+</span> Tambah Paket Baru
            </a>
        </div>

        <livewire:admin.package-index />
    </div>
@endsection
