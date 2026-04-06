@extends('admin.layouts.sidebar') {{-- Sesuaikan dengan nama layout admin kamu, misalnya 'layouts.admin' --}}

@section('content')
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="p-6 border-b flex flex-col lg:flex-row lg:justify-between lg:items-center bg-gray-50 gap-4">

            <form action="{{ route('admin.packages.index') }}" method="GET" id="filter-form"
                class="flex flex-wrap items-center gap-3 w-full lg:w-auto">

                <div class="relative w-full sm:w-56">
                    <span class="absolute left-3 top-2.5 text-gray-400">🔍</span>
                    <input type="text" name="search" id="search-input" value="{{ $search }}"
                        placeholder="Ketik nama paket..."
                        class="w-full px-4 py-2 pl-10 border rounded-lg focus:ring-2 focus:ring-blue-200 outline-none shadow-sm">
                </div>

                <div class="relative w-full sm:w-48">
                    <span class="absolute left-3 top-2.5 text-gray-400">📁</span>
                    <select name="category_id" onchange="document.getElementById('filter-form').submit()"
                        class="w-full px-4 py-2 pl-10 border rounded-lg focus:ring-2 focus:ring-blue-200 outline-none appearance-none shadow-sm bg-white cursor-pointer text-sm font-medium text-gray-700">
                        <option value="" class="font-normal text-gray-500">Semua Kategori</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="relative w-full sm:w-40">
                    <span class="absolute left-3 top-2.5 text-gray-400">🏷️</span>
                    <select name="type" onchange="document.getElementById('filter-form').submit()"
                        class="w-full px-4 py-2 pl-10 border rounded-lg focus:ring-2 focus:ring-blue-200 outline-none appearance-none shadow-sm bg-white cursor-pointer text-sm font-medium text-gray-700">
                        <option value="" class="font-normal text-gray-500">Semua Tipe</option>
                        <option value="premium" {{ $type == 'premium' ? 'selected' : '' }}>💎 Premium</option>
                        <option value="gratis" {{ $type == 'gratis' ? 'selected' : '' }}>🆓 Gratis</option>
                    </select>
                </div>
            </form>

            <a href="{{ route('admin.packages.create') }}"
                class="w-full lg:w-auto text-center bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg shadow-sm transition-colors whitespace-nowrap">
                + Tambah Paket Baru
            </a>
        </div>



        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-white">
                    <tr>
                        <th class="px-6 py-4 text-left font-bold text-gray-500 uppercase text-xs w-16">No</th>
                        <th class="px-6 py-4 text-left font-bold text-gray-500 uppercase text-xs">Nama Paket</th>
                        <th class="px-6 py-4 text-left font-bold text-gray-500 uppercase text-xs">Kategori</th>
                        <th class="px-6 py-4 text-center font-bold text-gray-500 uppercase text-xs">Tipe</th>
                        <th class="px-6 py-4 text-left font-bold text-gray-500 uppercase text-xs">Durasi</th>
                        <th class="px-6 py-4 text-center font-bold text-gray-500 uppercase text-xs">Jumlah Soal</th>
                        <th class="px-6 py-4 text-right font-bold text-gray-500 uppercase text-xs">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($packages as $index => $package)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-3 py-4 text-xs text-gray-500 text-center">{{ $packages->firstItem() + $index }}
                            </td>

                            <td class="px-4 py-4 text-sm font-bold text-gray-900 whitespace-nowrap">{{ $package->title }}
                            </td>

                            <td class="px-4 py-4">
                                <span
                                    class="bg-blue-100 text-blue-800 px-2 py-0.5 rounded text-[10px] font-bold border border-blue-200 whitespace-nowrap">
                                    {{ $package->examCategory?->name ?? 'Dihapus' }}
                                </span>
                            </td>

                            <td class="px-4 py-4 text-center">
                                @if ($package->is_premium)
                                    <span
                                        class="bg-red-100 text-red-800 px-2 py-0.5 rounded-full text-[10px] font-bold border border-red-200 whitespace-nowrap">💎
                                        Premium</span>
                                @else
                                    <span
                                        class="bg-green-100 text-green-800 px-2 py-0.5 rounded-full text-[10px] font-bold border border-green-200 whitespace-nowrap">🆓
                                        Gratis</span>
                                @endif
                            </td>

                            <td class="px-4 py-4 text-xs font-medium text-gray-600 whitespace-nowrap">⏱️
                                {{ $package->time_limit }}m</td>

                            <td class="px-4 py-4 text-center">
                                <span
                                    class="text-xs font-bold {{ $package->questions_count > 0 ? 'text-blue-600' : 'text-gray-400' }} whitespace-nowrap">
                                    {{ $package->questions_count }} Soal
                                </span>
                            </td>

                            <td class="px-4 py-4 text-right flex justify-end gap-1.5">
                                <a href="{{ route('admin.packages.show', $package->id) }}" title="Kelola"
                                    class="bg-indigo-50 text-indigo-700 hover:bg-indigo-100 p-1.5 rounded border border-indigo-200 text-xs shadow-sm">⚙️</a>
                                <a href="{{ route('admin.packages.edit', $package->id) }}" title="Edit"
                                    class="bg-yellow-50 text-yellow-700 hover:bg-yellow-100 p-1.5 rounded border border-yellow-200 text-xs shadow-sm">✏️</a>

                                <form action="{{ route('admin.packages.destroy', $package->id) }}" method="POST"
                                    class="inline-block" onsubmit="return confirm('Yakin hapus?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Hapus"
                                        class="bg-red-50 text-red-700 hover:bg-red-200 p-1.5 rounded border border-red-200 text-xs shadow-sm">🗑️</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t bg-gray-50">
            {{ $packages->links() }}
        </div>

        <script>
            let typingTimer;
            const doneTypingInterval = 500; // Waktu tunggu 0.5 detik
            const searchInput = document.getElementById('search-input');
            const filterForm = document.getElementById('filter-form');

            // Saat user mulai mengetik
            searchInput.addEventListener('keyup', function() {
                clearTimeout(typingTimer);
                typingTimer = setTimeout(function() {
                    filterForm.submit(); // Submit otomatis setelah 0.5 detik berhenti mengetik
                }, doneTypingInterval);
            });

            // Hapus timer jika user masih lanjut mengetik
            searchInput.addEventListener('keydown', function() {
                clearTimeout(typingTimer);
            });

            // Memindahkan kursor otomatis ke akhir teks saat halaman termuat ulang (agar tidak mengganggu saat mengetik panjang)
            window.onload = function() {
                if (searchInput.value.length > 0) {
                    searchInput.focus();
                    let val = searchInput.value;
                    searchInput.value = '';
                    searchInput.value = val;
                }
            };
        </script>
    </div>
@endsection
