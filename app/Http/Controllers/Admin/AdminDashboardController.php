<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\ExamPackage;
use App\Models\Transaction;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // 1. Ambil data admin yang sedang login saat ini
        $admin = Auth::guard('admin')->user();

        // 2. Hitung total siswa (langsung hitung semua dari tabel users)
        $totalUsers = User::count();

        // 3. Hitung total paket ujian
        $totalPackages = ExamPackage::count();

        // 4. Menjumlahkan semua nominal transaksi yang statusnya 'success'
        $revenue = Transaction::where('status', 'success')->sum('amount');

        // 5. Mengambil 5 transaksi sukses terbaru untuk ditampilkan di Aktivitas
        $recentActivities = Transaction::with('user')
            ->where('status', 'success')
            ->orderBy('paid_at', 'desc')
            ->take(5)
            ->get();

        // 6. Arahkan ke file tampilan (Blade) dan bawa SEMUA data sekaligus
        return view('admin.dashboard', compact('admin', 'totalUsers', 'totalPackages', 'revenue', 'recentActivities'));
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
