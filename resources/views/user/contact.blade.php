@extends('user.layouts.app')

@section('title', 'Hubungi Kami - UKAICHAMPION')

@section('content')
    <div class="max-w-5xl mx-auto py-10 px-4">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-black text-gray-900 mb-4">Hubungi Kami 👋</h1>
            <p class="text-gray-600 text-lg">Berikut adalah informasi kontak resmi kami untuk bantuan dan pertanyaan.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

            <a href="https://wa.me/{{ $contactData['whatsapp'] }}" target="_blank"
                class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 hover:border-green-500 hover:shadow-lg transition-all text-center block group cursor-pointer">
                <div class="text-4xl mb-4 group-hover:scale-110 transition-transform">🟢</div>
                <h3 class="font-bold text-gray-800 text-lg">WhatsApp</h3>
                <p class="text-blue-600 font-bold mt-2 text-sm">+{{ $contactData['whatsapp'] }}</p>
                <p class="text-[10px] text-gray-400 mt-1 uppercase tracking-widest font-bold">Klik untuk Chat</p>
            </a>

            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 text-center">
                <div class="text-4xl mb-4">📸</div>
                <h3 class="font-bold text-gray-800 text-lg">Instagram</h3>
                <p class="text-pink-600 font-bold mt-2 text-sm">@ {{ $contactData['instagram'] }}</p>
                <p class="text-[10px] text-gray-400 mt-1 uppercase tracking-widest font-bold">Update Info</p>
            </div>

            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 text-center">
                <div class="text-4xl mb-4">✈️</div>
                <h3 class="font-bold text-gray-800 text-lg">Telegram</h3>
                <p class="text-blue-400 font-bold mt-2 text-sm">@ {{ $contactData['telegram'] }}</p>
                <p class="text-[10px] text-gray-400 mt-1 uppercase tracking-widest font-bold">Komunitas</p>
            </div>

            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 text-center">
                <div class="text-4xl mb-4">📩</div>
                <h3 class="font-bold text-gray-800 text-lg">Email</h3>
                <p class="text-gray-700 font-bold mt-2 text-[11px] break-all">{{ $contactData['email'] }}</p>
                <p class="text-[10px] text-gray-400 mt-1 uppercase tracking-widest font-bold">Resmi</p>
            </div>

        </div>

        <div
            class="mt-12 bg-blue-600 rounded-3xl p-8 text-white flex flex-col md:flex-row items-center justify-between shadow-2xl shadow-blue-200 border border-blue-400">
            <div class="mb-6 md:mb-0 text-center md:text-left">
                <h3 class="text-2xl font-bold mb-2">Ingin Akses Semua Soal? 👑</h3>
                <p class="text-blue-100">Silakan hubungi Admin melalui nomor WhatsApp di atas untuk aktivasi Akun Premium.
                </p>
            </div>
            <div class="bg-white/10 px-6 py-4 rounded-2xl border border-white/20 backdrop-blur-sm">
                <p class="text-xs uppercase tracking-widest font-bold text-blue-200 mb-1 text-center">Nomor Admin</p>
                <p class="text-xl font-black text-white">+{{ $contactData['whatsapp'] }}</p>
            </div>
        </div>
    </div>
@endsection
