@extends('user.layouts.app')

@section('title', 'Pembahasan Ujian - UKAICHAMPION APP')

@section('content')
    <style>
        .watermark-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            pointer-events: none;
            z-index: 9999;
            opacity: 0.05;
            background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' version='1.1' height='180px' width='280px'><text transform='translate(10, 140) rotate(-35)' fill='rgb(0,0,0)' font-size='24' font-weight='900' font-family='Arial, sans-serif' opacity='0.6'>UKAICHAMPION</text></svg>");
            background-repeat: repeat;
            user-select: none;
        }

        .protect-text {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }

        /* 1. CSS UNTUK GAYA ZOOM & MODAL */
        /* Beri tanda kursor zoom pada semua gambar di prose dan opsi */
        .prose img,
        .option-image {
            cursor: zoom-in;
            transition: transform 0.2s ease-in-out;
        }

        .prose img:hover,
        .option-image:hover {
            transform: scale(1.015);
            /* Efek angkat sedikit saat dihover */
        }

        /* Membatasi ukuran gambar soal agar tetap ringkas di layout awal */
        .prose img {
            max-height: 550px !important;
            width: auto !important;
            object-fit: contain;
            border-radius: 0.5rem;
            margin-top: 1rem;
            margin-bottom: 1rem;
        }

        /* Gaya Modal Lightbox */
        #imageLightboxModal {
            transition: opacity 0.3s ease-in-out, visibility 0.3s ease-in-out;
        }

        #imageLightboxModal.hidden {
            opacity: 0;
            visibility: hidden;
            display: none;
        }

        #imageLightboxModal.flex {
            opacity: 1;
            visibility: visible;
            display: flex;
        }
    </style>

    <div class="watermark-overlay"></div>

    <<div id="imageLightboxModal"
        class="hidden fixed inset-0 w-full h-full bg-black/90 z-[10000] flex-col items-center justify-center p-4 md:p-8 cursor-zoom-out"
        onclick="closeLightbox()">

        <button class="absolute top-4 right-4 text-white hover:text-red-400 text-5xl font-black transition-colors z-[10001]"
            onclick="closeLightbox()">×</button>

        <img id="lightboxImage" src="" alt="Zoomed Image"
            class="max-w-full max-h-[90vh] object-contain rounded-xl shadow-2xl border-4 border-white/20 cursor-default"
            onclick="event.stopPropagation()">

        <p class="text-gray-400 text-xs mt-4 font-mono">Klik di mana saja atau tombol X untuk menutup</p>
        </div>


        <div class="w-full px-4 md:px-8 relative z-10 mb-12 mt-4">

            <div class="flex items-center justify-between mb-4">
                <div>
                    <a href="{{ route('user.history') }}"
                        class="text-gray-500 hover:text-blue-600 font-bold transition-colors text-xs flex items-center gap-1 mb-1">⬅️
                        Kembali</a>
                    <h1 class="text-xl font-extrabold text-gray-900">Review & Pembahasan</h1>
                </div>
                <div
                    class="bg-white px-4 py-2 rounded-lg shadow-sm border border-gray-200 text-center flex items-center gap-3">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Skor Kamu</span>
                    <span
                        class="text-2xl font-black {{ $result->score >= 70 ? 'text-green-600' : 'text-red-600' }}">{{ number_format($result->score, 1) }}</span>
                </div>
            </div>

            <div
                class="bg-white p-4 rounded-lg shadow-sm border border-blue-200 mb-4 border-l-4 border-l-blue-600 flex justify-between items-center">
                <div>
                    <span
                        class="bg-blue-100 text-blue-800 text-[10px] font-bold px-2 py-0.5 rounded uppercase">{{ $result->examPackage->examCategory->name ?? 'Kategori' }}</span>
                    <h2 class="text-sm font-bold text-gray-800 mt-1">{{ $result->examPackage->title }}</h2>
                </div>
                <div class="text-right text-[11px] text-gray-500">
                    Selesai pada:<br>
                    <span
                        class="font-bold text-gray-800">{{ \Carbon\Carbon::parse($result->finished_at)->translatedFormat('d M Y, H:i') }}</span>
                </div>
            </div>

            <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 mb-6 relative z-20">
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                    🧩 Peta Jawaban
                    <span class="normal-case font-medium text-gray-400 text-[10px]">(Klik nomor melompat ke
                        pembahasan)</span>
                </h3>
                <div class="flex flex-wrap gap-1.5 overflow-visible">
                    @foreach ($result->examPackage->questions as $index => $q)
                        @php
                            $ans = $result->userAnswers->where('question_id', $q->id)->first();
                            $isAnswered = $ans && $ans->selected_option;
                            $isCorrect = $ans ? $ans->is_correct : false;
                            $pageTarget = floor($index / 20) + 1;

                            if (!$isAnswered) {
                                $btnClass = 'bg-yellow-400 border-yellow-500 text-yellow-900';
                            } elseif ($isCorrect) {
                                $btnClass = 'bg-green-500 border-green-600 text-white';
                            } else {
                                $btnClass = 'bg-red-500 border-red-600 text-white';
                            }
                        @endphp

                        <button type="button" onclick="goToSoal('pembahasan-{{ $q->id }}', {{ $pageTarget }})"
                            class="w-7 h-7 flex items-center justify-center rounded border text-xs font-bold transition-all hover:opacity-80 {{ $btnClass }}">
                            {{ $index + 1 }}
                        </button>
                    @endforeach
                </div>
            </div>

            @php
                $chunks = $result->examPackage->questions->chunk(20);
            @endphp

            <div id="question-container">
                @foreach ($chunks as $chunkIndex => $chunk)
                    @php $pageNum = $chunkIndex + 1; @endphp

                    <div id="page-{{ $pageNum }}" class="question-page space-y-4 {{ $pageNum > 1 ? 'hidden' : '' }}">

                        @foreach ($chunk as $index => $q)
                            @php
                                $realIndex = $index;
                                $ans = $result->userAnswers->where('question_id', $q->id)->first();
                                $userOpt = $ans ? $ans->selected_option : null;
                                $correctOpt = $q->correct_answer;
                                $isCorrect = $ans ? $ans->is_correct : false;
                                $isAnswered = $ans && $ans->selected_option;

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
                                class="bg-white rounded-lg shadow-sm border scroll-mt-20 transition-all duration-300 {{ $boxBorder }} overflow-hidden">
                                <div class="px-4 py-2 border-b flex justify-between items-center {{ $headerBg }}">
                                    <h3 class="font-bold text-gray-800 text-sm">Soal No. {{ $realIndex + 1 }}</h3>
                                    @if (!$isAnswered)
                                        <span
                                            class="bg-yellow-500 text-white text-[10px] font-bold px-2 py-0.5 rounded shadow-sm">⚠️
                                            Tidak Dijawab</span>
                                    @elseif ($isCorrect)
                                        <span
                                            class="bg-green-500 text-white text-[10px] font-bold px-2 py-0.5 rounded shadow-sm">✅
                                            Benar</span>
                                    @else
                                        <span
                                            class="bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded shadow-sm">❌
                                            Salah</span>
                                    @endif
                                </div>

                                <div class="p-4 border-b border-gray-100">
                                    <div class="prose max-w-none text-sm text-gray-800 mb-6 protect-text leading-relaxed"
                                        onclick="if(event.target.tagName==='IMG') openLightbox(event.target.src)">
                                        {{-- FIX: Tambahkan ini --}}
                                        {!! $q?->question_text ?? 'Soal dihapus.' !!}
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 relative z-10">
                                        @foreach (['A', 'B', 'C', 'D', 'E'] as $opt)
                                            @php
                                                $optField = 'option_' . strtolower($opt);
                                                $optText = $q->$optField;
                                            @endphp

                                            @if ($optText)
                                                @php
                                                    $bgClass = 'bg-gray-50 border-gray-200';
                                                    $textClass = 'text-gray-700';
                                                    $icon = '';

                                                    if ($opt === $correctOpt) {
                                                        $bgClass = 'bg-green-50 border-green-500 ring-1 ring-green-500';
                                                        $textClass = 'text-green-800 font-bold';
                                                        $icon = '✅';
                                                    } elseif ($opt === $userOpt && $opt !== $correctOpt) {
                                                        $bgClass = 'bg-red-50 border-red-500 ring-1 ring-red-500';
                                                        $textClass = 'text-red-800 font-bold';
                                                        $icon = '❌';
                                                    } elseif ($opt === $userOpt && $opt === $correctOpt) {
                                                        $icon = '✅';
                                                    }
                                                @endphp

                                                <div
                                                    class="flex items-start gap-3 p-3 border rounded-lg {{ $bgClass }}">
                                                    <div class="font-bold text-base {{ $textClass }} pt-0.5">
                                                        {{ $opt }}.</div>
                                                    <div
                                                        class="flex-grow text-sm {{ $textClass }} protect-text overflow-hidden">
                                                        @if ($q->is_answer_image)
                                                            @php
                                                                // FIX: Bersihkan path agar asset() tidak dobel panggil /storage/
                                                                $cleanOptPath = ltrim(
                                                                    str_replace(
                                                                        ['/storage/', 'storage/'],
                                                                        '',
                                                                        $optText,
                                                                    ),
                                                                    '/',
                                                                );
                                                            @endphp
                                                            <img src="{{ asset('storage/' . $cleanOptPath) }}"
                                                                onclick="openLightbox(this.src)" {{-- FIX: Tambahkan fungsi zoom --}}
                                                                class="option-image max-h-48 md:max-h-64 w-auto object-contain rounded-lg border border-gray-200 mt-1 pointer-events-auto shadow-sm cursor-zoom-in">
                                                        @else
                                                            {!! $optText !!}
                                                        @endif
                                                    </div>
                                                    @if ($icon)
                                                        <div class="flex-shrink-0 text-sm">{{ $icon }}</div>
                                                    @endif
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>

                                    @if (!$isAnswered)
                                        <div
                                            class="mt-4 p-2 bg-yellow-50 border border-yellow-200 text-yellow-800 text-xs font-bold rounded text-center">
                                            ⚠️ Kosong. Jawaban benar: <b>{{ $correctOpt }}</b>.
                                        </div>
                                    @endif
                                </div>

                                @if ($q->explanation)
                                    <div class="p-4 bg-blue-50/60">
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-2 gap-2">
                                            <p class="text-xs font-bold text-blue-800 uppercase flex items-center gap-1.5">
                                                <span>💡</span> Pembahasan
                                            </p>
                                            <div
                                                class="px-2 py-1 bg-blue-100/80 border-l border-blue-400 text-[9px] text-blue-700 font-bold rounded flex items-center gap-1 w-max">
                                                <span>🛡️</span> © UKAICHAMPION.
                                            </div>
                                        </div>
                                        <div class="prose max-w-none text-sm text-gray-800 protect-text leading-relaxed"
                                            onclick="if(event.target.tagName==='IMG') openLightbox(event.target.src)">
                                            {{-- FIX: Tambahkan ini --}}
                                            {!! $q->explanation !!}
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>

            @if ($chunks->count() > 1)
                <div
                    class="flex flex-wrap justify-center items-center gap-2 mt-6 p-4 bg-white rounded-lg shadow-sm border border-gray-200 relative z-10">
                    <span class="text-xs font-bold text-gray-500 uppercase mr-2">Halaman:</span>
                    @foreach ($chunks as $chunkIndex => $chunk)
                        @php $pageNum = $chunkIndex + 1; @endphp
                        <button type="button" id="btn-page-{{ $pageNum }}" onclick="changePage({{ $pageNum }})"
                            class="page-btn w-8 h-8 flex items-center justify-center rounded border text-xs font-bold transition-colors 
                        {{ $pageNum === 1 ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 hover:bg-gray-100 border-gray-300' }}">
                            {{ $pageNum }}
                        </button>
                    @endforeach
                </div>
            @endif
            <div id="examLightboxModal"
                class="hidden fixed inset-0 w-full h-full bg-black/80 z-[99999] flex items-center justify-center p-4"
                style="position: fixed !important; top:0; left:0;"
                onclick="this.classList.add('hidden'); document.body.style.overflow='auto'">
                <button class="absolute top-4 right-4 text-white text-5xl font-black">&times;</button>
                <img id="examLightboxImage" src=""
                    class="max-w-full max-h-[85vh] object-contain rounded-xl shadow-2xl border-2 border-white/20"
                    onclick="event.stopPropagation()">
            </div>
        </div>

        <script>
            // --- LOGIKA IMAGE LIGHTBOX ZOOM ---
            const modal = document.getElementById('imageLightboxModal');
            const modalImg = document.getElementById('lightboxImage');

            // Fungsi membuka modal saat gambar diklik
            function openLightbox(src) {
                if (!src) return;
                const modal = document.getElementById('examLightboxModal');
                document.getElementById('examLightboxImage').src = src;
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }

            // Fungsi menutup modal
            function closeLightbox() {
                modal.classList.remove('flex');
                modal.classList.add('hidden');
                modalImg.src = ''; // Hapus src agar bersih saat buka lagi
                // Kembalikan scrolling halaman utama
                document.body.style.overflow = '';
            }

            // Daftarkan event listener klik ke SEMUA gambar yang boleh di-zoom
            function initializeImageZoom() {
                // Targetkan gambar di dalam .prose (soal/pembahasan) dan yang punya class .option-image
                const zoomableImages = document.querySelectorAll('.prose img, .option-image');

                zoomableImages.forEach(img => {
                    // Beri tanda pointer agar user tahu bisa diklik
                    img.classList.add('cursor-zoom-in');

                    img.addEventListener('click', function(e) {
                        e.stopPropagation(); // Cegah propagasi agar tidak bentrok dengan klik lain
                        // Jika gambar ada di dalam opsi, jangan zoom jika diklik di area padding opsi, 
                        // tapi kodenya sudah pointer-events-auto, jadi aman.
                        openLightbox(this.src);
                    });
                });
            }

            // Daftarkan event listener keyboard (ESC untuk tutup modal)
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                    closeLightbox();
                }
            });


            // --- LOGIKA PAGINASI ---
            function changePage(pageNum) {
                document.querySelectorAll('.question-page').forEach(el => el.classList.add('hidden'));
                document.getElementById('page-' + pageNum).classList.remove('hidden');

                document.querySelectorAll('.page-btn').forEach(el => {
                    el.classList.remove('bg-blue-600', 'text-white', 'border-blue-600');
                    el.classList.add('bg-white', 'text-gray-600', 'border-gray-300');
                });
                let activeBtn = document.getElementById('btn-page-' + pageNum);
                if (activeBtn) {
                    activeBtn.classList.remove('bg-white', 'text-gray-600', 'border-gray-300');
                    activeBtn.classList.add('bg-blue-600', 'text-white', 'border-blue-600');
                }
                document.getElementById('question-container').scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }

            function goToSoal(elementId, pageTarget) {
                changePage(pageTarget);
                setTimeout(() => {
                    const element = document.getElementById(elementId);
                    if (element) {
                        element.classList.add('ring-2', 'ring-blue-400', 'shadow-md');
                        element.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                        setTimeout(() => element.classList.remove('ring-2', 'ring-blue-400', 'shadow-md'), 2000);
                    }
                }, 100);
            }

            // --- INITIALIZE SEMUA SAAT HALAMAN MUAT ---
            document.addEventListener('DOMContentLoaded', function() {
                initializeImageZoom();

                // Daftarkan ulang zoom saat pindah halaman paginasi 
                // (Sebenarnya tidak perlu karena paginasi kita cuma hide/show DOM, bukan ganti DOM,
                // tapi initializeImageZoom sudah menargetkan semua DOM di awal, jadi aman).
            });

            document.addEventListener('contextmenu', event => event.preventDefault());
        </script>
    @endsection
