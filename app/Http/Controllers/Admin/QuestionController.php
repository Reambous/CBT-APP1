<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\ExamPackage;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    // Method index biarkan kosong atau arahkan ke Livewire/Volt yang sudah kita buat
    public function index()
    {
        return view('admin.questions.index');
    }

    public function create(Request $request)
    {
        // Tangkap package_id dari URL (jika ada)
        $selectedPackageId = $request->query('package_id');

        $packages = ExamPackage::latest()->get();
        return view('admin.questions.create', compact('packages', 'selectedPackageId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'exam_package_id' => 'required|exists:exam_packages,id',
            'question_text' => 'required|string',
            'option_a' => 'nullable|string',
            'option_b' => 'nullable|string',
            'option_c' => 'nullable|string',
            'option_d' => 'nullable|string',
            'option_e' => 'nullable|string',
            'correct_answer' => 'required|in:A,B,C,D,E',
            'explanation' => 'nullable|string',
        ]);

        Question::create($validated);

        // Cek tombol mana yang ditekan oleh Admin
        if ($request->input('action') === 'save_and_add') {
            // Jika "Simpan & Tambah Lagi", kembalikan ke form kosong dengan paket yang sama
            return redirect()->route('admin.questions.create', ['package_id' => $validated['exam_package_id']])
                ->with('success', 'Soal sebelumnya berhasil disimpan! Silakan ketik soal berikutnya.');
        }

        // Jika "Simpan & Kembali", arahkan kembali ke Ruang Kelola Soal Paket tersebut
        return redirect()->route('admin.packages.show', $validated['exam_package_id'])
            ->with('success', 'Soal berhasil ditambahkan ke dalam paket!');
    }

    public function edit(string $id)
    {
        // Cari soal yang mau diedit
        $question = Question::findOrFail($id);

        // Ambil data paket untuk dropdown
        $packages = ExamPackage::latest()->get();

        // Buka halaman form edit
        return view('admin.questions.edit', compact('question', 'packages'));
    }

    public function update(Request $request, string $id)
    {
        // Validasi data baru
        $validated = $request->validate([
            'exam_package_id' => 'required|exists:exam_packages,id',
            'question_text' => 'required|string',
            'option_a' => 'nullable|string',
            'option_b' => 'nullable|string',
            'option_c' => 'nullable|string',
            'option_d' => 'nullable|string',
            'option_e' => 'nullable|string',
            'correct_answer' => 'required|in:A,B,C,D,E',
            'explanation' => 'nullable|string',
        ]);

        // Cari soal lama dan perbarui dengan data baru
        $question = Question::findOrFail($id);
        $question->update($validated);

        // Kembalikan ke Ruang Kelola Soal paket tersebut
        return redirect()->route('admin.packages.show', $question->exam_package_id)
            ->with('success', 'Perubahan soal berhasil disimpan!');
    }
}
