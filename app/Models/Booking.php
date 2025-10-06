<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Pindah;
use App\Models\Membership;
use App\Models\Field;

class Booking extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function field()
    {
        return $this->belongsTo(Field::class);
    }
    
    // Relasi dengan PindahJadwal
    public function pindahJadwal()
    {
        return $this->hasOne(Pindah::class);
    }
    // Relasi dengan membership
    public function membership()
    {
        return $this->hasOne(Membership::class);
    }

    // Daftar kolom yang diizinkan untuk mass assignment
    protected $fillable = [
        'user_id',
        'field_id',
        'booking_type',
        'booking_date',
        'start_time',
        'end_time',
        'duration',
        'total_hours',
        'remaining_hours',
        'days',
        'schedule_details',
        'amount_paid',
        'dp_amount',
        'remaining_amount',
        'proof_of_payment',
        'status',
        'valid_until',
        'status_perpanjangan',
        'pending_data',
        'payment_order_id',
        'payment_status',
    ];

    protected $casts = [
        'days' => 'array', // Casting untuk menyimpan array sebagai JSON
        'schedule_details' => 'array', // Casting untuk menyimpan array sebagai JSON
    ];
}
