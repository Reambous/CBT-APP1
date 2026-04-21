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
    public $selectedTier = '';
    public $selectedStatus = ''; // 1. FILTER BARU: Status Pengerjaan

    public function updatingSearch()
    {
        $this->resetPage();
    }
    public function updatingSelectedCategory()
    {
        $this->resetPage();
    }
    public function updatingSelectedTier()
    {
        $this->resetPage();
    }
    public function updatingSelectedStatus()
    {
        $this->resetPage();
    } // 2. Reset halaman saat filter status diubah

    public function with(): array
    {
        $user = Auth::user();

        // 3. Siapkan Query Dasar
        $query = ExamPackage::with('examCategory')
            ->withCount('questions')
            ->has('questions')
            ->whereHas('examCategory', function ($q) {
                $q->whereNull('deleted_at');
            })
            // FILTER KATEGORI
            ->when($this->selectedCategory, function ($q) {
                $q->where('exam_category_id', $this->selectedCategory);
            })
            // FILTER PAKET/TIER
            ->when($this->selectedTier, function ($q) {
                $q->where('minimum_tier', $this->selectedTier);
            })
            // 4. FILTER STATUS PENGERJAAN BARU
            ->when($this->selectedStatus, function ($q) use ($user) {
                if ($this->selectedStatus === 'finished') {
                    // Hanya tampilkan paket yang ID-nya ADA di tabel nilai milik user ini
                    $q->whereIn('id', function ($subQuery) use ($user) {
                        $subQuery->select('exam_package_id')->from('user_results')->where('user_id', $user->id)->whereNotNull('finished_at');
                    });
                } elseif ($this->selectedStatus === 'unfinished') {
                    // Hanya tampilkan paket yang ID-nya TIDAK ADA di tabel nilai milik user ini
                    $q->whereNotIn('id', function ($subQuery) use ($user) {
                        $subQuery->select('exam_package_id')->from('user_results')->where('user_id', $user->id)->whereNotNull('finished_at');
                    });
                }
            })
            // PENCARIAN TEKS
            ->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')->orWhereHas('examCategory', function ($subQ) {
                    $subQ->whereNull('deleted_at')->where('name', 'like', '%' . $this->search . '%');
                });
            });

        $packages = $query->latest()->paginate(10);
        $packageIds = $packages->pluck('id');

        $allUserResults = UserResult::where('user_id', $user->id)->whereIn('exam_package_id', $packageIds)->whereNotNull('finished_at')->latest('finished_at')->get()->groupBy('exam_package_id');

        return [
            'packages' => $packages,
            'userResults' => $allUserResults,
            'categories' => ExamCategory::all(),
        ];
    }
}; ?>

<div>
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-8 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Paket Tryout</h2>
            <p class="text-gray-500 mt-1">Pilih dan kerjakan paket ujian untuk mengasah kemampuanmu.</p>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
            <select wire:model.live="selectedCategory"
                class="px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-200 outline-none shadow-sm bg-white text-gray-700 font-medium w-full sm:w-auto">
                <option value="">Semua Kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>

            <select wire:model.live="selectedTier"
                class="px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-200 outline-none shadow-sm bg-white text-gray-700 font-medium w-full sm:w-auto">
                <option value="">Semua Paket</option>
                <option value="gratis">🆓 Gratis</option>
                <option value="plus">✨ Plus</option>
                <option value="pro">👑 Pro</option>
                <option value="ultra">🔮 Ultra</option>
            </select>

            <select wire:model.live="selectedStatus"
                class="px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-200 outline-none shadow-sm bg-white text-gray-700 font-medium w-full sm:w-auto">
                <option value="">Semua Status</option>
                <option value="unfinished">⏳ Belum Dikerjakan</option>
                <option value="finished">✅ Sudah Selesai</option>
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

                            @if ($package->minimum_tier == 'ultra')
                                <span
                                    class="bg-purple-100 text-purple-800 text-[10px] font-bold px-2 py-0.5 rounded-full border border-purple-200 uppercase tracking-wider">🔮
                                    Ultra</span>
                            @elseif ($package->minimum_tier == 'pro')
                                <span
                                    class="bg-yellow-100 text-yellow-800 text-[10px] font-bold px-2 py-0.5 rounded-full border border-yellow-200 uppercase tracking-wider">👑
                                    Pro</span>
                            @elseif ($package->minimum_tier == 'plus')
                                <span
                                    class="bg-blue-100 text-blue-800 text-[10px] font-bold px-2 py-0.5 rounded-full border border-blue-200 uppercase tracking-wider">✨
                                    Plus</span>
                            @else
                                <span
                                    class="bg-gray-100 text-gray-600 text-[10px] font-bold px-2 py-0.5 rounded-full border border-gray-200 uppercase tracking-wider">🆓
                                    Gratis</span>
                            @endif

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
                        @php
                            $isLocked = !auth()->user()->canAccessTier($package->minimum_tier);
                            $hasFinished = $recentScores->count() > 0;
                        @endphp

                        @if ($hasFinished)
                            {{-- PRIORITAS 1: Kalau sudah pernah selesai, langsung gembok permanen --}}
                            <button type="button" disabled
                                class="w-full md:w-36 inline-flex justify-center items-center bg-gray-200 text-gray-500 border border-transparent font-bold py-2 px-4 rounded-lg cursor-not-allowed shadow-sm text-sm gap-1">
                                ✅ Selesai
                            </button>
                        @elseif ($isLocked)
                            {{-- PRIORITAS 2: Kalau belum selesai, tapi kastanya tidak cukup --}}
                            <button type="button" onclick="showUpgradeModal('{{ $package->minimum_tier }}')"
                                class="w-full md:w-36 inline-flex justify-center items-center bg-gray-100 text-gray-500 hover:bg-yellow-50 hover:text-yellow-700 hover:border-yellow-300 border border-transparent font-bold py-2 px-4 rounded-lg transition-colors shadow-sm text-sm gap-1 cursor-pointer">
                                🔒 Terkunci
                            </button>
                        @else
                            {{-- PRIORITAS 3: Belum selesai dan kastanya cukup --}}
                            <form action="{{ route('exam.start', $package->id) }}" method="POST"
                                class="m-0 p-0 w-full">
                                @csrf
                                <button type="submit"
                                    class="w-full md:w-36 text-center bg-blue-600 text-white hover:bg-blue-700 font-bold py-2 px-4 rounded-lg transition-colors shadow-sm text-sm">
                                    🚀 Mulai
                                </button>
                            </form>
                        @endif
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

    <div id="upgradeModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 hidden opacity-0 transition-opacity duration-300">
        <div class="bg-white rounded-2xl p-8 max-w-sm w-full mx-4 shadow-2xl transform scale-95 transition-transform duration-300 relative"
            id="upgradeModalContent">

            <button onclick="closeUpgradeModal()"
                class="absolute top-4 right-4 text-gray-400 hover:text-red-500 text-xl font-bold">&times;</button>

            <div class="text-center">
                <div id="modalIcon" class="text-6xl mb-4 drop-shadow-md">🔒</div>
                <h3 class="text-2xl font-black text-gray-900 mb-2">Akses Terkunci</h3>

                <p class="text-gray-500 text-sm mb-6 leading-relaxed">
                    Paket ujian ini eksklusif. Untuk membukanya, Anda harus memiliki minimal <br>
                    <span id="modalTierName"
                        class="font-bold text-lg text-gray-800 mt-2 inline-block px-3 py-1 bg-gray-100 rounded-lg border border-gray-200">Paket</span>
                </p>

                <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 mb-6">
                    <p class="text-xs text-blue-600 font-bold uppercase tracking-wider mb-1">Mulai Dari</p>
                    <p class="text-2xl font-black text-blue-700" id="modalPrice">Rp 0</p>
                    <p class="text-xs text-blue-500 mt-1">per tahun</p>
                </div>

                <div class="flex flex-col gap-3">
                    <a href="{{ route('user.upgrade') }}"
                        class="w-full bg-yellow-400 hover:bg-yellow-500 text-yellow-900 font-bold py-3 px-4 rounded-xl shadow-md transition-colors text-sm flex justify-center items-center gap-2">
                        🚀 Lihat Info Pembayaran
                    </a>
                    <button onclick="closeUpgradeModal()"
                        class="w-full bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold py-3 px-4 rounded-xl transition-colors text-sm">
                        Nanti Saja
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // UBAH DI SINI: Samakan harga dengan yang ada di upgrade.blade.php
        const tierData = {
            'plus': {
                name: '✨ Paket PLUS',
                price: 'Rp 9.999',
                icon: '✨'
            },
            'pro': {
                name: '👑 Paket PRO',
                price: 'Rp 29.999',
                icon: '👑'
            },
            'ultra': {
                name: '🔮 Paket ULTRA',
                price: 'Rp 49.999',
                icon: '🔮'
            }
        };

        function showUpgradeModal(tier) {
            const data = tierData[tier];
            if (!data) return;

            // Suntikkan data ke dalam HTML Modal
            document.getElementById('modalTierName').innerText = data.name;
            document.getElementById('modalPrice').innerText = data.price;
            document.getElementById('modalIcon').innerText = data.icon;

            // Tampilkan Modal dengan animasi
            const modal = document.getElementById('upgradeModal');
            const content = document.getElementById('upgradeModalContent');

            modal.classList.remove('hidden');
            // Pancing browser untuk nge-render ulang (reflow) agar animasi jalan
            void modal.offsetWidth;
            modal.classList.remove('opacity-0');
            content.classList.remove('scale-95');
        }

        function closeUpgradeModal() {
            const modal = document.getElementById('upgradeModal');
            const content = document.getElementById('upgradeModalContent');

            // Jalankan animasi keluar
            modal.classList.add('opacity-0');
            content.classList.add('scale-95');

            // Sembunyikan sepenuhnya setelah animasi selesai (300ms)
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }
    </script>
</div>
