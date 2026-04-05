<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads; // FITUR UPLOAD
use Illuminate\Support\Str;
use App\Models\Question;
use App\Models\ExamPackage;
use Maatwebsite\Excel\Facades\Excel; // FITUR EXCEL
use App\Imports\QuestionsImport; // MESIN EXCEL KITA

new class extends Component {
    use WithPagination;
    use WithFileUploads;

    public ExamPackage $package;
    public $search = '';

    // VARIABEL EXCEL
    public $excelFile;

    // VARIABEL BULK DELETE
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

    // FUNGSI IMPORT EXCEL
    public function importExcel()
    {
        $this->validate([
            'excelFile' => 'required|mimes:xlsx,xls,csv|max:5120', // Maksimal 5MB
        ]);

        try {
            Excel::import(new QuestionsImport($this->package->id), $this->excelFile);
            $this->excelFile = null; // Kosongkan file setelah sukses
            session()->flash('success', 'Ratusan soal berhasil di-import dari Excel! 🚀');
            $this->resetPage();
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan format Excel: ' . $e->getMessage());
            $this->excelFile = null;
        }
    }

    public function with(): array
    {
        return [
            'questions' => Question::where('exam_package_id', $this->package->id)
                ->where('question_text', 'like', '%' . $this->search . '%')
                ->latest()
                ->paginate(10),
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

        <div class="flex gap-2 items-center">
            @if (count($selected) > 0)
                <button wire:click="deleteSelected"
                    wire:confirm="PERINGATAN! Anda yakin ingin menghapus {{ count($selected) }} soal terpilih secara permanen?"
                    class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded-lg shadow-lg transition-colors animate-pulse">
                    🗑️ Hapus Terpilih ({{ count($selected) }})
                </button>
            @endif

            <input type="file" wire:model="excelFile" id="upload-excel" class="hidden" accept=".xlsx, .xls, .csv">

            @if ($excelFile)
                <button wire:click="importExcel"
                    class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-6 rounded-lg shadow-lg transition-colors animate-bounce flex items-center gap-2">
                    🚀 Proses Import Data
                </button>
                <button wire:click="$set('excelFile', null)"
                    class="text-red-500 font-bold hover:underline">Batal</button>
            @else
                <button onclick="document.getElementById('upload-excel').click()"
                    class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-lg shadow transition-colors flex items-center gap-2">
                    📊 Upload Excel
                </button>
            @endif
        </div>

    </div>

    @if (session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 m-4 rounded">
            <span class="font-bold">Gagal!</span> {{ session('error') }}
        </div>
    @endif
    @if (session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 m-4 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="p-6 bg-gray-50 border-b">
        <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
            🧩 Peta Butir Soal <span class="normal-case font-medium text-gray-400">(Klik nomor untuk meluncur ke
                soal)</span>
        </h4>
        <div class="flex flex-wrap gap-2">
            @foreach ($questions as $index => $q)
                <button type="button" onclick="scrollToSoal('soal-{{ $q->id }}')"
                    class="w-10 h-10 flex items-center justify-center rounded-lg border bg-white text-sm font-bold text-gray-600 hover:bg-indigo-600 hover:text-white hover:border-indigo-600 transition-all shadow-sm">
                    {{ $questions->firstItem() + $index }}
                </button>
            @endforeach

            <a href="{{ route('admin.questions.create', ['package_id' => $package->id]) }}"
                class="w-10 h-10 flex items-center justify-center rounded-lg border border-dashed border-gray-400 text-gray-400 hover:bg-green-50 hover:text-green-600 hover:border-green-600 transition-all">
                ➕
            </a>
        </div>
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
                    <th class="px-6 py-4 text-left font-bold text-gray-500 uppercase text-xs">Cuplikan Soal</th>
                    <th class="px-6 py-4 text-center font-bold text-gray-500 uppercase text-xs">Kunci</th>
                    <th class="px-6 py-4 text-right font-bold text-gray-500 uppercase text-xs">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($questions as $index => $q)
                    <tr id="soal-{{ $q->id }}"
                        class="transition-colors scroll-mt-20 {{ in_array($q->id, $selected) ? 'bg-blue-50' : 'hover:bg-gray-50' }}">

                        <td class="px-6 py-4 text-center">
                            <input type="checkbox" wire:model.live="selected" value="{{ $q->id }}"
                                class="w-5 h-5 text-blue-600 border-gray-300 rounded cursor-pointer focus:ring-blue-500">
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $questions->firstItem() + $index }}</td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900 line-clamp-2">{!! Str::limit(strip_tags($q->question_text), 100) !!}</div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span
                                class="bg-green-100 text-green-800 font-bold px-3 py-1 rounded-full text-xs shadow-sm">{{ $q->correct_answer }}</span>
                        </td>
                        <td class="px-6 py-4 text-right text-sm">
                            <a href="{{ route('admin.questions.edit', $q->id) }}"
                                class="bg-yellow-100 text-yellow-700 hover:bg-yellow-200 px-3 py-1 rounded transition-colors mr-2 font-bold shadow-sm">Edit</a>
                            <button wire:click="delete({{ $q->id }})" wire:confirm="Hapus soal ini?"
                                class="bg-red-100 text-red-700 hover:bg-red-200 px-3 py-1 rounded transition-colors font-bold shadow-sm">Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="text-4xl mb-3">📭</div>
                            <p class="text-gray-500 font-medium text-lg">Belum ada soal di dalam paket ini.</p>
                            <p class="text-sm text-gray-400 mt-2">Gunakan tombol 'Upload Excel' untuk memasukkan ratusan
                                soal sekaligus.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-4 border-t bg-gray-50">{{ $questions->links() }}</div>
</div>

<script>
    function scrollToSoal(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            element.classList.add('bg-yellow-100');
            element.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
            setTimeout(() => {
                element.classList.remove('bg-yellow-100');
            }, 2000);
        }
    }
</script>
