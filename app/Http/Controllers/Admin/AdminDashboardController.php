<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Ambil data admin yang sedang login saat ini
        $admin = Auth::guard('admin')->user();

        // Arahkan ke file tampilan (Blade) dan bawa data admin tersebut
        return view('admin.dashboard', [
            'totalUsers' => \App\Models\User::count(),
            'totalPackages' => \App\Models\ExamPackage::count(),
            'revenue' => 0, // Ganti dengan logika pendapatanmu nanti
        ]);
    }
    public function profile()
    {
        return view('admin.profile');
    }

    public function transactions()
    {
        return view('admin.transactions');
    }
}
