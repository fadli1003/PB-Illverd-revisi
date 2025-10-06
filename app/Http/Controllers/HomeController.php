<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Field;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function home(Request $request)
    {
        // Ambil tanggal hari ini sebagai default jika tidak ada tanggal yang dipilih
        $selectedDate = $request->input('date', Carbon::today()->format('Y-m-d'));
        // Ambil semua lapangan
        $fields = Field::all();
        
        // Ambil semua pemesanan untuk tanggal yang dipilih (hanya yang sudah disetujui)
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
        
        // Decode schedule_details untuk setiap booking
        foreach ($bookings as $booking) {
            if (is_string($booking->schedule_details)) {
                $booking->schedule_details = json_decode($booking->schedule_details, true);
            }
        }
        
        // Filter pemesanan member berdasarkan valid_until
        $filteredBookings = $bookings->filter(function ($booking) use ($selectedDate) {
            if ($booking->booking_type === 'member' && $booking->valid_until) {
                // Pastikan tanggal saat ini belum melewati valid_until
                return Carbon::now()->lessThanOrEqualTo($booking->valid_until);
            }
            return true; // Tampilkan pemesanan biasa atau tanpa valid_until
        });

        // Buat array waktu dari 07:00 hingga 23:00 (setiap jam)
        $timeSlots = [];
        for ($hour = 7; $hour <= 23; $hour++) {
            $timeSlots[] = sprintf('%02d:00:00', $hour);
        }

        // Kirim variabel ke view
        return view('home', compact('fields','filteredBookings', 'selectedDate', 'timeSlots'));
    }

    public function laporan(Request $request)
    {
        // Ambil bulan dari request, default ke bulan saat ini
        $month = $request->input('month', now()->format('Y-m'));

        // Format bulan menjadi tanggal awal dan akhir
        $startDate = Carbon::parse($month)->startOfMonth();
        $endDate = Carbon::parse($month)->endOfMonth();

        // Ambil semua pemesanan dalam bulan yang dipilih
        $bookings = Booking::whereBetween('booking_date', [$startDate, $endDate])
                   ->orWhereBetween('valid_until', [$startDate, $endDate])
                   ->with('field')
                   ->orderBy('booking_date')
                   ->get();

        $totalIncome = $bookings->sum('dp_amount');
        $totalBookings = $bookings->count();
        $avgPerDay = $totalBookings > 0 ? $totalBookings / $startDate->daysInMonth : 0;
        $totalMembers = $bookings->where('booking_type', 'member')->count();

        // Kumpulkan data untuk view
        $reportData = [
            'total_income' => $totalIncome,
            'total_bookings' => $totalBookings,
            'avg_per_day' => $avgPerDay,
            'total_members' => $totalMembers,
            'bookings' => $bookings,
            'month' => $month,
        ];

        return view('admin.laporan', compact('reportData','bookings'));
    }

    public function notifikasi()
    {
        // Ambil semua booking milik user yang login dengan tanggal_sewa besok
        $tomorrow = Carbon::tomorrow()->toDateString();
        
        $notifications = Booking::where('user_id', auth()->id())
                                ->whereDate('booking_date', $tomorrow)
                                ->get();

        return view('notifikasi', compact('notifications'));
    }
}
