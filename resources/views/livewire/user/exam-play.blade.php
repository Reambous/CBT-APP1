<?php

use Livewire\Volt\Component;
use App\Models\UserResult;
use App\Models\UserAnswer;
use App\Models\Question;
use Illuminate\Support\Facades\Redis;

new class extends Component {
    public $result_id;
    public $exam_package_id;
    public $questions;
    public $currentQuestionIndex = 0;
    public $answers = [];
    public $endTime;

    public function mount($result_id)
    {
        $this->result_id = $result_id;
        $result = UserResult::with('examPackage')->findOrFail($result_id);

        // 1. Cek apakah ujian sudah disubmit normal sebelumnya
        if ($result->finished_at) {
            return redirect()->route('user.dashboard');
        }

        // ========================================================
        // 2. FITUR BARU: CEK WAKTU KIAMAT SERVER
        // Kita cek ends_at tidak null dulu sebelum membandingkan waktu
        // ========================================================
        if ($result->ends_at && now()->greaterThanOrEqualTo($result->ends_at)) {
            $this->finishExam(); // Tarik Redis ke DB & hitung nilai
            return;
        }

        $this->exam_package_id = $result->exam_package_id;
        $this->questions = Question::where('exam_package_id', $this->exam_package_id)->get();

        $this->currentQuestionIndex = session('last_q_' . $this->result_id, 0);

        // ========================================================
        // 3. SETEL TIMER JAVASCRIPT
        // Jika ends_at ada (data baru), pakai itu. Jika tidak ada (data lama), pakai created_at.
        // ========================================================
        if ($result->ends_at) {
            $this->endTime = \Carbon\Carbon::parse($result->ends_at)->toIso8601String();
        } else {
            // Backup untuk data ujian lama sebelum fitur ends_at aktif
            $durationMinutes = $result->examPackage->time_limit;
            $this->endTime = $result->created_at->addMinutes($durationMinutes)->toIso8601String();
        }

        $existingAnswers = UserAnswer::where('result_id', $result_id)->get();
        foreach ($existingAnswers as $ans) {
            $this->answers[$ans->question_id] = $ans->selected_option;
        }
    }

    public function updateSessionIndex()
    {
        session(['last_q_' . $this->result_id => $this->currentQuestionIndex]);
    }

    public function answerQuestion($questionId, $selectedOption)
    {
        $this->answers[$questionId] = $selectedOption;
        UserAnswer::updateOrCreate(['result_id' => $this->result_id, 'question_id' => $questionId], ['selected_option' => $selectedOption]);
    }

    public function finishExam()
    {
        // Hapus jejak navigasi session
        session()->forget('last_q_' . $this->result_id);

        // Ambil data result saat ini
        $result = UserResult::findOrFail($this->result_id);

        // =========================================================
        // 1. LOGIKA PENGUNCI DURASI (AGAR TIDAK BENGKAK)
        // =========================================================
        $waktuSelesaiSimpan = now();

        // Jika siswa telat (sekarang sudah lewat ends_at),
        // maka set waktu selesainya pas di batas maksimal saja.
        if ($result->ends_at && $waktuSelesaiSimpan->greaterThan($result->ends_at)) {
            $waktuSelesaiSimpan = $result->ends_at;
        }

        // =========================================================
        // 2. SINKRONISASI PAKSA REDIS (SAPU BERSIH)
        // =========================================================
        $redisKey = 'exam_answers:' . $this->result_id;
        $answersFromRedis = Redis::hgetall($redisKey);

        if (!empty($answersFromRedis)) {
            foreach ($answersFromRedis as $questionId => $answer) {
                UserAnswer::updateOrCreate(['result_id' => $this->result_id, 'question_id' => $questionId], ['selected_option' => $answer]);
            }
            Redis::del($redisKey);
        }

        // =========================================================
        // 3. PERHITUNGAN NILAI
        // =========================================================
        $totalQuestions = count($this->questions);
        $correctAnswersCount = 0;

        // Ambil jawaban dari DB yang sudah lengkap disinkronkan tadi
        $userAnswers = UserAnswer::where('result_id', $this->result_id)->get();

        foreach ($this->questions as $question) {
            $userAns = $userAnswers->where('question_id', $question->id)->first();

            if ($userAns) {
                $isCorrect = trim(strtoupper($userAns->selected_option)) === trim(strtoupper($question->correct_answer));
                if ($isCorrect) {
                    $correctAnswersCount++;
                }
                $userAns->update(['is_correct' => $isCorrect]);
            }
        }

        $finalScore = $totalQuestions > 0 ? ($correctAnswersCount / $totalQuestions) * 100 : 0;

        // =========================================================
        // 4. UPDATE STATUS SELESAI
        // =========================================================
        $result->update([
            'score' => $finalScore,
            'finished_at' => $waktuSelesaiSimpan, // Pakai waktu yang sudah "dikunci"
        ]);

        return redirect()
            ->route('user.exams')
            ->with('success', 'Ujian Selesai! Skor Anda: ' . number_format($finalScore, 1));
    }

    public function nextQuestion()
    {
        if ($this->currentQuestionIndex < count($this->questions) - 1) {
            $this->currentQuestionIndex++;
            $this->updateSessionIndex();
        }
    }

    public function prevQuestion()
    {
        if ($this->currentQuestionIndex > 0) {
            $this->currentQuestionIndex--;
            $this->updateSessionIndex();
        }
    }

    public function jumpToQuestion($index)
    {
        $this->currentQuestionIndex = $index;
        $this->updateSessionIndex();
    }
}; ?>

<div>
    <style>
        /* Batasan gambar soal WYSIWYG agar tidak kebesaran di awal */
        .question-content img {
            max-height: 550px !important;
            width: auto !important;
            object-fit: contain;
            border-radius: 0.5rem;
            cursor: zoom-in;
            transition: transform 0.2s ease-in-out;
            margin-top: 1rem;
            margin-bottom: 1rem;
        }

        .question-content img:hover {
            transform: scale(1.015);
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }

        /* Batasan gambar opsi */
        .option-image {
            cursor: zoom-in;
            transition: transform 0.2s ease-in-out;
        }

        .option-image:hover {
            transform: scale(1.02);
        }

        /* Transisi Modal */
        #examLightboxModal {
            transition: opacity 0.3s ease-in-out, visibility 0.3s ease-in-out;
        }
    </style>

    <div id="examLightboxModal"
        class="hidden fixed inset-0 w-full h-full bg-black/90 z-[9999] flex-col items-center justify-center p-4 md:p-8 cursor-zoom-out"
        onclick="closeLightbox()">
        <button
            class="absolute top-4 right-4 text-white hover:text-red-400 text-5xl font-black transition-colors z-[10000]"
            onclick="closeLightbox()">×</button>
        <img id="examLightboxImage" src="" alt="Zoomed Image"
            class="max-w-full max-h-[90vh] object-contain rounded-xl shadow-2xl border-4 border-white/20 cursor-default"
            onclick="event.stopPropagation()">
        <p class="text-gray-400 text-xs mt-4 font-mono">Klik di mana saja atau tombol X untuk menutup</p>
    </div>

    <div x-data="{
        timer: timerData(),
        localAnswers: @js($answers)
    }" x-init="timer.startTimer()">
        @if (count($questions) > 0)
            @php $currentQ = $questions[$currentQuestionIndex]; @endphp

            <div class="flex flex-col md:flex-row gap-6 relative z-10">
                <div id="top-of-question"
                    class="w-full md:w-3/4 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden relative scroll-mt-24">

                    <div class="bg-blue-50 p-4 border-b flex justify-between items-center">
                        <div class="flex items-center gap-4">
                            <a href="{{ route('user.dashboard') }}"
                                onclick="return confirm('Keluar dari ruang ujian? Anda tetap bisa melanjutkan selama waktu masih tersedia.')"
                                class="text-gray-500 hover:text-red-600 font-bold transition-colors">
                                ✕ Keluar
                            </a>
                            <div class="h-6 w-px bg-gray-300"></div>
                            <h3 class="text-lg font-bold text-blue-800">Soal No. {{ $currentQuestionIndex + 1 }}</h3>
                        </div>

                        <div
                            class="flex items-center gap-2 bg-red-600 text-white font-mono font-bold px-4 py-1 rounded-full shadow-sm">
                            <span>⏱️</span>
                            <span x-text="displayTime">00:00:00</span>
                        </div>
                    </div>

                    <div class="p-4 md:p-6 text-gray-800 text-base md:text-lg leading-relaxed border-b question-content"
                        wire:key="q-text-{{ $currentQ->id }}">
                        {!! $currentQ->question_text !!}
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 p-4 md:p-6 bg-gray-50/50"
                        wire:key="options-wrapper-{{ $currentQ->id }}">
                        @foreach (['a', 'b', 'c', 'd', 'e'] as $opt)
                            @php $val = "option_$opt"; @endphp

                            @if ($currentQ->$val)
                                <label wire:key="option-{{ $currentQ->id }}-{{ $opt }}"
                                    class="flex items-start gap-3 p-3 bg-white border rounded-lg cursor-pointer hover:bg-blue-50 hover:border-blue-300 transition shadow-sm {{ isset($answers[$currentQ->id]) && $answers[$currentQ->id] == strtoupper($opt) ? 'bg-blue-50 border-blue-500 ring-1 ring-blue-500' : 'border-gray-200' }}">

                                    <div class="pt-1">
                                        <input type="radio" name="answer_{{ $currentQ->id }}"
                                            value="{{ strtoupper($opt) }}"
                                            class="w-5 h-5 text-blue-600 flex-shrink-0 cursor-pointer"
                                            @click="localAnswers[{{ $currentQ->id }}] = '{{ strtoupper($opt) }}'; simpanJawabanKeServer({{ $currentQ->id }}, '{{ strtoupper($opt) }}')"
                                            :checked="localAnswers[{{ $currentQ->id }}] === '{{ strtoupper($opt) }}'">
                                    </div>

                                    <div class="text-gray-800 w-full relative overflow-hidden">
                                        <div class="font-bold text-sm text-gray-500 mb-1 absolute top-0 left-0">
                                            {{ strtoupper($opt) }}.</div>
                                        <div class="pl-6">
                                            @if ($currentQ->is_answer_image)
                                                <img src="{{ asset('storage/' . $currentQ->$val) }}"
                                                    onclick="openLightbox(this.src); event.preventDefault(); event.stopPropagation();"
                                                    class="option-image max-h-48 md:max-h-64 w-auto object-contain rounded border border-gray-200 mt-1 relative z-10 cursor-zoom-in">
                                            @else
                                                <div class="text-sm md:text-base">{!! $currentQ->$val !!}</div>
                                            @endif
                                        </div>
                                    </div>
                                </label>
                            @endif
                        @endforeach
                    </div>

                    <div class="bg-white p-4 border-t flex justify-between items-center">
                        <button wire:click="prevQuestion" onclick="scrollToTopQuestion()"
                            class="px-6 py-2 bg-white border border-gray-300 hover:bg-gray-100 text-gray-700 font-bold rounded-lg transition-colors {{ $currentQuestionIndex == 0 ? 'invisible' : '' }}">
                            ⬅️ Sebelumnya
                        </button>

                        <div class="flex items-center gap-2">
                            @if ($currentQuestionIndex < count($questions) - 1)
                                <button wire:click="nextQuestion" onclick="scrollToTopQuestion()"
                                    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow-sm transition-colors">
                                    Selanjutnya ➡️
                                </button>
                            @endif

                            @if ($currentQuestionIndex == count($questions) - 1)
                                <button wire:click="finishExam"
                                    wire:confirm="Apakah Anda yakin ingin mengakhiri ujian ini? Nilai akan segera dihitung."
                                    wire:loading.attr="disabled"
                                    class="px-8 py-2 bg-green-500 hover:bg-green-600 text-white font-bold rounded-lg shadow-lg transition-colors disabled:opacity-50">
                                    <span wire:loading.remove>✅ Selesai & Kirim</span>
                                    <span wire:loading>⌛ Memproses...</span>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="w-full md:w-1/4">
                    <div class="bg-white p-4 md:p-6 rounded-xl shadow-sm border sticky top-6">
                        <div class="flex items-center justify-between border-b pb-3 mb-4">
                            <h4 class="font-bold text-gray-800 text-sm">Navigasi Soal</h4>
                            <span
                                class="text-xs font-bold text-gray-400 bg-gray-100 px-2 py-1 rounded-full">{{ count($questions) }}
                                Butir</span>
                        </div>

                        <div class="max-h-[60vh] overflow-y-auto scrollbar-thin p-2">
                            <div class="flex flex-wrap gap-3 justify-start pb-4">
                                @foreach ($questions as $index => $q)
                                    <button wire:click="jumpToQuestion({{ $index }})"
                                        onclick="scrollToTopQuestion()"
                                        class="w-9 h-9 flex items-center justify-center font-bold text-xs border rounded-lg transition-all relative"
                                        :class="{
                                            /* 1. Jika Sudah Dijawab & BUKAN soal saat ini */
                                            'bg-blue-100 border-blue-400 text-blue-700': localAnswers[
                                                    {{ $q->id }}] &&
                                                {{ $index !== $currentQuestionIndex ? 'true' : 'false' }},
                                        
                                            /* 2. Jika Belum Dijawab & BUKAN soal saat ini */
                                            'bg-white border-gray-300 text-gray-600 hover:bg-gray-50': !localAnswers[
                                                    {{ $q->id }}] &&
                                                {{ $index !== $currentQuestionIndex ? 'true' : 'false' }},
                                        
                                            /* 3. Jika ini adalah SOAL SAAT INI (Aktif) - Tanpa Scale! */
                                            'bg-blue-600 border-blue-600 text-white ring-2 ring-blue-300 ring-offset-2 z-10 shadow-md': {{ $index === $currentQuestionIndex ? 'true' : 'false' }}
                                        }">
                                        {{ $index + 1 }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        @if (count($questions) > 30)
                            <div class="text-[10px] text-center text-gray-400 mt-3 font-medium">
                                Scroll ke bawah untuk melihat nomor lain
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <div class="bg-white rounded-xl shadow-sm border p-10 text-center">
                <h2 class="text-2xl font-bold text-gray-400">📭 Ujian ini belum memiliki soal.</h2>
                <a href="{{ route('user.dashboard') }}"
                    class="text-blue-600 hover:underline mt-4 inline-block font-bold">Kembali ke Dashboard</a>
            </div>
        @endif

        <script>
            function timerData() {
                return {
                    endTime: new Date("{{ $endTime }}").getTime(),
                    displayTime: '00:00:00',
                    startTimer() {
                        let interval = setInterval(() => {
                            let now = new Date().getTime();
                            let distance = this.endTime - now;

                            if (distance < 0) {
                                clearInterval(interval);
                                this.displayTime = "HABIS";
                                @this.call('finishExam');
                                return;
                            }

                            let hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                            let minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                            let seconds = Math.floor((distance % (1000 * 60)) / 1000);

                            this.displayTime =
                                (hours < 10 ? "0" + hours : hours) + ":" +
                                (minutes < 10 ? "0" + minutes : minutes) + ":" +
                                (seconds < 10 ? "0" + seconds : seconds);
                        }, 1000);
                    }
                }
            }

            // --- JAVASCRIPT UNTUK LIGHTBOX ZOOM ---
            const modal = document.getElementById('examLightboxModal');
            const modalImg = document.getElementById('examLightboxImage');

            function openLightbox(src) {
                modalImg.src = src;
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }

            function closeLightbox() {
                modal.classList.remove('flex');
                modal.classList.add('hidden');
                modalImg.src = '';
            }

            document.addEventListener('click', function(e) {
                if (e.target.tagName === 'IMG' && e.target.closest('.question-content')) {
                    openLightbox(e.target.src);
                }
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                    closeLightbox();
                }
            });

            // --- JAVASCRIPT UNTUK AUTO-SCROLL KE SOAL ---
            function scrollToTopQuestion() {
                // Jeda 100ms agar Livewire selesai me-render soal baru sebelum kita scroll
                setTimeout(() => {
                    const el = document.getElementById('top-of-question');
                    if (el) {
                        el.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                }, 100);
            }
            // Variabel untuk menyimpan ID Kertas Ujian saat ini
            const RESULT_ID = {{ $result_id }};

            function simpanJawabanKeServer(questionId, jawaban) {
                // Tembak data ke API tanpa memuat ulang halaman (ke Redis)
                fetch('{{ route('api.exam.save') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            result_id: RESULT_ID,
                            question_id: questionId,
                            answer: jawaban
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            console.log('Jawaban ' + jawaban + ' tersimpan di Redis!');

                            // --- INI KUNCINYA ---
                            // Memberitahu Livewire bahwa jawaban sudah berubah 
                            // agar navigasi di samping otomatis berubah warna jadi biru
                            // @this.set('answers.' + questionId, jawaban);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Gagal menyimpan jawaban, pastikan internet Anda stabil.');
                    });
            }
        </script>
    </div>
</div>
