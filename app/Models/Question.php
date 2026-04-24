<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'exam_package_id',
        'question_text',
        'order_num',
        'option_a',
        'option_b',
        'option_c',
        'option_d',
        'option_e',
        'correct_answer',
        'explanation',
        'is_answer_image',
    ];

    // Relasi ke Atas: Bagian dari 1 Paket Soal
    public function examPackage()
    {
        return $this->belongsTo(ExamPackage::class);
    }
}
