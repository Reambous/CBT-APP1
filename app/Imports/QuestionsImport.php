<?php

namespace App\Imports;

use App\Models\Question;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class QuestionsImport implements ToModel, WithHeadingRow
{
    public $packageId;

    // Menangkap ID Paket Ujian dari Livewire
    public function __construct($packageId)
    {
        $this->packageId = $packageId;
    }

    public function model(array $row)
    {
        // Cegah error: Jika kolom 'soal' kosong, lewati baris ini
        if (!isset($row['soal']) || empty(trim($row['soal']))) {
            return null;
        }

        // Kolom 'nomor' dari Excel tidak kita simpan ke database
        // karena sistem web sudah mengurutkan nomor secara otomatis.

        return new Question([
            'exam_package_id' => $this->packageId,
            // Kita bungkus dengan tag <p> agar saat diedit di web, formatnya rapi terbaca oleh Editor
            'question_text'   => '<p>' . $row['soal'] . '</p>',
            'option_a'        => $row['opsi_a'] ?? null,
            'option_b'        => $row['opsi_b'] ?? null,
            'option_c'        => $row['opsi_c'] ?? null,
            'option_d'        => $row['opsi_d'] ?? null,
            'option_e'        => $row['opsi_e'] ?? null,
            'correct_answer'  => strtoupper(trim($row['kunci_jawaban'])), // Pastikan jadi A/B/C/D/E besar
            'explanation'     => isset($row['pembahasan']) ? '<p>' . $row['pembahasan'] . '</p>' : null,
        ]);
    }
}
