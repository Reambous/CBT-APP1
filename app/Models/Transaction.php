<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'amount',
        'status',
        'paid_at',
        'tier', // <--- TAMBAHKAN BARIS INI
    ];

    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
        ];
    }

    // Relasi ke Atas: Dimiliki oleh 1 User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
