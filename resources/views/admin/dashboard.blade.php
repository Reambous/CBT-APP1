@extends('admin.layouts.sidebar')

@section('content')
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Overview Sistem</h1>
        <p class="text-gray-500 mt-1">Selamat datang kembali di panel kendali UKAICHAMPION.</p>
    </div>

    @if (session('success'))
        <div
            class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg shadow-sm font-bold flex items-center gap-2">
            <span>✅</span> {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-6 border-t-4 border-blue-500 hover:shadow-md transition-shadow">
            <h3 class="text-gray-500 text-sm font-bold uppercase tracking-wider">Total Pengguna</h3>
            <div class="flex items-end gap-2 mt-2">
                <p class="text-4xl font-black text-gray-800">{{ $totalUsers ?? 0 }}</p>
                <p class="text-gray-400 text-sm mb-1 font-medium">Siswa</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border-t-4 border-purple-500 hover:shadow-md transition-shadow">
            <h3 class="text-gray-500 text-sm font-bold uppercase tracking-wider">Total Paket Ujian</h3>
            <div class="flex items-end gap-2 mt-2">
                <p class="text-4xl font-black text-gray-800">{{ $totalPackages ?? 0 }}</p>
                <p class="text-gray-400 text-sm mb-1 font-medium">Paket</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border-t-4 border-green-500 hover:shadow-md transition-shadow">
            <h3 class="text-gray-500 text-sm font-bold uppercase tracking-wider">Estimasi Pendapatan</h3>
            <p class="text-3xl font-black text-gray-800 mt-2">Rp {{ number_format($revenue ?? 0, 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="mt-10 bg-white p-6 md:p-8 rounded-xl border border-gray-100 shadow-sm">
        <div class="flex justify-between items-center mb-6">
            <h3 class="font-bold text-gray-700 flex items-center gap-2">
                <span>⚡</span> Aktivitas Transaksi Terakhir
            </h3>
            <a href="{{ route('admin.transactions') ?? '#' }}"
                class="text-xs font-bold text-blue-600 hover:text-blue-800 transition-colors">Lihat Semua &rarr;</a>
        </div>

        @if (isset($recentActivities) && $recentActivities->count() > 0)
            <div class="space-y-4">
                @foreach ($recentActivities as $activity)
                    <div
                        class="flex flex-col sm:flex-row sm:items-center justify-between p-4 border rounded-xl hover:bg-gray-50 transition-colors gap-4">
                        <div class="flex items-center gap-4">
                            <div
                                class="bg-green-100 text-green-600 w-10 h-10 rounded-full flex items-center justify-center font-bold text-lg flex-shrink-0">
                                💰
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-800">{{ $activity->user->name ?? 'User Dihapus' }}</p>
                                <p class="text-xs text-gray-500">Membayar tagihan
                                    #{{ str_pad($activity->id, 5, '0', STR_PAD_LEFT) }}</p>
                            </div>
                        </div>
                        <div class="sm:text-right pl-14 sm:pl-0">
                            <p class="text-sm font-black text-green-600">+ Rp
                                {{ number_format($activity->amount, 0, ',', '.') }}</p>
                            <p class="text-[10px] font-bold text-gray-400 mt-0.5">
                                {{ \Carbon\Carbon::parse($activity->paid_at)->diffForHumans() }}
                                ({{ \Carbon\Carbon::parse($activity->paid_at)->format('d M') }})
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-gray-50 border border-dashed border-gray-200 rounded-xl p-8 text-center">
                <span class="text-3xl block mb-2">📭</span>
                <p class="text-sm font-bold text-gray-500">Belum ada transaksi sukses.</p>
                <p class="text-xs text-gray-400 mt-1">Aktivitas pembayaran user akan otomatis muncul di sini.</p>
            </div>
        @endif
    </div>
@endsection
