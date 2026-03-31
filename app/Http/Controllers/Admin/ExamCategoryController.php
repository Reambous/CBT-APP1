<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamCategory;
use Illuminate\Http\Request;

class ExamCategoryController extends Controller
{
    // 1. Tampilkan Halaman Daftar Kategori
    public function index()
    {
        return view('admin.categories.index'); // Hapus query pencariannya
    }

    // 2. Tampilkan Form Tambah Kategori
    public function create()
    {
        return view('admin.categories.create');
    }

    // 3. Proses Simpan Data ke Database
    public function store(Request $request)
    {
        // Validasi ketat (wajib diisi, unik, maksimal 255 karakter)
        $request->validate([
            'name' => 'required|string|max:255|unique:exam_categories,name'
        ]);

        ExamCategory::create([
            'name' => $request->name
        ]);

        // Redirect dengan membawa pesan sukses (Flash Message)
        return redirect()->route('admin.categories.index')->with('success', 'Kategori ujian berhasil ditambahkan!');
    }

    // 4. Tampilkan Form Edit Kategori
    public function edit(ExamCategory $category) // Route Model Binding (Standar Pro)
    {
        return view('admin.categories.edit', compact('category'));
    }

    // 5. Proses Update Data
    public function update(Request $request, ExamCategory $category)
    {
        $request->validate([
            // Pengecualian unique ID agar saat disave ulang namanya sendiri tidak dianggap duplikat
            'name' => 'required|string|max:255|unique:exam_categories,name,' . $category->id
        ]);

        $category->update([
            'name' => $request->name
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Kategori ujian berhasil diperbarui!');
    }

    // 6. Proses Hapus Data (Soft Delete)
    public function destroy(ExamCategory $category)
    {
        $category->delete(); // Ini otomatis menjadi Soft Delete karena model kita pakai softDeletes()
        return redirect()->route('admin.categories.index')->with('success', 'Kategori ujian berhasil dihapus!');
    }
}
