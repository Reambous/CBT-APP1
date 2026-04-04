<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DummyDataSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();
        $password = Hash::make('password'); // Password default: password

        // 1. Generate 5 Admins
        for ($i = 1; $i <= 5; $i++) {
            DB::table('admins')->insert([
                'name' => "Admin Ke-$i",
                'email' => "admin$i@cbt.com",
                'password' => $password,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // 2. Generate 5 Users (Siswa)
        for ($i = 1; $i <= 5; $i++) {
            DB::table('users')->insert([
                'name' => "Siswa Ke-$i",
                'email' => "siswa$i@cbt.com",
                'password' => $password,
                'is_premium' => ($i % 2 == 0) ? true : false, // User genap jadi premium
                'premium_until' => ($i % 2 == 0) ? $now->copy()->addYear() : null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // 3. Generate 5 Exam Categories
        for ($i = 1; $i <= 5; $i++) {
            DB::table('exam_categories')->insert([
                'name' => "Kategori Ujian $i",
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // 4. Generate 5 Exam Packages
        for ($i = 1; $i <= 5; $i++) {
            DB::table('exam_packages')->insert([
                'exam_category_id' => $i,
                'title' => "Paket Tryout Super $i",
                'time_limit' => 120, // 120 menit
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // 5. Generate 5 Questions (Dimasukkan ke Paket 1 agar mudah dites)
        $options = ['A', 'B', 'C', 'D', 'E'];
        for ($i = 1; $i <= 5; $i++) {
            DB::table('questions')->insert([
                'exam_package_id' => 1,
                'question_text' => "<p>Ini adalah teks soal dummy nomor $i. Ibukota negara Indonesia adalah?</p>",
                'option_a' => "Jakarta",
                'option_b' => "Bandung",
                'option_c' => "Surabaya",
                'option_d' => "Medan",
                'option_e' => "Semarang",
                'correct_answer' => $options[array_rand($options)], // Jawaban benar diacak
                'explanation' => "<p>Pembahasan soal nomor $i. Silakan pelajari kembali peta geografi.</p>",
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // --- TRANSACTIONS DIHILANGKAN SESUAI PERMINTAAN ---

        // 6. Generate 5 User Results (Siswa 1 sampai 5 mengerjakan Paket 1)
        for ($i = 1; $i <= 5; $i++) {
            $resultId = DB::table('user_results')->insertGetId([
                'user_id' => $i,
                'exam_package_id' => 1,
                'attempt_number' => 1,
                'score' => rand(40, 100), // Skor acak
                'finished_at' => $now->copy()->subMinutes(rand(10, 100)),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // 7. Generate 5 User Answers untuk setiap hasil ujian di atas
            for ($j = 1; $j <= 5; $j++) {
                DB::table('user_answers')->insert([
                    'result_id' => $resultId,
                    'question_id' => $j,
                    'selected_option' => $options[array_rand($options)], // Jawaban pilihan siswa diacak
                    'is_correct' => (rand(0, 1) == 1), // Diacak benar atau salah
                ]);
            }
        }
    }
}
