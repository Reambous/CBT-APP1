<?php

namespace App\Imports;

use App\Models\Question;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
// TAMBAHAN TURBO EXCEL
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class QuestionsImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading
{
    public $packageId;

    public function __construct($packageId)
    {
        $this->packageId = $packageId;
    }

    public function model(array $row)
    {
        if (!isset($row['soal']) || empty(trim($row['soal']))) {
            return null;
        }

        return new Question([
            'exam_package_id' => $this->packageId,
            'question_text'   => '<p>' . $row['soal'] . '</p>',
            'option_a'        => $row['opsi_a'] ?? null,
            'option_b'        => $row['opsi_b'] ?? null,
            'option_c'        => $row['opsi_c'] ?? null,
            'option_d'        => $row['opsi_d'] ?? null,
            'option_e'        => $row['opsi_e'] ?? null,
            'correct_answer'  => strtoupper(trim($row['kunci_jawaban'])),
            'explanation'     => isset($row['pembahasan']) ? '<p>' . $row['pembahasan'] . '</p>' : null,

            // PENYESUAIAN DATABASE BARU: Set default false karena Excel isinya teks
            'is_answer_image' => false,
        ]);
    }

    // ==========================================
    // MESIN TURBO START
    // ==========================================

    /**
     * Memasukkan data ke database per 100 baris sekaligus (Bukan 1 per 1).
     * Ini mencegah Ngrok Timeout karena proses database sangat cepat.
     */
    public function batchSize(): int
    {
        return 100;
    }

    /**
     * Membaca file Excel per 100 baris ke memori RAM agar server tidak jebol/hang
     * saat membaca file Excel yang berisi ribuan soal.
     */
    public function chunkSize(): int
    {
        return 100;
    }
}
