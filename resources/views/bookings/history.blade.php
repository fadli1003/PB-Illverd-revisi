@extends('layouts.main')

@section('content')
    @php
        use Carbon\Carbon;
    @endphp
    <div class="card mb-1">    
        <div class="card-header text-center">
            <h4 class="mb-0">Riwayat Pemesanan</h4>
        </div>
    </div>

    <!-- Tampilkan pesan kesalahan jika ada -->
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="card mb-3">
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
                    <th>Total Harga</th>
                    <th>Jumlah Bayar</th>
                    <th>Sisa Bayar</th>                    
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($bookings as $booking)
                    @php
                        $membership = $booking->membership;
                    @endphp
                    <tr>
                        <td class="text-center">{{ \Carbon\Carbon::parse($booking->created_at)->format('d M Y') }}</td>
                        <td class="text-center">
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
                        <td class="text-center">{{ $booking->field->name }}</td>
                        <td class="text-center">{{ ucfirst($booking->booking_type) }}</td>
                        <td class="text-center">                    
                            @php
                                $badgeClass = match ($booking->status) {
                                    'pending' => 'bg-warning',
                                    'approved' => 'bg-success',
                                    'rejected' => 'bg-danger',
                                    'pending_reschedule' => 'bg-info',
                                    default => 'bg-secondary', // Default jika status tidak dikenali
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{$booking->status}}</span>                        
                        </td>
                        <td class="text-center"> {{ \Carbon\Carbon::parse($booking->booking_date ?? $booking->valid_until)->format('d M Y') }}</td>
                        <td>Rp {{ number_format(($booking->status === 'membership' && $membership ? 
                            $membership->total_bayar + $booking->amount_paid : $booking->amount_paid), 0, ',', '.') }}</td>
                        <td>Rp {{ number_format(($booking->status === 'membership' && $membership ? 
                            $membership->jumlah_bayar + $booking->dp_amount : $booking->dp_amount), 0, ',', '.') }}</td>
                        <td>Rp {{ number_format(($booking->status === 'membership' && $membership ? 
                            $membership->sisa_bayar + $booking->remaining_amount : $booking->remaining_amount), 0, ',', '.') }}</td>                        
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                    data-bs-toggle="dropdown">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <div class="dropdown-menu text-center">                                    
                                    <a href="{{ route('cetak', ['id' => $booking->id]) }}" class="btn btn-primary btn-sm" target="_blank">
                                        Cetak
                                    </a>                                   
                                    @if ($booking->proof_of_payment)
                                        @if ($booking->status === 'membership' && $membership)
                                            <a class="btn btn-primary btn-sm" href="{{ asset('storage/' . $membership->bukti_transfer ) }}" target="_blank">
                                                    Lihat Bukti Transfer
                                            </a>
                                        @else
                                            <a class="btn btn-primary btn-sm" href="{{ asset('storage/' . $booking->proof_of_payment ) }}" target="_blank">
                                                    Lihat Bukti Transfer
                                            </a>
                                        @endif
                                    @else
                                        <span class="text-danger">Tidak Ada</span>
                                    @endif                                    
                                    @if (in_array($booking->status, ['pending', 'approved', 'membership']))
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
                                                <form action="{{ route('bookings.cancel', ['id' => $booking->id]) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin mengajukan pembatalan pesanan ini?')">Ajukan Pembatalan</button>
                                                </form>
                                            @elseif (Carbon::now()->greaterThanOrEqualTo($hLewat))
                                                <span class="text-danger">Pemesanan sudah lewat</span>
                                            @else
                                                <span class="text-danger">Tidak dapat mengajukan pembatalan</span>
                                            @endif
                                        @elseif ($booking->booking_type === 'member' && $booking->status === 'approved'))                           
                                            <a href="{{ route('memberExtend', ['id' => $booking->id]) }}" method="POST" class="btn btn-warning btn-sm">
                                                perpanjang
                                            </a>
                                        @elseif ($booking->booking_type === 'member' && $booking->status === 'pending')                            
                                            <form action="{{ route('bookings.cancel', ['id' => $booking->id]) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin mengajukan pembatalan pesanan ini?')">Ajukan Pembatalan</button>
                                            </form>                           
                                        @endif
                                    @elseif ((in_array($booking->status, ['pembatalan', 'memberExtend','pindah']))) 
                                        <span class="badge bg-label-info">Menunggu Persetujuan</span>
                                        
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
                        <td colspan="10" class="text-center">Tidak ada riwayat pemesanan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Tabel Membership yang Telah Diperpanjang -->
    <h3 class="text-center">Membership yang Telah Diperpanjang</h3>
    <div class="card mb-3">
        <table class="table table-bordered text-center">
            <thead>
                <tr>
                    <th>ID Pemesanan</th>
                    <th>Tanggal Perpanjangan</th>
                    <th>Lapangan</th>
                    <th>Tambahan Jam</th>
                    <th>Berlaku Sampai</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($memberships as $membership)
                    <tr>
                        <td>{{ $membership->booking->id }}</td>
                        <td>{{ \Carbon\Carbon::parse($membership->booking->valid_until)->format('d M Y') }}</td>
                        <td>{{ $membership->field->name }}</td>
                        <td>{{ $membership->additional_hours }} jam</td>
                        <td>{{ \Carbon\Carbon::parse($membership->new_valid_until)->format('d M Y') }}</td>
                        <td>
                            <span class="badge bg-success">{{ ucfirst($membership->status) }}</span>
                        </td>                      
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">Tidak ada membership yang diperpanjang.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            (function () {
                const horizVertExample = document.getElementById('both-scrollbars-example');    
                
                    if (horizVertExample) {
                    new PerfectScrollbar(horizVertExample, {
                        wheelPropagation: true
                    });
                    }
                })();
        });
    </script>
@endsection