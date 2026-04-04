@extends('admin.layouts.sidebar') {{-- PENTING: Sesuaikan dengan nama file layout admin kamu! --}}

@section('content')
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Daftar Pengguna (Peserta)</h1>
            <p class="text-gray-500 mt-1">Kelola data peserta, status premium, dan larangan akses (Banned).</p>
        </div>

        <a href="{{ route('admin.users.create') }}"
            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-sm transition-colors flex items-center gap-2">
            <span>➕</span> Tambah User Baru
        </a>
    </div>

    <livewire:admin.user-index />
@endsection
