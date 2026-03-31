<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\ExamCategory;

new class extends Component {
    use WithPagination;
    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function delete($id)
    {
        ExamCategory::findOrFail($id)->delete();
        session()->flash('success', 'Kategori ujian berhasil dihapus.');
    }

    public function with(): array
    {
        return [
            'categories' => ExamCategory::where('name', 'like', '%' . $this->search . '%')
                ->latest()
                ->paginate(10),
        ];
    }
}; ?>

<div class="bg-white rounded-xl shadow-sm border overflow-hidden">
    <div class="p-6 border-b flex justify-between items-center bg-gray-50">
        <div class="relative w-full max-w-md">
            <span class="absolute left-3 top-2.5 text-gray-400">🔍</span>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama kategori..."
                class="w-full px-4 py-2 pl-10 border rounded-lg focus:ring-2 focus:ring-blue-200 outline-none">
        </div>
        <div wire:loading class="text-blue-500 text-sm font-semibold animate-pulse">⏳ Mencari...</div>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 m-4 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-white">
                <tr>
                    <th class="px-6 py-4 text-left font-bold text-gray-500 uppercase text-xs w-16">No</th>
                    <th class="px-6 py-4 text-left font-bold text-gray-500 uppercase text-xs">Nama Kategori</th>
                    <th class="px-6 py-4 text-right font-bold text-gray-500 uppercase text-xs">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($categories as $index => $category)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $categories->firstItem() + $index }}</td>
                        <td class="px-6 py-4 font-bold text-gray-900">{{ $category->name }}</td>
                        <td class="px-6 py-4 text-right text-sm">
                            <a href="{{ route('admin.categories.edit', $category->id) }}"
                                class="bg-yellow-100 text-yellow-700 hover:bg-yellow-200 px-3 py-1 rounded transition-colors mr-2">Edit</a>
                            <button wire:click="delete({{ $category->id }})" wire:confirm="Hapus kategori ini?"
                                class="bg-red-100 text-red-700 hover:bg-red-200 px-3 py-1 rounded transition-colors">Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-8 text-center text-gray-500">Kategori tidak ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-4 border-t bg-gray-50">{{ $categories->links() }}</div>
</div>
