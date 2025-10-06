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
        Schema::table('bookings', function (Blueprint $table) {
            $table->decimal('dp_amount', 10, 2)->nullable()->after('amount_paid'); // Jumlah DP
            $table->decimal('remaining_amount', 10, 2)->nullable()->after('dp_amount'); // Sisa bayar
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['dp_amount', 'remaining_amount']);
        });
    }
};
