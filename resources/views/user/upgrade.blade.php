@extends('user.layouts.app') {{-- Sesuaikan jika nama file layout usermu berbeda --}}

@section('content')
    <div class="flex flex-col min-h-[calc(100vh-8rem)]">

        <div class="flex-grow">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-800">Status Langganan</h1>
                <p class="text-gray-500 mt-1">Kelola paket langganan Premium Anda di sini.</p>
            </div>

            <livewire:user.upgrade />
        </div>

        <div
            class="w-full bg-white border-t border-gray-200 p-4 md:px-8 mt-auto flex flex-col md:flex-row items-center justify-between gap-3 -mx-4 md:-mx-8 w-[calc(100%+2rem)] md:w-[calc(100%+4rem)]">

            <div class="text-center md:text-left">
                <h3 class="text-sm font-bold text-gray-800">Butuh Bantuan?</h3>
                <p class="text-[11px] text-gray-500">Hubungi kami jika mengalami kendala.</p>
            </div>

            <div class="flex flex-wrap justify-center md:justify-end gap-4 text-xs font-semibold text-gray-700">
                <a href="https://wa.me/{{ env('CBT_ADMIN_WA', '628000000') }}" target="_blank"
                    class="flex items-center gap-1.5 hover:text-green-600 transition">
                    <span class="text-base">💬</span> +{{ env('CBT_ADMIN_WA', '628000000') }}
                </a>

                <a href="mailto:{{ env('CBT_ADMIN_EMAIL', 'admin@cbt.com') }}"
                    class="flex items-center gap-1.5 hover:text-blue-600 transition">
                    <span class="text-base">✉️</span> {{ env('CBT_ADMIN_EMAIL', 'admin@cbt.com') }}
                </a>

                <div class="flex items-center gap-1.5">
                    <span class="text-base">🌐</span> {{ env('CBT_ADMIN_SOSMED', '@cbt_official') }}
                </div>
            </div>

        </div>
    </div>
@endsection
