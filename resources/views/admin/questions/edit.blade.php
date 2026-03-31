<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Soal - CBT ADMIN</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        #editor-question {
            height: 250px;
        }

        #editor-explanation {
            height: 150px;
        }
    </style>
</head>

<body class="bg-gray-100 flex min-h-screen">

    <div class="flex-1 p-8 max-w-5xl mx-auto w-full">

        <div class="flex items-center justify-between mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Edit Soal</h1>
            <a href="{{ route('admin.packages.show', $question->exam_package_id) }}"
                class="text-gray-500 hover:text-gray-800 font-semibold transition-colors">
                ✕ Batal & Kembali
            </a>
        </div>

        <form action="{{ route('admin.questions.update', $question->id) }}" method="POST" id="form-soal"
            class="bg-white p-8 rounded-xl shadow-lg border-t-4 border-yellow-500">
            @csrf
            @method('PUT')

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Paket Ujian <span
                        class="text-red-500">*</span></label>
                <select name="exam_package_id"
                    class="w-full px-4 py-3 rounded-lg border bg-gray-50 focus:bg-white focus:border-blue-500 outline-none transition-all font-semibold"
                    required>
                    @foreach ($packages as $package)
                        <option value="{{ $package->id }}"
                            {{ $question->exam_package_id == $package->id ? 'selected' : '' }}>
                            {{ $package->title }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-8">
                <label class="block text-gray-700 text-sm font-bold mb-2">Pertanyaan <span
                        class="text-red-500">*</span></label>
                <div id="editor-question" class="bg-white">{!! $question->question_text !!}</div>
                <input type="hidden" name="question_text" id="question_text" required>
            </div>

            <div class="mb-8 bg-gray-50 p-6 rounded-lg border">
                <div class="flex justify-between items-center mb-4">
                    <label class="text-gray-700 text-sm font-bold">Pilihan Jawaban</label>
                    <span class="text-xs text-gray-500 italic">Pilih bulatan biru untuk menandai Kunci Jawaban.</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach (['A', 'B', 'C', 'D', 'E'] as $opt)
                        @php $optionField = 'option_' . strtolower($opt); @endphp
                        <div class="flex items-start gap-3 bg-white p-3 rounded border shadow-sm">
                            <div class="flex items-center h-full pt-2">
                                <input type="radio" name="correct_answer" value="{{ $opt }}"
                                    class="w-5 h-5 text-blue-600 focus:ring-blue-500"
                                    {{ $question->correct_answer == $opt ? 'checked' : '' }} required>
                            </div>
                            <div class="w-full">
                                <label class="text-xs font-bold text-gray-500 mb-1 block">Opsi
                                    {{ $opt }}</label>
                                <textarea name="{{ $optionField }}" rows="2"
                                    class="w-full px-3 py-2 border rounded focus:border-blue-500 outline-none">{{ $question->$optionField }}</textarea>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mb-8">
                <label class="block text-gray-700 text-sm font-bold mb-2">Pembahasan Jawaban (Opsional)</label>
                <div id="editor-explanation" class="bg-white">{!! $question->explanation !!}</div>
                <input type="hidden" name="explanation" id="explanation">
            </div>

            <hr class="mb-6">

            <div class="flex justify-end gap-4">
                <button type="submit"
                    class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-3 px-8 rounded-lg shadow-lg transition-colors">
                    💾 Perbarui Soal
                </button>
            </div>
        </form>
    </div>

    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script>
        var quillQuestion = new Quill('#editor-question', {
            theme: 'snow',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline', 'strike'],
                    ['blockquote', 'code-block'],
                    [{
                        'list': 'ordered'
                    }, {
                        'list': 'bullet'
                    }],
                    [{
                        'script': 'sub'
                    }, {
                        'script': 'super'
                    }],
                    [{
                        'header': [1, 2, 3, false]
                    }],
                    ['link', 'image', 'formula'],
                    ['clean']
                ]
            }
        });

        var quillExplanation = new Quill('#editor-explanation', {
            theme: 'snow'
        });

        var form = document.getElementById('form-soal');
        form.onsubmit = function() {
            var questionHTML = quillQuestion.root.innerHTML;
            var explanationHTML = quillExplanation.root.innerHTML;

            if (questionHTML === '<p><br></p>' || questionHTML.trim() === '') {
                alert('Teks pertanyaan tidak boleh kosong!');
                return false;
            }

            document.getElementById('question_text').value = questionHTML;
            document.getElementById('explanation').value = explanationHTML === '<p><br></p>' ? '' : explanationHTML;
        };
    </script>
</body>

</html>
