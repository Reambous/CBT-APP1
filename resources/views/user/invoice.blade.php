@extends('user.layouts.app')

@section('title', 'Tagihan Pembayaran - UKAICHAMPION APP')

@section('content')
    <div class="max-w-2xl mx-auto w-full mt-6 mb-12">

        @if (session('success'))
            <div
                class="bg-green-100 border-l-4 border-green-500 text-green-700 p-3 mb-4 rounded text-sm font-bold flex items-center gap-2">
                <span>✅</span> {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">

            <div
                class="bg-gradient-to-r from-blue-700 to-indigo-600 p-6 text-white flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="text-center sm:text-left">
                    <p class="text-blue-600 text-xs font-bold uppercase tracking-widest mb-1">Total Tagihan</p>
                    <h2 class="text-3xl font-black tracking-tight text-blue-600">Rp
                        {{ number_format($transaction->amount, 0, ',', '.') }}
                    </h2>
                </div>
                <div class="text-right">
                    @if ($transaction->status === 'success')
                        <span
                            class="bg-green-400 text-green-950 px-3 py-1.5 rounded-lg text-xs font-black shadow-sm flex items-center gap-1">
                            ✅ LUNAS
                        </span>
                    @else
                        <span
                            class="bg-yellow-400 text-yellow-950 px-3 py-1.5 rounded-lg text-xs font-black shadow-sm flex items-center gap-1 animate-pulse">
                            ⏳ PENDING
                        </span>
                    @endif
                </div>
            </div>

            <div class="p-6">
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-100 flex flex-wrap gap-y-4 justify-between mb-6">
                    <div class="w-1/2 sm:w-auto">
                        <p class="text-[10px] text-gray-500 font-bold uppercase mb-0.5">No. Invoice</p>
                        <p class="text-sm font-bold text-gray-800 font-mono">
                            #{{ str_pad($transaction->id, 5, '0', STR_PAD_LEFT) }}</p>
                    </div>
                    <div class="w-1/2 sm:w-auto text-right sm:text-left">
                        <p class="text-[10px] text-gray-500 font-bold uppercase mb-0.5">Tanggal</p>
                        <p class="text-sm font-bold text-gray-800">{{ $transaction->created_at->format('d M Y, H:i') }}</p>
                    </div>
                    <div class="w-full sm:w-auto sm:border-l sm:border-gray-200 sm:pl-4">
                        <p class="text-[10px] text-gray-500 font-bold uppercase mb-0.5">Paket Pembelian</p>
                        <p class="text-sm font-bold text-blue-700">{{ $tierName }} <span
                                class="text-gray-400 font-normal text-xs">(Aktif 1 Tahun)</span></p>
                    </div>
                </div>

                <div class="mb-6">
                    <h3 class="text-sm font-bold text-gray-800 mb-3 flex items-center gap-2 border-b pb-2">💳 Transfer ke
                        Rekening Berikut:</h3>

                    {{-- Diubah menjadi 1 kolom saja agar lebih lega --}}
                    <div class="grid grid-cols-1 gap-3">
                        <div
                            class="border border-gray-200 rounded-lg p-4 flex justify-between items-center bg-white shadow-sm hover:border-blue-300 transition-colors">
                            <div>
                                <p class="text-[10px] text-gray-500 uppercase font-bold">Seabank</p>
                                <p class="font-mono font-bold text-lg text-gray-800 tracking-wide">9018-3663-4869</p>
                            </div>
                            <span class="text-[10px] font-bold bg-gray-100 px-3 py-1.5 rounded text-gray-500">A.N
                                ADMIN</span>
                        </div>
                    </div>
                </div>

                @php
                    $waText =
                        "Halo Admin, konfirmasi pembayaran UKAICHAMPION.\n\n" .
                        'Invoice: #' .
                        str_pad($transaction->id, 5, '0', STR_PAD_LEFT) .
                        "\n" .
                        'Akun: ' .
                        auth()->user()->name .
                        "\n" .
                        'Email: ' .
                        auth()->user()->email .
                        "\n" .
                        'Paket: ' .
                        $tierName .
                        "\n" .
                        'Transfer: Rp ' .
                        number_format($transaction->amount, 0, ',', '.') .
                        "\n\n" .
                        'Berikut bukti transfernya.';
                @endphp

                <div class="bg-blue-50/50 p-4 rounded-xl border border-blue-100 text-center flex flex-col items-center">
                    <p class="text-xs text-gray-500 mb-4">Lampirkan foto struk/screenshot transfer Anda melalui WhatsApp.
                    </p>

                    <div class="flex flex-col sm:flex-row items-center justify-center gap-3 w-full sm:w-auto">
                        <a href="https://wa.me/{{ env('CBT_ADMIN_WA', '6282329363662') }}?text={{ urlencode($waText) }}"
                            target="_blank"
                            class="w-full sm:w-auto bg-green-500 hover:bg-green-600 text-white font-bold py-2.5 px-6 rounded-lg shadow-sm transition-colors text-sm flex items-center justify-center gap-2">
                            <span>💬</span> Kirim Bukti via WA
                        </a>

                        @if ($transaction->status === 'pending')
                            <form action="{{ route('user.invoice.cancel', $transaction->id) }}" method="POST"
                                onsubmit="return confirm('Yakin ingin membatalkan pesanan ini?');" class="w-full sm:w-auto">
                                @csrf
                                <button type="submit"
                                    class="w-full sm:w-auto text-xs text-red-500 hover:text-red-700 font-bold py-2.5 px-4 rounded-lg hover:bg-red-50 transition-colors">
                                    Batalkan Pesanan
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
