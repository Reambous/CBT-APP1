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
        $user = Auth::user();

        // 1. JIKA USER BUKAN PREMIUM: Kembalikan data kosong agar database tidak capek bekerja
        if (!$user->is_premium) {
            return [
                'histories' => collect(), // Data kosong
                'categories' => [],
                'totalExams' => 0,
                'averageScore' => 0,
            ];
        }

        // 2. JIKA USER PREMIUM: Eksekusi Query seperti biasa
        $totalExams = UserResult::where('user_id', $user->id)->whereNotNull('finished_at')->count();
        $averageScore = UserResult::where('user_id', $user->id)->whereNotNull('finished_at')->avg('score') ?? 0;

        $histories = UserResult::with(['examPackage.examCategory'])
            ->where('user_id', $user->id)
            ->whereNotNull('finished_at')
            ->when($this->selectedCategory, function ($query) {
                $query->whereHas('examPackage', function ($q) {
                    $q->where('exam_category_id', $this->selectedCategory);
                });
            })
            ->when($this->search, function ($query) {
                $query->whereHas('examPackage', function ($q) {
                    $q->where('title', 'like', '%' . $this->search . '%')->orWhereHas('examCategory', function ($q2) {
                        $q2->where('name', 'like', '%' . $this->search . '%');
                    });
                });
            })
            ->latest('finished_at')
            ->paginate(10);

        return [
            'histories' => $histories,
            'categories' => ExamCategory::all(),
            'totalExams' => $totalExams,
            'averageScore' => $averageScore,
        ];
    }
}; ?>

<div>
    @if (auth()->user()->is_premium)

        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 mb-6 border-l-4 border-l-green-500">
            <h2 class="text-2xl font-bold text-gray-800">Riwayat Nilai & Evaluasi</h2>
            <p class="text-gray-500 mt-1">Pantau terus perkembangan belajarmu dari hasil ujian yang telah diselesaikan.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white p-6 rounded-xl shadow-sm border border-t-4 border-t-blue-500">
                <h3 class="text-gray-500 text-sm font-bold uppercase tracking-wider mb-2">Total Diselesaikan</h3>
                <p class="text-4xl font-extrabold text-gray-800 mt-2">{{ $totalExams }} <span
                        class="text-lg font-medium text-gray-500">paket</span></p>
            </div>
            <div
                class="bg-white p-6 rounded-xl shadow-sm border border-t-4 {{ $averageScore >= 70 ? 'border-t-green-500' : 'border-t-yellow-500' }}">
                <h3 class="text-gray-500 text-sm font-bold uppercase tracking-wider mb-2">Rata-rata Nilai</h3>
                <p
                    class="text-4xl font-extrabold {{ $averageScore >= 70 ? 'text-green-600' : 'text-yellow-600' }} mt-2">
                    {{ number_format($averageScore, 1) }} <span class="text-lg font-medium text-gray-500">/ 100</span>
                </p>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-4 gap-4">
            <h2 class="text-xl font-bold text-gray-800">📈 Daftar Rekam Jejak Ujian</h2>

            <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
                <select wire:model.live="selectedCategory"
                    class="px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-200 outline-none shadow-sm bg-white text-gray-700 font-medium text-sm">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>

                <div class="relative w-full sm:w-64">
                    <span class="absolute left-3 top-2.5 text-gray-400">🔍</span>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama ujian..."
                        class="w-full px-4 py-2 pl-10 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-200 outline-none shadow-sm text-sm">
                </div>
            </div>
        </div>

        <div wire:loading class="w-full mb-4 text-blue-500 text-sm font-bold animate-pulse">
            ⏳ Mencari data riwayat...
        </div>

        <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left font-bold text-gray-500 uppercase text-xs w-16">No</th>
                            <th class="px-6 py-4 text-left font-bold text-gray-500 uppercase text-xs">Informasi Ujian
                            </th>
                            <th class="px-6 py-4 text-center font-bold text-gray-500 uppercase text-xs">Waktu Selesai
                            </th>
                            <th class="px-6 py-4 text-center font-bold text-gray-500 uppercase text-xs">Skor Akhir</th>
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
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <span class="text-4xl mb-3 block">📭</span>
                                    <h3 class="text-lg font-bold text-gray-800">Riwayat tidak ditemukan</h3>
                                    <p class="text-gray-500 text-sm mt-1">Coba sesuaikan filter atau kata kunci
                                        pencarianmu.</p>
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
    @else
        <div
            class="bg-white rounded-3xl shadow-lg border border-gray-100 p-10 mt-8 text-center max-w-3xl mx-auto relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-32 bg-gradient-to-b from-blue-50 to-transparent"></div>

            <div class="relative z-10 flex flex-col items-center">
                <div class="text-7xl mb-6 drop-shadow-md">🔒</div>
                <h2 class="text-3xl font-black text-gray-900 mb-4 tracking-tight">Fitur Eksklusif Premium</h2>

                <p class="text-gray-500 text-lg mb-8 leading-relaxed max-w-xl">
                    Maaf, menu <b>Riwayat Nilai & Pembahasan Soal</b> dikunci untuk pengguna reguler. Upgrade akun Anda
                    sekarang untuk memantau grafik nilai dan melihat rahasia kunci jawaban!
                </p>

                <div class="bg-blue-50 p-6 rounded-2xl border border-blue-100 mb-8 w-full max-w-md">
                    <h4 class="font-bold text-blue-800 mb-3 text-sm uppercase tracking-widest">Keuntungan Premium:</h4>
                    <ul class="text-left text-sm text-blue-700 space-y-2 font-medium">
                        <li class="flex items-center gap-2"><span>✅</span> Akses ke SEMUA paket soal ujian</li>
                        <li class="flex items-center gap-2"><span>✅</span> Lihat riwayat nilai tanpa batas</li>
                        <li class="flex items-center gap-2"><span>✅</span> Baca pembahasan detail setiap soal</li>
                    </ul>
                </div>

                <a href="{{ route('user.upgrade') ?? route('user.contact') }}"
                    class="bg-yellow-400 hover:bg-yellow-500 text-yellow-900 font-black py-4 px-10 rounded-xl shadow-xl hover:shadow-2xl transition-all transform hover:-translate-y-1 text-lg flex items-center gap-2">
                    <span>👑</span> Upgrade Premium Sekarang
                </a>
            </div>
        </div>

    @endif
</div>
