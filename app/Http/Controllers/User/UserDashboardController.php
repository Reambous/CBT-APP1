<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ExamPackage;
use App\Models\UserResult;
use App\Models\Transaction; // TAMBAHAN PENTING UNTUK INVOICE
use Illuminate\Http\Request;

class UserDashboardController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        $user = $request->user();

        // 1. Tentukan batas Kasta (Tier) apa saja yang boleh dilihat oleh pelanggan ini
        $allowedTiers = ['gratis']; // Default: Hanya boleh lihat yang gratis

        $isPremiumActive = $user->is_premium && $user->premium_until && now()->lessThanOrEqualTo($user->premium_until);

        if ($isPremiumActive) {
            if ($user->premium_tier === 'ultra') {
                $allowedTiers = ['gratis', 'plus', 'pro', 'ultra'];
            } elseif ($user->premium_tier === 'pro') {
                $allowedTiers = ['gratis', 'plus', 'pro'];
            } elseif ($user->premium_tier === 'plus') {
                $allowedTiers = ['gratis', 'plus'];
            }
        }

        // 2. Siapkan Query Dasar & Filter berdasarkan Kasta Pelanggan
        // 2. Siapkan Query Dasar & Filter berdasarkan Kasta Pelanggan
        $query = ExamPackage::with('examCategory')
            ->withCount('questions')
            ->has('questions')
            ->where('is_published', true) // 👈 TAMBAHKAN INI (Kunci Pintu Dashboard)
            ->whereHas('examCategory', function ($q) {
                $q->whereNull('deleted_at');
            })
            // TAMPILKAN ETALASE: Hanya paket yang sesuai dengan kasta pelanggan
            ->whereIn('minimum_tier', $allowedTiers);

        // 3. Eksekusi: Ambil 3 data terbaru
        $packages = $query->latest()->take(3)->get();

        // 4. Hitung ujian yang sudah selesai
        $completedExamsCount = UserResult::where('user_id', $user->id)
            ->whereNotNull('finished_at')
            ->count();

        // Kirim status isPremiumActive agar tampilan Blade lebih mudah
        return view('user.dashboard', compact('packages', 'completedExamsCount', 'isPremiumActive'));
    }

    public function exams()
    {
        $packages = ExamPackage::with('examCategory')
            ->withCount('questions')
            ->where('is_published', true) // ✅ Sudah ada
            ->has('questions')           // ✅ Sudah ada
            ->latest()
            ->get();

        return view('user.exams', compact('packages'));
    }

    public function history()
    {
        return view('user.history');
    }

    public function profile()
    {
        return view('user.profile');
    }

    // Halaman Review Jawaban (Sistem Gembok Cerdas)
    public function review(Request $request, $id)
    {
        // 1. Ambil data pelanggan dan hasil ujian
        $user = \App\Models\User::find($request->user()->id);
        $result = UserResult::with(['examPackage.examCategory', 'userAnswers.question'])
            ->where('user_id', $user->id)
            ->findOrFail($id);

        // 2. Cek Kasta Paket Ujian tersebut
        $packageTier = $result->examPackage->minimum_tier;

        // 3. GEMBOK BACKEND: Gunakan fungsi pintar di Model User
        // Jika pelanggan turun kasta (misal dari Pro ke Gratis), dia tidak bisa lihat review soal Pro-nya lagi.
        if (!$user->canAccessTier($packageTier)) {
            return redirect()->route('user.history')
                ->with('error', 'Akses ditolak! Pembahasan ini eksklusif untuk kasta ' . ucfirst($packageTier) . '. Silakan Upgrade langganan Anda.');
        }

        return view('user.review', compact('result'));
    }

    public function upgrade()
    {
        return view('user.upgrade');
    }

    // =========================================================
    // FITUR BARU: CHECKOUT & INVOICE
    // =========================================================

    public function checkout(Request $request)
    {
        if (!$request->user()) return redirect()->route('login');

        $request->validate(['tier' => 'required|in:plus,pro,ultra']);
        $user = $request->user();

        // 1. SATPAM ANTI-SPAM: Cek apakah ada tagihan pending?
        $pendingTx = \App\Models\Transaction::where('user_id', $user->id)->where('status', 'pending')->first();
        if ($pendingTx) {
            return redirect()->route('user.invoice', $pendingTx->id)
                ->with('error', '⚠️ Anda masih memiliki tagihan yang belum diselesaikan. Selesaikan atau batalkan tagihan ini terlebih dahulu.');
        }

        // 2. SATPAM ANTI-DOWNGRADE: Cek kasta saat ini
        $tierWeights = ['gratis' => 0, 'plus' => 1, 'pro' => 2, 'ultra' => 3];
        $currentTier = $user->is_premium ? ($user->premium_tier ?? 'gratis') : 'gratis';

        if ($tierWeights[$request->tier] < $tierWeights[$currentTier] && $user->is_premium && now()->lessThan($user->premium_until)) {
            return back()->with('error', '⛔ Anda tidak bisa turun ke paket ' . strtoupper($request->tier) . ' karena Anda masih memiliki paket ' . strtoupper($currentTier) . ' aktif.');
        }

        // 3. Lolos Satpam -> Buat Transaksi
        // 3. Lolos Satpam -> Buat Transaksi
        $prices = ['plus' => 9999, 'pro' => 29999, 'ultra' => 49999];
        $transaction = \App\Models\Transaction::create([
            'user_id' => $user->id,
            'amount' => $prices[$request->tier],
            'tier' => $request->tier, // <--- TAMBAHKAN BARIS INI
            'status' => 'pending',
        ]);

        return redirect()->route('user.invoice', $transaction->id)->with('success', 'Pesanan berhasil dibuat! Selesaikan pembayaran Anda.');
    }

    public function invoice(Request $request, $id)
    {
        if (!$request->user()) return redirect()->route('login');

        try {
            $transaction = \App\Models\Transaction::where('user_id', $request->user()->id)->findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Jika tagihan tidak ketemu (karena sudah ditolak/dihapus Admin)
            return redirect()->route('user.upgrade')->with('error', 'Tagihan tidak ditemukan atau telah ditolak oleh Admin. Silakan buat pesanan baru.');
        }

        // Kita jadikan integer (int) agar kebal terhadap error tipe data
        $amount = (int) $transaction->amount;

        $tierName = 'Paket PLUS';
        if ($amount === 29999) $tierName = 'Paket PRO';
        if ($amount === 49999) $tierName = 'Paket ULTRA';

        return view('user.invoice', compact('transaction', 'tierName'));
    }

    // FITUR BARU: BATALKAN TAGIHAN
    public function cancelInvoice(Request $request, $id)
    {
        $transaction = \App\Models\Transaction::where('user_id', $request->user()->id)
            ->where('status', 'pending')
            ->findOrFail($id);

        $transaction->delete(); // Atau ubah statusnya jadi 'failed' / 'cancelled'
        return redirect()->route('user.upgrade')->with('success', 'Tagihan sebelumnya berhasil dibatalkan. Silakan pilih paket baru.');
    }
}
