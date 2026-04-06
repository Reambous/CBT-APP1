<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User; // Pastikan Model User di-import di atas sini
use Symfony\Component\HttpFoundation\Response;

class CheckPremiumExpiration
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && $user->is_premium && $user->premium_until) {

            if (Carbon::now()->greaterThan($user->premium_until)) {

                // PERBAIKAN DI SINI: Kita panggil Model User-nya secara langsung
                User::find($user->id)->update([
                    'is_premium' => false,
                    'premium_until' => null,
                ]);

                session()->flash('error', 'Masa aktif Premium Anda telah habis. Anda sekarang kembali menjadi akun Reguler.');
            }
        }

        return $next($request);
    }
}
