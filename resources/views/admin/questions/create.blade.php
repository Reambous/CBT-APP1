@extends('admin.layouts.sidebar')

@section('content')
    <div class="bg-gray-100 min-h-screen w-full overflow-hidden">
        <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

        <style>
            /* --- CSS Custom Switch --- */
            .simple-switch {
                position: relative;
                display: inline-block;
                width: 36px;
                height: 18px;
            }

            .simple-switch input {
                opacity: 0;
                width: 0;
                height: 0;
            }

            .slider {
                position: absolute;
                cursor: pointer;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: #ccc;
                transition: .4s;
                border-radius: 20px;
            }

            .slider:before {
                position: absolute;
                content: "";
                height: 12px;
                width: 12px;
                left: 3px;
                bottom: 3px;
                background-color: white;
                transition: .4s;
                border-radius: 50%;
            }

            input:checked+.slider {
                background-color: #2563eb;
            }

            input:checked+.slider:before {
                transform: translateX(18px);
            }

            /* --- Perbaikan Masalah Teks Melebar --- */
            .ql-editor {
                word-break: break-word !important;
                overflow-wrap: break-word !important;
                white-space: normal !important;
            }

            /* Memaksa editor tidak melebar keluar batas */
            .editor-container-fixed {
                max-width: 100%;
                display: block;
                overflow: hidden;
            }

            /* --- UI COMPACT --- */
            .ql-toolbar.ql-snow {
                padding: 2px 4px !important;
                border-top-left-radius: 6px;
                border-top-right-radius: 6px;
            }

            .ql-container.ql-snow {
                border-bottom-left-radius: 6px;
                border-bottom-right-radius: 6px;
                font-size: 13px;
            }

            .compact-label {
                font-size: 10px;
                font-weight: 800;
                color: #6b7280;
                margin-bottom: 2px;
                display: block;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
        </style>

        <form action="{{ route('admin.questions.store') }}" method="POST" enctype="multipart/form-data" id="form-soal"
            class="flex flex-col h-screen">
            @csrf

            <div class="bg-white border-b px-4 py-1.5 flex items-center justify-between shadow-sm z-20">
                <div class="flex items-center gap-3">
                    <h1 class="text-lg font-black text-gray-800 tracking-tighter">CREATE QUESTION</h1>
                    <div class="h-5 w-[1px] bg-gray-300"></div>
                    <p class="text-[11px] font-bold text-blue-600 truncate max-w-[200px]">
                        @foreach ($packages as $p)
                            @if ($p->id == $selectedPackageId)
                                {{ $p->title }}
                            @endif
                        @endforeach
                    </p>
                </div>

                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2 bg-blue-50 px-2 py-0.5 rounded border border-blue-200">
                        <span class="text-[10px] font-black text-blue-400 uppercase">Order:</span>
                        <input type="number" name="order_num" value="{{ session('next_order') ?? $lastOrder + 1 }}"
                            class="w-10 bg-transparent border-none p-0 text-center font-black text-blue-700 outline-none focus:ring-0 text-sm">
                    </div>
                    <a href="{{ route('admin.packages.show', $selectedPackageId) }}"
                        class="text-gray-400 hover:text-red-500 font-bold text-[11px]">✕ CANCEL</a>
                </div>
            </div>

            <div class="flex-1 overflow-hidden flex bg-gray-100">

                <div class="w-full h-full p-3 grid grid-cols-12 gap-3 overflow-y-auto">

                    @if (session('success'))
                        <div class="col-span-12 bg-green-500 text-white px-3 py-1.5 rounded text-xs font-bold shadow-sm">✅
                            {{ session('success') }}</div>
                    @endif

                    <input type="hidden" name="exam_package_id" value="{{ $selectedPackageId }}">

                    <div class="col-span-12 lg:col-span-7 space-y-3">
                        <div class="bg-white p-3 rounded shadow-sm border editor-container-fixed">
                            <label class="compact-label">PERTANYAAN</label>
                            <div id="editor-question" style="height: 220px;"></div>
                            <input type="hidden" name="question_text" id="question_text">
                        </div>

                        <div class="bg-white p-3 rounded shadow-sm border editor-container-fixed">
                            <label class="compact-label">PEMBAHASAN (OPSIONAL)</label>
                            <div id="editor-explanation" style="height: 120px;"></div>
                            <input type="hidden" name="explanation" id="explanation">
                        </div>
                    </div>

                    <div class="col-span-12 lg:col-span-5 h-full">
                        <div class="bg-white p-3 rounded shadow-sm border h-full flex flex-col">
                            <div class="flex justify-between items-center mb-2 border-b pb-1.5">
                                <label class="compact-label !mb-0">PILIHAN JAWABAN</label>
                                <div class="flex items-center gap-2">
                                    <span class="text-[9px] font-black text-gray-400 uppercase">IMAGE MODE</span>
                                    <label class="simple-switch">
                                        <input type="checkbox" name="is_answer_image" id="toggle-image" value="1">
                                        <span class="slider"></span>
                                    </label>
                                </div>
                            </div>

                            <div class="space-y-1.5 flex-1">
                                @foreach (['A', 'B', 'C', 'D', 'E'] as $opt)
                                    <div
                                        class="flex items-start gap-2 p-1.5 rounded bg-gray-50 border border-gray-100 hover:bg-white transition-all shadow-sm">
                                        <div class="flex flex-col items-center pt-1">
                                            <input type="radio" name="correct_answer" value="{{ $opt }}"
                                                class="w-4 h-4 text-blue-600 focus:ring-0" required>
                                            <span
                                                class="text-[10px] font-black mt-0.5 text-gray-500">{{ $opt }}</span>
                                        </div>

                                        <div class="flex-1 editor-container-fixed">
                                            <div id="text-wrapper-{{ strtolower($opt) }}" class="mode-text">
                                                <div id="editor-option-{{ strtolower($opt) }}" style="height: 70px;"></div>
                                                <input type="hidden" name="option_{{ strtolower($opt) }}"
                                                    id="input-option-{{ strtolower($opt) }}">
                                            </div>

                                            <div id="image-wrapper-{{ strtolower($opt) }}"
                                                class="mode-image hidden py-2 space-y-1">

                                                @if (isset($question) && $question->is_answer_image && $question->$field)
                                                    <div class="p-1 border rounded bg-white flex items-center gap-2 mb-1">
                                                        @php
                                                            $path = $question->$field;
                                                            $url =
                                                                str_starts_with($path, 'http') ||
                                                                str_starts_with($path, '/storage')
                                                                    ? asset($path)
                                                                    : asset('storage/' . $path);
                                                        @endphp
                                                        <img src="{{ $url }}"
                                                            class="h-10 w-10 object-contain rounded border shadow-inner">
                                                        <span class="text-[9px] font-bold text-gray-400">Gambar Saat
                                                            Ini.</span>
                                                    </div>
                                                @endif

                                                <div id="preview-box-{{ strtolower($opt) }}"
                                                    class="hidden p-1 border border-green-300 bg-green-50 rounded flex items-center gap-2 mb-1 transition-all">
                                                    <img id="preview-img-{{ strtolower($opt) }}" src=""
                                                        onclick="openModal(this.src)"
                                                        class="h-10 w-10 object-contain rounded shadow-sm cursor-pointer hover:opacity-80 transition-opacity"
                                                        title="Klik untuk perbesar">
                                                    <span class="text-[9px] font-black text-green-600">Preview File
                                                        Baru</span>
                                                </div>

                                                <input type="file" name="image_{{ strtolower($opt) }}"
                                                    onchange="previewImage(this, '{{ strtolower($opt) }}')"
                                                    class="text-[10px] w-full file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-[10px] file:font-bold file:bg-blue-600 file:text-white"
                                                    accept="image/*">
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white border-t px-4 py-2.5 flex justify-end gap-3 z-20">
                <button type="submit" name="action" value="save_and_continue"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-black py-2 px-8 rounded text-xs shadow transition-transform active:scale-95">
                    SAVE & CONTINUE ➔
                </button>
                <button type="submit" name="action" value="save_and_close"
                    class="bg-gray-800 hover:bg-black text-white font-black py-2 px-8 rounded text-xs shadow transition-transform active:scale-95">
                    SAVE & CLOSE
                </button>
            </div>
        </form>
        <div id="imageModal"
            class="fixed inset-0 z-[99] hidden bg-black/80 flex items-center justify-center p-4 backdrop-blur-sm transition-opacity"
            onclick="closeModal()">
            <span
                class="absolute top-4 right-6 text-white text-4xl font-bold cursor-pointer hover:text-red-500 transition-colors">&times;</span>
            <img id="modalImage" src=""
                class="max-w-full max-h-[90vh] object-contain rounded-lg shadow-2xl scale-95 transition-transform duration-300">
        </div>
    </div>

    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script>
        var toolbarOptions = [
            ['bold', 'italic', 'underline'],
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
            ['image', 'formula'],
            ['clean']
        ];

        var quillQuestion = new Quill('#editor-question', {
            theme: 'snow',
            modules: {
                toolbar: toolbarOptions
            }
        });
        var quillExplanation = new Quill('#editor-explanation', {
            theme: 'snow',
            modules: {
                toolbar: toolbarOptions
            }
        });

        var quillOptions = {};
        ['a', 'b', 'c', 'd', 'e'].forEach(function(opt) {
            quillOptions[opt] = new Quill('#editor-option-' + opt, {
                theme: 'snow',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'formula']
                    ]
                }
            });
        });

        const toggle = document.getElementById('toggle-image');
        toggle.addEventListener('change', function() {
            document.querySelectorAll('.mode-text').forEach(el => el.classList.toggle('hidden', this.checked));
            document.querySelectorAll('.mode-image').forEach(el => el.classList.toggle('hidden', !this.checked));
        });

        document.getElementById('form-soal').onsubmit = function() {
            document.getElementById('question_text').value = quillQuestion.root.innerHTML;
            document.getElementById('explanation').value = quillExplanation.root.innerHTML === '<p><br></p>' ? '' :
                quillExplanation.root.innerHTML;
            if (!toggle.checked) {
                ['a', 'b', 'c', 'd', 'e'].forEach(function(opt) {
                    let html = quillOptions[opt].root.innerHTML;
                    document.getElementById('input-option-' + opt).value = html === '<p><br></p>' ? '' : html;
                });
            }
        };
        // 👇 FITUR LIVE PREVIEW GAMBAR 👇
        function previewImage(input, optId) {
            const previewBox = document.getElementById('preview-box-' + optId);
            const previewImg = document.getElementById('preview-img-' + optId);

            // Cek apakah ada file yang dipilih
            if (input.files && input.files[0]) {
                const reader = new FileReader();

                // Saat file selesai dibaca, masukkan ke dalam tag <img src="...">
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    previewBox.classList.remove('hidden'); // Munculkan kotak hijau
                };

                reader.readAsDataURL(input.files[0]);
            } else {
                // Jika admin batal memilih file
                previewImg.src = '';
                previewBox.classList.add('hidden'); // Sembunyikan kotak hijau
            }
        }

        // 👇 FITUR ZOOM GAMBAR (MODAL) 👇
        function openModal(imgSrc) {
            if (!imgSrc) return; // Kalau tidak ada gambar, batalkan
            const modal = document.getElementById('imageModal');
            const modalImg = document.getElementById('modalImage');

            modalImg.src = imgSrc;
            modal.classList.remove('hidden');

            // Animasi membesar (scale up)
            setTimeout(() => {
                modalImg.classList.remove('scale-95');
            }, 10);
        }

        function closeModal() {
            const modal = document.getElementById('imageModal');
            const modalImg = document.getElementById('modalImage');

            // Animasi mengecil sebelum hilang
            modalImg.classList.add('scale-95');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 200);
        }
    </script>
@endsection
