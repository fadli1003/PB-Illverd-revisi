<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('bookings', function (Blueprint $table) {
        $table->string('phone_number')->nullable(); // Nomor HP
        $table->decimal('amount_paid', 10, 2)->nullable(); // Jumlah bayar
        $table->string('proof_of_payment')->nullable(); // Bukti transfer (path file)
    });
}

public function down()
{
    Schema::table('bookings', function (Blueprint $table) {
        $table->dropColumn(['phone_number', 'amount_paid', 'proof_of_payment']);
    });
}
};
