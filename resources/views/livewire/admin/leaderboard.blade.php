<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\UserResult;
use App\Models\ExamPackage;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $selectedPackage = '';

    // Reset pagination saat filter berubah
    public function updatingSearch()
    {
        $this->resetPage();
    }
    public function updatingSelectedPackage()
    {
        $this->resetPage();
    }

    public function with(): array
    {
        // 1. Ambil Query Dasar
        $query = UserResult::with(['user', 'examPackage'])->whereNotNull('finished_at');

        // 2. Filter berdasarkan Paket Soal
        if ($this->selectedPackage) {
            $query->where('exam_package_id', $this->selectedPackage);
        }

        // 3. Filter berdasarkan Pencarian Nama Siswa
        if ($this->search) {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            });
        }

        // 4. Hitung Statistik Singkat (berdasarkan filter saat ini)
        $stats = [
            'avg' => (clone $query)->avg('score') ?? 0,
            'total' => (clone $query)->count(),
            'max' => (clone $query)->max('score') ?? 0,
        ];

        return [
            'results' => $query->orderBy('score', 'desc')->paginate(15),
            'packages' => ExamPackage::all(),
            'stats' => $stats,
        ];
    }
}; ?>

<div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-blue-500">
            <h4 class="text-xs font-bold text-gray-400 uppercase">Total Partisipasi</h4>
            <p class="text-2xl font-black text-gray-800">{{ $stats['total'] }} Kali</p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-green-500">
            <h4 class="text-xs font-bold text-gray-400 uppercase">Rata-rata Skor</h4>
            <p class="text-2xl font-black text-green-600">{{ number_format($stats['avg'], 1) }}</p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-yellow-500">
            <h4 class="text-xs font-bold text-gray-400 uppercase">Skor Tertinggi</h4>
            <p class="text-2xl font-black text-yellow-600">{{ number_format($stats['max'], 0) }}</p>
        </div>
    </div>

    <div class="bg-white p-4 rounded-t-xl border border-b-0 flex flex-col md:flex-row gap-4 items-center">
        <div class="relative flex-1 w-full">
            <span class="absolute left-3 top-2.5 text-gray-400">🔍</span>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama siswa..."
                class="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-100 outline-none">
        </div>
        <select wire:model.live="selectedPackage"
            class="w-full md:w-64 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-100 outline-none bg-white">
            <option value="">Semua Paket Soal</option>
            @foreach ($packages as $p)
                <option value="{{ $p->id }}">{{ $p->title }} ({{ $p->minimum_tier }})</option>
            @endforeach
        </select>
    </div>

    <div class="bg-white rounded-b-xl shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Rank</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Nama Siswa</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Paket</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase">Skor</th>
                        {{-- TAMBAHAN: Header Durasi --}}
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase">Durasi</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase">Tanggal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($results as $index => $res)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <span
                                    class="w-8 h-8 rounded-full flex items-center justify-center font-bold {{ $results->firstItem() + $index <= 3 ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $results->firstItem() + $index }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-900">{{ $res->user->name }}</div>
                                <div class="text-xs text-gray-500">{{ $res->user->email }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="px-2 py-1 bg-blue-50 text-blue-700 rounded text-[10px] font-bold uppercase border border-blue-100">
                                    {{ $res->examPackage->title }}
                                </span>
                            </td>
                            <td
                                class="px-6 py-4 text-center font-black text-xl {{ $res->score >= 70 ? 'text-green-600' : 'text-red-500' }}">
                                {{ number_format($res->score, 1) }}
                            </td>
                            {{-- TAMBAHAN: Kolom Menghitung Durasi --}}
                            {{-- TAMBAHAN: Kolom Menghitung Durasi --}}
                            <td class="px-6 py-4 text-center text-sm font-bold text-gray-600">
                                @php
                                    // Menggunakan objek DateInterval bawaan PHP yang jauh lebih akurat
                                    $diff = $res->created_at->diff($res->finished_at);

                                    // Jika durasi mencapai 1 jam atau lebih, tampilkan 'j' (Jam)
                                    $hours = $diff->h > 0 ? $diff->h . 'j ' : '';
                                @endphp
                                <span class="bg-gray-100 px-2 py-1 rounded border border-gray-200 whitespace-nowrap">
                                    ⏱️ {{ $hours }}{{ $diff->i }}m {{ $diff->s }}s
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right text-xs text-gray-500">
                                {{ $res->finished_at->format('d M Y, H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            {{-- TAMBAHAN: Colspan diubah dari 5 menjadi 6 karena ketambahan 1 kolom baru --}}
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400 italic">Belum ada data
                                pengerjaan ujian.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 bg-gray-50 border-t">
            {{ $results->links() }}
        </div>
    </div>
</div>
