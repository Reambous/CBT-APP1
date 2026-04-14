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
        $user = \App\Models\User::find(Auth::id());

        // 1. AMBIL DATA PAKET TERLEBIH DAHULU
        $package = ExamPackage::withCount('questions')->findOrFail($package_id);

        // 2. CEK SOAL KOSONG
        if ($package->questions_count == 0) {
            return redirect()->route('user.exams')
                ->with('error', 'Paket ujian ini belum memiliki soal.');
        }

        // 3. CEK AKSES PREMIUM
        if ($package->is_premium && !$user->is_premium) {
            return redirect()->route('user.exams')
                ->with('error', 'Akses ditolak! Anda harus Upgrade ke Premium untuk membuka ujian ini.');
        }

        // --- MULAI PERUBAHAN SATPAM PINTU BELAKANG ---

        // 4. CEK ATTEMPT & LIMIT GRATIS
        // Kita cek apakah user sudah pernah menyelesaikan paket ini sebelumnya
        $lastAttempt = UserResult::where('user_id', $user->id)
            ->where('exam_package_id', $package_id)
            ->whereNotNull('finished_at') // Kita hitung yang sudah selesai saja
            ->max('attempt_number');

        // JIKA PAKET GRATIS DAN SUDAH PERNAH MENGERJAKAN (Attempt >= 1)
        if ($package->minimum_tier == 'gratis' && $lastAttempt >= 1) {
            return redirect()->route('user.exams')
                ->with('error', 'Maaf, paket gratis hanya bisa dikerjakan 1 kali. Silakan upgrade ke Premium untuk pengerjaan tanpa batas!');
        }

        // --- SELESAI PERUBAHAN ---

        $currentAttempt = $lastAttempt ? $lastAttempt + 1 : 1;

        // 5. BUAT KERTAS UJIAN
        $result = UserResult::create([
            'user_id'         => $user->id,
            'exam_package_id' => $package_id,
            'attempt_number'  => $currentAttempt,
            'score'           => 0,
            'finished_at'     => null,
        ]);

        // 6. Arahkan ke Halaman Livewire Ujian
        return redirect()->route('exam.play', $result->id);
    }

    public function play($result_id)
    {
        // Panggil halaman pembungkus ujian dan kirimkan ID result-nya
        return view('user.exam', compact('result_id'));
    }
}
