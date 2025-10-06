<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('membership', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade'); // Foreign key ke bookings
            $table->date('new_valid_until')->nullable(); // Tanggal valid baru
            $table->decimal('additional_hours', 8, 2)->nullable(); // Jumlah jam tambahan
            $table->string('status')->default('perpanjang'); // Status pengajuan (pending, approved, rejected)            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership');
    }
};
