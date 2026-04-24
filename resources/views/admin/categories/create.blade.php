<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Kategori - UKAICHAMPION ADMIN</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center p-6">

    <div class="bg-white rounded-xl shadow-lg border-t-4 border-blue-600 w-full max-w-lg p-8">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Tambah Kategori Baru</h2>
            <a href="{{ route('admin.categories.index') }}" class="text-gray-500 hover:text-gray-800 transition-colors">✕
                Batal</a>
        </div>

        <form action="{{ route('admin.categories.store') }}" method="POST">
            @csrf
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Nama Kategori Ujian</label>
                <input type="text" name="name"
                    class="w-full px-4 py-3 rounded-lg border focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all @error('name') border-red-500 @enderror"
                    placeholder="Contoh: UTBK SNBT 2026" value="{{ old('name') }}" required autofocus>

                @error('name')
                    <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg shadow transition-colors">
                Simpan Data Kategori
            </button>
        </form>
    </div>

</body>

</html>
