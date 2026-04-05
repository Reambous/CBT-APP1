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

        <form action="{{ route('admin.questions.store') }}" method="POST" id="form-soal" enctype="multipart/form-data"
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
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <label class="text-gray-700 text-sm font-bold">Pilihan Jawaban</label>
                        <p class="text-xs text-gray-500 mt-1">Tentukan apakah jawaban berupa Teks atau Gambar.</p>
                    </div>

                    <label class="inline-flex items-center cursor-pointer">
                        <span class="mr-3 text-sm font-medium text-gray-700">Mode Gambar</span>
                        <input type="checkbox" name="is_answer_image" id="toggle-image" class="sr-only peer"
                            value="1">
                        <div
                            class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                        </div>
                    </label>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach (['A', 'B', 'C', 'D', 'E'] as $opt)
                        <div class="flex items-start gap-3 bg-white p-4 rounded-lg border shadow-sm">
                            <div class="flex items-center h-full pt-2">
                                <input type="radio" name="correct_answer" value="{{ $opt }}"
                                    class="w-5 h-5 text-blue-600 focus:ring-blue-500" required>
                            </div>
                            <div class="w-full">
                                <label class="text-xs font-bold text-gray-500 mb-1 block">Opsi
                                    {{ $opt }}</label>

                                <div id="text-wrapper-{{ strtolower($opt) }}" class="mode-text">
                                    <textarea name="option_{{ strtolower($opt) }}" rows="2"
                                        class="w-full px-3 py-2 border rounded focus:border-blue-500 outline-none" placeholder="Ketik teks jawaban..."></textarea>
                                </div>

                                <div id="image-wrapper-{{ strtolower($opt) }}" class="mode-image hidden">
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

        // Konfigurasi Toolbar Mini (Untuk Opsi A-E)
        var miniToolbar = [
            ['bold', 'italic'],
            [{
                'script': 'sub'
            }, {
                'script': 'super'
            }],
            ['formula'] // Tombol image dihilangkan di sini karena sudah ada mode gambar khusus
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

        // Looping untuk membuat 5 Editor Opsi sekaligus (Mode Teks)
        var quillOptions = {};
        ['a', 'b', 'c', 'd', 'e'].forEach(function(opt) {
            quillOptions[opt] = new Quill('#editor-option-' + opt, {
                theme: 'snow',
                placeholder: 'Ketik teks opsi ' + opt.toUpperCase() + '...',
                modules: {
                    toolbar: miniToolbar
                }
            });
        });

        // --- LOGIKA TOGGLE (GANTI MODE) ---
        const toggle = document.getElementById('toggle-image');
        const modeTextElements = document.querySelectorAll('.mode-text');
        const modeImageElements = document.querySelectorAll('.mode-image');

        toggle.addEventListener('change', function() {
            if (this.checked) {
                // Mode Gambar Aktif: Sembunyikan Quill, Tampilkan Input File
                modeTextElements.forEach(el => el.classList.add('hidden'));
                modeImageElements.forEach(el => el.classList.remove('hidden'));
            } else {
                // Mode Teks Aktif: Tampilkan Quill, Sembunyikan Input File
                modeTextElements.forEach(el => el.classList.remove('hidden'));
                modeImageElements.forEach(el => el.classList.add('hidden'));
            }
        });

        // --- LOGIKA SAAT TOMBOL SIMPAN DIKLIK ---
        var form = document.getElementById('form-soal');
        form.onsubmit = function() {
            var questionHTML = quillQuestion.root.innerHTML;

            // Validasi Soal Kosong
            if (questionHTML === '<p><br></p>' || questionHTML.trim() === '') {
                alert('Teks pertanyaan tidak boleh kosong!');
                return false;
            }

            // Validasi Kunci Jawaban Belum Dipilih
            var checkedRadio = document.querySelector('input[name="correct_answer"]:checked');
            if (!checkedRadio) {
                alert('Silakan pilih salah satu kunci jawaban terlebih dahulu!');
                return false;
            }

            var selectedOption = checkedRadio.value.toLowerCase();
            var isImageMode = toggle.checked;

            if (isImageMode) {
                // 1. VALIDASI JIKA SEDANG MODE GAMBAR
                var fileInput = document.querySelector('input[name="image_' + selectedOption + '"]');
                if (!fileInput || fileInput.files.length === 0) {
                    alert('Gagal! Anda menjadikan Opsi ' + checkedRadio.value +
                        ' sebagai Kunci Jawaban, tapi Anda belum meng-upload gambarnya.');
                    return false;
                }
            } else {
                // 2. VALIDASI JIKA SEDANG MODE TEKS
                var isCorrectOptionEmpty = false;
                ['a', 'b', 'c', 'd', 'e'].forEach(function(opt) {
                    var optHTML = quillOptions[opt].root.innerHTML;
                    if (optHTML === '<p><br></p>') optHTML = '';

                    document.getElementById('input-option-' + opt).value = optHTML;

                    // Cek apakah opsi yang dijadikan kunci itu kosong
                    if (opt === selectedOption && optHTML.trim() === '') {
                        isCorrectOptionEmpty = true;
                    }
                });

                if (isCorrectOptionEmpty) {
                    alert('Gagal! Anda menjadikan Opsi ' + checkedRadio.value +
                        ' sebagai Kunci Jawaban, tapi kotak teksnya masih kosong.');
                    return false;
                }
            }

            // Pindahkan isi editor ke input hidden
            var explanationHTML = quillExplanation.root.innerHTML;
            document.getElementById('question_text').value = questionHTML;
            document.getElementById('explanation').value = explanationHTML === '<p><br></p>' ? '' : explanationHTML;
        };
    </script>
</body>

</html>
