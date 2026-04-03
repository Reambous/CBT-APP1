<?php

use Livewire\Volt\Component;
use App\Models\Transaction;

new class extends Component {
    public $pendingTransaction;

    // Siapkan variabel kosong
    public $price;
    public $bankName;
    public $bankAccount;
    public $accountName;
    public $adminWa;

    public function mount()
    {
        // 1. Ambil data dari file .env secara otomatis
        $this->price = env('CBT_PREMIUM_PRICE', 20000);
        $this->bankName = env('CBT_BANK_NAME', 'Bank Default');
        $this->bankAccount = env('CBT_BANK_ACC', '000000');
        $this->accountName = env('CBT_ACCOUNT_NAME', 'Admin');
        $this->adminWa = env('CBT_ADMIN_WA', '628000000');

        // 2. Cek apakah user sudah punya tagihan yang belum dibayar
        $this->pendingTransaction = Transaction::where('user_id', auth()->id())
            ->where('status', 'pending')
            ->first();
    }

    public function createTransaction()
    {
        $this->pendingTransaction = Transaction::create([
            'user_id' => auth()->id(),
            'amount' => $this->price,
            'status' => 'pending',
        ]);

        session()->flash('success', 'Pesanan berhasil dibuat! Silakan lakukan pembayaran.');
    }

    public function confirmViaWA()
    {
        $user = auth()->user();

        // Kita gunakan \n biasa untuk enter (tidak perlu %0A lagi)
        $text = "Halo Admin, saya ingin konfirmasi pembayaran Premium CBT.\n\n";
        $text .= '*ID Transaksi:* #' . str_pad($this->pendingTransaction->id, 5, '0', STR_PAD_LEFT) . "\n";
        $text .= '*Nama Akun:* ' . $user->name . "\n";
        $text .= '*Email:* ' . $user->email . "\n";

        // Gunakan amount dari database agar lebih akurat
        $text .= '*Total Transfer:* Rp ' . number_format($this->pendingTransaction->amount, 0, ',', '.') . "\n\n";
        $text .= 'Berikut saya lampirkan bukti transfernya.';

        // RAHASIANYA DI SINI: Gunakan urlencode() agar simbol # dan spasi tidak memotong link
        $waLink = "https://wa.me/{$this->adminWa}?text=" . urlencode($text);

        return redirect()->away($waLink);
    }
}; ?>

<div class="max-w-3xl mx-auto py-8">
    @if (auth()->user()->is_premium)
        <div class="bg-gradient-to-r from-yellow-400 to-yellow-500 rounded-xl shadow-lg p-8 text-center text-white">
            <div class="text-5xl mb-4">👑</div>
            <h2 class="text-2xl font-bold mb-2">Akun Anda Sudah Premium!</h2>
            <p class="text-yellow-50 mb-6">Nikmati akses penuh ke seluruh paket ujian hingga
                {{ \Carbon\Carbon::parse(auth()->user()->premium_until)->format('d M Y') }}.</p>
            <a href="{{ route('user.exams') }}"
                class="bg-white text-yellow-600 font-bold py-2 px-6 rounded-lg shadow hover:bg-yellow-50 transition">Lihat
                Daftar Ujian</a>
        </div>
    @elseif($pendingTransaction)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-blue-50 p-6 border-b text-center">
                <h2 class="text-xl font-bold text-blue-800">Selesaikan Pembayaran Anda</h2>
                <p class="text-sm text-gray-600 mt-1">ID Transaksi: <span
                        class="font-bold">#{{ str_pad($pendingTransaction->id, 5, '0', STR_PAD_LEFT) }}</span></p>
            </div>

            <div class="p-8">
                <div class="bg-gray-50 border rounded-xl p-6 text-center mb-6">
                    <p class="text-gray-500 text-sm mb-2">Total yang harus ditransfer</p>
                    <p class="text-4xl font-black text-gray-800">Rp
                        {{ number_format($pendingTransaction->amount, 0, ',', '.') }}</p>
                </div>

                <div class="space-y-4 mb-8">
                    <p class="text-gray-700 text-center">Silakan transfer ke rekening berikut:</p>
                    <div
                        class="border-2 border-dashed border-gray-300 rounded-lg p-4 bg-white flex justify-between items-center">
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-bold">{{ $bankName }}</p>
                            <p class="text-xl font-mono font-bold text-gray-800 tracking-wider">{{ $bankAccount }}</p>
                            <p class="text-sm text-gray-600">a.n. {{ $accountName }}</p>
                        </div>
                        <div class="text-4xl">🏦</div>
                    </div>
                </div>

                <div class="border-t pt-6">
                    <p class="text-sm text-gray-500 text-center mb-4">Sudah melakukan transfer? Konfirmasikan pembayaran
                        Anda ke Admin beserta bukti transfer.</p>
                    <button wire:click="confirmViaWA"
                        class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-4 rounded-xl shadow-md transition flex items-center justify-center gap-2">
                        <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24">
                            <path
                                d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z" />
                        </svg>
                        Konfirmasi via WhatsApp
                    </button>
                </div>
            </div>
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden text-center p-10">
            <div class="text-6xl mb-4">🚀</div>
            <h2 class="text-3xl font-black text-gray-800 mb-4">Upgrade ke Premium</h2>
            <p class="text-gray-600 mb-8 max-w-lg mx-auto">Dapatkan akses tak terbatas ke semua paket ujian, pembahasan
                soal mendetail, dan evaluasi hasil belajar selama 1 Tahun Penuh.</p>

            <div class="bg-gray-50 border rounded-xl p-6 max-w-sm mx-auto mb-8">
                <p class="text-sm text-gray-500 uppercase font-bold tracking-wider mb-2">Harga Langganan</p>
                <p class="text-5xl font-black text-blue-600">Rp {{ number_format($price, 0, ',', '.') }}</p>
                <p class="text-sm text-gray-500 mt-2">/ Tahun</p>
            </div>

            <button wire:click="createTransaction"
                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-10 rounded-full shadow-lg transition transform active:scale-95 text-lg">
                Pesan Sekarang
            </button>
        </div>
    @endif
</div>
