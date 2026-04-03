<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Transaction;
use App\Models\User;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $statusFilter = ''; // Filter untuk melihat yang 'pending' atau 'success'

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    // LOGIKA CRITICAL: Approve Pembayaran
    public function approve($id)
    {
        $transaction = Transaction::with('user')->findOrFail($id);

        if ($transaction->status === 'pending') {
            // 1. Update Transaksi menjadi Sukses
            $transaction->update([
                'status' => 'success',
                'paid_at' => now(),
            ]);

            // 2. Update Akun User menjadi Premium selama 1 Tahun
            $transaction->user->update([
                'is_premium' => true,
                'premium_until' => now()->addYear(),
            ]);

            session()->flash('success', 'Transaksi #' . str_pad($transaction->id, 5, '0', STR_PAD_LEFT) . ' disetujui! Akun ' . $transaction->user->name . ' kini Premium.');
        }
    }

    public function with(): array
    {
        // Query untuk memanggil transaksi beserta data user yang membelinya
        $query = Transaction::with('user')
            ->when($this->search, function ($q) {
                // Bisa cari pakai ID transaksi, atau Nama/Email user
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
        <div class="flex items-center gap-4 w-full md:w-auto">
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
                    <th class="px-6 py-4 text-left font-bold text-gray-500 uppercase text-xs w-24">ID Transaksi</th>
                    <th class="px-6 py-4 text-left font-bold text-gray-500 uppercase text-xs">Peserta (User)</th>
                    <th class="px-6 py-4 text-right font-bold text-gray-500 uppercase text-xs">Nominal</th>
                    <th class="px-6 py-4 text-center font-bold text-gray-500 uppercase text-xs">Tanggal Dibuat</th>
                    <th class="px-6 py-4 text-center font-bold text-gray-500 uppercase text-xs">Status</th>
                    <th class="px-6 py-4 text-right font-bold text-gray-500 uppercase text-xs">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($transactions as $item)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-sm font-mono font-bold text-gray-600">
                            #{{ str_pad($item->id, 5, '0', STR_PAD_LEFT) }}
                        </td>

                        <td class="px-6 py-4">
                            <div class="text-sm font-bold text-gray-900">{{ $item->user->name }}</div>
                            <div class="text-sm text-gray-500">{{ $item->user->email }}</div>
                        </td>

                        <td class="px-6 py-4 text-right">
                            <div class="text-sm font-bold text-gray-800">Rp
                                {{ number_format($item->amount, 0, ',', '.') }}</div>
                        </td>

                        <td class="px-6 py-4 text-center text-sm text-gray-500">
                            {{ $item->created_at->format('d M Y, H:i') }}
                        </td>

                        <td class="px-6 py-4 text-center">
                            @if ($item->status === 'success')
                                <span
                                    class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-bold border border-green-200">
                                    ✅ SUCCESS
                                </span>
                            @else
                                <span
                                    class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-xs font-bold border border-yellow-200 animate-pulse">
                                    ⏳ PENDING
                                </span>
                            @endif
                        </td>

                        <td class="px-6 py-4 text-right text-sm whitespace-nowrap">
                            @if ($item->status === 'pending')
                                <button wire:click="approve({{ $item->id }})"
                                    wire:confirm="Setujui pembayaran ini? Akun {{ $item->user->name }} akan langsung menjadi Premium selama 1 Tahun."
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center bg-blue-600 text-white hover:bg-blue-700 font-bold px-4 py-2 rounded-lg shadow-sm transition-colors disabled:opacity-50">
                                    <span wire:loading.remove>✔️ Setujui Pembayaran</span>
                                    <span wire:loading>⌛ Memproses...</span>
                                </button>
                            @else
                                <div class="text-xs text-gray-400 font-bold">
                                    Disetujui pada:<br>
                                    {{ \Carbon\Carbon::parse($item->paid_at)->format('d M Y, H:i') }}
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
