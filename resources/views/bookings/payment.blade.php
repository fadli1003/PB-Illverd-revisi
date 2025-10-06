@extends('layouts.main')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Pembayaran DP - Booking #{{ $booking->id }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Detail Booking</h5>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Tipe Booking:</strong></td>
                                    <td>{{ ucfirst($booking->booking_type) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Lapangan:</strong></td>
                                    <td>{{ $booking->field->name }}</td>
                                </tr>
                                @if($booking->booking_type === 'regular')
                                <tr>
                                    <td><strong>Tanggal:</strong></td>
                                    <td>{{ $booking->booking_date }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Waktu:</strong></td>
                                    <td>{{ $booking->start_time }} - {{ $booking->end_time }}</td>
                                </tr>
                                @else
                                <tr>
                                    <td><strong>Total Jam:</strong></td>
                                    <td>{{ $booking->total_hours }} jam</td>
                                </tr>
                                @endif
                                <tr>
                                    <td><strong>Total Harga:</strong></td>
                                    <td>Rp {{ number_format($booking->amount_paid, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>DP yang Harus Dibayar:</strong></td>
                                    <td><strong>Rp {{ number_format($booking->dp_amount, 0, ',', '.') }}</strong></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Instruksi Pembayaran</h5>
                            <p>Silakan lakukan pembayaran DP sebesar <strong>Rp {{ number_format($booking->dp_amount, 0, ',', '.') }}</strong> melalui Midtrans.</p>
                            <button id="pay-button" class="btn btn-success btn-lg">
                                <i class="fas fa-credit-card"></i> Bayar Sekarang
                            </button>
                            <a href="{{ Route('book') }}" class="btn btn-secondary mt-2">
                                edit kembali data pemesanan?
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
<script>
    document.getElementById('pay-button').onclick = function(){
        // Trigger snap popup. Replace with your transaction token
        fetch('{{ route('payment.create') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                booking_id: {{ $booking->id }}
            })
        })
        .then(response => response.json())
        .then(data => {
            if(data.snap_token) {
                snap.pay(data.snap_token, {
                    onSuccess: function(result){
                        alert("Pembayaran berhasil!");
                        window.location.href = "{{ route('book') }}";
                    },
                    onPending: function(result){
                        alert("Pembayaran sedang diproses");
                        window.location.href = "{{ route('book') }}";
                    },
                    onError: function(result){
                        alert("Pembayaran gagal!");
                    }
                });
            } else {
                alert("Gagal membuat pembayaran");
            }
        });
    };
    </script>
@endsection

<meta name="csrf-token" content="{{ csrf_token() }}">