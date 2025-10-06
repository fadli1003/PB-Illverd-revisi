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
    @if($bookings->isEmpty())
        <div class="card mb-1">
            <div class="card-body text-center">
                <h4>Tidak Ada Pengajuan Perpanjangan atau Pembatalan</h4>
            </div>
        </div>
    @else
        <div class="card mb-1">
            <div class="card-body text-center">
                <h3>Pengajuan Perpanjangan dan Pembatalan</h3>
            </div>
        </div>
        <div class="card mb-3 mt-1">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Tanggal Pemesanan</th>
                        <th>Lapangan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($bookings as $booking)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') }}</td>
                            <td>{{ $booking->field->name }}</td>
                            <td>
                                @if ($booking->status === 'pembatalan')
                                    <span class="badge bg-warning">Menunggu Persetujuan Pembatalan</span>
                                @endif
                            </td>
                            <td>
                                @if ($booking->status === 'pembatalan')
                                    <!-- Tombol Setujui Pembatalan -->
                                    <form action="{{ route('setujui_cancel', ['id' => $booking->id]) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-success btn-sm">Setujui</button>
                                    </form>

                                    <!-- Tombol Tolak Pembatalan -->
                                    <form action="{{ route('tolak_cancel', ['id' => $booking->id]) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-danger btn-sm">Tolak</button>
                                    </form>
                                                                                   
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection