@extends('user.layouts.app')

@section('content')
    <div class="flex flex-col min-h-[calc(100vh-8rem)]">

        <div class="flex-grow">
            <div class="mb-8 text-center md:text-left">
                <h1 class="text-3xl font-black text-gray-900">Upgrade Langganan</h1>
                <p class="text-gray-500 mt-2">Pilih paket terbaik untuk membuka ribuan soal dan fitur eksklusif lainnya.</p>
                <div
                    class="mt-4 inline-block bg-blue-50 border border-blue-100 text-blue-800 text-sm font-medium px-4 py-2 rounded-lg">
                    🏦 BCA: 1234567890 a.n. Admin CBT | Mandiri: 0987654321
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">

                <div
                    class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden flex flex-col hover:shadow-xl transition-shadow duration-300">
                    <div class="p-6 border-b border-gray-100 bg-gradient-to-br from-blue-50 to-white">
                        <span
                            class="bg-blue-100 text-blue-800 text-xs font-extrabold px-3 py-1 rounded-full uppercase tracking-wider">✨
                            Plus</span>
                        <h3 class="text-xl font-bold text-gray-900 mt-4 mb-1">Paket Dasar</h3>
                        <div class="flex items-baseline gap-1">
                            <span class="text-3xl font-black text-gray-900">Rp 50.000</span>
                            <span class="text-gray-500 font-medium text-sm">/tahun</span>
                        </div>
                    </div>
                    <div class="p-6 grow">
                        <ul class="space-y-3 text-gray-600 text-sm font-medium">
                            <li class="flex items-start gap-2"><span class="text-green-500">✓</span> Akses soal Gratis &
                                Plus</li>
                            <li class="flex items-start gap-2"><span class="text-green-500">✓</span> Pembahasan teks lengkap
                            </li>
                            <li class="flex items-start gap-2 text-gray-400"><span class="text-gray-300">✗</span> <del>Soal
                                    prediksi (Pro/Ultra)</del></li>
                        </ul>
                    </div>
                    <div class="p-6 pt-0 mt-auto">
                        <form action="{{ route('user.checkout') }}" method="POST" class="p-6 pt-0 mt-auto m-0">
                            @csrf
                            <input type="hidden" name="tier" value="plus">
                            <button type="submit"
                                class="block w-full py-3 px-4 text-center rounded-xl font-bold transition-colors bg-blue-50 text-blue-700 hover:bg-blue-600 hover:text-white border border-blue-200 hover:border-transparent">
                                Pilih Paket Plus
                            </button>
                        </form>
                    </div>
                </div>

                <div
                    class="bg-white rounded-2xl shadow-sm border border-yellow-300 overflow-hidden flex flex-col hover:shadow-xl transition-shadow duration-300">
                    <div class="p-6 border-b border-gray-100 bg-gradient-to-br from-yellow-50 to-white">
                        <span
                            class="bg-yellow-100 text-yellow-800 text-xs font-extrabold px-3 py-1 rounded-full uppercase tracking-wider">👑
                            Pro</span>
                        <h3 class="text-xl font-bold text-gray-900 mt-4 mb-1">Paket Intensif</h3>
                        <div class="flex items-baseline gap-1">
                            <span class="text-3xl font-black text-gray-900">Rp 99.000</span>
                            <span class="text-gray-500 font-medium text-sm">/tahun</span>
                        </div>
                    </div>
                    <div class="p-6 grow">
                        <ul class="space-y-3 text-gray-600 text-sm font-medium">
                            <li class="flex items-start gap-2"><span class="text-yellow-500">✓</span> Akses soal Gratis,
                                Plus & Pro</li>
                            <li class="flex items-start gap-2"><span class="text-yellow-500">✓</span> Pembahasan + Cara
                                Cepat</li>
                            <li class="flex items-start gap-2"><span class="text-yellow-500">✓</span> Analisis Grafik Nilai
                            </li>
                            <li class="flex items-start gap-2 text-gray-400"><span class="text-gray-300">✗</span>
                                <del>Simulasi Nasional</del>
                            </li>
                        </ul>
                    </div>
                    <div class="p-6 pt-0 mt-auto">
                        <form action="{{ route('user.checkout') }}" method="POST" class="p-6 pt-0 mt-auto m-0">
                            @csrf
                            <input type="hidden" name="tier" value="pro">
                            <button type="submit"
                                class="block w-full py-3 px-4 text-center rounded-xl font-bold transition-colors bg-yellow-50 text-yellow-800 hover:bg-yellow-400 hover:text-yellow-900 border border-yellow-300 hover:border-transparent">
                                Pilih Paket Pro
                            </button>
                        </form>
                    </div>
                </div>

                <div
                    class="bg-gray-900 rounded-2xl shadow-sm border border-gray-800 overflow-hidden flex flex-col hover:shadow-xl transition-shadow duration-300">
                    <div class="p-6 border-b border-gray-800 bg-gradient-to-br from-purple-900 to-gray-900">
                        <span
                            class="bg-purple-800 text-purple-100 text-xs font-extrabold px-3 py-1 rounded-full uppercase tracking-wider border border-purple-600">🔮
                            Ultra</span>
                        <h3 class="text-xl font-bold text-white mt-4 mb-1">Paket Sultan</h3>
                        <div class="flex items-baseline gap-1">
                            <span class="text-3xl font-black text-white">Rp 199.000</span>
                            <span class="text-gray-400 font-medium text-sm">/tahun</span>
                        </div>
                    </div>
                    <div class="p-6 grow">
                        <ul class="space-y-3 text-gray-300 text-sm font-medium">
                            <li class="flex items-start gap-2"><span class="text-purple-400">✓</span> <b>Akses Tanpa
                                    Batas</b> Semua!</li>
                            <li class="flex items-start gap-2"><span class="text-purple-400">✓</span> Soal Prediksi Akurat
                                99%</li>
                            <li class="flex items-start gap-2"><span class="text-purple-400">✓</span> Simulasi Ujian
                                Nasional</li>
                            <li class="flex items-start gap-2"><span class="text-purple-400">✓</span> Prioritas Server Cepat
                            </li>
                        </ul>
                    </div>
                    <div class="p-6 pt-0 mt-auto">
                        <form action="{{ route('user.checkout') }}" method="POST" class="p-6 pt-0 mt-auto m-0">
                            @csrf
                            <input type="hidden" name="tier" value="ultra">
                            <button type="submit"
                                class="block w-full py-3 px-4 text-center rounded-xl font-bold transition-colors bg-purple-600 text-white hover:bg-purple-500 border border-purple-500 hover:border-transparent">
                                Pilih Paket Ultra
                            </button>
                        </form>
                    </div>
                </div>

            </div>
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
