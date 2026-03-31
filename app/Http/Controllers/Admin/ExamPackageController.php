<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamPackage;
use App\Models\ExamCategory; // Wajib dipanggil karena kita butuh data kategori
use Illuminate\Http\Request;

class ExamPackageController extends Controller
{
    // 1. Tampilkan Daftar Paket Soal
    // Jangan lupa pastikan 'use Illuminate\Http\Request;' ada di bagian atas file

    public function index()
    {
        return view('admin.packages.index'); // Hapus query pencariannya
    }

    // 2. Tampilkan Form Tambah
    public function create()
    {
        // Ambil semua kategori untuk ditampilkan di dalam <select> dropdown
        $categories = ExamCategory::all();
        return view('admin.packages.create', compact('categories'));
    }

    // 3. Simpan Data ke Database
    public function store(Request $request)
    {
        $request->validate([
            'exam_category_id' => 'required|exists:exam_categories,id', // Pastikan ID kategori valid
            'title' => 'required|string|max:255',
            'time_limit' => 'required|integer|min:1', // Waktu minimal 1 menit
        ]);

        ExamPackage::create([
            'exam_category_id' => $request->exam_category_id,
            'title' => $request->title,
            'time_limit' => $request->time_limit,
        ]);

        return redirect()->route('admin.packages.index')->with('success', 'Paket soal berhasil ditambahkan!');
    }

    public function show(string $id)
    {
        // Cari paket berdasarkan ID, sekalian ambil data kategori dan soal-soalnya
        $package = ExamPackage::with(['examCategory', 'questions'])->findOrFail($id);

        // Buka halaman Ruang Kelola Soal
        return view('admin.packages.show', compact('package'));
    }
    // 4. Tampilkan Form Edit
    public function edit(ExamPackage $package)
    {
        $categories = ExamCategory::all(); // Butuh data kategori untuk dropdown
        return view('admin.packages.edit', compact('package', 'categories'));
    }

    // 5. Update Data di Database
    public function update(Request $request, ExamPackage $package)
    {
        $request->validate([
            'exam_category_id' => 'required|exists:exam_categories,id',
            'title' => 'required|string|max:255',
            'time_limit' => 'required|integer|min:1',
        ]);

        $package->update([
            'exam_category_id' => $request->exam_category_id,
            'title' => $request->title,
            'time_limit' => $request->time_limit,
        ]);

        return redirect()->route('admin.packages.index')->with('success', 'Paket soal berhasil diperbarui!');
    }

    // 6. Hapus Data (Soft Delete)
    public function destroy(ExamPackage $package)
    {
        $package->delete();
        return redirect()->route('admin.packages.index')->with('success', 'Paket soal berhasil dihapus!');
    }
}
