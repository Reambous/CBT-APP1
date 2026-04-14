<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class K6UserSeeder extends Seeder
{
    public function run(): void
    {
        // Matikan event dan observer sejenak agar proses seeding super cepat
        User::flushEventListeners();

        $users = [];
        $password = Hash::make('password123'); // Semua password sama biar mudah

        for ($i = 1; $i <= 500; $i++) {
            $users[] = [
                'name' => 'Siswa Robot ' . $i,
                'email' => 'siswa' . $i . '@ujian.com',
                'password' => $password,
                // Kolom 'role' SUDAH DIHAPUS DARI SINI
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Insert per 100 data agar memori tidak jebol
            if ($i % 100 == 0) {
                User::insert($users);
                $users = []; // Kosongkan array untuk 100 berikutnya
            }
        }
    }
}
