<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Booking;
use App\Models\Field;

class Pindah extends Model
{
    use HasFactory;
    protected $table = 'pindah_jadwal';

    // Relasi dengan Booking
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function field()
    {
        return $this->belongsTo(Field::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected $fillable = [
        'booking_id',
        'field_id',
        'tggl_baru',
        'waktu_mulai',
        'waktu_selesai',
        'hari',
        'jadwal_member',
        'alasan_pindah',
        'status',
    ];

    protected $casts = [
        'hari' => 'array', // Casting untuk menyimpan array sebagai JSON
        'jadwal_member' => 'array', // Casting untuk menyimpan array sebagai JSON
    ];
}