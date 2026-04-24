<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\ExamPackage;
use App\Models\ExamCategory;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $categoryFilter = '';
    public $tierFilter = '';

    public $selected = [];
    public $selectAll = false;

    public function updatingSearch()
    {
        $this->resetPage();
        $this->selected = [];
        $this->selectAll = false;
    }
    public function updatingCategoryFilter()
    {
        $this->resetPage();
        $this->selected = [];
        $this->selectAll = false;
    }
    public function updatingTierFilter()
    {
        $this->resetPage();
        $this->selected = [];
        $this->selectAll = false;
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selected = ExamPackage::when($this->categoryFilter, fn($q) => $q->where('exam_category_id', $this->categoryFilter))
                ->when($this->tierFilter, fn($q) => $q->where('minimum_tier', $this->tierFilter))
                ->where(function ($q) {
                    $q->where('title', 'like', '%' . $this->search . '%')->orWhereHas('examCategory', fn($sub) => $sub->where('name', 'like', '%' . $this->search . '%'));
                })
                ->pluck('id')
                ->map(fn($id) => (string) $id)
                ->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function deleteSelected()
    {
        if (count($this->selected) > 0) {
            ExamPackage::whereIn('id', $this->selected)->delete();
            $this->selected = [];
            $this->selectAll = false;
            session()->flash('success', 'Paket ujian terpilih berhasil dihapus.');
        }
    }

    // 👇 FITUR BARU: PUBLISH & DRAFT MASSAL 👇
    public function publishSelected()
    {
        if (count($this->selected) > 0) {
            ExamPackage::whereIn('id', $this->selected)->update(['is_published' => true]);
            $this->selected = [];
            $this->selectAll = false;
            session()->flash('success', '✨ Paket ujian terpilih berhasil di-Publish (Ditampilkan).');
        }
    }

    public function draftSelected()
    {
        if (count($this->selected) > 0) {
            ExamPackage::whereIn('id', $this->selected)->update(['is_published' => false]);
            $this->selected = [];
            $this->selectAll = false;
            session()->flash('success', '📁 Paket ujian terpilih berhasil di-Draft (Disembunyikan).');
        }
    }
    // 👆 BATAS FITUR BARU 👆

    public function delete($id)
    {
        ExamPackage::findOrFail($id)->delete();
        session()->flash('success', 'Paket ujian berhasil dihapus.');
    }

    // 👇 FITUR BARU: UBAH STATUS PUBLISH 👇
    public function togglePublish($id)
    {
        $package = ExamPackage::findOrFail($id);
        // Ubah nilainya (kalau true jadi false, kalau false jadi true)
        $package->is_published = !$package->is_published;
        $package->save();

        $status = $package->is_published ? 'di-Publish (Tampil ke User)' : 'di-Draft (Disembunyikan)';
        session()->flash('success', "Status paket '{$package->title}' berhasil diubah menjadi {$status}.");
    }
    // 👆 BATAS FITUR BARU 👆

    public function with(): array
    {
        $packages = ExamPackage::with('examCategory')
            ->withCount('questions')
            ->when($this->categoryFilter, fn($q) => $q->where('exam_category_id', $this->categoryFilter))
            ->when($this->tierFilter, fn($q) => $q->where('minimum_tier', $this->tierFilter))
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('title', 'like', '%' . $this->search . '%')->orWhereHas('examCategory', fn($c) => $c->where('name', 'like', '%' . $this->search . '%'));
                });
            })
            ->latest()
            ->paginate(10);

        return [
            'packages' => $packages,
            'categories' => ExamCategory::orderBy('name')->get(),
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

            <div class="relative w-full max-w-xs md:w-48">
                <span class="absolute left-3 top-2.5 text-gray-400">📁</span>
                <select wire:model.live="categoryFilter"
                    class="w-full px-4 py-2 pl-10 border rounded-lg focus:ring-2 focus:ring-blue-200 outline-none appearance-none shadow-sm bg-white cursor-pointer font-medium text-gray-700 text-sm">
                    <option value="">-- Semua Kategori --</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="relative w-full max-w-xs md:w-40">
                <span class="absolute left-3 top-2.5 text-gray-400">🏷️</span>
                <select wire:model.live="tierFilter"
                    class="w-full px-4 py-2 pl-10 border rounded-lg focus:ring-2 focus:ring-blue-200 outline-none appearance-none shadow-sm bg-white cursor-pointer font-medium text-gray-700 text-sm">
                    <option value="">Semua Paket</option>
                    <option value="gratis">🆓 Gratis</option>
                    <option value="plus">✨ Plus</option>
                    <option value="pro">👑 Pro</option>
                    <option value="ultra">🔮 Ultra</option>
                </select>
            </div>
            <div wire:loading class="text-blue-500 text-sm font-semibold animate-pulse">⏳ Memuat...</div>
        </div>

        <a href="{{ route('admin.packages.create') }}"
            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-lg shadow-sm transition-colors whitespace-nowrap text-sm mt-4 md:mt-0">
            + Tambah Paket
        </a>

        @if (count($selected) > 0)
            <div class="flex items-center gap-2 mt-4 md:mt-0 bg-white p-1.5 rounded-lg border shadow-sm">
                <span class="text-xs font-bold text-gray-500 px-2">{{ count($selected) }} Terpilih:</span>

                <button wire:click="publishSelected" wire:loading.attr="disabled"
                    class="bg-green-100 hover:bg-green-600 text-green-700 hover:text-white font-bold py-1.5 px-3 rounded text-xs transition-colors shadow-sm">
                    ✅ Publish
                </button>

                <button wire:click="draftSelected" wire:loading.attr="disabled"
                    class="bg-gray-100 hover:bg-gray-600 text-gray-700 hover:text-white font-bold py-1.5 px-3 rounded text-xs transition-colors shadow-sm">
                    📁 Draft
                </button>

                <div class="w-[1px] h-5 bg-gray-300 mx-1"></div> <button wire:click="deleteSelected"
                    wire:confirm="PERINGATAN! Anda yakin ingin menghapus {{ count($selected) }} paket ini beserta soalnya?"
                    class="bg-red-100 hover:bg-red-600 text-red-700 hover:text-white font-bold py-1.5 px-3 rounded text-xs transition-colors shadow-sm">
                    🗑️ Hapus
                </button>
            </div>
        @endif
    </div>

    @if (session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 m-4 rounded font-medium shadow-sm">
            ✅ {{ session('success') }}
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-white">
                <tr>
                    <th class="px-6 py-4 w-10 text-center"><input type="checkbox" wire:model.live="selectAll"
                            class="w-5 h-5 text-blue-600 border-gray-300 rounded cursor-pointer"></th>
                    <th class="px-6 py-4 text-left font-bold text-gray-500 uppercase text-xs w-16">No</th>
                    <th class="px-6 py-4 text-left font-bold text-gray-500 uppercase text-xs">Nama Paket</th>
                    <th class="px-6 py-4 text-left font-bold text-gray-500 uppercase text-xs">Kategori</th>
                    <th class="px-6 py-4 text-center font-bold text-gray-500 uppercase text-xs">Paket</th>
                    <th class="px-6 py-4 text-left font-bold text-gray-500 uppercase text-xs">Durasi</th>
                    <th class="px-6 py-4 text-center font-bold text-gray-500 uppercase text-xs">Jumlah Soal</th>

                    <th class="px-6 py-4 text-center font-bold text-gray-500 uppercase text-xs">Status</th>

                    <th class="px-6 py-4 text-right font-bold text-gray-500 uppercase text-xs">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($packages as $index => $package)
                    <tr
                        class="transition-colors {{ in_array($package->id, $selected) ? 'bg-blue-50' : 'hover:bg-gray-50' }}">
                        <td class="px-6 py-4 text-center"><input type="checkbox" wire:model.live="selected"
                                value="{{ $package->id }}"
                                class="w-5 h-5 text-blue-600 border-gray-300 rounded cursor-pointer"></td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $packages->firstItem() + $index }}</td>
                        <td class="px-6 py-4 font-bold text-gray-900">{{ $package->title }}</td>
                        <td class="px-6 py-4"><span
                                class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-[10px] font-bold border border-blue-200 whitespace-nowrap uppercase">{{ $package->examCategory?->name ?? 'Kategori Dihapus' }}</span>
                        </td>

                        <td class="px-6 py-4 text-center">
                            @if ($package->minimum_tier == 'ultra')
                                <span
                                    class="bg-purple-100 text-purple-800 px-2 py-0.5 rounded-full text-[10px] font-bold border border-purple-200 whitespace-nowrap">🔮
                                    Ultra</span>
                            @elseif ($package->minimum_tier == 'pro')
                                <span
                                    class="bg-yellow-100 text-yellow-800 px-2 py-0.5 rounded-full text-[10px] font-bold border border-yellow-200 whitespace-nowrap">👑
                                    Pro</span>
                            @elseif ($package->minimum_tier == 'plus')
                                <span
                                    class="bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full text-[10px] font-bold border border-blue-200 whitespace-nowrap">✨
                                    Plus</span>
                            @else
                                <span
                                    class="bg-gray-100 text-gray-800 px-2 py-0.5 rounded-full text-[10px] font-bold border border-gray-200 whitespace-nowrap">🆓
                                    Gratis</span>
                            @endif
                        </td>

                        <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">⏱️ {{ $package->time_limit }} Menit
                        </td>
                        <td
                            class="px-6 py-4 text-center text-sm font-bold {{ $package->questions_count > 0 ? 'text-blue-600' : 'text-gray-400' }}">
                            {{ $package->questions_count }}</td>

                        <td class="px-6 py-4 text-center">
                            <button wire:click="togglePublish({{ $package->id }})"
                                class="relative inline-flex items-center h-6 w-11 rounded-full transition-colors focus:outline-none shadow-inner {{ $package->is_published ? 'bg-green-500' : 'bg-gray-300' }}"
                                title="{{ $package->is_published ? 'Klik untuk Sembunyikan' : 'Klik untuk Publish' }}">
                                <span
                                    class="inline-block w-4 h-4 transform bg-white rounded-full transition-transform {{ $package->is_published ? 'translate-x-6' : 'translate-x-1' }} shadow-md"></span>
                            </button>
                            <div
                                class="text-[9px] mt-1 font-black tracking-wider uppercase {{ $package->is_published ? 'text-green-600' : 'text-gray-400' }}">
                                {{ $package->is_published ? 'PUBLISHED' : 'DRAFT' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right flex justify-end gap-1.5">
                            <a href="{{ route('admin.packages.show', $package->id) }}" title="Kelola Soal"
                                class="bg-indigo-50 text-indigo-700 hover:bg-indigo-100 p-1.5 rounded border border-indigo-200 text-xs shadow-sm">⚙️</a>
                            <a href="{{ route('admin.packages.edit', $package->id) }}" title="Edit"
                                class="bg-yellow-50 text-yellow-700 hover:bg-yellow-100 p-1.5 rounded border border-yellow-200 text-xs shadow-sm">✏️</a>
                            <button wire:click="delete({{ $package->id }})"
                                wire:confirm="Yakin ingin menghapus paket ini?" title="Hapus"
                                class="bg-red-50 text-red-700 hover:bg-red-100 p-1.5 rounded border border-red-200 text-xs shadow-sm">🗑️</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center">
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
