<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Question;

new class extends Component {
    use WithPagination;

    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function delete($id)
    {
        Question::findOrFail($id)->delete();
        session()->flash('success', 'Soal berhasil dihapus.');
    }

    public function with(): array
    {
        return [
            'questions' => Question::with('examPackage')
                ->where('question_text', 'like', '%' . $this->search . '%')
                ->orWhereHas('examPackage', function ($q) {
                    $q->where('title', 'like', '%' . $this->search . '%');
                })
                ->latest()
                ->paginate(10),
        ];
    }
}; ?>

<div class="bg-white p-6 rounded-xl shadow-sm border">

    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <h2 class="text-2xl font-bold text-gray-800">Bank Soal Ujian</h2>

        <div class="flex items-center gap-4 w-full md:w-auto">
            <div class="relative w-full md:w-72">
                <span class="absolute left-3 top-2.5 text-gray-400">🔍</span>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari teks soal atau paket..."
                    class="w-full px-4 py-2 pl-10 border rounded-lg focus:ring-2 focus:ring-blue-200 outline-none transition-all">
            </div>

            <a href="{{ route('admin.questions.create') }}"
                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow whitespace-nowrap transition-colors">
                + Tambah Soal
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div wire:loading class="text-blue-500 text-sm font-semibold animate-pulse mb-4">
        ⏳ Memproses pencarian...
    </div>

    <div class="overflow-x-auto border rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-4 text-left font-bold text-gray-500 uppercase text-xs">Paket Ujian</th>
                    <th class="px-6 py-4 text-left font-bold text-gray-500 uppercase text-xs">Pertanyaan</th>
                    <th class="px-6 py-4 text-center font-bold text-gray-500 uppercase text-xs">Kunci</th>
                    <th class="px-6 py-4 text-right font-bold text-gray-500 uppercase text-xs">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($questions as $q)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="bg-indigo-100 text-indigo-800 px-2 py-1 rounded text-xs font-semibold">
                                {{ $q->examPackage->title }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-gray-800 line-clamp-2">
                                {{ Str::limit(strip_tags($q->question_text), 80) }}</p>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="font-bold text-green-600">{{ $q->correct_answer }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="#" class="text-yellow-600 hover:text-yellow-900 mr-3">Edit</a>
                            <button wire:click="delete({{ $q->id }})"
                                wire:confirm="Apakah Anda yakin ingin menghapus soal ini?"
                                class="text-red-600 hover:text-red-900">
                                Hapus
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                            Belum ada data soal ujian.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $questions->links() }}
    </div>
</div>
