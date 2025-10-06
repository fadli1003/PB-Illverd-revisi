<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Field;
use App\Models\Booking;
use App\Models\User;

class Membership extends Model
{
    protected $fillable = [
        'booking_id', 
        'field_id', 
        'hari', 
        'jadwal', 
        'new_valid_until', 
        'additional_hours', 
        'total_bayar', 
        'jumlah_bayar', 
        'sisa_bayar', 
        'bukti_transfer', 
        'status',
    ];

    protected $table = 'membership';
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
}