<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\Booking;
use Carbon\Carbon;
use App\Notifications\RemainingHoursNotification;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        
        // Aktifkan data baru setelah valid_until lama habis
        $schedule->call(function () {
            $pendingBookings = Booking::where('status_perpanjangan', 'pending')
                ->where('valid_until', '<=', Carbon::today())
                ->get();

            foreach ($pendingBookings as $booking) {
                // Ambil data dari pending_data
                $pendingData = json_decode($booking->pending_data, true);

                // Aktifkan data baru
                $booking->update([
                    'field_id' => $pendingData['field_id'],
                    'valid_until' => $pendingData['valid_until'],
                    'total_hours' => $pendingData['total_hours'],
                    'amount_paid' => $pendingData['amount_paid'],
                    'dp_amount' => $pendingData['dp_amount'],
                    'remaining_amount' => $pendingData['remaining_amount'],
                    'schedule_details' => $pendingData['schedule_details'],
                    'status_perpanjangan' => 'active', // Jadwal baru aktif
                    'pending_data' => null, // Kosongkan pending_data
                ]);
            }
            \Log::info('Activating new data for booking ID: ' . $booking->id);
        })->daily();
    
        // Jalankan tugas setiap menit untuk memperbarui remaining_hours
        $schedule->call(function () {
            // Ambil semua pemesanan member yang sudah disetujui
            $memberBookings = Booking::where('booking_type', 'member')
                                      ->where('status', 'approved')
                                      ->get();

            foreach ($memberBookings as $booking) {
                // Decode schedule_details
                $scheduleDetails = json_decode($booking->schedule_details, true);
                
                if (is_array($scheduleDetails)) {
                    foreach ($scheduleDetails as $day => $timeRange) {
                        $currentDay = Carbon::now()->format('l'); // Hari dalam bahasa Inggris
                        $currentTime = Carbon::now()->toTimeString();

                        // Periksa apakah hari ini cocok dengan salah satu hari dalam schedule_details
                        if ($day === $currentDay && $currentTime > $timeRange['end']) {
                            // Kurangi remaining_hours jika waktu sudah lewat
                            if ($booking->remaining_hours > 0) {
                                $durationInHours = Carbon::createFromFormat('H:i', $timeRange['end'])
                                                         ->diffInMinutes(Carbon::createFromFormat('H:i', $timeRange['start'])) / 60;

                                $booking->update([
                                    'remaining_hours' => max(0, $booking->remaining_hours - $durationInHours),
                                ]);

                                // Jika remaining_hours habis, ubah role menjadi pelanggan
                                if ($booking->remaining_hours <= 0) {
                                    $booking->user->update(['role' => 'pelanggan']);
                                }
                            }
                        }
                    }
                }
            }
            \Log::info('Daily task running at ' . now());
        })->everyMinute();

        $schedule->call(function () {
            // Ambil semua pemesanan member yang sudah disetujui
            $memberBookings = Booking::where('booking_type', 'member')
                                      ->where('status', 'approved')
                                      ->get();
        
            foreach ($memberBookings as $booking) {
                // Cek jika remaining_hours kurang dari 4 jam (sekitar seminggu sebelum habis)
                if ($booking->remaining_hours > 0 && $booking->remaining_hours <= 4) {
                    // Kirim notifikasi ke member
                    $user = $booking->user;
                    $user->notify(new RemainingHoursNotification($booking));
                }
            }
            \Log::info('Daily task running at ' . now());
        })->daily(); // Jalankan setiap hari
    }


    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
