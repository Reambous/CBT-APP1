<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;

new class extends Component {
    use WithFileUploads;

    public $name;
    public $email;
    public $password;
    public $profile_picture;
    public $old_picture;

    public function mount()
    {
        $admin = auth('admin')->user(); // Gunakan guard admin
        $this->name = $admin->name;
        $this->email = $admin->email;
        $this->old_picture = $admin->profile_picture;
    }

    public function updateProfile()
    {
        $admin = auth('admin')->user();

        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email,' . $admin->id,
            'password' => 'nullable|min:3',
            'profile_picture' => 'nullable|image|max:3072', // 3MB
        ]);

        $data = [
            'name' => $this->name,
            'email' => $this->email,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->profile_picture) {
            // Hapus foto lama jika ada (Sesuai aturan bisnis)
            if ($admin->profile_picture && Storage::disk('public')->exists($admin->profile_picture)) {
                Storage::disk('public')->delete($admin->profile_picture);
            }
            $path = $this->profile_picture->store('admin-photos', 'public');
            $data['profile_picture'] = $path;
        }

        $admin->update($data);

        return redirect()->route('admin.dashboard')->with('success', 'Profil Admin berhasil diperbarui!');
    }
}; ?>

<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-lg border-t-4 border-red-600 overflow-hidden">
        <div class="p-6 border-b bg-gray-50 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-800">Pengaturan Profil Admin</h2>
                <p class="text-sm text-gray-500">Kelola identitas akses kontrol panel Anda.</p>
            </div>
            <span class="bg-red-100 text-red-700 text-xs font-bold px-3 py-1 rounded-full uppercase">Administrator</span>
        </div>

        <form wire:submit.prevent="updateProfile" class="p-8 space-y-6">
            <div class="flex flex-col md:flex-row gap-10">
                <div class="flex flex-col items-center space-y-4">
                    <div class="relative">
                        <img src="{{ $profile_picture ? $profile_picture->temporaryUrl() : ($old_picture ? asset('storage/' . $old_picture) : 'https://ui-avatars.com/api/?background=EF4444&color=fff&name=' . urlencode($name)) }}"
                            class="w-40 h-40 rounded-2xl object-cover border-4 border-gray-100 shadow-xl">
                        <div wire:loading wire:target="profile_picture"
                            class="absolute inset-0 flex items-center justify-center bg-black/20 rounded-2xl text-white font-bold">
                            ⏳ Uploading...
                        </div>
                    </div>

                    <label
                        class="w-full cursor-pointer bg-gray-800 hover:bg-gray-900 text-white text-center px-4 py-2 rounded-lg text-sm font-bold transition shadow-md">
                        <span>Pilih Foto Baru</span>
                        <input type="file" wire:model="profile_picture" class="hidden" accept="image/*">
                    </label>
                    <p class="text-[10px] text-gray-400 font-bold tracking-widest uppercase">Maksimal 3MB</p>
                </div>

                <div class="flex-1 space-y-5">
                    <div class="grid grid-cols-1 gap-5">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Nama Admin</label>
                            <input type="text" wire:model="name"
                                class="w-full px-4 py-3 rounded-lg border bg-gray-50 focus:bg-white focus:border-red-500 outline-none transition-all">
                            @error('name')
                                <span class="text-red-500 text-xs font-bold">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Email Login</label>
                            <input type="email" wire:model="email"
                                class="w-full px-4 py-3 rounded-lg border bg-gray-50 focus:bg-white focus:border-red-500 outline-none transition-all">
                            @error('email')
                                <span class="text-red-500 text-xs font-bold">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="pt-4 border-t border-gray-100 mt-2">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Ganti Password <span
                                    class="text-xs font-normal text-gray-400">(Biarkan kosong jika tidak
                                    diganti)</span></label>
                            <input type="password" wire:model="password"
                                class="w-full px-4 py-3 rounded-lg border bg-gray-50 focus:bg-white focus:border-red-500 outline-none transition-all"
                                placeholder="••••••••">
                            @error('password')
                                <span class="text-red-500 text-xs font-bold">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="pt-6 flex justify-end">
                        <button type="submit"
                            class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-10 rounded-xl shadow-lg shadow-red-200 transition transform active:scale-95">
                            Update Profil Admin
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
