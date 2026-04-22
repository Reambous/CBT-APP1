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

        // FITUR REDIS: Tetap pakai toArray() agar stabil
        $this->questions = \Illuminate\Support\Facades\Cache::remember('questions_package_' . $this->exam_package_id, 7200, function () {
            return Question::where('exam_package_id', $this->exam_package_id)->get()->toArray();
        });

        $this->currentQuestionIndex = session('last_q_' . $this->result_id, 0);

        if ($result->ends_at) {
            $this->endTime = \Carbon\Carbon::parse($result->ends_at)->toIso8601String();
        } else {
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
            // DISINI JUGA UBAH: $question adalah array, akses pakai ['id']
            $userAns = $userAnswers->where('question_id', $question['id'])->first();

            if ($userAns) {
                // AKSES PAKAI ['correct_answer']
                $isCorrect = trim(strtoupper($userAns->selected_option)) === trim(strtoupper($question['correct_answer']));
                if ($isCorrect) {
                    $correctAnswersCount++;
                }
                $userAns->update(['is_correct' => $isCorrect]);
            }
        }

        $finalScore = $totalQuestions > 0 ? ($correctAnswersCount / $totalQuestions) * 100 : 0;

        $result->update([
            'score' => $finalScore,
            'finished_at' => $waktuSelesaiSimpan,
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

        .option-image {
            cursor: zoom-in;
            transition: transform 0.2s ease-in-out;
        }

        .option-image:hover {
            transform: scale(1.02);
        }

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
        <img id="examLightboxImage" src=""
            class="max-w-full max-h-[90vh] object-contain rounded-xl shadow-2xl border-4 border-white/20 cursor-default"
            onclick="event.stopPropagation()">
    </div>

    <div x-data="{ timer: timerData(), localAnswers: @js($answers) }" x-init="timer.startTimer()">
        @if (count($questions) > 0)
            @php $currentQ = $questions[$currentQuestionIndex]; @endphp

            <div class="flex flex-col md:flex-row gap-6 relative z-10">
                <div id="top-of-question"
                    class="w-full md:w-3/4 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden relative scroll-mt-24">

                    <div class="bg-blue-50 p-4 border-b flex justify-between items-center">
                        <div class="flex items-center gap-4">
                            <a href="{{ route('user.dashboard') }}" onclick="return confirm('Keluar?')"
                                class="text-gray-500 hover:text-red-600 font-bold">✕ Keluar</a>
                            <div class="h-6 w-px bg-gray-300"></div>
                            <h3 class="text-lg font-bold text-blue-800">Soal No. {{ $currentQuestionIndex + 1 }}</h3>
                        </div>
                        <div
                            class="flex items-center gap-2 bg-red-600 text-white font-mono font-bold px-4 py-1 rounded-full shadow-sm">
                            <span>⏱️</span><span x-text="timer.displayTime">00:00:00</span>
                        </div>
                    </div>

                    {{-- PERBAIKAN DISINI: Pakai ['id'] dan ['question_text'] --}}
                    <div class="p-4 md:p-6 text-gray-800 text-base md:text-lg leading-relaxed border-b question-content"
                        wire:key="q-text-{{ $currentQ['id'] }}">
                        {!! $currentQ['question_text'] !!}
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 p-4 md:p-6 bg-gray-50/50"
                        wire:key="options-wrapper-{{ $currentQ['id'] }}">
                        @foreach (['a', 'b', 'c', 'd', 'e'] as $opt)
                            @php $val = "option_$opt"; @endphp

                            {{-- PERBAIKAN DISINI: Pakai array access $currentQ[$val] --}}
                            @if ($currentQ[$val])
                                <label wire:key="option-{{ $currentQ['id'] }}-{{ $opt }}"
                                    class="flex items-start gap-3 p-3 bg-white border rounded-lg cursor-pointer hover:bg-blue-50 hover:border-blue-300 transition shadow-sm {{ isset($answers[$currentQ['id']]) && $answers[$currentQ['id']] == strtoupper($opt) ? 'bg-blue-50 border-blue-500 ring-1 ring-blue-500' : 'border-gray-200' }}">

                                    <div class="pt-1">
                                        <input type="radio" name="answer_{{ $currentQ['id'] }}"
                                            value="{{ strtoupper($opt) }}"
                                            class="w-5 h-5 text-blue-600 flex-shrink-0 cursor-pointer"
                                            @click="localAnswers[{{ $currentQ['id'] }}] = '{{ strtoupper($opt) }}'; simpanJawabanKeServer({{ $currentQ['id'] }}, '{{ strtoupper($opt) }}')"
                                            :checked="localAnswers[{{ $currentQ['id'] }}] === '{{ strtoupper($opt) }}'">
                                    </div>

                                    <div class="text-gray-800 w-full relative overflow-hidden">
                                        <div class="font-bold text-sm text-gray-500 mb-1 absolute top-0 left-0">
                                            {{ strtoupper($opt) }}.</div>
                                        <div class="pl-6">
                                            @if ($currentQ['is_answer_image'])
                                                <img src="{{ asset('storage/' . $currentQ[$val]) }}"
                                                    onclick="openLightbox(this.src); event.preventDefault(); event.stopPropagation();"
                                                    class="option-image max-h-48 md:max-h-64 w-auto object-contain rounded border border-gray-200 mt-1 z-10">
                                            @else
                                                <div class="text-sm md:text-base">{!! $currentQ[$val] !!}</div>
                                            @endif
                                        </div>
                                    </div>
                                </label>
                            @endif
                        @endforeach
                    </div>

                    <div class="bg-white p-4 border-t flex justify-between items-center">
                        <button wire:click="prevQuestion" onclick="scrollToTopQuestion()"
                            class="px-6 py-2 bg-white border border-gray-300 text-gray-700 font-bold rounded-lg {{ $currentQuestionIndex == 0 ? 'invisible' : '' }}">⬅️
                            Sebelumnya</button>
                        <div class="flex items-center gap-2">
                            @if ($currentQuestionIndex < count($questions) - 1)
                                <button wire:click="nextQuestion" onclick="scrollToTopQuestion()"
                                    class="px-6 py-2 bg-blue-600 text-white font-bold rounded-lg shadow-sm">Selanjutnya
                                    ➡️</button>
                            @else
                                <button wire:click="finishExam" wire:confirm="Yakin selesai?"
                                    wire:loading.attr="disabled"
                                    class="px-8 py-2 bg-green-500 text-white font-bold rounded-lg shadow-lg">
                                    <span wire:loading.remove>✅ Selesai</span>
                                    <span wire:loading>⌛...</span>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="w-full md:w-1/4">
                    <div class="bg-white p-4 md:p-6 rounded-xl shadow-sm border sticky top-6">
                        <div class="flex items-center justify-between border-b pb-3 mb-4">
                            <h4 class="font-bold text-gray-800 text-sm">Navigasi</h4>
                            <span
                                class="text-xs font-bold text-gray-400 bg-gray-100 px-2 py-1 rounded-full">{{ count($questions) }}
                                Butir</span>
                        </div>
                        <div class="max-h-[60vh] overflow-y-auto p-2">
                            <div class="flex flex-wrap gap-3 justify-start pb-4">
                                @foreach ($questions as $index => $q)
                                    <button wire:click="jumpToQuestion({{ $index }})"
                                        onclick="scrollToTopQuestion()"
                                        class="w-9 h-9 flex items-center justify-center font-bold text-xs border rounded-lg transition-all"
                                        :class="{
                                            'bg-blue-100 border-blue-400 text-blue-700': localAnswers[
                                                    {{ $q['id'] }}] &&
                                                {{ $index !== $currentQuestionIndex ? 'true' : 'false' }},
                                            'bg-white border-gray-300 text-gray-600': !localAnswers[
                                                    {{ $q['id'] }}] &&
                                                {{ $index !== $currentQuestionIndex ? 'true' : 'false' }},
                                            'bg-blue-600 border-blue-600 text-white ring-2 ring-blue-300 ring-offset-2 z-10 shadow-md': {{ $index === $currentQuestionIndex ? 'true' : 'false' }}
                                        }">
                                        {{ $index + 1 }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-white rounded-xl shadow-sm border p-10 text-center">
                <h2 class="text-2xl font-bold text-gray-400">📭 Belum ada soal.</h2>
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
                            let hours = Math.floor((distance % (86400000)) / 3600000);
                            let minutes = Math.floor((distance % 3600000) / 60000);
                            let seconds = Math.floor((distance % 60000) / 1000);
                            this.displayTime = (hours < 10 ? '0' + hours : hours) + ':' + (minutes < 10 ? '0' +
                                minutes : minutes) + ':' + (seconds < 10 ? '0' + seconds : seconds);
                        }, 1000);
                    }
                }
            }

            function openLightbox(src) {
                document.getElementById('examLightboxImage').src = src;
                document.getElementById('examLightboxModal').classList.replace('hidden', 'flex');
            }

            function closeLightbox() {
                document.getElementById('examLightboxModal').classList.replace('flex', 'hidden');
            }

            function scrollToTopQuestion() {
                setTimeout(() => {
                    document.getElementById('top-of-question')?.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }, 100);
            }

            const RESULT_ID = {{ $result_id }};
            let antreanKirim = {};

            function simpanJawabanKeServer(questionId, jawaban) {
                if (antreanKirim[questionId]) clearTimeout(antreanKirim[questionId]);
                antreanKirim[questionId] = setTimeout(() => {
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
                        .then(res => res.json()).then(data => {
                            if (data.status === 'success') console.log('Saved to Redis');
                        })
                        .catch(e => console.error(e));
                }, 1000);
            }
        </script>
    </div>
</div>
