<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Soal - CBT ADMIN</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 flex min-h-screen">

    <div class="flex-1 p-8 max-w-6xl mx-auto w-full">

        <div
            class="bg-white p-6 rounded-xl shadow-sm border-t-4 border-indigo-600 mb-6 flex justify-between items-center">
            <div>
                <p class="text-sm font-bold text-gray-500 mb-1">RUANG KELOLA SOAL</p>
                <h1 class="text-3xl font-extrabold text-gray-800">{{ $package->title }}</h1>
                <p class="text-gray-600 mt-2">
                    Kategori: <span class="font-semibold">{{ $package->examCategory->name }}</span> |
                    Durasi: <span class="font-semibold text-blue-600">⏱️ {{ $package->time_limit }} Menit</span> |
                    Total Soal: <span class="font-semibold text-green-600">{{ $package->questions->count() }}
                        Soal</span>
                </p>
            </div>
            <a href="{{ route('admin.packages.index') }}"
                class="text-gray-500 hover:text-gray-800 font-semibold transition-colors bg-gray-100 px-4 py-2 rounded-lg">
                ✕ Kembali ke Daftar Paket
            </a>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-gray-800">Daftar Soal</h2>

                <a href="{{ route('admin.questions.create', ['package_id' => $package->id]) }}"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow whitespace-nowrap transition-colors">
                    + Tambah Soal Baru
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 border rounded-lg">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left font-bold text-gray-500 uppercase text-xs w-16">No</th>
                            <th class="px-6 py-4 text-left font-bold text-gray-500 uppercase text-xs">Pertanyaan</th>
                            <th class="px-6 py-4 text-center font-bold text-gray-500 uppercase text-xs w-24">Kunci</th>
                            <th class="px-6 py-4 text-right font-bold text-gray-500 uppercase text-xs w-48">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($package->questions as $index => $q)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-500 font-medium">{{ $index + 1 }}</td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-800 line-clamp-2">{!! Str::limit(strip_tags($q->question_text), 100) !!}</p>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span
                                        class="bg-green-100 text-green-800 font-bold px-3 py-1 rounded-full text-xs">{{ $q->correct_answer }}</span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm">
                                    <a href="#"
                                        class="text-yellow-600 hover:text-yellow-900 mr-3 font-semibold">Edit</a>
                                    <a href="#" class="text-red-600 hover:text-red-900 font-semibold">Hapus</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-gray-500">
                                    <p class="mb-2 text-lg">📭 Belum ada soal di paket ini.</p>
                                    <p class="text-sm">Klik tombol "+ Tambah Soal Baru" di atas untuk mulai membuat
                                        soal.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm border mt-6 border-t-4 border-green-500">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-800">Navigasi Cepat (Lompat ke Soal)</h3>
                    <span class="text-sm text-gray-500 italic">Klik angka untuk langsung mengedit soal tersebut.</span>
                </div>

                <div class="flex flex-wrap gap-2">
                    @forelse($package->questions as $index => $q)
                        <a href="{{ route('admin.questions.edit', $q->id) }}"
                            class="w-10 h-10 flex items-center justify-center rounded border border-gray-300 bg-gray-50 hover:bg-blue-600 hover:text-white hover:border-blue-600 transition-all font-bold text-gray-700 shadow-sm"
                            title="Edit Soal No. {{ $index + 1 }}">
                            {{ $index + 1 }}
                        </a>
                    @empty
                        <p class="text-sm text-gray-400">Belum ada kotak navigasi karena soal masih kosong.</p>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

</body>

</html>
