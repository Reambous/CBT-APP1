<?php

namespace App\Imports;

use App\Models\Question;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
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
        // 1. Cek apakah baris soal kosong (Jika kosong, lewati)
        if (!isset($row['soal']) || empty(trim($row['soal']))) {
            return null;
        }

        // 2. Ambil data Opsi dari kolom A, B, C, D, E
        $opsiA = $row['a'] ?? null;
        $opsiB = $row['b'] ?? null;
        $opsiC = $row['c'] ?? null;
        $opsiD = $row['d'] ?? null;
        $opsiE = $row['e'] ?? null;

        // 3. Ambil Teks Jawaban Asli (Misal: "Soekarno")
        $jawabanAsli = trim($row['kunci_jawaban'] ?? '');

        // 4. LOGIKA PENCARIAN KUNCI JAWABAN (Mendeteksi A/B/C/D/E secara otomatis)
        $correctAnswer = 'A'; // Default aman jika kebetulan kosong/tidak ada yang cocok

        // Kita ubah teks menjadi huruf kecil semua (strtolower) agar kebal dari Typo besar/kecil
        $jawabanAsliLower = strtolower($jawabanAsli);

        if ($jawabanAsliLower == strtolower(trim($opsiA))) {
            $correctAnswer = 'A';
        } elseif ($jawabanAsliLower == strtolower(trim($opsiB))) {
            $correctAnswer = 'B';
        } elseif ($jawabanAsliLower == strtolower(trim($opsiC))) {
            $correctAnswer = 'C';
        } elseif ($jawabanAsliLower == strtolower(trim($opsiD))) {
            $correctAnswer = 'D';
        } elseif ($jawabanAsliLower == strtolower(trim($opsiE))) {
            $correctAnswer = 'E';
        }

        $lastOrder = \App\Models\Question::where('exam_package_id', $this->packageId)->max('order_num');
        $nextOrder = $lastOrder ? $lastOrder + 1 : 1;

        // 5. Simpan ke Database
        return new Question([
            'exam_package_id' => $this->packageId,
            'order_num'       => $nextOrder,
            'question_text'   => '<p>' . $row['soal'] . '</p>',
            'option_a'        => $opsiA,
            'option_b'        => $opsiB,
            'option_c'        => $opsiC,
            'option_d'        => $opsiD,
            'option_e'        => $opsiE,
            'correct_answer'  => $correctAnswer, // Yang masuk ke DB tetap A/B/C/D/E hasil pencarian
            'explanation'     => isset($row['pembahasan']) ? '<p>' . $row['pembahasan'] . '</p>' : null,
            'is_answer_image' => false,
        ]);
    }

    // ==========================================
    // MESIN TURBO START
    // ==========================================

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
