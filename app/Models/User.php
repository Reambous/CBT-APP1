<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes; // Tambahkan ini

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes; // Tambahkan SoftDeletes di sini

    // 1. Kolom yang diizinkan untuk diisi (Mass Assignment)
    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_picture',
        'is_premium',
        'premium_until',
        'premium_tier',
        'google_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_premium' => 'boolean',
            'premium_until' => 'datetime', // Beritahu Laravel ini adalah format waktu
        ];
    }

    // 2. Relasi: Satu User memiliki banyak Transaksi
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    // 3. Relasi: Satu User memiliki banyak Riwayat Nilai
    public function userResults()
    {
        return $this->hasMany(UserResult::class);
    }

    public function canAccessTier($requiredTier)
    {
        // Beri nilai/skor untuk setiap kasta agar mudah dibandingkan
        $tiers = [
            'gratis' => 0,
            'plus'   => 1,
            'pro'    => 2,
            'ultra'  => 3
        ];

        // Cek dulu, apakah dia sedang premium dan waktunya masih aktif?
        $isPremiumActive = $this->is_premium && $this->premium_until && now()->lessThanOrEqualTo($this->premium_until);

        // Jika masa aktif habis atau bukan premium, paksa statusnya jadi 'gratis'
        $userTier = $isPremiumActive ? $this->premium_tier : 'gratis';

        // Kembalikan TRUE jika skor kasta user LEBIH BESAR atau SAMA DENGAN skor kasta yang dibutuhkan paket
        return $tiers[$userTier] >= $tiers[$requiredTier];
    }
}
