@extends('user.layouts.app')

@section('title', 'Hubungi Kami - UKAICHAMPION')

@section('content')
    <div class="max-w-5xl mx-auto py-10 px-4">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-black text-gray-900 mb-4">Hubungi Kami 👋</h1>
            <p class="text-gray-600 text-lg">Berikut adalah informasi kontak resmi kami untuk bantuan dan pertanyaan.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

            <a href="https://wa.me/{{ env('CBT_ADMIN_WA') }}" target="_blank"
                class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 hover:border-[#25D366] hover:shadow-lg transition-all text-center block group cursor-pointer">
                <div class="w-12 h-12 mx-auto mb-4 group-hover:scale-110 transition-transform">
                    <svg viewBox="0 0 24 24" fill="#25D366" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z" />
                    </svg>
                </div>
                <h3 class="font-bold text-gray-800 text-lg">WhatsApp</h3>
                <p class="text-[#25D366] font-bold mt-2 text-sm">{{ Str::replaceFirst('62', '0', env('CBT_ADMIN_WA')) }}</p>
                <p class="text-[10px] text-gray-400 mt-1 uppercase tracking-widest font-bold">Klik untuk Chat</p>
            </a>

            <a href="https://instagram.com/{{ env('CBT_ADMIN_IG') }}" target="_blank"
                class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 hover:border-pink-500 hover:shadow-lg transition-all text-center block group cursor-pointer">
                <div class="w-12 h-12 mx-auto mb-4 group-hover:scale-110 transition-transform">
                    <svg viewBox="0 0 24 24" fill="url(#ig-grad)" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <linearGradient id="ig-grad" x1="2" y1="2" x2="22" y2="22">
                                <stop offset="0%" stop-color="#f09433" />
                                <stop offset="25%" stop-color="#e6683c" />
                                <stop offset="50%" stop-color="#dc2743" />
                                <stop offset="75%" stop-color="#cc2366" />
                                <stop offset="100%" stop-color="#bc1888" />
                            </linearGradient>
                        </defs>
                        <path
                            d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z" />
                    </svg>
                </div>
                <h3 class="font-bold text-gray-800 text-lg">Instagram</h3>
                <p class="text-pink-600 font-bold mt-2 text-sm">{{ env('CBT_ADMIN_IG') }}</p>
                <p class="text-[10px] text-gray-400 mt-1 uppercase tracking-widest font-bold"></p>
            </a>

            {{-- PERBAIKAN TIKTOK: Menghapus '@' agar tidak bentrok dengan isi .env --}}
            <a href="https://www.tiktok.com/{{ Str::startsWith(env('CBT_ADMIN_TIKTOK'), '@') ? '' : '@' }}{{ env('CBT_ADMIN_TIKTOK') }}"
                target="_blank"
                class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 hover:border-black hover:shadow-lg transition-all text-center block group cursor-pointer">
                <div
                    class="w-12 h-12 mx-auto mb-4 group-hover:scale-110 transition-transform flex items-center justify-center">
                    <svg viewBox="-2 -2 28 28" fill="#000000" xmlns="http://www.w3.org/2000/svg" class="w-10 h-10">
                        <path
                            d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 2.78-1.15 5.54-3.33 7.39-2.19 1.88-5.32 2.45-8.1 1.83-2.81-.6-5.18-2.6-6.17-5.32-.98-2.66-.56-5.83 1.25-8.08 1.77-2.22 4.67-3.33 7.46-3.05V13.7c-1.89-.16-3.86.32-5.14 1.68-1.29 1.34-1.57 3.39-1.04 5.12.53 1.76 2.12 3.1 3.96 3.35 1.8.24 3.75-.24 4.97-1.47 1.24-1.23 1.61-3.09 1.59-4.83.05-5.84-.02-11.68.03-17.53z" />
                    </svg>
                </div>
                <h3 class="font-bold text-gray-800 text-lg">TikTok</h3>
                <p class="text-gray-900 font-bold mt-2 text-sm">{{ env('CBT_ADMIN_TIKTOK') }}</p>
                <p class="text-[10px] text-gray-400 mt-1 uppercase tracking-widest font-bold">Kunjungi Profil</p>
            </a>

            {{-- EMAIL TETAP SAMA (mailto: memang bertugas membuka aplikasi Email bawaan) --}}
            {{-- EMAIL: Diganti agar langsung membuka web Gmail (Compose) --}}
            <a href="https://mail.google.com/mail/?view=cm&fs=1&to={{ env('CBT_ADMIN_EMAIL') }}" target="_blank"
                class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 hover:border-blue-500 hover:shadow-lg transition-all text-center block group cursor-pointer">
                <div class="w-12 h-12 mx-auto mb-4 group-hover:scale-110 transition-transform">
                    <svg viewBox="0 0 24 24" fill="#3B82F6" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M2 5.5v13A1.5 1.5 0 0 0 3.5 20h17a1.5 1.5 0 0 0 1.5-1.5v-13A1.5 1.5 0 0 0 20.5 4h-17A1.5 1.5 0 0 0 2 5.5zm18.5-.5a.5.5 0 0 1 .5.5v1.365l-9 5.25-9-5.25V5.5a.5.5 0 0 1 .5-.5h17zM3 8.358l8.5 4.958a1 1 0 0 0 1 0L21 8.358V18.5a.5.5 0 0 1-.5.5h-17a.5.5 0 0 1-.5-.5V8.358z" />
                    </svg>
                </div>
                <h3 class="font-bold text-gray-800 text-lg">Email</h3>
                <p class="text-gray-700 font-bold mt-2 text-[11px] break-all">{{ env('CBT_ADMIN_EMAIL') }}</p>
                <p class="text-[10px] text-gray-400 mt-1 uppercase tracking-widest font-bold">Buka via Gmail Web</p>
            </a>

        </div>

        <div
            class="mt-12 bg-blue-600 rounded-3xl p-8 text-white flex flex-col md:flex-row items-center justify-between shadow-2xl shadow-blue-200 border border-blue-400">
            <div class="mb-6 md:mb-0 text-center md:text-left">
                <h3 class="text-2xl font-bold mb-2">Ingin Akses Semua Soal? 👑</h3>
                <p class="text-blue-100">Silakan hubungi Admin melalui nomor WhatsApp di atas untuk aktivasi Akun Premium.
                </p>
            </div>
            <div class="bg-white/10 px-6 py-4 rounded-2xl border border-white/20 backdrop-blur-sm text-center">
                <p class="text-xs uppercase tracking-widest font-bold text-blue-200 mb-1">Nomor Admin</p>
                <p class="text-xl font-black text-white">{{ Str::replaceFirst('62', '0', env('CBT_ADMIN_WA')) }}</p>
            </div>
        </div>
    </div>
@endsection
