<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;
use App\Models\User;
use App\Models\ExamCategory;
use App\Models\ExamPackage;
use App\Models\Question;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Akun Admin
        Admin::create([
            'name' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('admin'),
        ]);

        Admin::create([
            'name' => 'admin1',
            'email' => 'admin1@gmail.com',
            'password' => Hash::make('admin1'),
        ]);

        // 2. Buat Akun User Premium
        User::create(
            [
                'name' => 'haidar',
                'email' => 'haidar@gmail.com',
                'password' => Hash::make('haidar'),
                'is_premium' => true,
                'premium_until' => Carbon::now()->addYear(),
            ],
        );

        User::create(
            [
                'name' => 'anas',
                'email' => 'anas@gmail.com',
                'password' => Hash::make('anas'),
                'is_premium' => false,
                'premium_until' => null,
            ]
        );

        // 3. Buat Kategori Ujian
        $kategori = ExamCategory::create([
            'name' => 'Persiapan CPNS 2026',
        ]);

        // 4. Buat Paket Ujian
        $paket = ExamPackage::create([
            'exam_category_id' => $kategori->id,
            'title' => 'Tryout TIU (Tes Intelegensia Umum) - HOTS',
            'time_limit' => 30, // 30 Menit
        ]);

        // 5. Buat Soal Dummy (Teks Panjang)
        Question::create([
            'exam_package_id' => $paket->id,
            'question_text' => '<p>Pesawat tanpa awak UAV (Unmanned Aerial Vehicle) merupakan jenis pesawat terbang yang dikendalikan alat sistem kendali jarak jauh. Apa fungsi utama UAV dalam bidang militer pada masa-masa awal perkembangannya?</p>',
            'option_a' => '<p>Digunakan sebagai pesawat penumpang komersial antar benua.</p>',
            'option_b' => '<p>Digunakan sebagai pesawat sasaran tembak (Drone) untuk latihan militer.</p>',
            'option_c' => '<p>Digunakan untuk mengantarkan logistik ke daerah terpencil secara otomatis.</p>',
            'option_d' => '<p>Berfungsi murni sebagai rudal balistik yang hancur saat mengenai sasaran.</p>',
            'option_e' => '<p>Digunakan untuk pemetaan cuaca global oleh stasiun meteorologi.</p>',
            'correct_answer' => 'B',
            'explanation' => '<p>Sejarah awal pesawat tanpa awak (Drone) adalah digunakan sebagai sasaran tembak dalam latihan militer.</p>',
        ]);

        Question::create([
            'exam_package_id' => $paket->id,
            'question_text' => '<p>Jika <b>X</b> adalah waktu yang dibutuhkan untuk menyelesaikan soal ini, dan <b>Y</b> adalah tingkat kesulitan soal, maka pernyataan manakah yang paling logis?</p>',
            'option_a' => '<p>X berbanding lurus dengan Y.</p>',
            'option_b' => '<p>X berbanding terbalik dengan Y.</p>',
            'option_c' => '<p>X tidak ada hubungannya dengan Y.</p>',
            'option_d' => '<p>Y selalu konstan meskipun X berubah.</p>',
            'option_e' => '<p>Semua jawaban di atas salah.</p>',
            'correct_answer' => 'A',
            'explanation' => '<p>Secara logis, semakin tinggi tingkat kesulitan (Y), maka waktu yang dibutuhkan (X) juga akan semakin lama.</p>',
        ]);
    }
}
