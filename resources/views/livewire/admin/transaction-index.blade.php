<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Transaction;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $statusFilter = '';

    public $selected = [];
    public $selectAll = false;

    public function updatingSearch()
    {
        $this->resetPage();
        $this->selected = [];
        $this->selectAll = false;
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
        $this->selected = [];
        $this->selectAll = false;
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selected = Transaction::when($this->search, function ($q) {
                $q->where('id', 'like', '%' . $this->search . '%')->orWhereHas('user', function ($userQuery) {
                    $userQuery->where('name', 'like', '%' . $this->search . '%')->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
                ->when($this->statusFilter, function ($q) {
                    $q->where('status', $this->statusFilter);
                })
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
            Transaction::whereIn('id', $this->selected)->delete();
            $this->selected = [];
            $this->selectAll = false;
            session()->flash('success', 'Semua riwayat transaksi yang dicentang berhasil dihapus.');
        }
    }

    public function approve($id)
    {
        $transaction = Transaction::findOrFail($id);

        if ($transaction->status === 'pending') {
            // --- INI PERUBAHANNYA ---
            // Langsung ambil nama paket dari database transaksi, tidak perlu tebak harga lagi!
            $newTier = $transaction->tier;
            // ------------------------

            $user = \App\Models\User::find($transaction->user_id);
            $newUntil = now()->addYear();

            // Logika perpanjangan waktu (sudah benar, tidak perlu diubah)
            if ($user->is_premium && $user->premium_until && now()->lessThan($user->premium_until)) {
                if ($user->premium_tier === $newTier) {
                    $newUntil = \Carbon\Carbon::parse($user->premium_until)->addYear();
                } else {
                    $newUntil = now()->addYear();
                }
            }

            // Update status transaksi
            $transaction->update([
                'status' => 'success',
                'paid_at' => now(),
            ]);

            // Update status user dengan tier yang dibeli
            $user->update([
                'is_premium' => true,
                'premium_tier' => $newTier,
                'premium_until' => $newUntil,
            ]);

            session()->flash('success', 'Transaksi #' . str_pad($transaction->id, 5, '0', STR_PAD_LEFT) . ' BERHASIL! Masa aktif dihitung sampai ' . $newUntil->format('d M Y'));
        }
    }

    // PERBAIKAN: Fungsi Tolak sekarang langsung menghapus transaksi (Anti-Error Database)
    public function reject($id)
    {
        $transaction = Transaction::findOrFail($id);

        if ($transaction->status === 'pending') {
            $transaction->delete(); // Langsung hapus
            session()->flash('success', 'Transaksi #' . str_pad($id, 5, '0', STR_PAD_LEFT) . ' berhasil DITOLAK & DIHAPUS.');
        }
    }

    public function with(): array
    {
        $query = Transaction::with('user')
            ->when($this->search, function ($q) {
                $q->where('id', 'like', '%' . $this->search . '%')->orWhereHas('user', function ($userQuery) {
                    $userQuery->where('name', 'like', '%' . $this->search . '%')->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter, function ($q) {
                $q->where('status', $this->statusFilter);
            })
            ->latest();

        return [
            'transactions' => $query->paginate(10),
        ];
    }
}; ?>

<div class="bg-white rounded-xl shadow-sm border overflow-hidden">
    <div class="p-6 border-b flex justify-between items-center bg-gray-50 flex-wrap gap-4">
        <div class="flex items-center gap-4 w-full md:w-auto flex-wrap">
            <div class="relative w-full md:w-80">
                <span class="absolute left-3 top-2.5 text-gray-400">🔍</span>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari Nama, Email, atau ID..."
                    class="w-full px-4 py-2 pl-10 border rounded-lg focus:ring-2 focus:ring-blue-200 outline-none">
            </div>

            <select wire:model.live="statusFilter"
                class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-200 outline-none bg-white">
                <option value="">Semua Status</option>
                <option value="pending">⏳ Pending</option>
                <option value="success">✅ Success</option>
            </select>

            <div wire:loading class="text-blue-500 text-sm font-semibold animate-pulse whitespace-nowrap">⏳ Memuat...
            </div>
        </div>

        @if (count($selected) > 0)
            <button wire:click="deleteSelected"
                wire:confirm="Anda yakin ingin menghapus permanen {{ count($selected) }} riwayat transaksi ini?"
                class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded-lg shadow-lg transition-colors w-full md:w-auto">
                🗑️ Hapus Terpilih ({{ count($selected) }})
            </button>
        @endif
    </div>

    @if (session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 m-4 rounded font-medium">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-white">
                <tr>
                    <th class="px-6 py-4 w-10">
                        <input type="checkbox" wire:model.live="selectAll"
                            class="w-5 h-5 text-blue-600 border-gray-300 rounded cursor-pointer">
                    </th>
                    <th class="px-6 py-4 text-left font-bold text-gray-500 uppercase text-xs w-24">ID Transaksi</th>
                    <th class="px-6 py-4 text-left font-bold text-gray-500 uppercase text-xs">Peserta (User)</th>
                    <th class="px-6 py-4 text-right font-bold text-gray-500 uppercase text-xs">Nominal</th>
                    <th class="px-6 py-4 text-center font-bold text-gray-500 uppercase text-xs">Status</th>
                    <th class="px-6 py-4 text-right font-bold text-gray-500 uppercase text-xs">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($transactions as $item)
                    <tr
                        class="transition-colors {{ in_array($item->id, $selected) ? 'bg-blue-50' : 'hover:bg-gray-50' }}">

                        <td class="px-6 py-4">
                            <input type="checkbox" wire:model.live="selected" value="{{ $item->id }}"
                                class="w-5 h-5 text-blue-600 border-gray-300 rounded cursor-pointer">
                        </td>

                        <td class="px-6 py-4 text-sm font-mono font-bold text-gray-600">
                            #{{ str_pad($item->id, 5, '0', STR_PAD_LEFT) }}<br>
                            <span
                                class="text-[10px] text-gray-400 font-sans">{{ $item->created_at->format('d M Y, H:i') }}</span>
                        </td>

                        <td class="px-6 py-4">
                            <div class="text-sm font-bold text-gray-900">{{ $item->user->name }}</div>
                            <div class="text-sm text-gray-500">{{ $item->user->email }}</div>
                        </td>

                        <td class="px-6 py-4 text-right">
                            <div class="text-sm font-bold text-gray-800">Rp
                                {{ number_format($item->amount, 0, ',', '.') }}</div>
                        </td>

                        <td class="px-6 py-4 text-center">
                            @if ($item->status === 'success')
                                <span
                                    class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-bold border border-green-200">✅
                                    SUCCESS</span>
                            @else
                                <span
                                    class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-xs font-bold border border-yellow-200 animate-pulse">⏳
                                    PENDING</span>
                            @endif
                        </td>

                        <td class="px-6 py-4 text-right text-sm whitespace-nowrap">
                            @if ($item->status === 'pending')
                                <div class="flex items-center justify-end gap-2">
                                    <button wire:click="approve({{ $item->id }})"
                                        wire:confirm="Setujui pembayaran ini?" wire:loading.attr="disabled"
                                        class="inline-flex items-center bg-blue-600 text-white hover:bg-blue-700 font-bold px-3 py-1.5 rounded-lg shadow-sm transition-colors disabled:opacity-50">
                                        <span wire:loading.remove wire:target="approve({{ $item->id }})">✔️
                                            Setujui</span>
                                        <span wire:loading wire:target="approve({{ $item->id }})">⌛...</span>
                                    </button>

                                    <button wire:click="reject({{ $item->id }})"
                                        wire:confirm="Yakin ingin MENOLAK pembayaran ini? Tagihan user akan dihapus."
                                        wire:loading.attr="disabled"
                                        class="inline-flex items-center bg-red-100 text-red-700 hover:bg-red-200 font-bold px-3 py-1.5 rounded-lg shadow-sm transition-colors disabled:opacity-50">
                                        <span wire:loading.remove wire:target="reject({{ $item->id }})">❌
                                            Tolak</span>
                                        <span wire:loading wire:target="reject({{ $item->id }})">⌛...</span>
                                    </button>
                                </div>
                            @elseif ($item->status === 'success')
                                <div class="text-[10px] text-gray-400 font-bold mt-1">
                                    Disetujui: {{ \Carbon\Carbon::parse($item->paid_at)->format('d M Y') }}
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-gray-500">📭 Belum ada riwayat transaksi.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-4 border-t bg-gray-50">
        {{ $transactions->links() }}
    </div>
</div>
