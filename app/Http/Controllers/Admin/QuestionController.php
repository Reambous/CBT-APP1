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
        return redirect()->route('admin.packages.index');
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
        $isImage = $request->has('is_answer_image');
        $options = ['a', 'b', 'c', 'd', 'e'];
        $data = $request->all();

        if ($isImage) {
            foreach ($options as $opt) {
                if ($request->hasFile("image_$opt")) {
                    // Simpan gambar ke folder storage/public/options
                    $path = $request->file("image_$opt")->store('options', 'public');
                    $data["option_$opt"] = $path;
                }
            }
        }

        // Set status is_answer_image ke database
        $data['is_answer_image'] = $isImage;

        \App\Models\Question::create($data);

        \Illuminate\Support\Facades\Cache::forget('questions_package_' . $request->exam_package_id);

        return redirect()->route('admin.packages.show', $request->exam_package_id)
            ->with('success', 'Soal berhasil disimpan!');
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

    public function update(Request $request, $id)
    {
        $question = \App\Models\Question::findOrFail($id);
        $isImage = $request->has('is_answer_image');
        $options = ['a', 'b', 'c', 'd', 'e'];

        $data = $request->all(); // Ambil semua data request
        $data['is_answer_image'] = $isImage;

        if ($isImage) {
            foreach ($options as $opt) {
                // Jika admin upload gambar baru, timpa yang lama
                if ($request->hasFile("image_$opt")) {
                    $path = $request->file("image_$opt")->store('options', 'public');
                    $data["option_$opt"] = $path;
                } else {
                    // Jika tidak upload baru, tetap gunakan path gambar yang lama
                    $data["option_$opt"] = $question->{"option_$opt"};
                }
            }
        }

        $question->update($data);

        \Illuminate\Support\Facades\Cache::forget('questions_package_' . $question->exam_package_id);

        return redirect()->route('admin.packages.show', $question->exam_package_id)
            ->with('success', 'Soal berhasil diperbarui!');
    }

    public function destroy(string $id)
    {
        // Cari soalnya
        $question = Question::findOrFail($id);

        // Simpan ID paketnya dulu sebelum soalnya dihapus (untuk arah kembali)
        $packageId = $question->exam_package_id;

        // Hapus soal dari database
        $question->delete();

        \Illuminate\Support\Facades\Cache::forget('questions_package_' . $packageId);

        // Kembalikan Admin ke Ruang Kelola Soal paket tersebut
        return redirect()->route('admin.packages.show', $packageId)
            ->with('success', 'Satu soal berhasil dihapus dari paket!');
    }
}
