<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentFieldsToBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('payment_order_id')->nullable();
            $table->string('payment_status')->default('unpaid'); // unpaid, pending, paid, failed, challenge
            
            // Tambahkan index untuk performa
            $table->index('payment_order_id');
            $table->index('payment_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['payment_order_id']);
            $table->dropIndex(['payment_status']);
            $table->dropColumn(['payment_order_id', 'payment_status']);
        });
    }
}