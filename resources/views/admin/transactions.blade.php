@extends('admin.layouts.sidebar') {{-- Sesuaikan dengan nama file layout admin kamu --}}

@section('content')
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Manajemen Transaksi</h1>
        <p class="text-gray-500 mt-1">Kelola dan setujui pembayaran Upgrade Premium peserta di sini.</p>
    </div>

    <livewire:admin.transaction-index />
@endsection
