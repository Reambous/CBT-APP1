<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\UserResult;
use App\Models\ExamCategory;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $selectedCategory = '';
    public $selectedTier = ''; // VARIABEL BARU UNTUK FILTER KASTA

    public function updatingSearch()
    {
        $this->resetPage();
    }
    public function updatingSelectedCategory()
    {
        $this->resetPage();
    }
    // RESET PAGE KETIKA FILTER KASTA BERUBAH
    public function updatingSelectedTier()
    {
        $this->resetPage();
    }

    public function with(): array
    {
        $user = Auth::user();

        // 1. Tentukan Kasta (Tier) apa saja yang boleh dilihat oleh user ini
        $allowedTiers = ['gratis']; // Default: Reguler hanya boleh lihat 'gratis'

        $isPremiumActive = $user->is_premium && $user->premium_until && now()->lessThanOrEqualTo($user->premium_until);

        if ($isPremiumActive) {
            if ($user->premium_tier === 'ultra') {
                $allowedTiers = ['gratis', 'plus', 'pro', 'ultra'];
            } elseif ($user->premium_tier === 'pro') {
                $allowedTiers = ['gratis', 'plus', 'pro'];
            } elseif ($user->premium_tier === 'plus') {
                $allowedTiers = ['gratis', 'plus'];
            }
        }

        // 2. Buat Query Dasar dengan Filter Kasta Hak Akses
        $query = UserResult::with(['examPackage.examCategory'])
            ->where('user_id', $user->id)
            ->whereNotNull('finished_at')
            ->whereHas('examPackage', function ($q) use ($allowedTiers) {
                // HANYA tampilkan riwayat dari paket ujian yang sesuai kasta user
                $q->whereIn('minimum_tier', $allowedTiers);
            });

        // 3. Filter Pencarian & Kategori
        if ($this->selectedCategory) {
            $query->whereHas('examPackage', function ($q) {
                $q->where('exam_category_id', $this->selectedCategory);
            });
        }

        // FILTER BARU: Filter Berdasarkan Kasta yang Dipilih User
        if ($this->selectedTier) {
            $query->whereHas('examPackage', function ($q) {
                $q->where('minimum_tier', $this->selectedTier);
            });
        }

        if ($this->search) {
            $query->whereHas('examPackage', function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')->orWhereHas('examCategory', function ($q2) {
                    $q2->where('name', 'like', '%' . $this->search . '%');
                });
            });
        }

        // 4. Hitung Statistik berdasarkan data yang BOLEH dia lihat saja
        $totalExams = (clone $query)->count();
        $averageScore = (clone $query)->avg('score') ?? 0;

        return [
            'histories' => $query->latest('finished_at')->paginate(10),
            'categories' => ExamCategory::all(),
            'totalExams' => $totalExams,
            'averageScore' => $averageScore,
            'isPremiumActive' => $isPremiumActive,
            'allowedTiers' => $allowedTiers, // Kirim allowedTiers ke view untuk dropdown
        ];
    }
}; ?>

<div>
    @if (auth()->user()->premium_tier !== 'ultra')
        <div
            class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-xl p-6 mb-6 text-white shadow-lg flex flex-col md:flex-row items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-bold flex items-center gap-2">
                    <span>🚀</span> Ingin akses penuh ke semua riwayat & pembahasan?
                </h3>
                <p class="text-blue-100 text-sm mt-1">Beberapa riwayat ujian kasta tinggi (Pro/Ultra) Anda mungkin
                    disembunyikan. Upgrade sekarang untuk membukanya!</p>
            </div>
            <a href="{{ route('user.upgrade') ?? '#' }}"
                class="bg-white text-blue-700 hover:bg-blue-50 font-black px-6 py-2.5 rounded-lg text-sm transition shadow-md whitespace-nowrap">
                👑 Upgrade Premium
            </a>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-t-4 border-t-blue-500">
            <h3 class="text-gray-500 text-sm font-bold uppercase tracking-wider mb-2">Diselesaikan (Sesuai Kasta)</h3>
            <p class="text-4xl font-extrabold text-gray-800 mt-2">{{ $totalExams }} <span
                    class="text-lg font-medium text-gray-500">paket</span></p>
        </div>
        <div
            class="bg-white p-6 rounded-xl shadow-sm border border-t-4 {{ $averageScore >= 70 ? 'border-t-green-500' : 'border-t-yellow-500' }}">
            <h3 class="text-gray-500 text-sm font-bold uppercase tracking-wider mb-2">Rata-rata Nilai</h3>
            <p class="text-4xl font-extrabold {{ $averageScore >= 70 ? 'text-green-600' : 'text-yellow-600' }} mt-2">
                {{ number_format($averageScore, 1) }} <span class="text-lg font-medium text-gray-500">/ 100</span></p>
        </div>
    </div>

    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center mb-4 gap-4">
        <h2 class="text-xl font-bold text-gray-800">📈 Daftar Rekam Jejak Ujian</h2>

        <div class="flex flex-col md:flex-row gap-3 w-full xl:w-auto">

            <select wire:model.live="selectedCategory"
                class="px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-200 outline-none shadow-sm bg-white text-gray-700 font-medium text-sm w-full md:w-auto">
                <option value="">Semua Kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>

            <select wire:model.live="selectedTier"
                class="px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-200 outline-none shadow-sm bg-white text-gray-700 font-medium text-sm w-full md:w-auto">
                <option value="">Semua Kasta</option>
                @if (in_array('gratis', $allowedTiers))
                    <option value="gratis">🆓 Gratis</option>
                @endif
                @if (in_array('plus', $allowedTiers))
                    <option value="plus">✨ Plus</option>
                @endif
                @if (in_array('pro', $allowedTiers))
                    <option value="pro">👑 Pro</option>
                @endif
                @if (in_array('ultra', $allowedTiers))
                    <option value="ultra">🔮 Ultra</option>
                @endif
            </select>

            <div class="relative w-full md:w-64 flex-shrink-0">
                <span class="absolute left-3 top-2.5 text-gray-400">🔍</span>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama ujian..."
                    class="w-full px-4 py-2 pl-10 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-200 outline-none shadow-sm text-sm">
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left font-bold text-gray-500 uppercase text-xs w-16">No</th>
                        <th class="px-6 py-4 text-left font-bold text-gray-500 uppercase text-xs">Informasi Ujian</th>
                        <th class="px-6 py-4 text-center font-bold text-gray-500 uppercase text-xs">Kasta</th>
                        <th class="px-6 py-4 text-center font-bold text-gray-500 uppercase text-xs">Waktu Selesai</th>
                        <th class="px-6 py-4 text-center font-bold text-gray-500 uppercase text-xs">Skor</th>
                        <th class="px-6 py-4 text-right font-bold text-gray-500 uppercase text-xs w-48">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($histories as $index => $history)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-sm text-gray-500 font-medium">
                                {{ $histories->firstItem() + $index }}</td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-900 mb-1 line-clamp-1">
                                    {{ $history->examPackage->title }}</div>
                                <div class="flex gap-2 items-center mt-1">
                                    <span
                                        class="bg-blue-50 text-blue-700 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">
                                        {{ $history->examPackage?->examCategory?->name ?? 'Kategori Terhapus' }}
                                    </span>
                                    <span
                                        class="bg-purple-50 text-purple-700 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider border border-purple-100">
                                        Percobaan ke-{{ $history->attempt_number ?? 1 }}
                                    </span>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-center">
                                @php $tier = $history->examPackage->minimum_tier; @endphp
                                @if ($tier == 'ultra')
                                    <span
                                        class="bg-purple-100 text-purple-800 px-2 py-1 rounded text-xs font-bold uppercase border border-purple-200">🔮
                                        Ultra</span>
                                @elseif($tier == 'pro')
                                    <span
                                        class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs font-bold uppercase border border-yellow-200">👑
                                        Pro</span>
                                @elseif($tier == 'plus')
                                    <span
                                        class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-bold uppercase border border-blue-200">✨
                                        Plus</span>
                                @else
                                    <span
                                        class="bg-gray-100 text-gray-600 px-2 py-1 rounded text-xs font-bold uppercase border border-gray-200">🆓
                                        Gratis</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-center">
                                <div class="text-sm font-semibold text-gray-700">
                                    {{ \Carbon\Carbon::parse($history->finished_at)->format('d M Y') }}</div>
                                <div class="text-xs text-gray-500">
                                    {{ \Carbon\Carbon::parse($history->finished_at)->format('H:i') }} WIB</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span
                                    class="text-2xl font-black {{ $history->score >= 70 ? 'text-green-600' : 'text-red-500' }}">
                                    {{ number_format($history->score, 1) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('user.review', $history->id) }}"
                                    class="inline-block bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold py-2 px-4 rounded-lg transition-colors border border-indigo-100 shadow-sm text-sm">
                                    🔍 Pembahasan
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <span class="text-4xl mb-3 block">📭</span>
                                <h3 class="text-lg font-bold text-gray-800">Riwayat Ujian Kosong</h3>
                                <p class="text-gray-500 text-sm mt-1">Anda belum mengerjakan ujian di tingkat langganan
                                    ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if (method_exists($histories, 'hasPages') && $histories->hasPages())
            <div class="p-4 border-t bg-gray-50">
                {{ $histories->links() }}
            </div>
        @endif
    </div>
</div>
