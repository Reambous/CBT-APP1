<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use App\Models\Question;
use App\Models\ExamPackage;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\QuestionsImport;

new class extends Component {
    use WithPagination;
    use WithFileUploads;

    public ExamPackage $package;
    public $search = '';

    public $excelFile;
    public $selected = [];
    public $selectAll = false;

    public function updatingSearch()
    {
        $this->resetPage();
        $this->selected = [];
        $this->selectAll = false;
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selected = Question::where('exam_package_id', $this->package->id)
                ->where('question_text', 'like', '%' . $this->search . '%')
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
            Question::whereIn('id', $this->selected)->delete();
            $this->selected = [];
            $this->selectAll = false;
            session()->flash('success', 'Butir soal terpilih berhasil dihapus.');
        }
    }

    public function delete($id)
    {
        Question::findOrFail($id)->delete();
        session()->flash('success', 'Butir soal berhasil dihapus.');
    }

    public function importExcel()
    {
        $this->validate([
            'excelFile' => 'required|mimes:xlsx,xls,csv|max:5120',
        ]);

        try {
            ini_set('max_execution_time', 300);
            ini_set('memory_limit', '512M');
            Excel::import(new QuestionsImport($this->package->id), $this->excelFile);
            $this->excelFile = null;
            session()->flash('success', 'Ratusan soal berhasil di-import dari Excel! 🚀');
            $this->resetPage();
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan format Excel: ' . $e->getMessage());
            $this->excelFile = null;
        }
    }

    public function jumpToPageAndScroll($page, $elementId)
    {
        $this->gotoPage($page);
        $this->dispatch('scroll-to-soal', id: $elementId);
    }

    public function with(): array
    {
        $query = Question::where('exam_package_id', $this->package->id)
            ->where('question_text', 'like', '%' . $this->search . '%')
            ->orderBy('order_num', 'asc'); // Urutkan berdasarkan kolom order_num dari kecil ke besar

        return [
            'allQuestionIds' => (clone $query)->pluck('id'),
            // PERUBAHAN: Menjadi 20 soal per halaman
            'questions' => $query->paginate(20),
        ];
    }
}; ?>

<div class="bg-white rounded-xl shadow-sm border overflow-hidden">
    <div class="p-6 border-b flex justify-between items-center bg-gray-50 flex-wrap gap-4">

        <div class="flex items-center gap-4 w-full md:w-auto">
            <div class="relative w-full max-w-md md:w-80">
                <span class="absolute left-3 top-2.5 text-gray-400">🔍</span>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari potongan teks soal..."
                    class="w-full px-4 py-2 pl-10 border rounded-lg focus:ring-2 focus:ring-blue-200 outline-none shadow-sm">
            </div>
            <div wire:loading wire:target="search" class="text-blue-500 text-sm font-semibold animate-pulse">⏳ Mencari...
            </div>
        </div>

        <div class="flex gap-2 items-center flex-wrap">
            @if (count($selected) > 0)
                <button wire:click="deleteSelected"
                    wire:confirm="PERINGATAN! Anda yakin ingin menghapus {{ count($selected) }} soal terpilih secara permanen?"
                    class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg shadow-lg transition-colors animate-pulse text-sm">
                    🗑️ Hapus ({{ count($selected) }})
                </button>
            @endif

            <input type="file" wire:model="excelFile" id="upload-excel" class="hidden" accept=".xlsx, .xls, .csv">

            @if ($excelFile)
                <button wire:click="importExcel"
                    class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded-lg shadow-lg transition-colors animate-bounce flex items-center gap-2 text-sm">
                    🚀 Proses Import
                </button>
                <button wire:click="$set('excelFile', null)"
                    class="text-red-500 font-bold hover:underline text-sm">Batal</button>
            @else
                <button onclick="document.getElementById('upload-excel').click()"
                    class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg shadow transition-colors flex items-center gap-2 text-sm">
                    📊 Upload Excel
                </button>
            @endif


        </div>

    </div>

    @if (session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 m-4 rounded font-medium">
            <span class="font-bold">Gagal!</span> {{ session('error') }}
        </div>
    @endif
    @if (session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 m-4 rounded font-bold">
            ✅ {{ session('success') }}
        </div>
    @endif

    @if (count($allQuestionIds) > 0)
        <div class="p-4 bg-gray-50 border-b">
            <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                🧩 Peta Seluruh Soal ({{ count($allQuestionIds) }} Butir)
                <span class="normal-case font-medium text-gray-400 text-[10px]">(Klik nomor untuk melompat ke
                    halaman/soal)</span>
            </h4>

            <div class="flex flex-wrap gap-1.5 max-h-40 overflow-y-auto pr-2 scrollbar-thin">
                @foreach ($allQuestionIds as $index => $qId)
                    @php
                        // PERUBAHAN: Disesuaikan dengan batas 20 soal per halaman
                        $targetPage = floor($index / 20) + 1;
                    @endphp
                    <button type="button"
                        wire:click="jumpToPageAndScroll({{ $targetPage }}, 'soal-{{ $qId }}')"
                        class="w-7 h-7 flex items-center justify-center rounded border text-xs font-bold transition-all hover:opacity-80
                    {{ in_array($qId, $selected) ? 'bg-blue-600 border-blue-700 text-white shadow-inner' : 'bg-white border-gray-300 text-gray-600 hover:bg-blue-50 hover:border-blue-300 hover:text-blue-600' }}"
                        title="Ke Soal No. {{ $index + 1 }}">
                        {{ $index + 1 }}
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-white">
                <tr>
                    <th class="px-6 py-4 w-10 text-center">
                        <input type="checkbox" wire:model.live="selectAll"
                            class="w-5 h-5 text-blue-600 border-gray-300 rounded cursor-pointer focus:ring-blue-500">
                    </th>
                    <th class="px-6 py-4 text-left font-bold text-gray-500 uppercase text-xs w-16">No</th>
                    <th class="px-6 py-4 text-left font-bold text-gray-500 uppercase text-xs">Cuplikan Soal</th>
                    <th class="px-6 py-4 text-center font-bold text-gray-500 uppercase text-xs">Kunci</th>
                    <th class="px-6 py-4 text-right font-bold text-gray-500 uppercase text-xs w-44">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($questions as $index => $q)
                    <tr id="soal-{{ $q->id }}"
                        class="transition-colors scroll-mt-24 {{ in_array($q->id, $selected) ? 'bg-blue-50' : 'hover:bg-gray-50' }}">

                        <td class="px-6 py-4 text-center">
                            <input type="checkbox" wire:model.live="selected" value="{{ $q->id }}"
                                class="w-5 h-5 text-blue-600 border-gray-300 rounded cursor-pointer focus:ring-blue-500">
                        </td>
                        <td class="px-6 py-4 text-sm font-bold text-gray-600 text-center">
                            <span
                                class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs shadow-sm">{{ $q->order_num ?? 0 }}</span>
                        </td>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900 line-clamp-2">{!! Str::limit(strip_tags($q->question_text), 100) !!}</div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span
                                class="bg-green-100 text-green-800 font-bold px-3 py-1 rounded-full text-xs shadow-sm border border-green-200">{{ $q->correct_answer }}</span>
                        </td>
                        <td class="px-6 py-4 text-right text-sm">
                            <a href="{{ route('admin.questions.edit', $q->id) }}"
                                class="inline-block bg-yellow-100 text-yellow-700 hover:bg-yellow-200 px-3 py-1.5 rounded transition-colors mr-1 font-bold shadow-sm">Edit</a>
                            <button wire:click="delete({{ $q->id }})" wire:confirm="Hapus soal ini?"
                                class="inline-block bg-red-100 text-red-700 hover:bg-red-200 px-3 py-1.5 rounded transition-colors font-bold shadow-sm">Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="text-5xl mb-4">📭</div>
                            <p class="text-gray-800 font-bold text-lg">Belum ada soal di dalam paket ini.</p>
                            <p class="text-sm text-gray-500 mt-2">Gunakan tombol 'Upload Excel' atau 'Tambah Manual' di
                                pojok kanan atas.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-4 border-t bg-gray-50">{{ $questions->links() }}</div>
</div>

<script>
    document.addEventListener('scroll-to-soal', (event) => {
        setTimeout(() => {
            let targetId = event.detail.id;
            const element = document.getElementById(targetId);

            if (element) {
                element.classList.add('bg-indigo-50', 'ring-2', 'ring-indigo-400');

                element.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });

                setTimeout(() => {
                    element.classList.remove('bg-indigo-50', 'ring-2', 'ring-indigo-400');
                }, 2000);
            }
        }, 300);
    });
</script>
