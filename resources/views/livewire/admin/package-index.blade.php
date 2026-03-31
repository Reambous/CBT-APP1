<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\ExamPackage;

new class extends Component {
    use WithPagination;
    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function delete($id)
    {
        ExamPackage::findOrFail($id)->delete();
        session()->flash('success', 'Paket ujian berhasil dihapus.');
    }

    public function with(): array
    {
        return [
            'packages' => ExamPackage::with('examCategory')
                ->where('title', 'like', '%' . $this->search . '%')
                ->orWhereHas('examCategory', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                })
                ->latest()
                ->paginate(10),
        ];
    }
}; ?>

<div class="bg-white rounded-xl shadow-sm border overflow-hidden">
    <div class="p-6 border-b flex justify-between items-center bg-gray-50">
        <div class="relative w-full max-w-md">
            <span class="absolute left-3 top-2.5 text-gray-400">🔍</span>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama paket atau kategori..."
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
                    <th class="px-6 py-4 text-left font-bold text-gray-500 uppercase text-xs">Nama Paket</th>
                    <th class="px-6 py-4 text-left font-bold text-gray-500 uppercase text-xs">Kategori</th>
                    <th class="px-6 py-4 text-left font-bold text-gray-500 uppercase text-xs">Durasi</th>
                    <th class="px-6 py-4 text-right font-bold text-gray-500 uppercase text-xs">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($packages as $index => $package)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $packages->firstItem() + $index }}</td>
                        <td class="px-6 py-4 font-bold text-gray-900">{{ $package->title }}</td>
                        <td class="px-6 py-4"><span
                                class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-semibold">{{ $package->examCategory->name }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm font-medium">⏱️ {{ $package->time_limit }} Menit</td>
                        <td class="px-6 py-4 text-right text-sm">
                            <a href="{{ route('admin.packages.edit', $package->id) }}"
                                class="bg-yellow-100 text-yellow-700 hover:bg-yellow-200 px-3 py-1 rounded transition-colors mr-2">Edit</a>
                            <button wire:click="delete({{ $package->id }})" wire:confirm="Hapus paket ini?"
                                class="bg-red-100 text-red-700 hover:bg-red-200 px-3 py-1 rounded transition-colors">Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">Paket tidak ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-4 border-t bg-gray-50">{{ $packages->links() }}</div>
</div>
