@extends('layouts.main')

@section('content')
    @php
        use Carbon\Carbon;
    @endphp
    <div class="card mb-1">    
        <div class="card-header text-center">
            <h4 class="mb-0">Silahkan Pilih Pemesanan Yang Ingin Dipindahkan</h4>
        </div>
    </div>

    <!-- Tampilkan pesan kesalahan jika ada -->
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    <div class="card mb-4">
        <!-- Tampilkan tabel riwayat pemesanan -->
            <table class="table table-bordered">
                <thead>
                    <tr class="text-center">
                        <th>Tanggal Pemesanan</th>
                        <th>Jadwal Pakai</th>
                        <th>Lapangan</th>
                        <th>Jenis Pemesanan</th>
                        <th>Status</th>
                        <th>Berlaku Sampai</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bookings as $booking)
                        <tr class="text-center">
                            <td>{{ \Carbon\Carbon::parse($booking->created_at)->format('d M Y') }}                    </td>
                            <td style="max-width: 200px">
                                @if ($booking->booking_type === 'member')
                                    @php
                                        $jadwal = json_decode($booking->schedule_details, true); // Decode JSON menjadi array
                                        $dayMapping = [
                                            'Monday' => 'Senin',
                                            'Tuesday' => 'Selasa',
                                            'Wednesday' => 'Rabu',
                                            'Thursday' => 'Kamis',
                                            'Friday' => 'Jumat',
                                            'Saturday' => 'Sabtu',
                                            'Sunday' => 'Minggu',
                                        ];
                                    @endphp

                                    @if (is_array($jadwal) && !empty($jadwal))
                                        @foreach ($jadwal as $day => $timeRange)
                                            {{ $dayMapping[$day] ?? 'Hari tidak dikenali' }} 
                                            Jam {{ $timeRange['start'] }} - {{ $timeRange['end'] }}
                                            @if (!$loop->last),@endif 
                                        @endforeach
                                    @else
                                        <span class="text-danger">Jadwal tidak tersedia</span>
                                    @endif
                                @else
                                    {{ \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') }}
                                    Jam {{ $booking->start_time }} - {{ $booking->end_time }}
                                @endif
                            </td>
                            <td>{{ $booking->field->name }}</td> 
                            <td>{{ ucfirst($booking->booking_type) }}</td>
                            <td>                    
                                @php
                                    $badgeClass = match ($booking->status) {
                                        'approved' => 'bg-success',
                                        'rejected' => 'bg-danger',
                                        default => 'bg-secondary', // Default jika status tidak dikenali
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{$booking->status}}</span>                        
                            </td>
                            <td>{{ \Carbon\Carbon::parse($booking->booking_date ?? $booking->valid_until)->format('d M Y') }}
                            </td>                                       
                            <td>
                                <div class="dropdown">
                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                        data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <div class="dropdown-menu text-center">
                                        @if (in_array($booking->status, ['approved','memberExtend', 'membership']))
                                            <!-- Hitung H-2 -->
                                            @php
                                                $hMinus2 = \Carbon\Carbon::parse($booking->booking_date)->subDays(2);
                                                $hLewat = \Carbon\Carbon::parse($booking->end_time)->setDate(
                                                    \Carbon\Carbon::parse($booking->booking_date)->year,
                                                    \Carbon\Carbon::parse($booking->booking_date)->month,
                                                    \Carbon\Carbon::parse($booking->booking_date)->day
                                                )->addMinute(); // Tambah 1 menit
                                            @endphp
                                    
                                            <!-- Tampilkan tombol pembatalan sesuai jenis pemesanan -->
                                            @if ($booking->booking_type === 'regular')
                                                <!-- Batasi pembatalan hanya untuk H-2 -->
                                                @if (Carbon::now()->lessThanOrEqualTo($hMinus2))
                                                    <a href="{{ route('pindah', ['id' => $booking->id]) }}" method="POST" class="btn btn-warning btn-sm">
                                                        Pindahkan Jadwal
                                                    </a>
                                                @elseif (Carbon::now()->greaterThanOrEqualTo($hLewat))
                                                    <span class="text-danger">Pemesanan sudah lewat</span>
                                                @else
                                                    <span class="text-danger">Tidak dapat melakukan pindah jadwal setelah h-2</span>
                                                @endif
                                            @elseif ($booking->booking_type === 'member' && in_array ($booking->status, ['approved', 'memberExtend', 'membership']))                           
                                                <a href="{{ route('pindah_member', ['id' => $booking->id]) }}" method="POST" class="btn btn-warning btn-sm">
                                                    Pindahkan Jadwal
                                                </a>                                                                              
                                            @endif
                                        @elseif (in_array($booking->status, ['pembatalan'])) 
                                            <span class="badge bg-label-secondary">Menunggu Persetujuan</span>
                                            
                                            <form action="{{ route('batalkan_pengajuan', ['id' => $booking->id]) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin membatalkan pengajuan ini?')">Batalkan Pengajuan</button>
                                            </form>                            
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Tidak ada riwayat pemesanan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
    </div>
@endsection