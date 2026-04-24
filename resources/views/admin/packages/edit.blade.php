<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Paket - UKAICHAMPION ADMIN</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center p-6">

    <div class="bg-white rounded-xl shadow-lg border-t-4 border-yellow-500 w-full max-w-lg p-8">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Edit Paket Ujian</h2>
            <a href="{{ route('admin.packages.index') }}" class="text-gray-500 hover:text-gray-800 transition-colors">✕
                Batal</a>
        </div>

        <form action="{{ route('admin.packages.update', $package->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Pilih Kategori Induk</label>
                <select name="exam_category_id"
                    class="w-full px-4 py-3 rounded-lg border focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 outline-none transition-all bg-white"
                    required>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}"
                            {{ (old('exam_category_id') ?? $package->exam_category_id) == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('exam_category_id')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Nama Paket</label>
                <input type="text" name="title"
                    class="w-full px-4 py-3 rounded-lg border focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 outline-none transition-all"
                    value="{{ old('title', $package->title) }}" required>
                @error('title')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Durasi Waktu (Menit)</label>
                <input type="number" name="time_limit"
                    class="w-full px-4 py-3 rounded-lg border focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 outline-none transition-all"
                    value="{{ old('time_limit', $package->time_limit) }}" min="1" required>
                @error('time_limit')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-8">
                <label class="block text-gray-700 text-sm font-bold mb-2">Kasta / Tipe Akses Paket</label>
                <select name="minimum_tier"
                    class="w-full px-4 py-3 rounded-lg border focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 outline-none transition-all bg-gray-50 font-medium text-gray-800"
                    required>
                    <option value="gratis"
                        {{ old('minimum_tier', $package->minimum_tier) == 'gratis' ? 'selected' : '' }}>🆓 Gratis (Bisa
                        diakses semua user)</option>
                    <option value="plus"
                        {{ old('minimum_tier', $package->minimum_tier) == 'plus' ? 'selected' : '' }}>✨ Plus (Minimal
                        kasta Plus)</option>
                    <option value="pro"
                        {{ old('minimum_tier', $package->minimum_tier) == 'pro' ? 'selected' : '' }}>👑 Pro (Minimal
                        kasta Pro)</option>
                    <option value="ultra"
                        {{ old('minimum_tier', $package->minimum_tier) == 'ultra' ? 'selected' : '' }}>🔮 Ultra
                        (Eksklusif kasta tertinggi)</option>
                </select>
                <p class="text-xs text-gray-500 mt-2">Pilih batas minimal kasta *membership* yang dibutuhkan untuk
                    mengakses paket soal ini.</p>
                @error('minimum_tier')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                class="w-full bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-3 px-4 rounded-lg shadow transition-colors">
                Simpan Perubahan
            </button>
        </form>
    </div>

</body>

</html>
