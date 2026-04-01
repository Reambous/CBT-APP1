<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ExamPackage;
use App\Models\UserResult;
use Illuminate\Support\Facades\Auth;

class ExamController extends Controller
{
    // Fungsi saat tombol "Mulai Kerjakan" ditekan
    public function startExam($package_id)
    {
        $user = Auth::user();

        // 1. CEK PREMIUM: Tolak jika bukan user premium
        if (!$user->is_premium) {
            // PERBAIKAN: Lempar ke user.exams agar tetap di halaman Katalog
            return redirect()->route('user.exams')
                ->with('error', 'Akses ditolak! Anda harus Upgrade ke Premium untuk mengerjakan ujian ini.');
        }

        $package = ExamPackage::withCount('questions')->findOrFail($package_id);

        // Validasi: Jangan mulai jika paket belum ada soalnya
        if ($package->questions_count == 0) {
            // PERBAIKAN: Lempar ke user.exams
            return redirect()->route('user.exams')
                ->with('error', 'Paket ujian ini belum memiliki soal.');
        }

        // 2. CEK ATTEMPT: Cari tahu ini percobaan ke-berapa (LOGIKAMU SUDAH KEREN!)
        $lastAttempt = UserResult::where('user_id', $user->id)
            ->where('exam_package_id', $package_id)
            ->max('attempt_number');

        $currentAttempt = $lastAttempt ? $lastAttempt + 1 : 1;

        // 3. BUAT KERTAS UJIAN: Catat ke tabel USER_RESULTS
        $result = UserResult::create([
            'user_id'         => $user->id,
            'exam_package_id' => $package_id,
            'attempt_number'  => $currentAttempt,
            'score'           => 0, // Nilai awal 0
            'finished_at'     => null, // Null menandakan ujian sedang berlangsung
        ]);

        // 4. Arahkan ke Halaman Livewire Ujian yang sesungguhnya
        return redirect()->route('exam.play', $result->id);
    }

    public function play($result_id)
    {
        // Panggil halaman pembungkus ujian dan kirimkan ID result-nya
        return view('user.exam', compact('result_id'));
    }
}
