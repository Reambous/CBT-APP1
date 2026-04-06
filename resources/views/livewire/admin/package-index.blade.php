<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\ExamPackage;
use App\Models\ExamCategory;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $categoryFilter = ''; // VARIABEL BARU: Untuk menyimpan ID kategori yang dipilih

    // VARIABEL BULK DELETE
    public $selected = [];
    public $selectAll = false;

    public function updatingSearch()
    {
        $this->resetPage();
        $this->selected = [];
        $this->selectAll = false;
    }

    // FUNGSI BARU: Reset halaman jika Admin mengganti filter dropdown
    public function updatingCategoryFilter()
    {
        $this->resetPage();
        $this->selected = [];
        $this->selectAll = false;
    }

    // FUNGSI SELECT ALL
    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selected = ExamPackage::when($this->categoryFilter, function ($q) {
                $q->where('exam_category_id', $this->categoryFilter);
            })
                ->where(function ($q) {
                    $q->where('title', 'like', '%' . $this->search . '%')->orWhereHas('examCategory', function ($sub) {
                        $sub->where('name', 'like', '%' . $this->search . '%');
                    });
                })
                ->pluck('id')
                ->map(fn($id) => (string) $id)
                ->toArray();
        } else {
            $this->selected = [];
        }
    }

    // FUNGSI HAPUS MASSAL
    public function deleteSelected()
    {
        if (count($this->selected) > 0) {
            ExamPackage::whereIn('id', $this->selected)->delete();
            $this->selected = [];
            $this->selectAll = false;
            session()->flash('success', 'Paket ujian terpilih berhasil dihapus.');
        }
    }

    public function delete($id)
    {
        ExamPackage::findOrFail($id)->delete();
        session()->flash('success', 'Paket ujian berhasil dihapus.');
    }

    public function with(): array
    {
        // LOGIKA FILTER: Gabungkan filter pencarian Teks dan Dropdown Kategori
        $packages = ExamPackage::with('examCategory')
            ->when($this->categoryFilter, function ($q) {
                // Jika dropdown dipilih, saring berdasarkan ID kategori
                $q->where('exam_category_id', $this->categoryFilter);
            })
            ->when($this->search, function ($q) {
                // Jika ada teks pencarian
                $q->where(function ($sub) {
                    $sub->where('title', 'like', '%' . $this->search . '%')->orWhereHas('examCategory', function ($c) {
                        $c->where('name', 'like', '%' . $this->search . '%');
                    });
                });
            })
            ->latest()
            ->paginate(10);

        return [
            'packages' => $packages,
            'categories' => ExamCategory::orderBy('name')->get(), // Ambil semua kategori untuk Dropdown
        ];
    }
}; ?>

<div class="bg-white rounded-xl shadow-sm border overflow-hidden">
    <div class="p-6 border-b flex justify-between items-center bg-gray-50 flex-wrap gap-4">

        <div class="flex items-center gap-4 w-full md:w-auto flex-wrap">
            <div class="relative w-full max-w-xs md:w-64">
                <span class="absolute left-3 top-2.5 text-gray-400">🔍</span>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama paket..."
                    class="w-full px-4 py-2 pl-10 border rounded-lg focus:ring-2 focus:ring-blue-200 outline-none shadow-sm">
            </div>

            <div class="relative w-full max-w-xs md:w-56">
                <span class="absolute left-3 top-2.5 text-gray-400">📁</span>
                <select wire:model.live="categoryFilter"
                    class="w-full px-4 py-2 pl-10 border rounded-lg focus:ring-2 focus:ring-blue-200 outline-none appearance-none shadow-sm bg-white cursor-pointer">
                    <option value="">-- Semua Kategori --</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z" />
                    </svg>
                </div>
            </div>

            <div wire:loading class="text-blue-500 text-sm font-semibold animate-pulse">⏳ Memuat...</div>
        </div>

        @if (count($selected) > 0)
            <button wire:click="deleteSelected"
                wire:confirm="PERINGATAN! Anda yakin ingin menghapus {{ count($selected) }} paket ujian beserta semua soal di dalamnya?"
                class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded-lg shadow-lg transition-colors whitespace-nowrap">
                🗑️ Hapus Terpilih ({{ count($selected) }})
            </button>
        @endif
    </div>



    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-white">
                <tr>
                    <th class="px-6 py-4 w-10 text-center">
                        <input type="checkbox" wire:model.live="selectAll"
                            class="w-5 h-5 text-blue-600 border-gray-300 rounded cursor-pointer focus:ring-blue-500">
                    </th>
                    <th class="px-6 py-4 text-left font-bold text-gray-500 uppercase text-xs w-16">No</th>
                    <th class="px-6 py-4 text-left font-bold text-gray-500 uppercase text-xs">Nama Paket</th>
                    <th class="px-6 py-4 text-left font-bold text-gray-500 uppercase text-xs">Kategori</th>
                    <th class="px-6 py-4 text-left font-bold text-gray-500 uppercase text-xs">Durasi</th>
                    <th class="px-6 py-4 text-right font-bold text-gray-500 uppercase text-xs">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($packages as $index => $package)
                    <tr
                        class="transition-colors {{ in_array($package->id, $selected) ? 'bg-blue-50' : 'hover:bg-gray-50' }}">

                        <td class="px-6 py-4 text-center">
                            <input type="checkbox" wire:model.live="selected" value="{{ $package->id }}"
                                class="w-5 h-5 text-blue-600 border-gray-300 rounded cursor-pointer focus:ring-blue-500">
                        </td>

                        <td class="px-6 py-4 text-sm text-gray-500">{{ $packages->firstItem() + $index }}</td>
                        <td class="px-6 py-4 font-bold text-gray-900">{{ $package->title }}</td>
                        <td class="px-6 py-4">
                            <span
                                class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-semibold border border-blue-200">{{ $package->examCategory?->name ?? 'Kategori Dihapus' }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm font-medium">⏱️ {{ $package->time_limit }} Menit</td>

                        <td class="px-6 py-4 text-right text-sm flex justify-end gap-2">
                            <a href="{{ route('admin.packages.show', $package->id) }}"
                                class="bg-indigo-100 text-indigo-700 hover:bg-indigo-200 px-3 py-1 rounded transition-colors font-bold shadow-sm border border-indigo-200">
                                ⚙️ Kelola Soal
                            </a>
                            <a href="{{ route('admin.packages.edit', $package->id) }}"
                                class="bg-yellow-100 text-yellow-700 hover:bg-yellow-200 px-3 py-1 rounded transition-colors font-bold border border-yellow-200">Edit</a>
                            <button wire:click="delete({{ $package->id }})" wire:confirm="Hapus paket ini?"
                                class="bg-red-100 text-red-700 hover:bg-red-200 px-3 py-1 rounded transition-colors font-bold border border-red-200">Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="text-4xl mb-3">📭</div>
                            <p class="text-gray-500 font-medium">Tidak ada paket ujian yang ditemukan.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-4 border-t bg-gray-50">{{ $packages->links() }}</div>
</div>
