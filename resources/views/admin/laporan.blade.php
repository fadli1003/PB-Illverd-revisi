@extends('layouts.main')

@section('content')
    <div class="card">
        <div class="card-body">
            <!-- Form Filter Bulan -->
            <div class=" mb-1">
                <div class="col-">
                </div>
                <div class="col- h3 mt-1 mb-0 text-center">
                    <div class="card-header">
                        <a>Laporan Penyewaan</a>
                    </div>
                </div>
                <div class="col- mt-0 mb-2">
                    <form action="{{ route('laporan') }}" method="GET">
                        <div class="input-group">
                            <input type="month" name="month" class="input-tggl" value="{{ request('bulan', now()->format('Y-m')) }}">
                            <button class="btn btn-primary1 ml-1" type="submit">Tampilkan</button>
                        </div>
                    </form>
                </div>
            </div>

            @if ($reportData)
                <div class="row mb-4 text-black">
                    <div class="col-md-6 col-lg-3">
                        <div class="card bg-info mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Total Pendapatan</h5>
                                <p class="card-text fs-4">{{ number_format($reportData['total_pendapatan'], 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <div class="card bg-success mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Jumlah Pesanan</h5>
                                <p class="card-text fs-4">{{ $reportData['total_booking'] }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <div class="card bg-warning mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Pesanan Per Hari</h5>
                                <p class="card-text fs-4">{{ number_format($reportData['rerata_per_hari'], 2, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <div class="card bg-secondary mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Total Member</h5>
                                <p class="card-text fs-4">{{ $reportData['total_member'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabel Pemesanan -->
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Tanggal</th>
                                <th>Lapangan</th>
                                <th>Jam Sewa</th>
                                <th>Jenis</th>
                                <th>Total Harga</th>
                                <th>Sisa Bayar</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($bookings as $booking)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ \Carbon\Carbon::parse($booking->booking_date ?? $booking->create_at)->format('d M Y') }}</td>
                                    <td>{{ $booking->field->name }}</td>
                                    <td style="max-width: 160px">
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
                                                    {{ \Carbon\Carbon::parse($timeRange['start'])->format('H:i') }} - {{ \Carbon\Carbon::parse($timeRange['end'])->format('H:i') }}
                                                    @if (!$loop->last), <br>@endif
                                                @endforeach
                                            @else
                                                <span class="text-danger">Jadwal tidak tersedia</span>
                                            @endif
                                        @else
                                            {{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}
                                        @endif
                                        </td>
                                    <td>{{ ucfirst($booking->booking_type) }}</td>
                                    <td>Rp. {{ number_format($booking->amount_paid, 0, ',', '.') }}</td>
                                    <td>Rp. {{ number_format($booking->remaining_amount, 0, ',', '.') }}</td>
                                    <td>{{ ucfirst($booking->status) }}</td>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow btn-toggle"
                                                    data-bs-toggle="dropdown">
                                                    <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                <a href="{{ url('admin/edit_pesanan/' . $booking->id) }}" method="POST"
                                                    class="btn btn-secondary btn-sm edit-btn" >Edit</a>
                                                <form action="{{ url('admin/pesanan_masuk/' . $booking->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pesanan ini?')">
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
            @else
            <div class="alert alert-info">Tidak ada data pemesanan untuk bulan tersebut.</div>
        </div>
        @endif
    </div>
@endsection
