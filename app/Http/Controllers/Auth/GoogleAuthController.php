<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class GoogleAuthController extends Controller
{
    // 1. Melempar user ke halaman Login Google
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    // 2. Menerima kembalian data dari Google
    public function callback()
    {
        try {
            // Ambil data KTP Digital dari Google
            $googleUser = Socialite::driver('google')->user();

            // Cek apakah user ini sudah pernah login pakai Google sebelumnya?
            $user = User::where('google_id', $googleUser->id)->first();

            if ($user) {
                // Skenario A: Sudah pernah, langsung suruh masuk!
                Auth::login($user);
            } else {
                // Skenario B: Belum pernah pakai Google. Cek apakah emailnya sudah terdaftar manual?
                $existingUser = User::where('email', $googleUser->email)->first();

                if ($existingUser) {
                    // Kalau email sudah ada (misal dibikinkan Admin), kita sambungkan saja KTP Google-nya
                    $existingUser->update([
                        'google_id' => $googleUser->id,
                    ]);
                    Auth::login($existingUser);
                } else {
                    // Skenario C: User benar-benar baru! Buatkan akun otomatis.
                    $newUser = User::create([
                        'name' => $googleUser->name,
                        'email' => $googleUser->email,
                        'google_id' => $googleUser->id,
                        // Password tidak perlu diisi karena sudah kita buat nullable
                        // Kita beri kasta gratis sebagai default
                        'premium_tier' => 'gratis',
                        'is_premium' => false,
                    ]);
                    Auth::login($newUser);
                }
            }

            // Setelah sukses login, mau dilempar ke mana? (Sesuaikan dengan nama route dashboard siswa kamu)
            return redirect()->route('user.dashboard')->with('success', 'Berhasil login dengan Google!');
        } catch (Exception $e) {
            // Kalau batal login atau ada error jaringan
            return redirect()->route('login')->with('error', 'Terjadi kesalahan saat login dengan Google. Silakan coba lagi.');
        }
    }
}
