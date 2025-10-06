<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Field;
use App\Models\Booking;
use App\Models\Pindah;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PindahController extends Controller
{
    public function index()
    {
        // Ambil semua pemesanan pengguna saat ini
        $bookings = Booking::where('user_id', auth()->id())
                            ->where(function ($query) {
                            // Booking member yang masih valid
                            $query->where('booking_type', 'member')
                                    ->where('valid_until', '>=', Carbon::today()->format('Y-m-d'));
                            $query->orWhere(function ($q) {
                                $q->where('booking_type', 'regular')
                                    ->where('status', 'approved');
                            });
                            })
                           ->orderBy('booking_date') // Urutkan berdasarkan tanggal pemesanan
                           ->get();
        
        // Kirim data pemesanan sebelumnya ke view
        $fields = Field::all();
        return view('jadwal.pindah', compact('bookings', 'fields'));
    }

    public function formPindah($id)
    {
        // Cari pemesanan member berdasarkan ID
        $booking = Booking::findOrFail($id);
        
        // Pastikan pemesanan milik pengguna saat ini
        if ($booking->user_id !== auth()->id()) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk memperpanjang pesanan ini.');
        }

        // Kirim data pemesanan sebelumnya ke view
        $fields = Field::all();
        return view('jadwal.form_pindah', compact('booking', 'fields'));
    }

    public function formPindahMember($id)
    {
        // Cari pemesanan member berdasarkan ID
        $booking = Booking::findOrFail($id);
        
        // Pastikan pemesanan milik pengguna saat ini
        if ($booking->user_id !== auth()->id()) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk memperpanjang pesanan ini.');
        }

        // Kirim data pemesanan sebelumnya ke view
        $fields = Field::all();
        return view('jadwal.form_pindah_member', compact('booking', 'fields'));
    }

    // Proses pengajuan pindah jadwal
    public function pengajuanPindahRegular(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);
        $pindahJadwal = Pindah:: all();
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
        $waktuSelesai = $request->waktu_selesai;

        // Cek konflik untuk booking regular
        if ($request->booking_type === 'regular') {
            $conflict = Booking::where('field_id', $validated['field_id'])
                ->where('booking_date', $validated['tggl_baru'])
                ->where('status', ['approved', 'pembatalan', 'pindah', 'memberExtend', 'membership'])
                ->where('id', '!=', $booking->id) // Tambahkan ini: jangan cek konflik dengan dirinya sendiri
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

        // Pastikan pemesanan milik pengguna yang sedang login
        if ($booking->user_id !== auth()->id()) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke pemesanan ini.');
        }

        // Perbarui status menjadi "pending_reschedule"
        $booking->update([ 
            'field_id' => $validated['field_id'],       
            'booking_date' => $validated['tggl_baru'],
            'start_time' => $waktuMulai,
            'end_time' => $waktuSelesai,
        ]);

        $booking->update(['status' => 'approved']);

        return redirect()->route('pindah_jadwal')->with('success', 'Jadwal berhasil diperbarui.');
    }

    public function updatePindahMember(Request $request, $id): RedirectResponse
    {
        $booking = Booking::findOrFail($id);

        // Pastikan booking milik user yang sedang login dan adalah booking member
        if ($booking->user_id !== Auth::id() || $booking->booking_type !== 'member') {
            abort(403);
        }

        // Ambil hari-hari yang dipilih dari request
        $selectedDays = $request->input('days', []); // Ini adalah array value dari checkbox yang dicentang (e.g., ['Monday', 'Tuesday'])

        // Buat aturan validasi secara dinamis berdasarkan hari yang dipilih
        $validationRules = [
            'field_id' => 'required|exists:fields,id',
            // 'total_hours' => 'required|numeric|min:12', // total_hours readonly, tidak perlu divalidasi di sini
            'days' => 'required|array|min:1', // Validasi bahwa setidaknya satu hari dipilih
            'days.*' => 'string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday', // Validasi format hari
        ];

        // Tambahkan aturan untuk setiap hari yang dipilih
        foreach ($selectedDays as $day) {
            // Validasi bahwa start dan end untuk hari yang dipilih ada dan formatnya benar
            $validationRules["schedule_details.{$day}.start"] = 'required|date_format:H:i';
            $validationRules["schedule_details.{$day}.end"] = 'required|date_format:H:i|after:schedule_details.' . $day . '.start';
        }

        // Validasi input dasar
        $validator = Validator::make($request->all(), $validationRules);

        // Cek apakah validasi gagal
        if ($validator->fails()) {
            return redirect()->back()
                             ->withErrors($validator)
                             ->withInput();
        }

        $validated = $validator->validated();

        // Ambil schedule_details yang sudah divalidasi (hanya berisi hari yang dipilih)
        $newScheduleDetails = $validated['schedule_details'];

        // --- LOGIKA VALIDASI SESUAI FORM BOOKING UTAMA (VALIDASI DURASI) ---
        // 1. Validasi Total Jam Mingguan Harus Sama dengan Alokasi
        $totalHours = $booking->total_hours; // Ambil dari booking lama, karena readonly di form
        $allocatedWeeklyHours = $totalHours / 4; // Hitung alokasi mingguan

        $scheduledWeeklyHours = 0;
        foreach ($newScheduleDetails as $day => $timeRange) {
            $start = $timeRange['start'];
            $end = $timeRange['end'];

            // Parsing waktu untuk menghitung durasi akurat
            $startCarbon = Carbon::createFromFormat('H:i', $start);
            $endCarbon = Carbon::createFromFormat('H:i', $end);

            // Tidak perlu cek lessThanOrEqualTo karena sudah di validasi 'after'
            $durationInMinutes = $endCarbon->diffInMinutes($startCarbon);
            $durationInHours = $durationInMinutes / 60;

            $scheduledWeeklyHours += $durationInHours;
        }

        // Validasi harus persis sama (dengan toleransi kecil)
        if (abs($scheduledWeeklyHours - $allocatedWeeklyHours) > 0.01) {
            $formattedAllocated = number_format($allocatedWeeklyHours, 2);
            $formattedScheduled = number_format($scheduledWeeklyHours, 2);
            return redirect()->back()->with('error', "Total jam sewa mingguan anda saat ini : {$formattedScheduled}  jam. Anda berhak menggunakan jadwal mingguan : {$formattedAllocated} jam/minggu.");
        }

        // --- LOGIKA VALIDASI KONFLIK ---
        // 2. Cek Konflik Jadwal Member dengan Member Lain (atau member yang aktif)
        foreach ($newScheduleDetails as $day => $timeRange) {
            $startTime = $timeRange['start'];
            $endTime = $timeRange['end'];

            // Cari booking lain (bukan booking ini sendiri) yang aktif dan konflik
            $conflictingBookings = Booking::where('id', '!=', $booking->id) // Jangan cek konflik dengan dirinya sendiri
                ->where('booking_type', 'member')
                ->whereIn('status', ['approved', 'membership', 'memberExtend']) // Status booking member aktif
                ->where('payment_status', 'paid') // Harus sudah dibayar
                ->whereJsonContains('schedule_details', [$day => $timeRange]) // Cek konflik waktu di hari yang sama
                ->exists();

            if ($conflictingBookings) {
                return redirect()->back()->with('error', "Jadwal mingguan pada hari {$day} sudah terisi oleh booking lain. Silakan pilih jadwal lain.");
            }
        }

        // 3. Cek Konflik Jadwal Member dengan Booking Regular Aktif
        $startOfWeek = Carbon::today()->startOfWeek(); // Ambil awal minggu ini sebagai referensi
        foreach ($newScheduleDetails as $day => $timeRange) {
            $startTime = $timeRange['start'];
            $endTime = $timeRange['end'];

            // Hitung tanggal untuk $day dalam minggu ini
            $targetDate = $startOfWeek->copy()->modify($day); // Contoh: modify("Monday")

            // Cari booking regular aktif pada tanggal tersebut yang waktunya overlap
            $conflictingRegularBookings = Booking::where('booking_type', 'regular')
                ->where('booking_date', $targetDate->format('Y-m-d'))
                ->where('id', '!=', $booking->id) // Jangan cek konflik dengan dirinya sendiri
                ->whereIn('status', ['approved', 'pembatalan', 'memberExtend', 'membership', 'pindah']) // Status booking regular aktif
                ->where('payment_status', 'paid') // Harus sudah dibayar
                ->where(function ($query) use ($startTime, $endTime) {
                    $query->where('start_time', '<', $endTime) // Mulai sebelum selesai slot member
                          ->where('end_time', '>', $startTime); // Selesai setelah mulai slot member
                })
                ->exists();

            if ($conflictingRegularBookings) {
                $hariLokal = array_search($day, [
                    'Senin' => 'Monday',
                    'Selasa' => 'Tuesday',
                    'Rabu' => 'Wednesday',
                    'Kamis' => 'Thursday',
                    'Jumat' => 'Friday',
                    'Sabtu' => 'Saturday',
                    'Minggu' => 'Sunday',
                ]) ?: $day;

                return redirect()->back()->with('error', "Jadwal mingguan pada hari {$hariLokal} (tanggal {$targetDate->format('Y-m-d')}) sudah terisi oleh booking regular lain. Silakan pilih jadwal lain.");
            }
        }

        // --- LOGIKA PERUBAHAN TIDAK ADA ---
        // Bandingkan data baru dengan data lama
        $oldScheduleDetails = json_decode($booking->schedule_details, true);
        $newScheduleDetailsForComparison = $newScheduleDetails; // Ambil dari request yang sudah divalidasi

        // Urutkan array berdasarkan key (hari) untuk perbandingan yang konsisten
        ksort($oldScheduleDetails);
        ksort($newScheduleDetailsForComparison);

        // Bandingkan JSON string dari array yang sudah diurutkan
        $isUnchanged = json_encode($oldScheduleDetails) === json_encode($newScheduleDetailsForComparison);

        if ($isUnchanged && $booking->field_id == $validated['field_id']) {
             return redirect()->back()->with('error', 'Anda tidak mengubah apapun. Silakan kembali ke halaman utama.');
        }

        try {
            $booking->update([
                'field_id' => $validated['field_id'],
                'schedule_details' => json_encode($validated['schedule_details']),
                'status' => 'approved',
            ]);
            if ($booking->status == 'membership'){
                $booking->update(['status' => 'membership']);
            }
            else{$booking->update(['status' => 'approved']);}
            return redirect()->route('book')->with('success', 'jadwal berhasil diperbarui.');

        } catch (\Exception $e) {
            Log::error('Booking Member Pindah Jadwal Error: ' + $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.');
        }
    }


        
}
