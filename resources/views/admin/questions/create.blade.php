<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Soal - CBT ADMIN</title>
    <script src="https://cdn.tailwindcss.com"></script>

    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

    <style>
        /* Penyesuaian tinggi editor agar nyaman diketik */
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
                    <span class="text-xs text-gray-500 italic">Pilih bulatan biru untuk menandai Kunci Jawaban.
                        Kosongkan opsi jika tidak dibutuhkan.</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach (['A', 'B', 'C', 'D', 'E'] as $opt)
                        <div class="flex items-start gap-3 bg-white p-3 rounded border shadow-sm">
                            <div class="flex items-center h-full pt-2">
                                <input type="radio" name="correct_answer" value="{{ $opt }}"
                                    class="w-5 h-5 text-blue-600 focus:ring-blue-500" required>
                            </div>
                            <div class="w-full">
                                <label class="text-xs font-bold text-gray-500 mb-1 block">Opsi
                                    {{ $opt }}</label>
                                <textarea name="option_{{ strtolower($opt) }}" rows="2"
                                    class="w-full px-3 py-2 border rounded focus:border-blue-500 outline-none"
                                    placeholder="Ketik jawaban {{ $opt }}..."></textarea>
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
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script>
        // Inisialisasi Quill untuk Soal
        var quillQuestion = new Quill('#editor-question', {
            theme: 'snow',
            placeholder: 'Ketik pertanyaan di sini...',
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

        // Inisialisasi Quill untuk Pembahasan
        var quillExplanation = new Quill('#editor-explanation', {
            theme: 'snow',
            placeholder: 'Ketik pembahasan (opsional)...'
        });

        // Saat form disubmit, validasi dan pindahkan isi
        var form = document.getElementById('form-soal');
        form.onsubmit = function() {
            var questionHTML = quillQuestion.root.innerHTML;
            var explanationHTML = quillExplanation.root.innerHTML;

            // 1. Validasi manual jika teks soal kosong
            if (questionHTML === '<p><br></p>' || questionHTML.trim() === '') {
                alert('Teks pertanyaan tidak boleh kosong!');
                return false;
            }

            // 2. VALIDASI KUNCI JAWABAN (Mencegah Kunci Kosong)
            var checkedRadio = document.querySelector('input[name="correct_answer"]:checked');

            // Pastikan ada radio button yang dipilih (meskipun di HTML sudah 'required')
            if (checkedRadio) {
                var selectedOption = checkedRadio.value; // Mendapatkan 'A', 'B', 'C', dll.
                // Cari textarea yang sesuai dengan radio button yang dipilih
                var textareaName = 'option_' + selectedOption.toLowerCase();
                var optionText = document.querySelector('textarea[name="' + textareaName + '"]').value;

                // Jika textarea-nya kosong, hentikan form!
                if (optionText.trim() === '') {
                    alert('Gagal! Anda memilih Opsi ' + selectedOption +
                        ' sebagai Kunci Jawaban, tapi kotak teks Opsi ' + selectedOption + ' masih KOSONG.');
                    return false; // Membatalkan pengiriman form
                }
            } else {
                alert('Silakan pilih salah satu kunci jawaban terlebih dahulu!');
                return false;
            }

            // Jika semua aman, masukkan data ke input hidden
            document.getElementById('question_text').value = questionHTML;
            document.getElementById('explanation').value = explanationHTML === '<p><br></p>' ? '' : explanationHTML;
        };
    </script>

</body>

</html>
