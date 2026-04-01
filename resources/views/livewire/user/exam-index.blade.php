<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\ExamPackage;
use App\Models\ExamCategory;
use App\Models\UserResult;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $selectedCategory = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }
    public function updatingSelectedCategory()
    {
        $this->resetPage();
    }

    public function with(): array
    {
        $userId = Auth::id();

        $packages = ExamPackage::with('examCategory')
            ->withCount('questions')
            // KUNCI PERBAIKAN: Hanya tampilkan paket yang memiliki minimal 1 soal
            ->has('questions')
            ->whereHas('examCategory', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->when($this->selectedCategory, function ($query) {
                $query->where('exam_category_id', $this->selectedCategory);
            })
            ->where(function ($query) {
                $query->where('title', 'like', '%' . $this->search . '%')->orWhereHas('examCategory', function ($q) {
                    $q->whereNull('deleted_at')->where('name', 'like', '%' . $this->search . '%');
                });
            })
            ->latest()
            ->paginate(10);

        $packageIds = $packages->pluck('id');

        $allUserResults = UserResult::where('user_id', $userId)->whereIn('exam_package_id', $packageIds)->whereNotNull('finished_at')->latest('finished_at')->get()->groupBy('exam_package_id');

        return [
            'packages' => $packages,
            'userResults' => $allUserResults,
            'categories' => ExamCategory::all(),
        ];
    }
}; ?>

<div>
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Katalog Ujian</h2>
            <p class="text-gray-500 mt-1">Pilih dan kerjakan paket ujian untuk mengasah kemampuanmu.</p>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
            <select wire:model.live="selectedCategory"
                class="px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-200 outline-none shadow-sm bg-white text-gray-700 font-medium">
                <option value="">Semua Kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>

            <div class="relative w-full sm:w-64">
                <span class="absolute left-3 top-2.5 text-gray-400">🔍</span>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama paket..."
                    class="w-full px-4 py-2 pl-10 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-200 outline-none shadow-sm">
            </div>
        </div>
    </div>

    <div wire:loading class="w-full mb-4 text-center text-blue-500 text-sm font-bold animate-pulse">
        ⏳ Memuat data...
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="divide-y divide-gray-100">
            @forelse($packages as $package)
                @php $recentScores = $userResults->get($package->id, collect())->take(3); @endphp
                <div
                    class="p-5 md:p-6 hover:bg-blue-50/50 transition-colors flex flex-col md:flex-row items-start md:items-center justify-between gap-6">

                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2 flex-wrap">
                            <span
                                class="bg-blue-100 text-blue-700 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">
                                {{ $package->examCategory?->name ?? 'Tanpa Kategori' }}
                            </span>
                            <span class="text-gray-500 text-xs font-bold flex items-center gap-1">
                                ⏱️ {{ $package->time_limit }} Menit
                            </span>
                            <span class="text-gray-500 text-xs font-bold flex items-center gap-1">
                                📝 {{ $package->questions_count }} Soal
                            </span>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 line-clamp-1">{{ $package->title }}</h3>
                    </div>

                    <div class="w-full md:w-auto flex flex-col items-start md:items-center">
                        @if ($recentScores->count() > 0)
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Nilai Terakhir:
                            </p>
                            <div class="flex gap-1">
                                @foreach ($recentScores as $score)
                                    <span
                                        class="px-2 py-1 text-xs font-black rounded text-center min-w-[36px] {{ $score->score >= 70 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}"
                                        title="Selesai pada: {{ \Carbon\Carbon::parse($score->finished_at)->format('d M Y') }}">
                                        {{ number_format($score->score, 0) }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <p
                                class="text-xs font-medium text-gray-400 italic bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-100">
                                Belum dikerjakan</p>
                        @endif
                    </div>

                    <div class="w-full md:w-auto flex-shrink-0">
                        <form action="{{ route('exam.start', $package->id) }}" method="POST" class="m-0 p-0 w-full">
                            @csrf
                            <button type="submit"
                                class="w-full md:w-36 text-center {{ $recentScores->count() > 0 ? 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200' : 'bg-blue-600 text-white hover:bg-blue-700' }} font-bold py-2 px-4 rounded-lg transition-colors shadow-sm text-sm">
                                {{ $recentScores->count() > 0 ? '🔄 Ulangi' : '🚀 Mulai' }}
                            </button>
                        </form>
                    </div>

                </div>
            @empty
                <div class="p-10 text-center">
                    <span class="text-4xl mb-3 block">📭</span>
                    <h3 class="text-lg font-bold text-gray-800">Paket ujian tidak ditemukan</h3>
                    <p class="text-gray-500 text-sm mt-1">Coba kata kunci pencarian yang lain.</p>
                </div>
            @endforelse
        </div>
    </div>

    @if ($packages->hasPages())
        <div class="mt-8">{{ $packages->links() }}</div>
    @endif
</div>
