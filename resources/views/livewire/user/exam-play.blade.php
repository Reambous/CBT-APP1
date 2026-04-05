<?php

use Livewire\Volt\Component;
use App\Models\UserResult;
use App\Models\UserAnswer;
use App\Models\Question;

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

        // Jika sudah selesai, tendang kembali ke dashboard/riwayat
        if ($result->finished_at) {
            return redirect()->route('user.dashboard');
        }

        $this->exam_package_id = $result->exam_package_id;
        $this->questions = Question::where('exam_package_id', $this->exam_package_id)->get();

        // LOGIKA REFRESH: Ambil posisi soal terakhir dari session
        $this->currentQuestionIndex = session('last_q_' . $this->result_id, 0);

        // Atur Waktu Selesai (EndTime)
        $durationMinutes = $result->examPackage->time_limit;
        $this->endTime = $result->created_at->addMinutes($durationMinutes)->toIso8601String();

        // Ambil jawaban yang sudah pernah dipilih user sebelumnya
        $existingAnswers = UserAnswer::where('result_id', $result_id)->get();
        foreach ($existingAnswers as $ans) {
            $this->answers[$ans->question_id] = $ans->selected_option;
        }
    }

    // Fungsi untuk menyimpan posisi soal terakhir ke session
    public function updateSessionIndex()
    {
        session(['last_q_' . $this->result_id => $this->currentQuestionIndex]);
    }

    // Fungsi saat user mengklik opsi A, B, C, D, atau E
    public function answerQuestion($questionId, $selectedOption)
    {
        $this->answers[$questionId] = $selectedOption;

        UserAnswer::updateOrCreate(['result_id' => $this->result_id, 'question_id' => $questionId], ['selected_option' => $selectedOption]);
    }

    // Fungsi Utama: Mengakhiri Ujian dan Menghitung Nilai
    public function finishExam()
    {
        // 1. Bersihkan session agar tidak nyangkut di ujian lain
        session()->forget('last_q_' . $this->result_id);

        $result = UserResult::findOrFail($this->result_id);
        $totalQuestions = count($this->questions);
        $correctAnswersCount = 0;

        // 2. Ambil semua jawaban sekaligus agar database tidak keberatan
        $userAnswers = UserAnswer::where('result_id', $this->result_id)->get();

        // 3. Kalkulasi Skor
        foreach ($this->questions as $question) {
            $userAns = $userAnswers->where('question_id', $question->id)->first();

            if ($userAns) {
                // Kebal huruf besar/kecil & spasi
                $isCorrect = trim(strtoupper($userAns->selected_option)) === trim(strtoupper($question->correct_answer));

                if ($isCorrect) {
                    $correctAnswersCount++;
                }

                $userAns->update(['is_correct' => $isCorrect]);
            }
        }

        // 4. Hitung skala 100 dan Simpan
        $finalScore = $totalQuestions > 0 ? ($correctAnswersCount / $totalQuestions) * 100 : 0;

        $result->update([
            'score' => $finalScore,
            'finished_at' => now(),
        ]);

        // 5. Arahkan ke halaman riwayat ujian
        return redirect()
            ->route('user.exams')
            ->with('success', 'Ujian Selesai! Skor Anda: ' . number_format($finalScore, 1));
    }

    // --- FUNGSI NAVIGASI SOAL ---
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
    <div x-data="timerData()" x-init="startTimer()">
        @if (count($questions) > 0)
            @php $currentQ = $questions[$currentQuestionIndex]; @endphp

            <div class="flex flex-col md:flex-row gap-6">
                <div class="w-full md:w-3/4 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

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

                    <div class="p-6 text-gray-800 text-lg leading-relaxed border-b"
                        wire:key="q-text-{{ $currentQ->id }}">
                        {!! $currentQ->question_text !!}
                    </div>

                    <div class="grid grid-cols-1 gap-4 mt-6 p-6" wire:key="options-wrapper-{{ $currentQ->id }}">
                        @foreach (['a', 'b', 'c', 'd', 'e'] as $opt)
                            @php $val = "option_$opt"; @endphp

                            @if ($currentQ->$val)
                                <label wire:key="option-{{ $currentQ->id }}-{{ $opt }}"
                                    class="flex items-center gap-4 p-4 border rounded-xl cursor-pointer hover:bg-blue-50 transition {{ isset($answers[$currentQ->id]) && $answers[$currentQ->id] == strtoupper($opt) ? 'bg-blue-50 border-blue-400 ring-1 ring-blue-400' : '' }}">

                                    <input type="radio" name="answer_{{ $currentQ->id }}"
                                        value="{{ strtoupper($opt) }}"
                                        wire:click="answerQuestion({{ $currentQ->id }}, '{{ strtoupper($opt) }}')"
                                        class="w-5 h-5 text-blue-600"
                                        {{ isset($answers[$currentQ->id]) && $answers[$currentQ->id] == strtoupper($opt) ? 'checked' : '' }}>

                                    <div class="text-gray-800 w-full">
                                        @if ($currentQ->is_answer_image)
                                            <img src="{{ asset('storage/' . $currentQ->$val) }}"
                                                class="max-h-96 w-auto object-contain rounded-lg border border-gray-200 shadow-sm">
                                        @else
                                            {!! $currentQ->$val !!}
                                        @endif
                                    </div>
                                </label>
                            @endif
                        @endforeach
                    </div>

                    <div class="bg-gray-50 p-4 border-t flex justify-between items-center">
                        <button wire:click="prevQuestion"
                            class="px-6 py-2 bg-white border border-gray-300 hover:bg-gray-100 text-gray-700 font-bold rounded-lg transition-colors {{ $currentQuestionIndex == 0 ? 'invisible' : '' }}">
                            ⬅️ Sebelumnya
                        </button>

                        <div class="flex items-center gap-2">
                            @if ($currentQuestionIndex < count($questions) - 1)
                                <button wire:click="nextQuestion"
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
                    <div class="bg-white p-6 rounded-xl shadow-sm border sticky top-6">
                        <h4 class="font-bold text-gray-800 mb-4 text-center border-b pb-2">Navigasi Soal</h4>
                        <div class="grid grid-cols-5 gap-2">
                            @foreach ($questions as $index => $q)
                                <button wire:click="jumpToQuestion({{ $index }})"
                                    class="w-full aspect-square flex items-center justify-center font-bold text-sm border rounded-lg transition-all 
                                    {{ isset($answers[$q->id]) ? 'bg-blue-500 border-blue-600 text-white shadow-sm' : 'bg-white border-gray-300 text-gray-600 hover:bg-gray-50' }} 
                                    {{ $index === $currentQuestionIndex ? 'ring-2 ring-blue-800 ring-offset-2' : '' }}">
                                    {{ $index + 1 }}
                                </button>
                            @endforeach
                        </div>
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

                            // Jika Waktu Habis
                            if (distance < 0) {
                                clearInterval(interval);
                                this.displayTime = "HABIS";
                                @this.call('finishExam'); // Panggil fungsi Livewire
                                return;
                            }

                            // Perhitungan Jam, Menit, Detik
                            let hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                            let minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                            let seconds = Math.floor((distance % (1000 * 60)) / 1000);

                            // Format 00:00:00
                            this.displayTime =
                                (hours < 10 ? "0" + hours : hours) + ":" +
                                (minutes < 10 ? "0" + minutes : minutes) + ":" +
                                (seconds < 10 ? "0" + seconds : seconds);
                        }, 1000);
                    }
                }
            }
        </script>
    </div>
</div>
