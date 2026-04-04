@extends('admin.layouts.sidebar') {{-- Sesuaikan dengan nama layout admin kamu --}}

@section('content')
    <div class="max-w-4xl mx-auto w-full">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Edit Profil User</h1>
            <a href="{{ route('admin.users.index') }}"
                class="text-gray-500 hover:text-gray-800 font-semibold transition-colors">
                ✕ Batal
            </a>
        </div>

        <form action="{{ route('admin.users.update', $user->id) }}" method="POST"
            class="bg-white p-8 rounded-xl shadow-lg border-t-4 border-yellow-500">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ $user->name }}"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-200 outline-none" required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Alamat Email</label>
                    <input type="email" name="email" value="{{ $user->email }}"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-200 outline-none" required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Password Baru</label>
                    <input type="password" name="password"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-200 outline-none"
                        placeholder="Kosongkan jika tidak diubah">
                    <p class="text-xs text-gray-400 mt-1">*Isi jika ingin meriset password user.</p>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Membership</label>
                    <div class="flex items-center gap-4 bg-yellow-50 p-3 rounded-lg border border-yellow-200">
                        <input type="checkbox" name="is_premium" id="is_premium" value="1"
                            class="w-5 h-5 text-yellow-600 focus:ring-yellow-500" {{ $user->is_premium ? 'checked' : '' }}
                            onchange="togglePremiumDate()">
                        <label for="is_premium" class="text-sm font-bold text-yellow-800 cursor-pointer">Status Premium
                            👑</label>
                    </div>
                </div>

                <div id="premium_date_container" class="mb-4 {{ $user->is_premium ? '' : 'hidden' }}">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Berlaku Hingga</label>
                    <input type="date" name="premium_until"
                        value="{{ $user->premium_until ? \Carbon\Carbon::parse($user->premium_until)->format('Y-m-d') : '' }}"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-yellow-200 outline-none">
                </div>
            </div>

            <hr class="my-6">

            <div class="flex justify-end gap-4">
                <button type="submit"
                    class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-3 px-8 rounded-lg shadow-lg transition-colors">
                    💾 Perbarui Data User
                </button>
            </div>
        </form>
    </div>

    <script>
        function togglePremiumDate() {
            const checkbox = document.getElementById('is_premium');
            const container = document.getElementById('premium_date_container');
            container.classList.toggle('hidden', !checkbox.checked);
        }
    </script>
@endsection
