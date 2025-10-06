@extends('layouts.main')

@section('content')
    <!-- Tampilkan pesan kesalahan jika ada -->
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    
    <!-- Tampilkan informasi pemesanan -->
    <div class="card mb-1">
        <div class="card-header d-flex align-items-center">
            <h3 class="mb-0" style="justify-self: center">Informasi Pemesanan Sebelumnya</h3>
        </div>
        <div class="card-body">
            <p><strong>Lapangan:</strong> {{ $booking->field->name }}</p>
            <p><strong>Tanggal Lama:</strong> {{ \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') }}</p>
            <p><strong>Waktu Lama:</strong> {{ $booking->start_time }} - {{ $booking->end_time }}</p>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header d-flex align-items-center">
            <h3 class="mb-0" style="justify-self: center">Form Pindah Jadwal</h3>
        </div>
        <div class="card-body">
            <form action="{{ Route('editPesananRegular', ['id' => $booking->id]) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <!-- Toggle Pilihan Jenis Pemesanan -->
                <div class="mb-3">
                    <label for="booking_type" class="form-label">Jenis Pemesanan:</label>
                    <input type="text" id="booking_type" name="booking_type" class="form-control" 
                        value="{{ old('booking_type', $booking->booking_type ?? '') }}"  readonly>
                </div>

                <!-- Pilihan Lapangan -->
                <div class="mb-3">
                    <label for="field_id" class="form-label">Pilih Lapangan:</label>
                    <select id="field_id" name="field_id" class="form-select" required>
                        <option value="">-- Pilih Lapangan --</option>
                        @if(isset($fields) && $fields->isNotEmpty())
                            @foreach ($fields as $field)
                                <option value="{{ $field->id }}" {{ $booking->field_id == $field->id ? 'selected' : '' }}>
                                    {{ $field->name }}
                                </option>
                            @endforeach
                        @else
                            <option value="" disabled>Data lapangan tidak tersedia.</option>
                        @endif
                    </select>
                </div>

                <!-- Formulir Pemesanan Biasa -->
                <div id="regular_booking" class="booking-form">
                    <!-- Tanggal Pemesanan -->
                    <div class="mb-3">
                        <label for="booking_date" class="form-label">Tanggal Pemesanan</label>
                        <input type="date" class="form-control" id="booking_date" name="tggl_baru"
                            value="{{ old('booking_date', $booking->booking_date ?? '') }}" >
                    </div>

                    <!-- Waktu Mulai -->
                    <div class="mb-3">
                        <label for="start_time" class="form-label">Waktu Mulai</label>
                        <input type="time" class="form-control" id="waktu_mulai" name="waktu_mulai"
                            value="{{ old('waktu_mulai', \Carbon\Carbon::createFromFormat('H:i:s', $booking->start_time)->format('H:i')) }}" >
                    </div>

                    <!-- Waktu Selesai -->
                    <div class="mb-3">
                        <label for="end_time" class="form-label">Waktu Selesai</label>
                        <input type="time" class="form-control" id="waktu_selesai" name="waktu_selesai"
                            value="{{ old('waktu_selesai', \Carbon\Carbon::createFromFormat('H:i:s', $booking->end_time)->format('H:i')) }}" >
                    </div>
                    <!-- Jumlah Bayar -->
                            <div class="mb-3">
                                <label for="amount_paid" class="form-label">Total Harga</label>
                                <input type="text" class="form-control" id="amount_paid" name="amount_paid" value="{{ number_format($booking->amount_paid, 0, '', '') }}" readonly>
                            </div>
                            <!-- DP (Down Payment) -->
                            <div class="mb-3">
                                <label for="dp_baru" class="form-label">Jumlah DP/Bayar Lunas </label>
                                <input type="number" class="form-control" id="dp_baru" name="dp_baru" value="{{$booking->dp_amount}}">
                            </div>
                            <!-- Sisa Bayar -->
                            <div class="mb-3">
                                <label for="remaining_amount" class="form-label">Sisa Bayar</label>
                                <input type="text" class="form-control" id="remaining_amount" name="remaining_amount" value="{{$booking->remaining_amount}}" readonly>
                            </div>
                </div>
                <!-- Tombol Submit -->
                <button type="submit" class="btn btn-primary">Ubah Pesanan</button>
            </form>
        </div>
    </div>

    <script>
            // Hitung sisa bayar berdasarkan DP
        document.getElementById('dp_baru')?.addEventListener('input', calculateRemaining);    
        function calculateRemaining() {
            const totalPrice = parseFloat(document.getElementById('amount_paid').value.replace(/[^0-9]/g, '')) || 0;
            const dpAmount = parseFloat(document.getElementById('dp_baru').value) || 0;
            const remainingAmount = totalPrice - dpAmount;

            document.getElementById('remaining_amount').value = `Rp ${remainingAmount.toLocaleString('id-ID')}`;
        }
    </script>
@endsection