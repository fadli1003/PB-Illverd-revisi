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
            <form id="pindahJadwalForm" action="{{ Route('update_pindah_member', ['id' => $booking->id]) }}" method="POST" enctype="multipart/form-data">
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
                        <label for="total_hours" class="form-label">Total Jam Sewa (per Bulan) - Tetap:</label>
                        <input type="number" class="form-control" id="total_hours" name="total_hours" value="{{ old('total_hours', $booking->total_hours ?? '') }}" readonly>
                        <small class="text-muted">Alokasi mingguan: <span id="weekly_hours_display">{{ number_format($booking->total_hours / 4, 2) }}</span> jam</small>
                    </div>

                    <!-- Jadwal Mingguan -->
                    <div class="mb-3">
                        <label class="form-label">Jadwal Mingguan Baru: <span id="weekly_hours">0.00</span> jam per minggu</label>
                        <small class="text-muted d-block mb-2">Batas maksimal: <span id="weekly_limit">{{ number_format($booking->total_hours / 4, 2) }}</span> jam per minggu</small>
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
                            // Ambil jadwal lama untuk inisialisasi
                            $oldScheduleDetails = old('schedule_details', $scheduleDetails);
                            ?>
                            @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $day)
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        @php
                                            $dayValue = $hariMap[$day];
                                            $isChecked = is_array($oldScheduleDetails) && isset($oldScheduleDetails[$dayValue]);
                                            $oldStart = $isChecked ? ($oldScheduleDetails[$dayValue]['start'] ?? '') : '';
                                            $oldEnd = $isChecked ? ($oldScheduleDetails[$dayValue]['end'] ?? '') : '';
                                        @endphp
                                        <input class="form-check-input day-checkbox" type="checkbox" id="day_{{ $day }}" name="days[]" value="{{ $dayValue }}" {{ $isChecked ? 'checked' : '' }}>
                                        <label class="form-check-label" for="day_{{ $day }}">{{ $day }}</label>
                                        <div class="mt-2 time-range" style="display: {{ $isChecked ? 'block' : 'none' }};">
                                            <input type="time" class="form-control schedule-time" name="schedule_details[{{ $dayValue }}][start]" placeholder="Waktu Mulai" step="3600" min="07:00" max="23:00" value="{{ $oldStart }}">
                                            @error("schedule_details.$dayValue.start")
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                            <input type="time" class="form-control schedule-time mt-2" name="schedule_details[{{ $dayValue }}][end]" placeholder="Waktu Selesai" step="3600" min="07:00" max="23:00" value="{{ $oldEnd }}">
                                            @error("schedule_details.$dayValue.end")
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
                <button type="submit" class="btn btn-primary">Perbarui Jadwal</button>
                
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const totalHoursInput = document.getElementById('total_hours');
            const weeklyHoursSpan = document.getElementById('weekly_hours');
            const checkboxes = document.querySelectorAll('.day-checkbox');
            let maxWeeklyHours = 0;

            // Fungsi untuk menghitung jam mingguan berdasarkan total jam bulanan
            function calculateWeeklyHours() {
                const totalHours = parseFloat(totalHoursInput.value) || 0;
                maxWeeklyHours = totalHours / 4; // 4 minggu per bulan
                weeklyHoursSpan.textContent = maxWeeklyHours.toFixed(2);
                
                // Reset pesan error
                hapusSemuaPesanError();
                updateAllTimeConstraints();
            }

            // Fungsi untuk menghitung total jam mingguan saat ini
            function getCurrentWeeklyHours() {
                let totalMinutes = 0;

                checkboxes.forEach(checkbox => {
                    if (checkbox.checked) {
                        const day = checkbox.value;
                        const timeRangeDiv = checkbox.closest('.form-check').querySelector('.time-range');
                        const startInput = timeRangeDiv.querySelector('input[name$="[start]"]');
                        const endInput = timeRangeDiv.querySelector('input[name$="[end]"]');

                        const start = startInput.value;
                        const end = endInput.value;

                        if (start && end) {
                            const [startHour] = start.split(':').map(Number);
                            const [endHour] = end.split(':').map(Number);

                            if (endHour > startHour) {
                                totalMinutes += (endHour - startHour) * 60;
                            }
                        }
                    }
                });

                return totalMinutes / 60;
            }

            // Fungsi untuk menampilkan pesan error
            function showErrorMessage(element, message) {
                // Hapus error message sebelumnya
                hapusPesanError(element);
                
                const errorDiv = document.createElement('div');
                errorDiv.className = 'text-danger error-message';
                errorDiv.textContent = message;
                errorDiv.style.fontSize = '0.875em';
                element.parentNode.appendChild(errorDiv);
            }

            // Fungsi untuk menghapus pesan error
            function hapusPesanError(element) {
                const existingError = element.parentNode.querySelector('.error-message');
                if (existingError) {
                    existingError.remove();
                }
            }

            // Fungsi untuk menghapus semua pesan error
            function hapusSemuaPesanError() {
                document.querySelectorAll('.error-message').forEach(el => el.remove());
            }

            // Fungsi untuk memperbarui batasan waktu untuk semua input
            function updateAllTimeConstraints() {
                checkboxes.forEach(checkbox => {
                    if (checkbox.checked) {
                        const timeRangeDiv = checkbox.closest('.form-check').querySelector('.time-range');
                        const startInput = timeRangeDiv.querySelector('input[name$="[start]"]');
                        const endInput = timeRangeDiv.querySelector('input[name$="[end]"]');
                        
                        if (startInput && endInput) {
                            updateEndTimeConstraint(startInput, endInput);
                        }
                    }
                });
            }

            // Fungsi untuk memperbarui batasan waktu selesai
            function updateEndTimeConstraint(startInput, endInput) {
                const start = startInput.value;
                if (start && maxWeeklyHours > 0) {
                    const [startHour] = start.split(':').map(Number);
                    const maxEndHour = Math.min(23, startHour + Math.floor(maxWeeklyHours));
                    
                    endInput.min = start;
                    endInput.max = `${String(maxEndHour).padStart(2, '0')}:00`;
                }
            }

            // Fungsi untuk memvalidasi apakah total jam tidak melebihi batas
            function validateWeeklyHours() {
                const currentHours = getCurrentWeeklyHours();
                if (currentHours > maxWeeklyHours) {
                    return false;
                }
                return true;
            }

            // Event listener untuk total jam bulanan
            totalHoursInput.addEventListener('input', calculateWeeklyHours);

            // Setup event listener untuk setiap checkbox hari
            checkboxes.forEach(checkbox => {
                const day = checkbox.value;
                const timeRangeDiv = checkbox.closest('.form-check').querySelector('.time-range');
                const startInput = timeRangeDiv ? timeRangeDiv.querySelector('input[name$="[start]"]') : null;
                const endInput = timeRangeDiv ? timeRangeDiv.querySelector('input[name$="[end]"]') : null;

                // Toggle waktu input saat checkbox di klik
                checkbox.addEventListener('change', function () {
                    if (timeRangeDiv) {
                        timeRangeDiv.style.display = this.checked ? 'block' : 'none';
                        if (!this.checked) {
                            if (startInput) {
                                startInput.value = '';
                                hapusPesanError(startInput);
                            }
                            if (endInput) {
                                endInput.value = '';
                                hapusPesanError(endInput);
                            }
                        }
                    }
                });

                // Batasi waktu selesai berdasarkan waktu mulai dan batas mingguan
                if (startInput) {
                    startInput.addEventListener('change', function () {
                        if (endInput) {
                            updateEndTimeConstraint(this, endInput);
                            hapusPesanError(this);
                        }
                    });
                }

                // Validasi saat waktu selesai berubah
                if (endInput) {
                    endInput.addEventListener('change', function() {
                        const startInput = this.closest('.time-range').querySelector('input[name$="[start]"]');
                        const start = startInput.value;
                        const end = this.value;
                        
                        if (start && end) {
                            const [startHour] = start.split(':').map(Number);
                            const [endHour] = end.split(':').map(Number);
                            
                            if (endHour <= startHour) {
                                showErrorMessage(this, 'Waktu selesai harus lebih besar dari waktu mulai');
                                return;
                            }
                            
                            // Hitung jam untuk sesi ini
                            const sessionHours = endHour - startHour;
                            
                            // Hitung total jam mingguan tanpa sesi ini
                            const currentHours = getCurrentWeeklyHours();
                            const hoursWithoutThisSession = currentHours - sessionHours;
                            
                            // Hitung sisa jam yang bisa digunakan
                            const remainingHours = maxWeeklyHours - hoursWithoutThisSession;
                            
                            if (sessionHours > remainingHours) {
                                showErrorMessage(this, `Waktu sewa anda melebihi batas maksimal mingguan`);
                            } else {
                                hapusPesanError(this);
                            }
                            
                        }
                    });
                }
            });

            // Inisialisasi batasan waktu awal
            document.querySelectorAll('input[type="time"]').forEach(input => {
                input.min = '07:00';
                input.max = '23:00';
            });
        });
    </script>
@endsection