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

        .editor-option {
            height: 100px;
        }

        /* Tinggi untuk opsi A-E */
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
            enctype="multipart/form-data" class="bg-white p-8 rounded-xl shadow-lg border-t-4 border-yellow-500">
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
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <label class="text-gray-700 text-sm font-bold">Pilihan Jawaban</label>
                        <p class="text-xs text-gray-500 mt-1">Tentukan apakah jawaban berupa Teks atau Gambar.</p>
                    </div>

                    <label class="inline-flex items-center cursor-pointer">
                        <span class="mr-3 text-sm font-medium text-gray-700">Mode Gambar</span>
                        <input type="checkbox" name="is_answer_image" id="toggle-image" class="sr-only peer"
                            value="1" {{ $question->is_answer_image ? 'checked' : '' }}>
                        <div
                            class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                        </div>
                    </label>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach (['A', 'B', 'C', 'D', 'E'] as $opt)
                        @php $optionField = 'option_' . strtolower($opt); @endphp
                        <div class="flex items-start gap-3 bg-white p-4 rounded-lg border shadow-sm">
                            <div class="flex items-center h-full pt-2">
                                <input type="radio" name="correct_answer" value="{{ $opt }}"
                                    class="w-5 h-5 text-blue-600 focus:ring-blue-500"
                                    {{ $question->correct_answer == $opt ? 'checked' : '' }} required>
                            </div>
                            <div class="w-full">
                                <label class="text-xs font-bold text-gray-500 mb-2 block">Opsi
                                    {{ $opt }}</label>

                                <div id="text-wrapper-{{ strtolower($opt) }}"
                                    class="mode-text {{ $question->is_answer_image ? 'hidden' : '' }}">
                                    <div id="editor-option-{{ strtolower($opt) }}" class="editor-option bg-white">
                                        {!! $question->is_answer_image ? '' : $question->$optionField !!}</div>
                                    <input type="hidden" name="option_{{ strtolower($opt) }}"
                                        id="input-option-{{ strtolower($opt) }}">
                                </div>

                                <div id="image-wrapper-{{ strtolower($opt) }}"
                                    class="mode-image {{ $question->is_answer_image ? '' : 'hidden' }}">
                                    @if ($question->is_answer_image && $question->$optionField)
                                        <div class="mb-3 p-2 border rounded bg-gray-50 flex flex-col items-center">
                                            <img src="{{ asset('storage/' . $question->$optionField) }}"
                                                class="max-h-20 object-contain rounded shadow-sm mb-2">
                                            <span class="text-[10px] text-gray-500">Gambar saat ini. Upload baru untuk
                                                mengganti.</span>
                                        </div>
                                    @endif
                                    <input type="file" name="image_{{ strtolower($opt) }}"
                                        class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                        accept="image/*">
                                </div>

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
        var fullToolbar = [
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
        ];

        var miniToolbar = [
            ['bold', 'italic'],
            [{
                'script': 'sub'
            }, {
                'script': 'super'
            }],
            ['formula']
        ];

        var quillQuestion = new Quill('#editor-question', {
            theme: 'snow',
            modules: {
                toolbar: fullToolbar
            }
        });

        var quillExplanation = new Quill('#editor-explanation', {
            theme: 'snow',
            modules: {
                toolbar: fullToolbar
            }
        });

        var quillOptions = {};
        ['a', 'b', 'c', 'd', 'e'].forEach(function(opt) {
            quillOptions[opt] = new Quill('#editor-option-' + opt, {
                theme: 'snow',
                modules: {
                    toolbar: miniToolbar
                }
            });
        });

        // --- LOGIKA TOGGLE (GANTI MODE) ---
        const toggle = document.getElementById('toggle-image');
        const modeTextElements = document.querySelectorAll('.mode-text');
        const modeImageElements = document.querySelectorAll('.mode-image');

        // Fungsi untuk mengatur tampilan berdasarkan status saklar
        function applyToggleState() {
            if (toggle.checked) {
                // Jika saklar menyala (Mode Gambar)
                modeTextElements.forEach(el => el.classList.add('hidden'));
                modeImageElements.forEach(el => el.classList.remove('hidden'));
            } else {
                // Jika saklar mati (Mode Teks)
                modeTextElements.forEach(el => el.classList.remove('hidden'));
                modeImageElements.forEach(el => el.classList.add('hidden'));
            }
        }

        // Panggil fungsi saat halaman dimuat
        applyToggleState();

        toggle.addEventListener('change', applyToggleState);

        // --- LOGIKA SAAT TOMBOL SIMPAN DIKLIK ---
        var form = document.getElementById('form-soal');
        form.onsubmit = function() {
            var questionHTML = quillQuestion.root.innerHTML;

            if (questionHTML === '<p><br></p>' || questionHTML.trim() === '') {
                alert('Teks pertanyaan tidak boleh kosong!');
                return false;
            }

            var checkedRadio = document.querySelector('input[name="correct_answer"]:checked');
            var selectedOption = checkedRadio.value.toLowerCase();
            var isImageMode = toggle.checked;

            if (!isImageMode) {
                // VALIDASI MODE TEKS
                var isCorrectOptionEmpty = false;
                ['a', 'b', 'c', 'd', 'e'].forEach(function(opt) {
                    var optHTML = quillOptions[opt].root.innerHTML;
                    if (optHTML === '<p><br></p>') optHTML = '';
                    document.getElementById('input-option-' + opt).value = optHTML;

                    if (opt === selectedOption && optHTML.trim() === '') {
                        isCorrectOptionEmpty = true;
                    }
                });

                if (isCorrectOptionEmpty) {
                    alert('Gagal! Anda menjadikan Opsi ' + checkedRadio.value +
                        ' sebagai Kunci Jawaban, tapi teksnya masih kosong.');
                    return false;
                }
            }
            // Catatan: Untuk mode gambar saat Edit, kita tidak memaksa validasi "wajib upload", 
            // karena Admin mungkin hanya ingin mengedit teks soalnya saja tanpa mengganti gambar lamanya.

            document.getElementById('question_text').value = questionHTML;
            document.getElementById('explanation').value = quillExplanation.root.innerHTML === '<p><br></p>' ? '' :
                quillExplanation.root.innerHTML;
        };
    </script>
</body>

</html>
