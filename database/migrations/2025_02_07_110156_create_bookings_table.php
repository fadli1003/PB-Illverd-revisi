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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users'); 
            $table->foreignId('field_id')->constrained('fields'); 
            $table->string('booking_type');
            $table->integer('duration');        
            $table->date('booking_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->json('days')->nullable();
            $table->json('schedule_details')->nullable();
            $table->integer('total_hours')->nullable();
            $table->decimal('remaining_amount', 10, 2)->nullable();
            $table->date('valid_until')->nullable();
            $table->decimal('amount_paid', 10, 2)->nullable();
            $table->decimal('dp_amount', 10, 2)->nullable();
            $table->string('status')->default('pending');
            $table->string('payment_order_id')->nullable();
            $table->string('payment_status')->default('unpaid');
            // Tambahkan index untuk performa
            $table->index('payment_order_id');
            $table->index('payment_status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
