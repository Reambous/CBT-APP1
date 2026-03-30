<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategori Soal - CBT ADMIN</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="bg-gray-100 flex min-h-screen">

    <div class="w-64 bg-gray-900 text-white flex flex-col shadow-xl">
        <div class="p-6 text-center border-b border-gray-800">
            <h2 class="text-2xl font-extrabold text-red-500">CBT ADMIN</h2>
        </div>
        <div class="grow p-4">
            <a href="{{ route('admin.dashboard') }}"
                class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 mb-2">📊 Dashboard</a>
            <a href="{{ route('admin.categories.index') }}"
                class="block py-2.5 px-4 rounded transition duration-200 bg-gray-800 border-l-4 border-red-500 mb-2 font-bold">📚
                Kategori Soal</a>
        </div>
        <div class="p-4 border-t border-gray-800">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit"
                    class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded transition-colors">Logout</button>
            </form>
        </div>
    </div>

    <div class="flex-1 p-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Manajemen Kategori Ujian</h1>
            <a href="{{ route('admin.categories.create') }}"
                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow transition-colors">
                + Tambah Kategori Baru
            </a>
        </div>

        @if (session('success'))
            <div x-data="{ show: true }" x-show="show"
                class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded relative shadow-sm">
                <span class="block sm:inline font-semibold">{{ session('success') }}</span>
                <button @click="show = false" class="absolute top-0 bottom-0 right-0 px-4 py-3">
                    <span class="text-green-500 font-bold text-xl">&times;</span>
                </button>
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-16">No
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nama
                            Kategori</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider w-48">
                            Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($categories as $index => $category)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $categories->firstItem() + $index }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                {{ $category->name }}
                            </td>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium flex justify-end gap-2">
                                <a href="{{ route('admin.categories.edit', $category->id) }}"
                                    class="bg-yellow-100 text-yellow-700 hover:bg-yellow-200 px-3 py-1 rounded transition-colors">Edit</a>

                                <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST"
                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori ini? Data yang terhubung akan ikut tersembunyi.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="bg-red-100 text-red-700 hover:bg-red-200 px-3 py-1 rounded transition-colors">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-gray-500 font-medium">
                                Belum ada data kategori ujian. Silakan tambah baru.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if ($categories->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $categories->links() }}
                </div>
            @endif
        </div>
    </div>

</body>

</html>
