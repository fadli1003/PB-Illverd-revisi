<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Field;
use App\Models\Booking;
use App\Models\Pindah;
use App\Models\Membership;
use App\Models\User;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function kelolaJadwal(Request $request)
    {
        $bulan = $request->input('month', now()->format('Y-m'));

        $tgglAwal = Carbon::parse($bulan)->startOfMonth();
        $tgglAkhir = Carbon::parse($bulan)->endOfMonth();
        $sekarang = now()->toDateString();

        $bookings = Booking::where(function ($query) use ($tgglAwal, $tgglAkhir, $sekarang) {
            $query->whereBetween('booking_date', [$tgglAwal, $tgglAkhir])
                ->where('booking_date', '>=', $sekarang)
                ->orWhereBetween('valid_until', [$tgglAwal, $tgglAkhir])
                ->where('booking_date', '>=', $sekarang);
        })
        ->with('field')
        ->orderBy('booking_date')
        ->get();

        return view('admin.kelola-jadwal', compact('bookings'));
    }    

    public function pesananMasuk()
    {
        $kemarin = Carbon::yesterday()->format('Y-m-d');
        $hariIni = Carbon::today()->format('Y-m-d');

        $bookings = Booking::whereDate('created_at', '>=', $kemarin)
                           ->whereDate('created_at', '<=', $hariIni)
                           ->get();

        $fields = Field::all();

        return view('admin.incoming-orders', compact('bookings', 'fields'));
    }

    public function pengajuan()
    {
        $bookings = Booking::whereIn('status',['pembatalan', 'memberExtend', 'pindah', 'pindah_membership'])->get();

        return view('admin.pengajuan', compact('bookings'));
    }
     
    public function deleteOrder($id)
    {
        $booking = Booking::findOrFail($id);
        $booking->delete();

        return redirect()->back()->with('success', 'Pesanan berhasil dihapus.');
    }

    public function edit($id)
    {
        $booking = Booking::findOrFail($id);      
        $fields = Field::all();
        if($booking->booking_type === 'regular'){
            return view('admin.edit', compact('booking', 'fields'));
        }else{
            return view('admin.editMember', compact('booking', 'fields'));
        }
    }

    public function editPesananRegular(Request $request, $id)
    {        
        $booking = Booking::findOrFail($id);        
        $fields = Field::all();
        $validated = $request->validate([
            'field_id' => 'nullable|exists:fields,id',
            'tggl_baru' => 'nullable|date|after_or_equal:today',
            'waktu_mulai' => 'nullable|date_format:H:i',
            'waktu_selesai' => 'nullable|date_format:H:i|after:waktu_mulai',
        ]);

        $waktuMulai = $request->input('waktu_mulai') ?? $booking->start_time;
        $waktuSelesai = $request->input('waktu_selesai') ?? $booking->end_time;
        $ubahDp = $request->input('dp_baru') ?? $booking->dp_amount;
        $sisaBayar = $booking->amount_paid - $request->dp_baru;

        if ($request->booking_type === 'regular') {
            $conflict = Booking::where('field_id', $validated['field_id'])
                ->where('booking_date', $validated['tggl_baru'])
                ->where('status', ['approved', 'pembatalan', 'membership'])
                ->where(function ($query) use ($validated) {
                    $query->whereBetween('start_time', [$validated['waktu_mulai'], $validated['waktu_selesai']])
                        ->orWhereBetween('end_time', [$validated['waktu_mulai'], $validated['waktu_selesai']])
                        ->orWhere(function ($query) use ($validated) {
                            $query->where('start_time', '<=', $validated['waktu_mulai'])
                                ->where('end_time', '>=', $validated['waktu_selesai']);
                        });
                })
                ->exists();

            if ($conflict) {
                return redirect()->back()->with('error', 'Jadwal sudah dipesan. Silakan pilih waktu lain.');
            }
        }
       
        $booking->update([ 
            'field_id' => $validated['field_id'],       
            'booking_date' => $validated['tggl_baru'],
            'start_time' => $waktuMulai,
            'dp_amount' => $ubahDp,
            'remaining_amount' => $sisaBayar,
            'end_time' => $waktuSelesai,
        ]);
        return redirect()->route('kelola_jadwal')->with('success', 'Pemesanan berhasil diubah.');
    }

    public function editPesananMember(Request $request, $id)
    {
        $booking = Booking::findOrFail($id); 
        $pindahJadwal = Pindah:: all();
        $fields = Field::all();       
            $validated = $request->validate([                
                'field_id' => 'nullable|exists:fields,id',                
                'schedule_details' => 'required_if:booking_type,member|array',
                'days' => 'required_if:booking_type,member|array|min:1',
            ]);        
        if ($request->booking_type === 'member') {
            $selectedDays = $request->input('days', []);
            $scheduleDetails = $request->input('schedule_details', []);
            
            foreach ($selectedDays as $day) {
                $timeRange = $scheduleDetails[$day] ?? null;

                if (!$timeRange || !$timeRange['start'] || !$timeRange['end']) {
                    return redirect()->back()->with('error', "Jadwal untuk hari $day tidak lengkap.");
                }

                try {
                    Carbon::createFromFormat('H:i', $timeRange['start']);
                    Carbon::createFromFormat('H:i', $timeRange['end']);
                } catch (\Exception $e) {
                    return redirect()->back()->with('error', "Format waktu untuk hari $day tidak valid.");
                }
                
                $startTime = Carbon::createFromFormat('H:i', $timeRange['start']);
                $endTime = Carbon::createFromFormat('H:i', $timeRange['end']);
                if ($endTime->lessThanOrEqualTo($startTime)) {
                    return redirect()->back()->with('error', "Waktu selesai untuk hari $day harus lebih besar dari waktu mulai.");
                }                
            }            
        }
        
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
                return redirect()->back()->with('error', 'Harap isi waktu mulai dan selesai, minimal pilih satu hari.');
            }
        }

        if ($request->booking_type === 'member') {
            foreach ($request->schedule_details as $day => $timeRange) {
                $startTime = $timeRange['start'];
                $endTime = $timeRange['end'];

                $conflictingBookings = Booking::where('status', ['approved', 'membership', 'memberExtend'])
                    ->whereJsonContains('schedule_details', [$day => $timeRange])
                    ->exists();

                if ($conflictingBookings) {
                    return redirect()->back()->with('error', "Jadwal mingguan pada hari {$day} sudah terisi. Silakan pilih jadwal lain.");
                }
                
            }
        }
      
        if ($request->booking_type == 'member') {
            $booking->update([                    
                'field_id' => $validated['field_id'],                    
                'days' => json_encode(array_keys($filteredSchedule)),
                'schedule_details' => json_encode($filteredSchedule),
                
            ]);            
        }

        if ($booking->status == 'membership'){
            $booking->update(['status' => 'pindah_membership']);
        }
        else{$booking->update(['status' => 'pindah']);}
        
        return redirect()->route('kelola_jadwal')->with('success', 'Pesanan barhasil diperbarui');
    }

    public function setujuiCancel($id)
    {
        $booking = Booking::findOrFail($id);
        $booking->update(['status' => 'dibatalkan']);

        return redirect()->back()->with('success', 'Pembatalan pesanan disetujui.');
    }

    public function tolakCancel($id)
    {
        $booking = Booking::findOrFail($id);
        $booking->update(['status' => 'approved']);

        return redirect()->back()->with('success', 'Pembatalan pesanan ditolak.');
    }  
}
