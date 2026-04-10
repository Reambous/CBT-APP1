<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamPackage;
use App\Models\ExamCategory;
use Illuminate\Http\Request;

class ExamPackageController extends Controller
{
    // 1. Tampilkan Daftar Paket Soal
    public function index(Request $request)
    {
        $search = $request->input('search');
        $categoryId = $request->input('category_id');
        $tier = $request->input('minimum_tier'); // Berubah: pakai nama minimum_tier

        // PERBAIKAN BUG: Langsung panggil withCount di awal agar filter di bawahnya tidak tertimpa
        $query = ExamPackage::with('examCategory')->withCount('questions');

        // 1. Filter Pencarian Nama
        if ($search) {
            $query->where('title', 'like', '%' . $search . '%');
        }

        // 2. Filter Kategori
        if ($categoryId) {
            $query->where('exam_category_id', $categoryId);
        }

        // 3. Filter Kasta (Tier) Baru
        if ($tier) {
            $query->where('minimum_tier', $tier);
        }

        // Eksekusi data
        $packages = $query->latest()->paginate(10)->withQueryString();
        $categories = ExamCategory::orderBy('name')->get();

        // Mengirim $tier ke view menggantikan $type
        return view('admin.packages.index', compact('packages', 'categories', 'search', 'categoryId', 'tier'));
    }

    // 2. Tampilkan Form Tambah
    public function create()
    {
        $categories = ExamCategory::all();
        return view('admin.packages.create', compact('categories'));
    }

    // 3. Simpan Data ke Database
    public function store(Request $request)
    {
        $validated = $request->validate([
            'exam_category_id' => 'required|exists:exam_categories,id',
            'title' => 'required|string|max:255',
            'time_limit' => 'required|integer|min:1',
            'minimum_tier' => 'required|in:gratis,plus,pro,ultra',
        ]);

        // LANGSUNG SIMPAN: Tidak perlu lagi memanipulasi is_premium
        ExamPackage::create($validated);

        return redirect()->route('admin.packages.index')->with('success', 'Paket ujian berhasil ditambahkan.');
    }

    public function show(string $id)
    {
        $package = ExamPackage::with(['examCategory', 'questions'])->findOrFail($id);
        return view('admin.packages.show', compact('package'));
    }

    // 4. Tampilkan Form Edit
    public function edit(ExamPackage $package)
    {
        $categories = ExamCategory::all();
        return view('admin.packages.edit', compact('package', 'categories'));
    }

    // 5. Update Data di Database
    public function update(Request $request, $id)
    {
        $package = ExamPackage::findOrFail($id);

        $validated = $request->validate([
            'exam_category_id' => 'required|exists:exam_categories,id',
            'title' => 'required|string|max:255',
            'time_limit' => 'required|integer|min:1',
            'minimum_tier' => 'required|in:gratis,plus,pro,ultra',
        ]);

        // LANGSUNG UPDATE: Tidak perlu lagi memanipulasi is_premium
        $package->update($validated);

        return redirect()->route('admin.packages.index')->with('success', 'Paket ujian berhasil diperbarui.');
    }

    // 6. Hapus Data (Soft Delete)
    public function destroy(ExamPackage $package)
    {
        $package->delete();
        return redirect()->route('admin.packages.index')->with('success', 'Paket soal berhasil dihapus!');
    }
}
