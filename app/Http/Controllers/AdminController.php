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
        // $bookings = Booking::All();

        // Ambil bulan dari request, default ke bulan saat ini
        $month = $request->input('month', now()->format('Y-m'));

        // Format bulan menjadi tanggal awal dan akhir
        $startDate = Carbon::parse($month)->startOfMonth();
        $endDate = Carbon::parse($month)->endOfMonth();
        $today = now()->toDateString();

        $bookings = Booking::where(function ($query) use ($startDate, $endDate, $today) {
            $query->whereBetween('booking_date', [$startDate, $endDate])
                ->where('booking_date', '>=', $today)
                ->orWhereBetween('valid_until', [$startDate, $endDate])
                ->where('booking_date', '>=', $today);
        })
        ->with('field')
        ->orderBy('booking_date')
        ->get();

        return view('admin.kelola-jadwal', compact('bookings'));
    }    

    public function pesananMasuk()
    {
        // Hitung tanggal kemarin, hari ini, dan besok
        $yesterday = Carbon::yesterday()->format('Y-m-d');
        $today = Carbon::today()->format('Y-m-d');

        // Ambil booking yang created_at-nya antara awal kemarin sampai akhir besok
        // Ini memastikan kita menangkap semua booking dari 3 hari tersebut
        $bookings = Booking::whereDate('created_at', '>=', $yesterday)
                           ->whereDate('created_at', '<=', $today)
                           // ->where('status', 'approved') // Hapus filter status ini jika ingin semua status
                           ->get();

        $fields = Field::all();

        return view('admin.incoming-orders', compact('bookings', 'fields'));
    }

    public function pengajuan()
    {
        // Ambil semua pesanan masuk beserta data pemesan
        $bookings = Booking::whereIn('status',['pembatalan', 'memberExtend', 'pindah', 'pindah_membership'])->get();

        return view('admin.pengajuan', compact('bookings'));
    }
     
    // public function tolakOrder($id)
    // {
    //     $booking = Booking::findOrFail($id);
    //     $booking->update(['status' => 'ditolak']);

    //     return redirect()->back()->with('success', 'Pesanan berhasil ditolak.');
    // }

    public function deleteOrder($id)
    {
        $booking = Booking::findOrFail($id);
        $booking->delete();

        return redirect()->back()->with('success', 'Pesanan berhasil dihapus.');
    }

    public function edit($id)
    {
        // Cari pemesanan member berdasarkan ID
        $booking = Booking::findOrFail($id);        
        // Kirim data pemesanan sebelumnya ke view
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
        // Validasi input
        $validated = $request->validate([
            'field_id' => 'nullable|exists:fields,id',
            'tggl_baru' => 'nullable|date|after_or_equal:today',
            'waktu_mulai' => 'nullable|date_format:H:i',
            'waktu_selesai' => 'nullable|date_format:H:i|after:waktu_mulai',
        ]);

        // Gunakan nilai default jika input kosong
        $waktuMulai = $request->input('waktu_mulai') ?? $booking->start_time;
        $waktuSelesai = $request->input('waktu_selesai') ?? $booking->end_time;
        $ubahDp = $request->input('dp_baru') ?? $booking->dp_amount;
        $sisaBayar = $booking->amount_paid - $request->dp_baru;
        // Cek konflik untuk booking regular
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
        // Cari pemesanan member berdasarkan ID
        $booking = Booking::findOrFail($id); 
        $pindahJadwal = Pindah:: all();
        $fields = Field::all();       
            $validated = $request->validate([                
                'field_id' => 'nullable|exists:fields,id',                
                'schedule_details' => 'required_if:booking_type,member|array',
                'days' => 'required_if:booking_type,member|array|min:1',
            ]);        

        // Jika pemesanan adalah member, validasi schedule_details
        if ($request->booking_type === 'member') {
            $selectedDays = $request->input('days', []);
            $scheduleDetails = $request->input('schedule_details', []);
            
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
            }            
        }
        
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

        // Cek konflik untuk booking member
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

        // Simpan ke database       
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

        // Ubah status menjadi canceled
        $booking->update(['status' => 'dibatalkan']);

        return redirect()->back()->with('success', 'Pembatalan pesanan disetujui.');
    }

    public function tolakCancel($id)
    {
        $booking = Booking::findOrFail($id);

        // Kembalikan status ke approved
        $booking->update(['status' => 'approved']);

        return redirect()->back()->with('success', 'Pembatalan pesanan ditolak.');
    }
    
    // public function perpanjangMember($id)
    // {
    //     $membership = Membership::findOrFail($id);        
    //     $booking = $membership->booking;
    //     // Perbarui data pemesanan dengan jadwal baru
    //     $booking->update([
    //         'pending_data' => [
    //         'field_id' => $membership->field_id,
    //         'valid_until' => $membership->new_valid_until,
    //         'total_hours' => $booking->total_hours + $membership->additional_hours,
    //         'amount_paid' => $booking->amount_paid + $membership->total_bayar,
    //         'dp_amount' => $booking->dp_amount + $membership->jumlah_bayar,
    //         'remaining_amount' => $booking->remaining_amount + $membership->sisa_bayar,
    //         'schedule_details' => $membership->jadwal,
    //     ],
    //     'status_perpanjangan' => 'pending', // Jadwal baru belum aktif
    //     'status' => 'membership'
    //     ]);
    //     $membership->update(['status' => 'disetujui']);

    //     return redirect()->back()->with('success', 'Perpanjangan member berhasil disetujui.');
    // }

    // public function tolakPerpanjangan(Request $request, $id)
    // {
    //     $booking = Booking::findOrFail($id);
    //     $membership = Membership ::findOrFail($id);
    //     $membership->update(['status' => 'perpanjanganDitolak']);
    //     $booking->update(['status' => 'approved']);
    //     return redirect()->back()->with('success', 'Perpanjangan member berhasil ditolak.');
    // }

    // public function setujuiPindah(Request $request, $id)
    // {
    //     $pindahJadwal = Pindah::findOrFail($id);
    //     $booking = $pindahJadwal->booking;
                
    //     if ($booking->booking_type === 'regular') {
    //         $booking->update([
    //             'field_id' => $pindahJadwal->field_id, // Perbarui field_id
    //             'booking_date' => $pindahJadwal->tggl_baru,
    //             'start_time' => $pindahJadwal->waktu_mulai,
    //             'end_time' => $pindahJadwal->waktu_selesai,
    //             'status' => 'approved',
    //         ]);
    //     }else{
    //         $booking->update([
    //             'field_id' => $pindahJadwal->field_id, // Perbarui field_id
    //             'schedule_details' => $pindahJadwal->jadwal_member,
    //             'days' => $pindahJadwal->hari,             
    //         ]);
    //         if ($booking->status == 'pindah_membership'){$booking->update(['status' => 'membership']);
    //         }else {$booking->update(['status' => 'approved']);}
    //     }
    //     $pindahJadwal->update(['status' => 'disetujui']);
    //     return redirect()->back()->with('success', 'Pindah jadwal disetujui.');
    // }
    
    // public function tolakPindah($id)
    // {
    //     $pindahJadwal = Pindah::findOrFail($id);
    //     $booking = $pindahJadwal->booking;
    //     $pindahJadwal->update(['status' => 'ditolak']);
    //     if ($booking->status == 'pindah_membership'){$booking->update(['status' => 'membership']);
    //         }else {$booking->update(['status' => 'approved']);}
    //     $pindahJadwal->update(['status' => 'ditolak']);
    //     return redirect()->back()->with('success', 'Pindah jadwal ditolak.');
    // }    
}
