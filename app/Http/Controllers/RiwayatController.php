<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Field;
use App\Models\Membership;
use App\Models\Pindah;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class RiwayatController extends Controller
{
    // Menampilkan halaman riwayat pemesanan pengguna
    public function index()
    {
        // Ambil semua pemesanan pengguna saat ini
        $bookings = Booking::where('user_id', auth()->id())
                           ->orderBy('booking_date') // Urutkan berdasarkan tanggal pemesanan
                           ->get();
        
        // Ambil data membership yang telah disetujui
        $memberships = Membership::where('status', 'disetujui')
            ->whereHas('booking', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->with('booking') 
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('bookings.history', compact('bookings', 'memberships'));
    }

    public function extend($id)
    {
        $booking = Booking::findOrFail($id);              
        $fields = Field::all();
        return view('bookings.extend', compact('booking', 'fields'));
    }
    
    public function pengajuanMembership(Request $request, $id)
    {
        // Validasi input
        $validated = $request->validate([
            'field_id' => 'nullable|exists:fields,id',
            'total_hours' => 'required|numeric|min:12',
            'proof_of_payment' => 'required|file|image|max:2048',            
            'dp_amount' => 'nullable|numeric|min:0',
            'schedule_details' => 'required|array',
            'days' => 'required|array',
        ]);
        // Cari pemesanan member berdasarkan ID
        $booking = Booking::findOrFail($id);
        $pindahJadwal = Pindah:: all();
        $fields = Field::all(); 

        // Simpan file bukti transfer
        $path = $request->file('proof_of_payment')->store('proofs', 'public');

        $rateMember = 20000;
        $totalPrice = $request->total_hours * $rateMember;
        $dpAmount = $request->input('dp_amount') ?? ($totalPrice * 0.5);
        $remainingAmount = $totalPrice - $dpAmount;
        
        // Filter hanya hari yang valid (dipilih dan diisi start & end)
        $filteredSchedule = [];
        if ($booking->booking_type === 'member') {
            
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

        // Hitung new_valid_until dengan menambahkan 4 minggu ke valid_until sebelumnya
        $newValidUntil = Carbon::parse($booking->valid_until)->addWeeks(4);
        
        // Update data pemesanan
        $booking->membership()->create([
            'field_id' => $validated['field_id'],
            'additional_hours' => $validated['total_hours'],
            'hari' => json_encode(array_keys($filteredSchedule)),
            'jadwal' => json_encode($filteredSchedule),
            'bukti_transfer' => $path,
            'total_bayar' => $totalPrice,
            'jumlah_bayar' => $dpAmount,
            'sisa_bayar' => $remainingAmount,
            'new_valid_until' =>  $newValidUntil,
        ]);
        $booking->update(['status' => 'memberExtend']);

        return redirect('/')->with('success', 'Perpanjangan berhasil diajukan. Tunggu persetujuan admin.');
    }

    public function cetak($id)
    {
        // Ambil data pemesanan berdasarkan ID
        $booking = Booking::findOrFail($id);

        // Kirim data ke view PDF
        $pdf = Pdf::loadView('bookings.cetak_pemesanan', compact('booking'));

        // Unduh file PDF
        return $pdf->download('pemesanan-' . $booking->id . '.pdf');
    }

    // Mengajukan pembatalan pesanan
    public function cancel($id)
    {
        // Cari pemesanan berdasarkan ID
        $booking = Booking::findOrFail($id);

        // Pastikan pemesanan milik pengguna saat ini
        if ($booking->user_id !== auth()->id()) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk membatalkan pesanan ini.');
        }

        // Pastikan status pemesanan adalah pending atau approved
        if (!in_array($booking->status, ['pending', 'approved'])) {
            return redirect()->back()->with('error', 'Hanya pemesanan dengan status pending atau approved yang dapat dibatalkan.');
        }

        // Batasi pembatalan hanya untuk H-2 atau lebih
        if ($booking->booking_type === 'regular'){    
            $hMinus2 = Carbon::parse($booking->booking_date)->subDays(2);
            if (Carbon::now()->greaterThan($hMinus2)) {
                return redirect()->back()->with('error', 'Pembatalan hanya dapat dilakukan H-2 sebelum tanggal pemesanan.');
            }
        }
        // Ubah status menjadi pending_cancellation
        $booking->update(['status' => 'pembatalan']);

        return redirect()->back()->with('success', 'Pembatalan berhasil diajukan. Tunggu persetujuan admin.');
    }

    // Mengajukan pembatalan pesanan
    public function batalkanPengajuan($id)
    {
        // Cari pemesanan berdasarkan ID
        $booking = Booking::findOrFail($id);

        // Pastikan pemesanan milik pengguna saat ini
        if ($booking->user_id !== auth()->id()) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk membatalkan pesanan ini.');
        }

        // Pastikan status pemesanan adalah pending atau approved
        if (!in_array($booking->status, ['pembatalan', 'memberExtend', 'pindah'])) {
            return redirect()->back()->with('error', 'Hanya pemesanan dengan status pengajuan yang dapat dibatalkan.');
        }

        // Ubah status pending cancel menjadi pending atau approved 
        if ($booking->status === 'pembatalan') {
            $booking->update(['status' => 'pending']);
            return redirect()->back()->with('success', 'Pembatalan pengajuan berhasil.');
        } elseif ($booking->status === 'memberExtend'){
            $booking->update(['status' => 'approved']);
            return redirect()->back()->with('success', 'Pembatalan pengajuan berhasil.');
        } elseif ($booking->status === 'pindah'){
            $booking->update(['status' => 'approved']);
            return redirect()->back()->with('success', 'Pembatalan pengajuan berhasil.');
        }


    }
}