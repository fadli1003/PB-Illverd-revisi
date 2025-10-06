<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Pindah;
use App\Models\Membership;

class Field extends Model
{
    use HasFactory;

    protected $fillable = ['name'];
    // Jika Anda memiliki kolom timestamps yang dinonaktifkan, tambahkan ini:
    public $timestamps = false;

    // Jika Anda ingin menentukan tabel secara eksplisit:
    protected $table = 'fields';

    public function pindahJadwal()
    {
        return $this->hasMany(Pindah::class);
    }

    public function membership()
    {
        return $this->hasMany(Membership::class);
    }
}

