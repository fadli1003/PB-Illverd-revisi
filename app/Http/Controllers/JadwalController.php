<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Field;
use Carbon\Carbon;

class JadwalController extends Controller {
    // Tampilkan jadwal berdasarkan tanggal yang dipilih
    // public function schedule(Request $request)
    // {
    //     // Ambil tanggal hari ini sebagai default jika tidak ada tanggal yang dipilih
    //     $selectedDate = $request->input('date', Carbon::today()->format('Y-m-d'));
    //     // Ambil semua lapangan
    //     $fields = Field::all();

    //     // Ambil semua pemesanan untuk tanggal yang dipilih ATAU booking member yang masih valid, TAPI hanya yang sudah dibayar
    //     $bookings = Booking::where(function ($query) use ($selectedDate) {
    //             // Booking regular untuk tanggal tertentu YANG SUDAH DIBAYAR
    //             $query->where('booking_date', $selectedDate)
    //                 ->where('payment_status', 'paid');
    //         })
    //         ->orWhere(function ($query) use ($selectedDate) {
    //             // Booking member YANG MASIH VALID (valid_until >= hari_ini) DAN SUDAH DIBAYAR
    //             $query->where('booking_type', 'member')
    //                 ->where('valid_until', '>=', Carbon::today()->format('Y-m-d'))
    //                 ->where('payment_status', 'paid');
    //         })
    //         ->get();
    //     // Decode schedule_details untuk setiap booking
    //     foreach ($bookings as $booking) {
    //         if (is_string($booking->schedule_details)) {
    //             $booking->schedule_details = json_decode($booking->schedule_details, true);
    //         }
    //     }

    //     // Filter pemesanan member berdasarkan valid_until
    //     $filteredBookings = $bookings->filter(function ($booking) use ($selectedDate) {
    //         if ($booking->booking_type === 'member' && $booking->valid_until) {
    //             // Pastikan tanggal saat ini belum melewati valid_until
    //             return Carbon::now()->lessThanOrEqualTo($booking->valid_until);
    //         }
    //         return true; // Tampilkan pemesanan biasa atau tanpa valid_until
    //     });

    //     // Buat array waktu dari 07:00 hingga 23:00 (setiap jam)
    //     $timeSlots = [];
    //     for ($hour = 7; $hour <= 22; $hour++) {
    //         $timeSlots[] = sprintf('%02d:00:00', $hour);
    //     }

    //     // Kirim variabel ke view
    //     return view('schedule', compact('fields','filteredBookings', 'selectedDate', 'timeSlots'));
    // }

    public function getJadwal(Request $request)
    {
        $selectedDate = $request->input('date');

        $fields = Field::all();

        $bookings = Booking::where(function ($query) use ($selectedDate) {
                // Booking regular untuk tanggal tertentu YANG SUDAH DIBAYAR
                $query->where('booking_date', $selectedDate)
                    ->where('payment_status', 'paid');
            })
            ->orWhere(function ($query) use ($selectedDate) {
                // Booking member YANG MASIH VALID (valid_until >= hari_ini) DAN SUDAH DIBAYAR
                $query->where('booking_type', 'member')
                    ->where('valid_until', '>=', Carbon::today()->format('Y-m-d'))
                    ->where('payment_status', 'paid');
            })
            ->get();

        foreach ($bookings as $booking) {
            if (is_string($booking->schedule_details)) {
                $booking->schedule_details = json_decode($booking->schedule_details, true);
            }
        }

        $filteredBookings = $bookings->filter(function ($booking) use ($selectedDate) {
            if ($booking->booking_type === 'member' && $booking->valid_until) {
                return Carbon::now()->lessThanOrEqualTo($booking->valid_until);
            }
            return true;
        });

        $timeSlots = [];
        for ($hour = 7; $hour <= 22; $hour++) {
            $timeSlots[] = sprintf('%02d:00:00', $hour);
        }

        return view('jadwal', [
            'fields' => $fields,
            'filteredBookings' => $filteredBookings,
            'selectedDate' => $selectedDate,
            'timeSlots' => $timeSlots
        ])->render();
    }
}
