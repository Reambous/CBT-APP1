<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Paket - UKAICHAMPION ADMIN</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center p-6">

    <div class="bg-white rounded-xl shadow-lg border-t-4 border-blue-600 w-full max-w-lg p-8">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Tambah Paket Ujian</h2>
            <a href="{{ route('admin.packages.index') }}" class="text-gray-500 hover:text-gray-800 transition-colors">✕
                Batal</a>
        </div>

        <form action="{{ route('admin.packages.store') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Pilih Kategori Induk</label>
                <select name="exam_category_id"
                    class="w-full px-4 py-3 rounded-lg border focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all bg-white"
                    required>
                    <option value="">-- Pilih Kategori --</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}"
                            {{ old('exam_category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('exam_category_id')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Nama Paket (Contoh: Tryout 1)</label>
                <input type="text" name="title"
                    class="w-full px-4 py-3 rounded-lg border focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all"
                    value="{{ old('title') }}" required>
                @error('title')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Durasi Waktu (Menit)</label>
                <input type="number" name="time_limit"
                    class="w-full px-4 py-3 rounded-lg border focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all"
                    placeholder="Contoh: 90" value="{{ old('time_limit') }}" min="1" required>
                @error('time_limit')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-8">
                <label class="block text-gray-700 text-sm font-bold mb-2">Kasta / Tipe Akses Paket</label>
                <select name="minimum_tier"
                    class="w-full px-4 py-3 rounded-lg border focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all bg-gray-50 font-medium text-gray-800"
                    required>
                    <option value="gratis" {{ old('minimum_tier') == 'gratis' ? 'selected' : '' }}>🆓 Gratis (Bisa
                        diakses semua user)</option>
                    <option value="plus" {{ old('minimum_tier') == 'plus' ? 'selected' : '' }}>✨ Plus (Minimal kasta
                        Plus)</option>
                    <option value="pro" {{ old('minimum_tier') == 'pro' ? 'selected' : '' }}>👑 Pro (Minimal kasta
                        Pro)</option>
                    <option value="ultra" {{ old('minimum_tier') == 'ultra' ? 'selected' : '' }}>🔮 Ultra (Eksklusif
                        kasta tertinggi)</option>
                </select>
                <p class="text-xs text-gray-500 mt-2">Pilih batas minimal kasta *membership* yang dibutuhkan untuk
                    mengakses paket soal ini.</p>
                @error('minimum_tier')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg shadow transition-colors">
                Simpan Paket Ujian
            </button>
        </form>
    </div>

</body>

</html>
