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
    <div class="card mb-4">
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
            <form action="{{ Route('update_pindah', ['id' => $booking->id]) }}" method="POST" enctype="multipart/form-data">
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
                        <label for="start_time" class="form-label">Waktu Mulai Baru</label>
                        <input type="time" class="form-control" id="waktu_mulai" name="waktu_mulai"
                            value="{{ old('waktu_mulai', \Carbon\Carbon::createFromFormat('H:i:s', $booking->start_time)->format('H:i')) }}" 
                            min="07:00" max="23:00" step="3600">
                    </div>

                    <!-- Durasi Sewa -->
                            <div class="mb-3">
                                <label for="duration" class="form-label">Durasi Sewa (jam)</label>
                                <input type="text" id="duration" name="duration" class="form-control" 
                                    value="{{ old('duration', $booking->duration ?? '') }}"  readonly>
                            </div>

                    <!-- Waktu Selesai -->
                    <div class="mb-3">
                        <label for="end_time" class="form-label">Waktu Selesai Baru (Otomatis)</label>
                        <input type="time" class="form-control" id="waktu_selesai" name="waktu_selesai"
                               value="{{ old('waktu_selesai', \Carbon\Carbon::createFromFormat('H:i:s', $booking->end_time)->format('H:i')) }}" readonly>
                    </div>
                </div>

                <!-- Tombol Submit -->
                <button type="submit" class="btn btn-primary">Perbarui Jadwal</button>
                
            </form>
        </div>
    </div>

        <!-- Tambahkan script validasi di sini -->
     <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Ambil elemen input
            const startTimeInput = document.getElementById('waktu_mulai');
            const durationInput = document.getElementById('duration'); // Ambil input durasi
            const endTimeInput = document.getElementById('waktu_selesai'); // Ambil input end time untuk ditampilkan

            // Ambil durasi lama dari input readonly
            const originalDuration = parseFloat(durationInput.value) || 0;

            // Fungsi untuk menampilkan pesan error
            function showErrorMessage(inputElement, message) {
                // Hapus error message sebelumnya (jika ada)
                hapusPesanError(inputElement);

                const errorDiv = document.createElement('div');
                errorDiv.className = 'text-danger error-message';
                errorDiv.textContent = message;
                errorDiv.style.fontSize = '0.875em'; // Ukuran font kecil
                // Tambahkan elemen error setelah input
                inputElement.parentNode.insertBefore(errorDiv, inputElement.nextSibling);
            }

            // Fungsi untuk menghapus pesan error
            function hapusPesanError(inputElement) {
                // Cari elemen error yang terkait dengan input ini
                const existingError = inputElement.parentNode.querySelector('.error-message');
                if (existingError) {
                    existingError.remove(); // Hapus elemen error
                }
            }

            // Fungsi untuk menghitung dan menampilkan waktu selesai baru
            function updateEndTimeDisplay(startTimeValue, duration) {
                 if (!startTimeValue || duration <= 0) {
                    endTimeInput.value = '';
                    return;
                 }

                 const [startHour, startMinute] = startTimeValue.split(':').map(Number);
                 const startInMinutes = startHour * 60 + startMinute;
                 const totalDurationMinutes = duration * 60;
                 const endInMinutes = startInMinutes + totalDurationMinutes;

                 const endHour = Math.floor(endInMinutes / 60);
                 const endMinute = endInMinutes % 60;

                 // Format jam dan menit menjadi string 'HH:MM'
                 const formattedEndHour = String(endHour).padStart(2, '0');
                 const formattedEndMinute = String(endMinute).padStart(2, '0');

                 endTimeInput.value = `${formattedEndHour}:${formattedEndMinute}`;
            }


            // Validasi waktu mulai (start_time) dan durasi
            if (startTimeInput) {
                startTimeInput.addEventListener('change', function() {
                    const startTimeValue = this.value;
                    let hasError = false; // Flag untuk menandai apakah ada error

                    // Hapus pesan error sebelumnya untuk start_time
                    hapusPesanError(this);

                    if (startTimeValue) {
                        const [startHour, startMinute] = startTimeValue.split(':').map(Number);

                        // 1. Validasi batas operasional untuk start_time
                        if (startHour < 7 || startHour > 23) {
                            showErrorMessage(this, 'Waktu mulai harus antara 07:00 dan 23:00.');
                            hasError = true;
                        } else {
                            // Jika start_time valid, lanjutkan ke validasi durasi
                            if (originalDuration > 0) {
                                const startInMinutes = startHour * 60 + startMinute;
                                const totalDurationMinutes = originalDuration * 60;
                                const endInMinutes = startInMinutes + totalDurationMinutes;
                                const endHour = Math.floor(endInMinutes / 60);
                                const endMinute = endInMinutes % 60;

                                // Pastikan waktu selesai hasil perhitungan juga dalam batas operasional
                                if (endHour > 23 || (endHour === 23 && endMinute > 0)) {
                                    // Jika start + durasi melebihi 23:00, beri error di start_time
                                    showErrorMessage(this, `Waktu mulai + durasi (${originalDuration} jam) melebihi batas operasional (23:00).`);
                                    hasError = true;
                                } else {
                                    // Jika tidak ada error, update tampilan waktu selesai
                                    updateEndTimeDisplay(startTimeValue, originalDuration);
                                }
                            }
                        }
                    } else {
                        // Jika input dikosongkan, hapus pesan error dan kosongkan end_time
                        updateEndTimeDisplay('', originalDuration); // Kosongkan end_time
                    }

                    // Jika tidak ada error pada start_time, hapus error dari end_time juga (jika ada)
                    if (!hasError) {
                        hapusPesanError(endTimeInput);
                    } else {
                        // Jika ada error di start_time, pastikan end_time kosong dan tidak ada error sendiri
                        endTimeInput.value = '';
                        hapusPesanError(endTimeInput); // Hapus error spesifik end_time jika ada, karena error sebenarnya ada di start
                    }
                });
            }

            // Panggil updateEndTimeDisplay saat halaman dimuat jika startTimeInput ada
            if (startTimeInput && startTimeInput.value && originalDuration > 0) {
                updateEndTimeDisplay(startTimeInput.value, originalDuration);
            }

        });
    </script>

@endsection