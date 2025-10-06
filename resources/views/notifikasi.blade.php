@extends('layouts.main')

@section('title', 'Notifikasi Jadwal')

@section('content')
<div class="card">
    <div class="card-header  text-center">
        <h3 class="mb-0">Notifikasi Anda</h3>
    </div>
    <div class="card-body mt-0">
        @if($notifications->isEmpty())
            <p>Tidak ada notifikasi terbaru.</p>
        @else
            <ul class="list-group">
                @foreach ($notifications as $booking)
                    <li class="list-group-item">
                        Anda memiliki jadwal sewa lapangan besok:
                        <br>
                        <strong>Tanggal:</strong> {{ $booking->booking_date }}<br>
                        <strong>Waktu:</strong> {{ $booking->start_time }} - {{ $booking->end_time }}<br>
                        <strong>Lapangan:</strong> {{ $booking->field->name ?? '-' }}
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
@endsection