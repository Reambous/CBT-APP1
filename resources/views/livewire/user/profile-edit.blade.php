<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

new class extends Component {
    use WithFileUploads;

    public $name;
    // 1. public $email; <--- SUDAH DIHAPUS
    public $password;
    public $profile_picture;
    public $old_picture;

    public function mount()
    {
        $user = auth()->user();
        $this->name = $user->name;
        // 2. $this->email = $user->email; <--- SUDAH DIHAPUS
        $this->old_picture = $user->profile_picture;
    }

    public function updateProfile()
    {
        $user = auth()->user();

        $this->validate([
            'name' => 'required|string|max:255',
            // 3. validasi email <--- SUDAH DIHAPUS
            'password' => ['nullable', Password::defaults()],
            'profile_picture' => 'nullable|image|max:3072', // Maksimal 3MB
        ]);

        $data = [
            'name' => $this->name,
            // 4. 'email' => $this->email, <--- SUDAH DIHAPUS
        ];

        // Logika Ganti Password
        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        // Logika Upload Foto (Hapus foto lama)
        if ($this->profile_picture) {
            if ($user->profile_picture && $user->profile_picture !== 'default.png') {
                Storage::disk('public')->delete($user->profile_picture);
            }
            $path = $this->profile_picture->store('profile-photos', 'public');
            $data['profile_picture'] = $path;
        }

        $user->update($data);

        return redirect()->route('user.dashboard')->with('success', 'Profil berhasil diperbarui!');
    }

    // FUNGSI BARU: Hapus Akun Sendiri (Soft Delete)
    public function deleteAccount()
    {
        $user = auth()->user();

        // 1. Lakukan Soft Delete
        $user->delete();

        // 2. Logout secara paksa
        auth()->logout();
        session()->invalidate();
        session()->regenerateToken();

        // 3. Arahkan kembali ke halaman login
        return redirect()->route('login')->with('success', 'Akun Anda telah berhasil dihapus dari sistem.');
    }
}; ?>

<div class="max-w-4xl mx-auto space-y-8 pb-12">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b bg-gray-50/50">
            <h2 class="text-xl font-bold text-gray-800">Pengaturan Profil</h2>
            <p class="text-sm text-gray-500">Kelola informasi pribadi dan keamanan akunmu.</p>
        </div>

        <form wire:submit.prevent="updateProfile" class="p-6 space-y-6">
            @if (session('success'))
                <div
                    class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm flex items-center gap-2">
                    <span>✅</span> {{ session('success') }}
                </div>
            @endif

            <div class="flex flex-col md:flex-row gap-8">
                <div class="flex flex-col items-center space-y-4">
                    <div class="relative group">
                        <img src="{{ $profile_picture ? $profile_picture->temporaryUrl() : ($old_picture ? asset('storage/' . $old_picture) : 'https://ui-avatars.com/api/?name=' . urlencode($name)) }}"
                            class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-md group-hover:opacity-75 transition-opacity">
                        <div wire:loading wire:target="profile_picture"
                            class="absolute inset-0 flex items-center justify-center bg-white/50 rounded-full">
                            <span class="animate-spin text-blue-600">⌛</span>
                        </div>
                    </div>

                    <label
                        class="cursor-pointer bg-white border border-gray-300 px-4 py-2 rounded-lg text-sm font-bold text-gray-700 hover:bg-gray-50 transition shadow-sm">
                        <span>Ganti Foto</span>
                        <input type="file" wire:model="profile_picture" class="hidden" accept="image/*">
                    </label>
                    <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider text-center">Maks. 3MB
                        (JPG/PNG)</p>
                    @error('profile_picture')
                        <span class="text-red-500 text-xs font-bold">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex-1 space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Nama Lengkap</label>
                        <input type="text" wire:model="name"
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-100 outline-none @error('name') border-red-500 @enderror">
                        @error('name')
                            <span class="text-red-500 text-xs font-bold">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Email (Terhubung Google SSO)</label>

                        {{-- Hapus wire:model, gunakan value langsung dari Auth, tambah readonly --}}
                        <input type="email" value="{{ auth()->user()->email }}" readonly
                            class="w-full px-4 py-2 border border-gray-200 bg-gray-100 text-gray-500 rounded-lg cursor-not-allowed focus:outline-none select-none">

                        <p class="text-xs text-red-500 mt-1 font-medium">*Email tidak dapat diubah demi keamanan akun.
                        </p>
                    </div>

                    <div class="pt-4 border-t border-dashed">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Ganti Password
                            <span class="text-xs font-normal text-gray-400">(Kosongkan jika tidak ingin ganti)</span>
                        </label>
                        <input type="password" wire:model="password" placeholder="••••••••"
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-100 outline-none">
                        @error('password')
                            <span class="text-red-500 text-xs font-bold">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="pt-6 flex justify-end">
                        <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-8 rounded-lg shadow-md transition transform active:scale-95">
                            Simpan Perubahan
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="bg-red-50 rounded-xl shadow-sm border border-red-200 overflow-hidden">
        <div class="p-6 border-b border-red-200 bg-red-100/50">
            <h2 class="text-xl font-bold text-red-800">Zona Berbahaya</h2>
            <p class="text-sm text-red-600">Tindakan di bawah ini tidak dapat dibatalkan secara mandiri.</p>
        </div>
        <div class="p-6 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h3 class="font-bold text-gray-800 text-lg">Hapus Akun Saya</h3>
                <p class="text-sm text-gray-600 mt-1 max-w-2xl">
                    Setelah dihapus, Anda akan otomatis keluar dari aplikasi dan tidak bisa masuk kembali tanpa bantuan
                    Administrator. Data riwayat ujian Anda akan tetap aman di sistem kami (Soft Delete).
                </p>
            </div>

            <button wire:click="deleteAccount"
                wire:confirm="PERINGATAN KERAS! Apakah Anda sangat yakin ingin menghapus akun ini? Anda akan langsung dikeluarkan dari sistem."
                class="whitespace-nowrap bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg shadow-md transition transform active:scale-95">
                🗑️ Hapus Akun Saya
            </button>
        </div>
    </div>
</div>
