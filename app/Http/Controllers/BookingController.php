<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Field;
use App\Models\Membership;
use App\Models\Pindah;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    public function index(Request $request)
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
        return view('bookings.create', compact('fields', 'filteredBookings', 'selectedDate', 'timeSlots'));
    }

    public function create(Request $request)
    {
        $field_id = $request->query('field_id');
        $booking_date = $request->query('booking_date');
        $start_time = $request->query('start_time');
        $end_time = $request->query('end_time');
        $selectedDate = $request->query('booking_date');

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
    
        $fields = Field::all();
        return view('bookings.create', compact('field_id', 'booking_date', 'start_time', 'end_time', 'fields', 'filteredBookings', 'selectedDate', 'timeSlots' ));
    }
    
    public function getPendingBookings()
    {
        $count = Booking::where('status', 'approved')->count();
        return response()->json(['count' => $count]);
    }

    public function store(Request $request)
    {        
        // Validasi input
        if ($request->booking_type === 'regular') {
            $validated = $request->validate([
                'booking_type' => 'required|in:regular,member',
                'field_id' => 'required|exists:fields,id',
                'booking_date' => 'required_if:booking_type,regular|date|after_or_equal:today',
                'start_time' => 'required_if:booking_type,regular|date_format:H:i',
                'end_time' => 'required_if:booking_type,regular|date_format:H:i|after:start_time',
                // 'proof_of_payment' => 'nullable|file|image|max:2048',
            ]);
            
        } else {
            $validated = $request->validate([
                'booking_type' => 'required|in:regular,member',
                'field_id' => 'required|exists:fields,id',
                'total_hours' => 'required_if:booking_type,member|numeric|min:12',
                'dp_amount' => 'nullable|numeric|min:0',
                'schedule_details' => 'required_if:booking_type,member|array',
                'days' => 'required_if:booking_type,member|array|min:1',
                // 'proof_of_payment' => 'nullable|file|image|max:2048',
            ]);
            $this->validateMemberSchedule($request);
        }
        // Jika pemesanan adalah member, validasi schedule_details
        if ($request->booking_type === 'member') {
            $selectedDays = $request->input('days', []);
            $scheduleDetails = $request->input('schedule_details', []);
            $baseValidUntil = $request->input('base_valid_until');

            // Inisialisasi variabel untuk mencari waktu selesai terakhir
            $latestEndTime = null;

            foreach ($selectedDays as $day) {
                $timeRange = $scheduleDetails[$day] ?? null;

                // Pastikan start dan end tidak kosong
                if (!$timeRange || !$timeRange['start'] || !$timeRange['end']) {
                    return redirect()->back()->with('error', "Jadwal untuk hari $day tidak lengkap.");
                }

                // Validasi format waktu
                try {
                    Carbon::createFromFormat('H:i', $timeRange['start']);
                    Carbon::createFromFormat('H:i', $timeRange['end']);
                } catch (\Exception $e) {
                    return redirect()->back()->with('error', "Format waktu untuk hari $day tidak valid.");
                }

                // Pastikan waktu selesai lebih besar dari waktu mulai
                $startTime = Carbon::createFromFormat('H:i', $timeRange['start']);
                $endTime = Carbon::createFromFormat('H:i', $timeRange['end']);
                if ($endTime->lessThanOrEqualTo($startTime)) {
                    return redirect()->back()->with('error', "Waktu selesai untuk hari $day harus lebih besar dari waktu mulai.");
                }

                // Cari waktu selesai terakhir
                if (!$baseValidUntil && (!$latestEndTime || $endTime->greaterThan($latestEndTime))) {
                    $latestEndTime = $endTime;
                }
            }   
            // Hitung valid_until sebagai 4 minggu setelah waktu selesai terakhir
            if ($baseValidUntil) {
            // Jika base_valid_until ada (perpanjangan), hitung dari sana
            $validated['valid_until'] = \Carbon\Carbon::parse($baseValidUntil)->addWeeks(4)->format('Y-m-d');
            } else if ($latestEndTime) {
                $validated['valid_until'] = $latestEndTime->copy()->addWeeks(4)->format('Y-m-d');
            } else {
                return redirect()->back()->with('error', "Tidak ada jadwal yang valid untuk member.");
            }
        }
        // Cek konflik untuk booking regular
        if ($request->booking_type === 'regular') {
            $conflict = Booking::where('field_id', $validated['field_id'])
                ->where('booking_date', $validated['booking_date'])
                ->where('status', ['approved', 'pembatalan', 'memberExtend', 'membership'])
                ->where(function ($query) use ($validated) {
                    $query->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                        ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                        ->orWhere(function ($query) use ($validated) {
                            $query->where('start_time', '<=', $validated['start_time'])
                                ->where('end_time', '>=', $validated['end_time']);
                        });
                })
                ->exists();

            if ($conflict) {
                return redirect()->back()->with('error', 'Jadwal sudah dipesan. Silakan pilih waktu lain.');
            }
        }
        // Cek konflik untuk booking member
        if ($request->booking_type === 'member') {
            foreach ($request->schedule_details as $day => $timeRange) {
                $startTime = $timeRange['start'];
                $endTime = $timeRange['end'];
                $conflictingBookings = Booking::where('status', ['approved', 'membership'])
                    ->whereJsonContains('schedule_details', [$day => $timeRange])
                    ->exists();

                if ($conflictingBookings) {
                    return redirect()->back()->with('error', "Jadwal mingguan pada hari {$day} sudah terisi. Silakan pilih jadwal lain.");
                }
            }
        }
                
        // Hitung durasi dan harga
        if ($request->booking_type === 'regular') {
            $durations = $request->duration;

            $ratePerHour = 25000;
            $totalPrice = $durations * $ratePerHour;
        } else {
            $rateMember = 20000;
            $totalPrice = $request->total_hours * $rateMember;
        }
        
        $dpAmount = $request->input('dp_amount');
        if ($dpAmount < 0.5 * $totalPrice){
            return redirect()->back()->with('error', 'jumlah Dp minimal harus 50% dari total harga!!');
        }
        $remainingAmount = $totalPrice - $dpAmount;

        // Filter hanya hari yang valid (dipilih dan diisi start & end)
        $filteredSchedule = [];
        if ($request->booking_type === 'member') {
            
            foreach ($request->days as $day) {
                if (
                    isset($request->schedule_details[$day]['start'], $request->schedule_details[$day]['end']) &&
                    !empty($request->schedule_details[$day]['start']) &&
                    !empty($request->schedule_details[$day]['end'])
                ) {
                    $filteredSchedule[$day] = [
                        'start' => $request->schedule_details[$day]['start'],
                        'end' => $request->schedule_details[$day]['end'],
                    ];
                }
            }

            if (empty($filteredSchedule)) {
                return redirect()->back()->with('error', 'Harap isi waktu mulai dan selesai untuk minimal satu hari yang dipilih.');
            }
        }
        // Simpan ke database
        // try {
        $booking = null;
            if ($request->booking_type == 'member') {
                $booking = Booking::create([
                    'user_id' => auth()->id(),
                    'field_id' => $validated['field_id'],
                    'amount_paid' => $totalPrice,
                    'dp_amount' => $dpAmount,
                    'remaining_amount' => $remainingAmount,
                    // 'proof_of_payment' => $path,
                    'status' => Auth::user() && Auth::user()->role === 'admin' ? 'approved' : 'pending',
                    'booking_type' => $request->booking_type,
                    'total_hours' => $validated['total_hours'],
                    'days' => json_encode(array_keys($filteredSchedule)),
                    'schedule_details' => json_encode($filteredSchedule),
                    'remaining_hours' => $validated['total_hours'],
                    'valid_until' => $request->booking_type === 'member' ? $validated['valid_until'] : null,
                    'payment_status' => Auth::user() && Auth::user()->role === 'admin' ? 'paid' : 'unpaid' // Default status
                ]);
            } else {
                $booking = Booking::create([
                    'user_id' => auth()->id(),
                    'field_id' => $validated['field_id'],
                    'booking_date' => $validated['booking_date'],
                    'start_time' => $validated['start_time'],
                    'end_time' => $validated['end_time'],
                    'duration' => $request->duration,
                    'amount_paid' => $totalPrice,
                    'dp_amount' => $dpAmount,
                    'remaining_amount' => $remainingAmount,
                    // 'proof_of_payment' => $path,
                    'status' => Auth::user() && Auth::user()->role === 'admin' ? 'approved' : 'pending',
                    'booking_type' => $request->booking_type,
                    'payment_status' => Auth::user() && Auth::user()->role === 'admin' ? 'paid' : 'unpaid' // Default status
                ]);
            }
            
            if( Auth::user()->role === 'admin'){
                return redirect()->back()->with('success', 'Pemesanan berhasil dibuat');
            }else{
                return redirect()->route('payment.show', $booking->id);
            }
    }

    private function validateMemberSchedule(Request $request)
    {
        $totalHours = $request->input('total_hours', 0);
        $scheduleDetails = $request->input('schedule_details', []);
        $allocatedWeeklyHours = $totalHours / 4;
        $scheduledWeeklyHours = 0;

        foreach ($scheduleDetails as $day => $schedule) {
            if (isset($schedule['start']) && isset($schedule['end'])) {
                $start = \Carbon\Carbon::createFromFormat('H:i', $schedule['start']);
                $end = \Carbon\Carbon::createFromFormat('H:i', $schedule['end']);

                if ($end->lessThanOrEqualTo($start)) {
                    throw ValidationException::withMessages([
                        'schedule_details' => "Waktu selesai untuk hari {$day} harus lebih besar dari waktu mulai.",
                    ]);
                }
                $durationMinutes = $end->diffInMinutes($start);
                $durationHours = $durationMinutes / 60;
                $scheduledWeeklyHours += $durationHours;
            }
        }
        if (abs($scheduledWeeklyHours - $allocatedWeeklyHours) > 0.01) {
            $formattedAllocated = number_format($allocatedWeeklyHours, 2);
            $formattedScheduled = number_format($scheduledWeeklyHours, 2);
            throw ValidationException::withMessages([
                'schedule_details' => "Total jam sewa mingguan anda saat ini : {$formattedScheduled}  jam. Anda berhak menggunakan jadwal mingguan : {$formattedAllocated} jam/minggu.",
            ]);
        }
    }
}