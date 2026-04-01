<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ExamPackage;
use App\Models\UserResult;
use Illuminate\Http\Request; // 1. TAMBAHKAN INI UNTUK REQUEST

class UserDashboardController extends Controller
{
    // 2. TANGKAP $request DI SINI
    public function index(Request $request)
    {
        // Pengaman: Jika tiba-tiba user belum login tapi tersasar ke sini
        if (!$request->user()) {
            return redirect()->route('login');
        }

        $packages = ExamPackage::with('examCategory')
            ->withCount('questions')
            ->whereHas('examCategory', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->latest()
            ->take(3)
            ->get();

        // 3. GUNAKAN $request->user()->id SEBAGAI GANTI auth()->id()
        $completedExamsCount = UserResult::where('user_id', $request->user()->id)
            ->whereNotNull('finished_at')
            ->count();

        return view('user.dashboard', compact('packages', 'completedExamsCount'));
    }
}
