@extends('user.layouts.app') {{-- Sesuaikan jika nama file layout usermu berbeda --}}

@section('content')
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Status Langganan</h1>
        <p class="text-gray-500 mt-1">Kelola paket langganan Premium Anda di sini.</p>
    </div>

    <livewire:user.upgrade />
@endsection
