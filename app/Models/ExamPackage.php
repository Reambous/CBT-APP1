<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExamPackage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'exam_category_id',
        'title',
        'time_limit',
        'is_premium',
        'minimum_tier', // <--- Tambahkan ini
        'is_published'
    ];

    // Relasi ke Atas: Dimiliki oleh 1 Kategori
    public function examCategory()
    {
        // PERBAIKAN: Hapus return ganda & tuliskan nama kolomnya secara eksplisit (jelas)
        return $this->belongsTo(ExamCategory::class, 'exam_category_id', 'id')->withTrashed();
    }

    // Relasi ke Bawah: Memiliki banyak Pertanyaan
    public function questions()
    {
        // Sekalian kita buat eksplisit agar kebal dari error
        return $this->hasMany(Question::class, 'exam_package_id', 'id');
    }

    // Relasi ke Bawah: Pernah dikerjakan (menghasilkan banyak nilai)
    public function userResults()
    {
        return $this->hasMany(UserResult::class, 'exam_package_id', 'id');
    }

    protected static function booted()
    {
        // Otomatis soft delete semua soal jika paketnya dihapus
        static::deleted(function ($package) {
            $package->questions()->delete();
        });

        // Otomatis pulihkan semua soal jika paketnya dipulihkan
        static::restored(function ($package) {
            $package->questions()->withTrashed()->restore();
        });
    }
}
