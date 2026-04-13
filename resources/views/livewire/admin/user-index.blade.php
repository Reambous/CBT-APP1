<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\User;

new class extends Component {
    use WithPagination;

    public $search = '';

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
            $this->selected = User::withTrashed()
                ->where(function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')->orWhere('email', 'like', '%' . $this->search . '%');
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
            User::whereIn('id', $this->selected)->delete();
            $this->selected = [];
            $this->selectAll = false;
            session()->flash('success', 'Semua data yang dicentang berhasil di-Banned.');
        }
    }

    public function delete($id)
    {
        User::findOrFail($id)->delete();
        session()->flash('success', 'Akun user berhasil di-Banned (Nonaktifkan).');
    }

    public function restore($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();

        session()->flash('success', 'Akun ' . $user->name . ' berhasil dipulihkan! Mereka bisa login kembali.');
    }

    public function with(): array
    {
        return [
            'users' => User::withTrashed()
                ->where(function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')->orWhere('email', 'like', '%' . $this->search . '%');
                })
                ->latest()
                ->paginate(10),
        ];
    }
}; ?>

<div class="bg-white rounded-xl shadow-sm border overflow-hidden">
    <div class="p-6 border-b flex justify-between items-center bg-gray-50 flex-wrap gap-4">
        <div class="flex items-center gap-4 w-full md:w-auto">
            <div class="relative w-full md:w-96">
                <span class="absolute left-3 top-2.5 text-gray-400">🔍</span>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama atau email user..."
                    class="w-full px-4 py-2 pl-10 border rounded-lg focus:ring-2 focus:ring-blue-200 outline-none">
            </div>
            <div wire:loading class="text-blue-500 text-sm font-semibold animate-pulse whitespace-nowrap">⏳ Mencari...
            </div>
        </div>

        @if (count($selected) > 0)
            <button wire:click="deleteSelected"
                wire:confirm="Anda yakin ingin mem-Banned {{ count($selected) }} user terpilih?"
                class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded-lg shadow-lg transition-colors">
                🚫 Banned Terpilih ({{ count($selected) }})
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
                    <th class="px-6 py-4 text-left font-bold text-gray-500 uppercase text-xs w-16">No</th>
                    <th class="px-6 py-4 text-left font-bold text-gray-500 uppercase text-xs">Informasi User</th>
                    <th class="px-6 py-4 text-center font-bold text-gray-500 uppercase text-xs">Status Paket</th>
                    <th class="px-6 py-4 text-center font-bold text-gray-500 uppercase text-xs">Masa Aktif</th>
                    <th class="px-6 py-4 text-right font-bold text-gray-500 uppercase text-xs">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($users as $index => $item)
                    <tr
                        class="transition-colors {{ in_array($item->id, $selected) ? 'bg-blue-50' : ($item->trashed() ? 'bg-red-50 opacity-90' : 'hover:bg-gray-50') }}">

                        <td class="px-6 py-4">
                            <input type="checkbox" wire:model.live="selected" value="{{ $item->id }}"
                                class="w-5 h-5 text-blue-600 border-gray-300 rounded cursor-pointer">
                        </td>

                        <td class="px-6 py-4 text-sm text-gray-500">{{ $users->firstItem() + $index }}</td>

                        <td class="px-6 py-4">
                            <div class="flex items-center {{ $item->trashed() ? 'grayscale' : '' }}">
                                <img class="h-10 w-10 rounded-full object-cover border"
                                    src="{{ $item->profile_picture ? asset('storage/' . $item->profile_picture) : 'https://ui-avatars.com/api/?name=' . urlencode($item->name) }}">
                                <div class="ml-4">
                                    <div
                                        class="text-sm font-bold {{ $item->trashed() ? 'text-gray-500 line-through' : 'text-gray-900' }}">
                                        {{ $item->name }}
                                    </div>
                                    <div class="text-sm text-gray-500">{{ $item->email }}</div>
                                </div>
                            </div>
                        </td>

                        <td class="px-6 py-4 text-center">
                            @if ($item->trashed())
                                <span
                                    class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-[10px] font-bold border border-red-200">
                                    🚫 BANNED
                                </span>
                            @elseif ($item->is_premium && $item->premium_until && \Carbon\Carbon::parse($item->premium_until)->isPast())
                                <span
                                    class="bg-gray-200 text-gray-600 px-3 py-1 rounded-full text-[10px] font-bold border border-gray-300"
                                    title="Masa langganan telah habis">
                                    ❌ KEDALUWARSA
                                </span>
                            @elseif ($item->is_premium && $item->premium_tier == 'ultra')
                                <span
                                    class="bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-[10px] font-bold border border-purple-200">🔮
                                    ULTRA</span>
                            @elseif ($item->is_premium && $item->premium_tier == 'pro')
                                <span
                                    class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-[10px] font-bold border border-yellow-200">👑
                                    PRO</span>
                            @elseif ($item->is_premium && $item->premium_tier == 'plus')
                                <span
                                    class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-[10px] font-bold border border-blue-200">✨
                                    PLUS</span>
                            @else
                                <span
                                    class="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-[10px] font-bold border border-gray-200">🆓
                                    GRATIS</span>
                            @endif
                        </td>

                        <td class="px-6 py-4 text-center">
                            @if ($item->is_premium && $item->premium_until && !$item->trashed())
                                @php
                                    $isExpired = \Carbon\Carbon::parse($item->premium_until)->isPast();
                                @endphp

                                <div class="text-sm font-bold {{ $isExpired ? 'text-red-600' : 'text-gray-800' }}">
                                    {{ \Carbon\Carbon::parse($item->premium_until)->format('d M Y') }}
                                </div>
                                <div
                                    class="text-[10px] uppercase font-bold {{ $isExpired ? 'text-red-400' : 'text-gray-400' }}">
                                    ({{ \Carbon\Carbon::parse($item->premium_until)->diffForHumans() }})
                                </div>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>

                        <td class="px-6 py-4 text-right text-sm whitespace-nowrap">
                            @if ($item->trashed())
                                <button wire:click="restore({{ $item->id }})"
                                    wire:confirm="Pulihkan akun ini agar user bisa login kembali?"
                                    class="inline-flex items-center bg-green-100 text-green-800 hover:bg-green-200 font-bold px-4 py-1.5 rounded-lg text-xs transition-colors shadow-sm border border-green-200">
                                    ♻️ Pulihkan
                                </button>
                            @else
                                <a href="{{ route('admin.users.edit', $item->id) }}"
                                    class="inline-flex items-center bg-yellow-100 text-yellow-700 hover:bg-yellow-200 font-bold px-3 py-1.5 rounded-lg text-xs transition-colors mr-1 shadow-sm border border-yellow-200">
                                    ✏️ Edit
                                </a>

                                <button wire:click="delete({{ $item->id }})"
                                    wire:confirm="Anda yakin ingin mem-Banned user ini? Mereka tidak akan bisa login lagi."
                                    class="inline-flex items-center bg-red-50 text-red-700 hover:bg-red-100 font-bold px-3 py-1.5 rounded-lg text-xs transition-colors shadow-sm border border-red-200">
                                    🚫 Banned
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-gray-500">📭 Tidak ada data user.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-4 border-t bg-gray-50">
        {{ $users->links() }}
    </div>
</div>
