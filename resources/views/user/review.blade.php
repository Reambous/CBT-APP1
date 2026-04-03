@extends('user.layouts.app') {{-- Ganti jika folder layoutmu berbeda, di kodemu tertulis 'user.layouts.app' --}}

@section('title', 'Pembahasan Ujian - CBT APP')

@section('content')
    <div class="max-w-5xl mx-auto">

        <div class="flex items-center justify-between mb-6">
            <div>
                <a href="{{ route('user.history') }}"
                    class="text-gray-500 hover:text-blue-600 font-bold transition-colors text-sm flex items-center gap-1 mb-2">⬅️
                    Kembali ke Riwayat</a>
                <h1 class="text-3xl font-extrabold text-gray-900">Review & Pembahasan</h1>
            </div>
            <div class="bg-white px-6 py-3 rounded-xl shadow-sm border border-gray-200 text-center">
                <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Skor Kamu</span>
                <span
                    class="text-3xl font-black {{ $result->score >= 70 ? 'text-green-600' : 'text-red-600' }}">{{ number_format($result->score, 1) }}</span>
            </div>
        </div>

        <div
            class="bg-white p-6 rounded-xl shadow-sm border border-blue-200 mb-8 border-l-4 border-l-blue-600 flex justify-between items-center">
            <div>
                <span
                    class="bg-blue-100 text-blue-800 text-xs font-bold px-3 py-1 rounded-full uppercase">{{ $result->examPackage->examCategory->name ?? 'Kategori' }}</span>
                <h2 class="text-xl font-bold text-gray-800 mt-2">{{ $result->examPackage->title }}</h2>
            </div>
            <div class="text-right text-sm text-gray-500">
                Dikerjakan pada:<br>
                <span
                    class="font-bold text-gray-800">{{ \Carbon\Carbon::parse($result->finished_at)->translatedFormat('l, d F Y H:i') }}
                    WIB</span>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 mb-8">
            <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                🧩 Peta Jawaban Kamu
                <span class="normal-case font-medium text-gray-400 text-xs">(Klik nomor untuk melihat pembahasan)</span>
            </h3>
            <div class="flex flex-wrap gap-2">
                {{-- KITA LOOPING BERDASARKAN SOAL, BUKAN JAWABAN --}}
                @foreach ($result->examPackage->questions as $index => $q)
                    @php
                        // Cari apakah user menjawab soal ini
                        $ans = $result->userAnswers->where('question_id', $q->id)->first();
                        $isAnswered = $ans && $ans->selected_option;
                        $isCorrect = $ans ? $ans->is_correct : false;

                        // Tentukan warna tombol peta
                        if (!$isAnswered) {
                            $btnClass = 'bg-yellow-400 border-yellow-500 text-yellow-900';
                            $btnTitle = 'Kosong / Tidak Dijawab';
                        } elseif ($isCorrect) {
                            $btnClass = 'bg-green-500 border-green-600 text-white';
                            $btnTitle = 'Benar';
                        } else {
                            $btnClass = 'bg-red-500 border-red-600 text-white';
                            $btnTitle = 'Salah';
                        }
                    @endphp

                    <button type="button" onclick="scrollToSoal('pembahasan-{{ $q->id }}')"
                        class="w-10 h-10 flex items-center justify-center rounded-lg border text-sm font-bold transition-all shadow-sm hover:opacity-80 {{ $btnClass }}"
                        title="Soal No. {{ $index + 1 }} ({{ $btnTitle }})">
                        {{ $index + 1 }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="space-y-6">
            {{-- KITA LOOPING BERDASARKAN SOAL JUGA DI SINI --}}
            @foreach ($result->examPackage->questions as $index => $q)
                @php
                    $ans = $result->userAnswers->where('question_id', $q->id)->first();
                    $userOpt = $ans ? $ans->selected_option : null;
                    $correctOpt = $q->correct_answer;
                    $isCorrect = $ans ? $ans->is_correct : false;
                    $isAnswered = $ans && $ans->selected_option;

                    // Tentukan warna border & header box
                    if (!$isAnswered) {
                        $boxBorder = 'border-yellow-300';
                        $headerBg = 'bg-yellow-50';
                    } elseif ($isCorrect) {
                        $boxBorder = 'border-green-300';
                        $headerBg = 'bg-green-50';
                    } else {
                        $boxBorder = 'border-red-300';
                        $headerBg = 'bg-red-50';
                    }
                @endphp

                <div id="pembahasan-{{ $q->id }}"
                    class="bg-white rounded-xl shadow-sm border scroll-mt-24 transition-all duration-300 {{ $boxBorder }} overflow-hidden">

                    <div class="px-6 py-3 border-b flex justify-between items-center {{ $headerBg }}">
                        <h3 class="font-bold text-gray-800">Soal No. {{ $index + 1 }}</h3>

                        @if (!$isAnswered)
                            <span class="bg-yellow-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm">⚠️
                                Tidak Dijawab</span>
                        @elseif ($isCorrect)
                            <span class="bg-green-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm">✅
                                Benar</span>
                        @else
                            <span class="bg-red-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm">❌
                                Salah</span>
                        @endif
                    </div>

                    <div class="p-6 border-b border-gray-100">
                        <div class="prose max-w-none text-gray-800 mb-6">{!! $q?->question_text ?? 'Soal ini telah dihapus oleh Admin.' !!}</div>

                        <div class="space-y-3">
                            @foreach (['A', 'B', 'C', 'D', 'E'] as $opt)
                                @php
                                    $optField = 'option_' . strtolower($opt);
                                    $optText = $q->$optField;
                                @endphp

                                @if ($optText)
                                    @php
                                        $bgClass = 'bg-white border-gray-200';
                                        $textClass = 'text-gray-700';
                                        $icon = '';

                                        if ($opt === $correctOpt) {
                                            $bgClass = 'bg-green-50 border-green-500 ring-1 ring-green-500';
                                            $textClass = 'text-green-800 font-bold';
                                            $icon = '✅ Kunci Jawaban';
                                        } elseif ($opt === $userOpt && $opt !== $correctOpt) {
                                            $bgClass = 'bg-red-50 border-red-500 ring-1 ring-red-500';
                                            $textClass = 'text-red-800 font-bold';
                                            $icon = '❌ Jawabanmu';
                                        } elseif ($opt === $userOpt && $opt === $correctOpt) {
                                            $icon = '✅ Jawabanmu Benar';
                                        }
                                    @endphp

                                    <div class="flex items-start gap-4 p-4 border rounded-xl {{ $bgClass }}">
                                        <div class="font-bold text-lg {{ $textClass }} pt-0.5">{{ $opt }}.
                                        </div>
                                        <div class="flex-grow {{ $textClass }}">{!! $optText !!}</div>
                                        @if ($icon)
                                            <div
                                                class="flex-shrink-0 text-sm font-bold {{ $opt === $correctOpt ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $icon }}
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        @if (!$isAnswered)
                            <div
                                class="mt-4 p-3 bg-yellow-50 border border-yellow-200 text-yellow-800 text-sm font-bold rounded-lg text-center">
                                ⚠️ Kamu tidak menjawab soal ini (Dikosongkan). Jawaban yang benar adalah
                                <b>{{ $correctOpt }}</b>.
                            </div>
                        @endif
                    </div>

                    @if ($q->explanation)
                        <div class="p-6 bg-blue-50">
                            <p
                                class="text-xs font-bold text-blue-800 uppercase tracking-wider mb-3 flex items-center gap-2">
                                <span>💡</span> Penjelasan & Pembahasan
                            </p>
                            <div class="prose max-w-none text-sm text-gray-800">{!! $q->explanation !!}</div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <script>
        function scrollToSoal(elementId) {
            const element = document.getElementById(elementId);
            if (element) {
                element.classList.add('ring-4', 'ring-blue-300', 'shadow-lg', 'scale-[1.01]');
                element.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
                setTimeout(() => {
                    element.classList.remove('ring-4', 'ring-blue-300', 'shadow-lg', 'scale-[1.01]');
                }, 2000);
            }
        }
    </script>
@endsection
