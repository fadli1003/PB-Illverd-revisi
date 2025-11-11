@extends('layouts.main')

@section('content')
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('pesanan disetujui') }}
        </div>
    @endif

    <div class="card mt-0 mb-0">
            <div class=" mb-0 mt-0">                               
                <div class="card-header mb-0 text-center">
                    <h3 class="mb-0">Pilih Jadwal yang Ingin Dikelola</h3>                   
                </div>
                <div class="mt-0 ml-2">
                    <form action="{{ route('kelola_jadwal') }}" method="GET">
                        <div class="form-input">
                            <input type="month" name="month" class="form-contro input-tggl" value="{{ request('month', now()->format('Y-m')) }}">
                            <button class="btn jadwal-btn ml-2" type="submit">Tampilkan</button>
                        </div>
                    </form>
                </div>
            </div>
    </div>
    <div class="card mb-3 table-responsive mt-0">
            <table class="table table-bordered text-center">
                <thead>
                    <tr>
                        <th>Nama Pemesan</th>
                        <th>Lapangan</th>
                        <th>Tanggal Pemesanan</th>
                        <th>Jadwal</th>
                        <th>Total Harga</th>
                        <th>DP</th>
                        <th>Sisa Bayar</th>                    
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bookings as $booking)
                        <tr>
                            <td>{{ $booking->user->name ?? 'Tidak Diketahui' }}</td>
                            <td>{{ $booking->field->name }}</td>
                            <td>{{ \Carbon\Carbon::parse($booking->booking_date ?? $booking->valid_until)->format('d M Y') }}</td>
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
                                                    {{ $timeRange['start'] }} - {{ $timeRange['end'] }}
                                                    @if (!$loop->last),@endif 
                                                @endforeach
                                            @else
                                                <span class="text-danger">Jadwal tidak tersedia</span>
                                            @endif
                                        @else
                                            {{ $booking->start_time }} - {{ $booking->end_time }}
                                        @endif</td>                            
                            <td>Rp {{ number_format($booking->amount_paid, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($booking->dp_amount, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($booking->remaining_amount, 0, ',', '.') }}</td>
                            <td>
                                @if($booking->status === 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                @elseif($booking->status === 'approved')
                                    <span class="badge bg-success">Disetujui</span>
                                @elseif($booking->status === 'pembatalan')
                                    <span class="badge bg-danger">Pembatalan</span>
                                @elseif($booking->status === 'membership')
                                    <span class="badge bg-danger">Membership</span>
                                @else
                                    <span class="badge bg-danger">{{$booking->status}}</span>
                                @endif
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow btn-toggle"
                                            data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <div class="dropdown-menu text-center">                                                                                
                                        <a href="{{ url('admin/edit_pesanan/' . $booking->id) }}" method="POST"
                                            class="btn btn-secondary btn-sm edit-btn" >Edit
                                        </a>
                                        <form action="{{ url('admin/hapus-pesanan/' . $booking->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pesanan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm cancel-btn-table" >Hapus</button>
                                        </form>
                                        @if($booking->booking_type === 'member')
                                            <a href="{{ route('memberExtend', ['id' => $booking->id]) }}" method="POST" class="btn btn-warning btn-sm secondary-btn">
                                                Perpanjang
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
    </div>
@endsection