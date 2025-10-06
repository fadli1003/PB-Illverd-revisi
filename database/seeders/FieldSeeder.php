<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use App\Models\Field;

class FieldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Menambahkan data lapangan ke tabel fields
        Field::create(['name' => 'Lapangan 1']);
        Field::create(['name' => 'Lapangan 2']);
    }
}
