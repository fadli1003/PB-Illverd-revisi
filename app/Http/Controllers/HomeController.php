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
        $selectedDate = $request->input('date', Carbon::today()->format('Y-m-d'));
        $fields = Field::all();
        $bookings = Booking::where(function ($query) use ($selectedDate) {
                $query->where('booking_date', $selectedDate)
                    ->where('payment_status', 'paid');
            })
            ->orWhere(function ($query) use ($selectedDate) {
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

        $filteredBookings = $bookings->filter(function ($booking) use ($selectedDate) {
            if ($booking->booking_type === 'member' && $booking->valid_until) {
                return Carbon::now()->lessThanOrEqualTo($booking->valid_until);
            }
            return true;
        });

        $timeSlots = [];
        for ($jam = 7; $jam <= 22; $jam++) {
            $timeSlots[] = sprintf('%02d:00:00', $jam);
        }

        return view('main.home', compact('fields','filteredBookings', 'selectedDate', 'timeSlots'));
    }

    public function laporan(Request $request)
    {
        $bulan = $request->input('month', now()->format('Y-m'));
        $awalBulan = Carbon::parse($bulan)->startOfMonth();
        $akhirBulan = Carbon::parse($bulan)->endOfMonth();

        $bookings = Booking::whereBetween('booking_date', [$awalBulan, $akhirBulan])
                   ->orWhereBetween('valid_until', [$awalBulan, $akhirBulan])
                   ->with('field')
                   ->orderBy('booking_date')
                   ->get();

        $totalPendapatan = $bookings->sum('dp_amount');
        $totalBookings = $bookings->count();
        $rerataPerHari = $totalBookings > 0 ? $totalBookings / $awalBulan->daysInMonth : 0;
        $totalMembers = $bookings->where('booking_type', 'member')->count();

        $reportData = [
            'total_pendapatan' => $totalPendapatan,
            'total_booking' => $totalBookings,
            'rerata_per_hari' => $rerataPerHari,
            'total_member' => $totalMembers,
            'bookings' => $bookings,
            'bulan' => $bulan,
        ];

        return view('admin.laporan', compact('reportData','bookings'));
    }

    public function notifikasi()
    {
        $besok = Carbon::tomorrow()->toDateString();

        $notifikasi = Booking::where('user_id', auth()->id())
                                ->whereDate('booking_date', $besok)
                                ->get();

        return view('main.notifikasi', compact('notifikasi'));
    }
}
