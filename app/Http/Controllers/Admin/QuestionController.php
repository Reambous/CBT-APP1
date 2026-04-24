<?php

namespace App\Models; // Hanya memastikan model terpanggil
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\ExamPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class QuestionController extends Controller
{
    public function index()
    {
        return redirect()->route('admin.packages.index');
    }

    public function create(Request $request)
    {
        $selectedPackageId = $request->query('package_id');
        $packages = ExamPackage::latest()->get();

        // Ambil nomor urut terakhir untuk saran urutan di form
        $lastOrder = 0;
        if ($selectedPackageId) {
            $lastOrder = Question::where('exam_package_id', $selectedPackageId)->max('order_num') ?? 0;
        }

        return view('admin.questions.create', compact('packages', 'selectedPackageId', 'lastOrder'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'exam_package_id' => 'required|exists:exam_packages,id',
            'order_num'       => 'required|integer|min:1',
            'question_text'   => 'required',
            'correct_answer'  => 'required',
        ]);

        try {
            // Gunakan variabel lokal agar tidak ada drama "use" di closure
            $packageId = $request->exam_package_id;
            $orderNum = $request->order_num;
            $isImage = $request->has('is_answer_image');
            $data = $request->only([
                'exam_package_id',
                'order_num',
                'question_text',
                'option_a',
                'option_b',
                'option_c',
                'option_d',
                'option_e',
                'correct_answer',
                'explanation'
            ]);
            $data['is_answer_image'] = $isImage;

            DB::transaction(function () use ($request, $packageId, $orderNum, $isImage, &$data) {

                // 1. PAKSA GESER: Semua soal yang nomornya SAMA atau LEBIH BESAR harus +1
                // Kita pakai urutan DESC (besar ke kecil) saat update agar tidak bentrok di database
                $affected = Question::where('exam_package_id', $packageId)
                    ->where('order_num', '>=', $orderNum)
                    ->orderBy('order_num', 'desc')
                    ->increment('order_num');

                // 2. Logika Upload Gambar Opsi (Tetap sama)
                if ($isImage) {
                    foreach (['a', 'b', 'c', 'd', 'e'] as $opt) {
                        if ($request->hasFile("image_$opt")) {
                            $path = $request->file("image_$opt")->store('options', 'public');
                            $data["option_$opt"] = '/storage/' . $path;
                        }
                    }
                }

                // 3. Simpan soal baru kamu sebagai nomor 2 yang "Asli"
                Question::create($data);
            });

            Cache::forget('questions_package_' . $packageId);

            if ($request->action == 'save_and_continue') {
                return back()->with([
                    'success' => 'Soal berhasil disimpan!',
                    'next_order' => (int) $orderNum + 1
                ]);
            }

            return redirect()->route('admin.packages.show', $packageId)
                ->with('success', 'Soal berhasil disimpan!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(string $id)
    {
        $question = Question::findOrFail($id);
        $packages = ExamPackage::latest()->get();
        return view('admin.questions.edit', compact('question', 'packages'));
    }

    public function update(Request $request, $id)
    {
        // 1. Validasi dulu biar aman
        $request->validate([
            'exam_package_id' => 'required|exists:exam_packages,id',
            'order_num'       => 'required|integer|min:1',
            'question_text'   => 'required',
            'correct_answer'  => 'required',
        ]);

        $question = Question::findOrFail($id);
        $oldOrder = $question->order_num;
        $newOrder = (int) $request->order_num;
        $packageId = $request->exam_package_id;

        try {
            DB::transaction(function () use ($request, $question, $oldOrder, $newOrder, $packageId) {

                // --- LOGIKA GESER OTOMATIS ---
                if ($oldOrder != $newOrder) {
                    // Jika nomor diubah ke angka yang LEBIH KECIL (Contoh: soal no 5 jadi no 2)
                    if ($newOrder < $oldOrder) {
                        Question::where('exam_package_id', $packageId)
                            ->whereBetween('order_num', [$newOrder, $oldOrder - 1])
                            ->orderBy('order_num', 'desc') // Geser dari bawah biar gak bentrok
                            ->increment('order_num');
                    }
                    // Jika nomor diubah ke angka yang LEBIH BESAR (Contoh: soal no 2 jadi no 5)
                    else {
                        Question::where('exam_package_id', $packageId)
                            ->whereBetween('order_num', [$oldOrder + 1, $newOrder])
                            ->orderBy('order_num', 'asc')
                            ->decrement('order_num');
                    }
                }

                // 2. Siapkan Data Update
                $isImage = $request->has('is_answer_image');
                $data = $request->all();
                $data['is_answer_image'] = $isImage;

                // Logika Gambar (Sudah diperbaiki format /storage/ nya)
                if ($isImage) {
                    foreach (['a', 'b', 'c', 'd', 'e'] as $opt) {
                        if ($request->hasFile("image_$opt")) {
                            $path = $request->file("image_$opt")->store('options', 'public');
                            $data["option_$opt"] = '/storage/' . $path;
                        } else {
                            // Tetap pakai gambar lama kalau gak upload baru
                            $data["option_$opt"] = $question->{"option_$opt"};
                        }
                    }
                } else {
                    // Jika pindah ke mode teks, pastikan kolom gambar tidak merusak tampilan
                    // (Opsi teks diambil otomatis dari $request->all() di atas)
                }

                // 3. Eksekusi Update
                $question->update($data);
            });

            // 4. Bersihkan Cache
            Cache::forget('questions_package_' . $packageId);

            return redirect()->route('admin.packages.show', $packageId)
                ->with('success', 'Soal berhasil diupdate & urutan dirapikan!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal update: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(string $id)
    {
        $question = Question::findOrFail($id);
        $packageId = $question->exam_package_id;
        $question->delete();

        Cache::forget('questions_package_' . $packageId);

        return redirect()->route('admin.packages.show', $packageId)
            ->with('success', 'Soal berhasil dihapus!');
    }
}
