<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Illuminate\Console\Command;

class ConvertBookingDaysToEnglish extends Command
{
    protected $signature = 'convert:booking-days';
    protected $description = 'Convert schedule_details day keys to English';

    public function handle()
    {
        $hariMap = [
            'Senin' => 'Monday',
            'Selasa' => 'Tuesday',
            'Rabu' => 'Wednesday',
            'Kamis' => 'Thursday',
            'Jumat' => 'Friday',
            'Sabtu' => 'Saturday',
            'Minggu' => 'Sunday',
        ];

        $bookings = Booking::whereNotNull('schedule_details')->get();
        $converted = 0;

        foreach ($bookings as $booking) {
            $oldDetails = json_decode($booking->schedule_details, true);
            $newDetails = [];

            foreach ($oldDetails as $indoDay => $range) {
                $engDay = $hariMap[$indoDay] ?? $indoDay;
                $newDetails[$engDay] = $range;
            }

            $booking->schedule_details = json_encode($newDetails);
            $booking->save();
            $converted++;
        }

        $this->info("Berhasil mengonversi $converted booking.");
    }
}
