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

        if ($result->finished_at) {
            return redirect()->route('user.dashboard');
        }

        if ($result->ends_at && now()->greaterThanOrEqualTo($result->ends_at)) {
            $this->finishExam();
            return;
        }

        $this->exam_package_id = $result->exam_package_id;

        // FIX: Tambahkan orderBy agar urutan soal sinkron dengan Admin
        $this->questions = \Illuminate\Support\Facades\Cache::remember('questions_package_' . $this->exam_package_id, 7200, function () {
            return Question::where('exam_package_id', $this->exam_package_id)->orderBy('order_num', 'asc')->get()->toArray();
        });

        $this->currentQuestionIndex = session('last_q_' . $this->result_id, 0);

        // FIX: Pastikan format waktu ISO agar JS tidak Error (NaN)
        if ($result->ends_at) {
            $this->endTime = \Carbon\Carbon::parse($result->ends_at)->toIso8601String();
        } else {
            $durationMinutes = $result->examPackage->time_limit;
            $this->endTime = $result->created_at->addMinutes($durationMinutes)->toIso8601String();
        }

        // FIX: Ambil jawaban lama agar tidak hilang saat refresh
        $this->answers = UserAnswer::where('result_id', $result_id)->pluck('selected_option', 'question_id')->mapWithKeys(fn($item, $key) => [(string) $key => $item])->toArray();
    }

    public function updateSessionIndex()
    {
        session(['last_q_' . $this->result_id => $this->currentQuestionIndex]);
    }

    public function answerQuestion($questionId, $selectedOption)
    {
        $this->answers[(string) $questionId] = $selectedOption;
        UserAnswer::updateOrCreate(['result_id' => $this->result_id, 'question_id' => $questionId], ['selected_option' => $selectedOption]);
    }

    public function finishExam()
    {
        session()->forget('last_q_' . $this->result_id);
        $result = UserResult::findOrFail($this->result_id);
        $waktuSelesaiSimpan = now();

        if ($result->ends_at && $waktuSelesaiSimpan->greaterThan($result->ends_at)) {
            $waktuSelesaiSimpan = $result->ends_at;
        }

        $redisKey = 'exam_answers:' . $this->result_id;
        $answersFromRedis = Redis::hgetall($redisKey);

        if (!empty($answersFromRedis)) {
            foreach ($answersFromRedis as $questionId => $answer) {
                UserAnswer::updateOrCreate(['result_id' => $this->result_id, 'question_id' => $questionId], ['selected_option' => $answer]);
            }
            Redis::del($redisKey);
        }

        $totalQuestions = count($this->questions);
        $correctAnswersCount = 0;
        $userAnswers = UserAnswer::where('result_id', $this->result_id)->get();

        foreach ($this->questions as $question) {
            $userAns = $userAnswers->where('question_id', $question['id'])->first();
            if ($userAns) {
                $isCorrect = trim(strtoupper($userAns->selected_option)) === trim(strtoupper($question['correct_answer']));
                if ($isCorrect) {
                    $correctAnswersCount++;
                }
                $userAns->update(['is_correct' => $isCorrect]);
            }
        }

        $finalScore = $totalQuestions > 0 ? ($correctAnswersCount / $totalQuestions) * 100 : 0;
        $result->update(['score' => $finalScore, 'finished_at' => $waktuSelesaiSimpan]);

        return redirect()
            ->route('user.exams')
            ->with('success', 'Ujian Selesai! Skor: ' . number_format($finalScore, 1));
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
        .question-content img {
            max-height: 500px !important;
            width: auto !important;
            border-radius: 0.5rem;
            cursor: zoom-in;
            margin: 1rem 0;
        }

        .option-image {
            cursor: zoom-in;
            transition: transform 0.2s;
        }

        .option-image:hover {
            transform: scale(1.02);
        }
    </style>

    {{-- FIX: Modal ditaruh paling luar agar tidak terpotong sidebar --}}
    <div id="examLightboxModal"
        class="hidden fixed inset-0 w-full h-full bg-black/80 z-[99999] flex flex-col items-center justify-center p-4"
        style="position: fixed !important; top:0; left:0;" onclick="closeLightbox()">
        <button class="absolute top-4 right-4 text-white text-5xl font-black">&times;</button>
        <img id="examLightboxImage" src=""
            class="max-w-full max-h-[85vh] object-contain rounded-xl shadow-2xl border-2 border-white/20"
            onclick="event.stopPropagation()">
    </div>

    <div x-data="{ timer: timerData(), localAnswers: @js($answers) }" x-init="timer.startTimer()">
        @if (count($questions) > 0)
            @php $currentQ = $questions[$currentQuestionIndex]; @endphp

            <div class="flex flex-col md:flex-row gap-6 relative">
                <div id="top-of-question" class="w-full md:w-3/4 bg-white rounded-xl shadow-sm border overflow-hidden">
                    <div class="bg-blue-50 p-4 border-b flex justify-between items-center">
                        <div class="flex items-center gap-4">
                            <a href="{{ route('user.dashboard') }}" onclick="return confirm('Keluar?')"
                                class="text-gray-500 hover:text-red-600 font-bold text-sm">✕ Keluar</a>
                            <h3 class="font-bold text-blue-800">Soal {{ $currentQuestionIndex + 1 }}</h3>
                        </div>
                        <div class="bg-red-600 text-white font-mono font-bold px-4 py-1 rounded-full text-sm">
                            <span x-text="timer.displayTime">00:00:00</span>
                        </div>
                    </div>

                    {{-- FIX: Tambahkan handle klik gambar di teks soal --}}
                    <div class="p-6 text-gray-800 text-lg leading-relaxed border-b question-content"
                        onclick="if(event.target.tagName==='IMG') openLightbox(event.target.src)">
                        {!! $currentQ['question_text'] !!}
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 p-6 bg-gray-50/50">
                        @foreach (['a', 'b', 'c', 'd', 'e'] as $opt)
                            @php $val = "option_$opt"; @endphp
                            @if ($currentQ[$val])
                                <label
                                    class="flex items-start gap-3 p-4 bg-white border rounded-xl cursor-pointer hover:bg-blue-50 transition shadow-sm"
                                    :class="localAnswers['{{ $currentQ['id'] }}'] === '{{ strtoupper($opt) }}' ?
                                        'border-blue-500 ring-1 ring-blue-500 bg-blue-50' : 'border-gray-200'">

                                    <input type="radio" name="answer" value="{{ strtoupper($opt) }}"
                                        class="mt-1 w-5 h-5 text-blue-600"
                                        @click="localAnswers['{{ $currentQ['id'] }}'] = '{{ strtoupper($opt) }}'; $wire.answerQuestion({{ $currentQ['id'] }}, '{{ strtoupper($opt) }}'); simpanJawabanKeServer({{ $currentQ['id'] }}, '{{ strtoupper($opt) }}')"
                                        :checked="localAnswers['{{ $currentQ['id'] }}'] === '{{ strtoupper($opt) }}'">

                                    <div class="w-full">
                                        <span
                                            class="font-bold text-gray-400 text-sm uppercase">{{ $opt }}.</span>
                                        <div class="mt-1">
                                            @if ($currentQ['is_answer_image'])
                                                {{-- FIX: Pembersihan Path Gambar --}}
                                                <img src="{{ asset('storage/' . ltrim(str_replace(['/storage/', 'storage/'], '', $currentQ[$val]), '/')) }}"
                                                    onclick="openLightbox(this.src); event.preventDefault();"
                                                    class="option-image max-h-48 w-auto rounded border">
                                            @else
                                                <div class="text-base">{!! $currentQ[$val] !!}</div>
                                            @endif
                                        </div>
                                    </div>
                                </label>
                            @endif
                        @endforeach
                    </div>

                    <div class="p-4 bg-white border-t flex justify-between items-center">
                        <button wire:click="prevQuestion"
                            class="px-6 py-2 border border-gray-300 rounded-lg font-bold text-gray-500 transition-all duration-200 hover:bg-gray-100 hover:text-gray-700 hover:border-gray-400 {{ $currentQuestionIndex == 0 ? 'invisible' : '' }}">
                            ⬅️ Sebelumnya
                        </button>

                        <div class="flex items-center gap-2">
                            @if ($currentQuestionIndex < count($questions) - 1)
                                {{-- Tombol Selanjutnya: Gunakan wire:key agar tidak bentrok dengan tombol Selesai --}}
                                <button wire:click="nextQuestion" wire:key="btn-next-{{ $currentQuestionIndex }}"
                                    class="px-6 py-2 bg-blue-600 text-white font-bold rounded-lg shadow-md hover:bg-blue-700 transition-colors">
                                    Selanjutnya ➡️
                                </button>
                            @else
                                {{-- Tombol Selesai: Kita panggil via fungsi khusus agar konfirmasinya tidak nyangkut --}}
                                <button type="button" wire:key="btn-finish" onclick="konfirmasiSelesai()"
                                    class="px-8 py-2 bg-green-500 text-white font-bold rounded-lg shadow-md hover:bg-green-600 transition-colors">
                                    ✅ Selesai Ujian
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Sidebar --}}
                <div class="w-full md:w-1/4">
                    <div class="bg-white p-5 rounded-xl shadow-sm border sticky top-4">
                        <h4 class="font-bold text-gray-400 text-xs uppercase tracking-widest mb-4">Navigasi</h4>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($questions as $index => $q)
                                <button wire:click="jumpToQuestion({{ $index }})"
                                    class="w-9 h-9 flex items-center justify-center font-bold text-xs border rounded-lg transition-all"
                                    :class="{
                                        'bg-blue-600 text-white border-blue-600 shadow-md': {{ $index === $currentQuestionIndex ? 'true' : 'false' }},
                                        'bg-blue-50 border-blue-200 text-blue-700': localAnswers[
                                                '{{ $q['id'] }}'] &&
                                            {{ $index !== $currentQuestionIndex ? 'true' : 'false' }},
                                        'bg-white border-gray-200 text-gray-400': !localAnswers[
                                                '{{ $q['id'] }}'] &&
                                            {{ $index !== $currentQuestionIndex ? 'true' : 'false' }}
                                    }">
                                    {{ $index + 1 }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <script>
            function timerData() {
                return {
                    endTime: new Date("{{ $endTime }}").getTime(),
                    displayTime: '00:00:00',
                    startTimer() {
                        let interval = setInterval(() => {
                            let dist = this.endTime - new Date().getTime();
                            if (dist < 0) {
                                clearInterval(interval);
                                this.displayTime = "HABIS";
                                @this.call('finishExam');
                                return;
                            }
                            let h = Math.floor(dist / 3600000);
                            let m = Math.floor((dist % 3600000) / 60000);
                            let s = Math.floor((dist % 60000) / 1000);
                            this.displayTime =
                                `${h.toString().padStart(2,'0')}:${m.toString().padStart(2,'0')}:${s.toString().padStart(2,'0')}`;
                        }, 1000);
                    }
                }
            }

            function openLightbox(src) {
                document.getElementById('examLightboxImage').src = src;
                document.getElementById('examLightboxModal').classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }

            function closeLightbox() {
                document.getElementById('examLightboxModal').classList.add('hidden');
                document.body.style.overflow = 'auto';
            }

            function simpanJawabanKeServer(qId, ans) {
                fetch('{{ route('api.exam.save') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        result_id: {{ $result_id }},
                        question_id: qId,
                        answer: ans
                    })
                });
            }

            function konfirmasiSelesai() {
                if (confirm("Anda sudah berada di soal terakhir. Yakin ingin mengakhiri ujian dan mengirim semua jawaban?")) {
                    // Panggil fungsi finishExam di Livewire secara manual
                    @this.call('finishExam');
                }
            }
        </script>
    </div>
</div>
