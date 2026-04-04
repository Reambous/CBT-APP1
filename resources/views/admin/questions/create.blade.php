<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Soal - CBT ADMIN</title>
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

        /* Tinggi khusus untuk opsi A-E */
    </style>
</head>

<body class="bg-gray-100 flex min-h-screen">

    <div class="flex-1 p-8 max-w-5xl mx-auto w-full">

        <div class="flex items-center justify-between mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Tulis Soal Baru</h1>
            <a href="{{ route('admin.packages.show', $selectedPackageId) }}"
                class="text-gray-500 hover:text-gray-800 font-semibold transition-colors">
                ✕ Batal & Kembali ke Paket
            </a>
        </div>

        @if (session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg shadow-sm">
                <span class="font-bold">Berhasil!</span> {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('admin.questions.store') }}" method="POST" id="form-soal"
            class="bg-white p-8 rounded-xl shadow-lg border-t-4 border-blue-600">
            @csrf

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Paket Ujian <span
                        class="text-red-500">*</span></label>
                <select name="exam_package_id"
                    class="w-full px-4 py-3 rounded-lg border bg-gray-50 focus:bg-white focus:border-blue-500 outline-none transition-all font-semibold"
                    required>
                    <option value="">-- Silakan Pilih Paket --</option>
                    @foreach ($packages as $package)
                        <option value="{{ $package->id }}"
                            {{ isset($selectedPackageId) && $selectedPackageId == $package->id ? 'selected' : '' }}>
                            {{ $package->title }} ({{ $package->time_limit }} Menit)
                        </option>
                    @endforeach
                </select>
                @if (isset($selectedPackageId))
                    <p class="text-xs text-indigo-600 mt-2 font-medium">✅ Paket ujian telah dipilih secara otomatis.</p>
                @endif
            </div>

            <div class="mb-8">
                <label class="block text-gray-700 text-sm font-bold mb-2">Pertanyaan <span
                        class="text-red-500">*</span></label>
                <div id="editor-question" class="bg-white"></div>
                <input type="hidden" name="question_text" id="question_text" required>
            </div>

            <div class="mb-8 bg-gray-50 p-6 rounded-lg border">
                <div class="flex justify-between items-center mb-4">
                    <label class="text-gray-700 text-sm font-bold">Pilihan Jawaban</label>
                    <span class="text-xs text-gray-500 italic">Pilih bulatan biru untuk menandai Kunci. Sisipkan gambar
                        jika perlu.</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach (['A', 'B', 'C', 'D', 'E'] as $opt)
                        <div class="flex items-start gap-3 bg-white p-3 rounded-lg border shadow-sm">
                            <div class="flex items-center h-full pt-2">
                                <input type="radio" name="correct_answer" value="{{ $opt }}"
                                    class="w-5 h-5 text-blue-600 focus:ring-blue-500" required>
                            </div>
                            <div class="w-full">
                                <label class="text-xs font-bold text-gray-500 mb-1 block">Opsi
                                    {{ $opt }}</label>
                                <div id="editor-option-{{ strtolower($opt) }}" class="editor-option bg-white"></div>
                                <input type="hidden" name="option_{{ strtolower($opt) }}"
                                    id="input-option-{{ strtolower($opt) }}">
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mb-8">
                <label class="block text-gray-700 text-sm font-bold mb-2">Pembahasan Jawaban (Opsional)</label>
                <div id="editor-explanation" class="bg-white"></div>
                <input type="hidden" name="explanation" id="explanation">
            </div>

            <hr class="mb-6">

            <div class="flex justify-end gap-4">
                <button type="submit" name="action" value="save_and_close"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg shadow-lg transition-colors">
                    💾 Simpan & Kembali
                </button>
                <button type="submit" name="action" value="save_and_add"
                    class="bg-indigo-100 hover:bg-indigo-200 text-indigo-800 font-bold py-3 px-6 rounded-lg shadow-sm transition-colors border border-indigo-300">
                    🔄 Simpan & Tambah Soal Lain
                </button>
            </div>
        </form>
    </div>

    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script>
        // Konfigurasi Toolbar Umum (Lengkap)
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

        // Konfigurasi Toolbar Mini (Untuk Opsi A-E agar tidak kepenuhan)
        var miniToolbar = [
            ['bold', 'italic'],
            [{
                'script': 'sub'
            }, {
                'script': 'super'
            }],
            ['image', 'formula']
        ];

        var quillQuestion = new Quill('#editor-question', {
            theme: 'snow',
            placeholder: 'Ketik pertanyaan...',
            modules: {
                toolbar: fullToolbar
            }
        });
        var quillExplanation = new Quill('#editor-explanation', {
            theme: 'snow',
            placeholder: 'Ketik pembahasan...',
            modules: {
                toolbar: fullToolbar
            }
        });

        // Looping untuk membuat 5 Editor Opsi sekaligus
        var quillOptions = {};
        ['a', 'b', 'c', 'd', 'e'].forEach(function(opt) {
            quillOptions[opt] = new Quill('#editor-option-' + opt, {
                theme: 'snow',
                placeholder: 'Ketik atau sisipkan gambar opsi ' + opt.toUpperCase() + '...',
                modules: {
                    toolbar: miniToolbar
                }
            });
        });

        // Saat form disubmit
        var form = document.getElementById('form-soal');
        form.onsubmit = function() {
            var questionHTML = quillQuestion.root.innerHTML;

            if (questionHTML === '<p><br></p>' || questionHTML.trim() === '') {
                alert('Teks pertanyaan tidak boleh kosong!');
                return false;
            }

            var checkedRadio = document.querySelector('input[name="correct_answer"]:checked');
            var isCorrectOptionEmpty = false;

            // Masukkan data dari kelima Editor Opsi ke dalam input hidden
            ['a', 'b', 'c', 'd', 'e'].forEach(function(opt) {
                var optHTML = quillOptions[opt].root.innerHTML;
                if (optHTML === '<p><br></p>') optHTML = '';

                document.getElementById('input-option-' + opt).value = optHTML;

                // Cek apakah opsi yang dijadikan kunci itu kosong
                if (checkedRadio && checkedRadio.value.toLowerCase() === opt && optHTML.trim() === '') {
                    isCorrectOptionEmpty = true;
                }
            });

            if (!checkedRadio) {
                alert('Silakan pilih salah satu kunci jawaban terlebih dahulu!');
                return false;
            }

            if (isCorrectOptionEmpty) {
                alert('Gagal! Anda memilih Kunci Jawaban yang isinya (teks/gambar) masih kosong.');
                return false;
            }

            var explanationHTML = quillExplanation.root.innerHTML;
            document.getElementById('question_text').value = questionHTML;
            document.getElementById('explanation').value = explanationHTML === '<p><br></p>' ? '' : explanationHTML;
        };
    </script>
</body>

</html>
