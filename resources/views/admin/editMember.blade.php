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
    <div class="card mb-2">
        <div class="card-header d-flex align-items-center">
            <h3 class="mb-0" style="justify-self: center">Informasi Jadwal Sebelumnya</h3>
        </div>
        <div class="card-body">
            <p><strong>Lapangan:</strong> {{ $booking->field->name }}</p>
            <p><strong>Waktu Lama:</strong>     
                @php         
                    $scheduleDetails = json_decode($booking->schedule_details, true);
                @endphp
            
                @if ($scheduleDetails && is_array($scheduleDetails))
                    @foreach ($scheduleDetails as $day => $time)
                        <br>{{ ucfirst($day) }}: {{ $time['start'] }} - {{ $time['end'] }}
                    @endforeach
                @else
                    Tidak ada jadwal lama.
                @endif
            </p>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header d-flex align-items-center">
            <h3 class="mb-0" style="justify-self: center">Form Pindah Jadwal</h3>
        </div>
        <div class="card-body">
            <form action="{{ Route('editPesananMember', ['id' => $booking->id]) }}" method="POST" enctype="multipart/form-data">
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

                <!-- Formulir Tambahan untuk Member -->
                <div id="member_booking" class="booking-form">
                    <!-- Total Jam Sewa (per Bulan) -->
                    <div class="mb-3">
                        <label for="total_hours" class="form-label">Total Jam Sewa (per Bulan):</label>
                        <input type="number" class="form-control" id="total_hours" name="total_hours" value="{{ old('total_hours', $booking->total_hours ?? '') }}" readonly>
                    </div>

                    <!-- Jadwal Mingguan -->
                    <div class="mb-3">
                        <label class="form-label">Jadwal Mingguan: {{ number_format($booking->total_hours / 4, 2) }} jam per minggu</label>
                        <div class="row">
                            <?php 
                            $hariMap = [
                                'Senin' => 'Monday',
                                'Selasa' => 'Tuesday',
                                'Rabu' => 'Wednesday',
                                'Kamis' => 'Thursday',
                                'Jumat' => 'Friday',
                                'Sabtu' => 'Saturday',
                                'Minggu' => 'Sunday',
                            ];
                            ?>
                            @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $day)
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input day-checkbox" type="checkbox" id="day_{{ $day }}" name="days[]" value="{{ $hariMap[$day] }}">
                                        <label class="form-check-label" for="day_{{ $day }}">{{ $day }}</label>
                                        <div class="mt-2 time-range" style="display: none;">
                                            <input type="time" class="form-control" name="schedule_details[{{ $hariMap[$day] }}][start]" placeholder="Waktu Mulai">
                                            @error("schedule_details.$day.start")
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                            <input type="time" class="form-control mt-2" name="schedule_details[{{ $hariMap[$day] }}][end]" placeholder="Waktu Selesai">
                                            @error("schedule_details.$day.end")
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <!-- Tombol Submit -->
                <button type="submit" class="btn btn-primary">Ubah Jadwal</button>
                <script>
                    console.log(document.getElementById('submit-button')); // Debug elemen tombol
                </script>
            </form>
        </div>
    </div>

    <script>
        // Tampilkan input waktu jika hari dipilih
        document.querySelectorAll('.day-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                const timeRangeDiv = this.closest('.form-check').querySelector('.time-range');
                if (this.checked) {
                    timeRangeDiv.style.display = 'block';
                } else {
                    timeRangeDiv.style.display = 'none';
                }
            });
        });

        document.querySelector('form').addEventListener('submit', function (event) {
            const scheduleDetails = {};
            const days = document.querySelectorAll('.day-checkbox:checked');

            days.forEach(day => {
                const dayName = day.value;
                const startInput = day.closest('.form-check').querySelector('input[type="time"][placeholder="Waktu Mulai"]');
                const endInput = day.closest('.form-check').querySelector('input[type="time"][placeholder="Waktu Selesai"]');

                if (startInput.value && endInput.value) {
                    scheduleDetails[dayName] = {
                        start: startInput.value,
                        end: endInput.value,
                    };
                }
            });

            // Tambahkan schedule_details ke input tersembunyi
            const scheduleInput = document.createElement('input');
            scheduleInput.type = 'hidden';
            scheduleInput.name = 'schedule_details';
            scheduleInput.value = JSON.stringify(scheduleDetails);
            this.appendChild(scheduleInput);
        });
    
    </script>
@endsection