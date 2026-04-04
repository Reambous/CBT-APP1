@extends('admin.layouts.sidebar') {{-- Sesuaikan dengan nama layout admin kamu --}}

@section('content')
    <div class="max-w-4xl mx-auto w-full">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Daftarkan User Baru</h1>
            <a href="{{ route('admin.users.index') }}"
                class="text-gray-500 hover:text-gray-800 font-semibold transition-colors">
                ✕ Batal
            </a>
        </div>

        <form action="{{ route('admin.users.store') }}" method="POST"
            class="bg-white p-8 rounded-xl shadow-lg border-t-4 border-blue-600">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Nama Lengkap <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}"
                        class="w-full px-4 py-2 border @error('name') border-red-500 bg-red-50 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-blue-200 outline-none"
                        required placeholder="Contoh: Budi Santoso">
                    @error('name')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Alamat Email <span
                            class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}"
                        class="w-full px-4 py-2 border @error('email') border-red-500 bg-red-50 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-blue-200 outline-none"
                        required placeholder="budi@email.com">
                    @error('email')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Password <span
                            class="text-red-500">*</span></label>
                    <input type="password" name="password"
                        class="w-full px-4 py-2 border @error('password') border-red-500 bg-red-50 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-blue-200 outline-none"
                        required placeholder="Minimal 3 karakter">
                    @error('password')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Aktivasi Membership</label>
                    <div class="flex items-center gap-4 bg-yellow-50 p-3 rounded-lg border border-yellow-200">
                        <input type="checkbox" name="is_premium" id="is_premium" value="1"
                            {{ old('is_premium') ? 'checked' : '' }} class="w-5 h-5 text-yellow-600 focus:ring-yellow-500"
                            onchange="togglePremiumDate()">
                        <label for="is_premium" class="text-sm font-bold text-yellow-800 cursor-pointer">Jadikan User
                            Premium 👑</label>
                    </div>
                </div>

                <div id="premium_date_container" class="mb-4 {{ old('is_premium') ? '' : 'hidden' }}">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Berlaku Hingga</label>
                    <input type="date" name="premium_until"
                        class="w-full px-4 py-2 border @error('premium_until') border-red-500 bg-red-50 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-yellow-200 outline-none"
                        value="{{ old('premium_until', date('Y-m-d', strtotime('+1 year'))) }}">
                    @error('premium_until')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1 italic">*Default: 1 tahun dari sekarang.</p>
                </div>
            </div>

            <hr class="my-6">

            <div class="flex justify-end gap-4">
                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg shadow-lg transition-colors">
                    💾 Simpan Data User
                </button>
            </div>
        </form>
    </div>

    <script>
        function togglePremiumDate() {
            const checkbox = document.getElementById('is_premium');
            const container = document.getElementById('premium_date_container');
            if (checkbox.checked) {
                container.classList.remove('hidden');
            } else {
                container.classList.add('hidden');
            }
        }
    </script>
@endsection
