@extends('layouts.main')

@section('content')
    <h3>Silahkan Ajukan Pemesanan</h3>

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
    <div class="row">
        <div class="col-5">
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center">
                    <h5 class="mb-0">Form Pemesanan Lapangan PB Illverd</h5>
                </div>
                <div class="card-body">
                    <form action="{{ Route('bookings.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <!-- Toggle Pilihan Jenis Pemesanan -->
                        <div class="mb-3">
                            <label for="booking_type" class="form-label">Jenis Pemesanan:</label>
                            <select id="booking_type" name="booking_type" class="form-select" required>
                                <option value="regular">Pemesanan Biasa</option>
                                <option value="member">Pemesanan Member</option>
                            </select>
                        </div>
                        <!-- Pilihan Lapangan -->
                        <div class="mb-3">
                            <label for="field_id" class="form-label">Pilih Lapangan:</label>
                            <select id="field_id" name="field_id" class="form-select" required>
                                <option value="">-- Pilih Lapangan --</option>
                                @if(isset($fields) && $fields->isNotEmpty())
                                    @foreach ($fields as $field)
                                        <option value="{{ $field->id }}" {{ isset($field_id) && $field_id == $field->id ? 'selected' : '' }}>
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
                                <input type="date" class="form-control" id="booking_date" name="booking_date"
                                    value="{{ old('booking_date', $booking_date ?? '') }}" >
                            </div>
                            <div class="mb-3">
                                <label for="start_time" class="form-label">Waktu Mulai</label>
                                <input type="time" id="start_time" name="start_time" class="form-control" 
                                    value="{{ old('start_time', $start_time ?? '') }}" 
                                    min="07:00" max="23:00" step="3600">
                            </div>

                            <!-- Durasi Sewa -->
                            <div class="mb-3">
                                <label for="duration" class="form-label">Durasi Sewa (jam)</label>
                                <select id="duration" name="duration" class="form-control">
                                    <option value="">-- Pilih Durasi --</option>
                                    <option value="1" {{ (old('duration', $duration ?? '')) == '1' ? 'selected' : '' }}>1 Jam</option>
                                    <option value="2" {{ (old('duration', $duration ?? '')) == '2' ? 'selected' : '' }}>2 Jam</option>
                                    <option value="3" {{ (old('duration', $duration ?? '')) == '3' ? 'selected' : '' }}>3 Jam</option>
                                    <option value="4" {{ (old('duration', $duration ?? '')) == '4' ? 'selected' : '' }}>4 Jam</option>
                                    <option value="5" {{ (old('duration', $duration ?? '')) == '5' ? 'selected' : '' }}>5 Jam</option>
                                    <option value="6" {{ (old('duration', $duration ?? '')) == '6' ? 'selected' : '' }}>6 Jam</option>
                                </select>
                            </div>

                            <!-- Waktu Selesai -->
                            <div class="mb-3">
                                <label for="end_time" class="form-label">Waktu Selesai</label>
                                <input type="time" class="form-control" id="end_time" name="end_time" 
                                 value="{{ old('end_time', $end_time ?? '') }}" readonly>

                            </div>
                            <!-- Jumlah Bayar -->
                            <div class="mb-3">
                                <label for="amount_paid" class="form-label">Total Harga</label>
                                <input type="text" class="form-control" id="amount_paid" name="amount_paid" readonly>
                                <!-- Hidden input untuk menyimpan nilai angka -->
                                <input type="hidden" id="amount_paid" name="amount_paid" value="{{ old('amount_paid') }}">
                            </div>
                            <!-- DP (Down Payment) -->
                            <div class="mb-3">
                                <label for="dp_amount" class="form-label">Jumlah DP/Bayar Lunas </label>
                                <input type="number" class="form-control" id="dp_amount" name="dp_amount" placeholder="Masukkan jumlah DP minimal 50%">
                            </div>
                            <!-- Sisa Bayar -->
                            <div class="mb-3">
                                <label for="remaining_amount" class="form-label">Sisa Bayar</label>
                                <input type="text" class="form-control" id="remaining_amount" name="remaining_amount" readonly>
                                <input type="hidden" id="remaining_amount" name="remaining_amount" value="{{ old('remaining_amount') }}">
                            </div>
                        </div>
                        <!-- Formulir Tambahan untuk Member -->
                        <div id="member_booking" class="booking-form" style="display: none;">
                            <!-- Total Jam Sewa (per Bulan) -->
                            <div class="mb-3">
                                <label for="total_hours" class="form-label">Total Jam Sewa (per Bulan):</label>
                                <<select id="total_hours" name="total_hours" class="form-control">
                                    <option value="">-- Pilih Total Jam Sewa (per Bulan) --</option>
                                    <option value="12" {{ (old('duration', $duration ?? '')) == '12' ? 'selected' : '' }}>12 Jam</option>
                                    <option value="16" {{ (old('duration', $duration ?? '')) == '16' ? 'selected' : '' }}>16 Jam</option>
                                    <option value="20" {{ (old('duration', $duration ?? '')) == '20' ? 'selected' : '' }}>20 Jam</option>
                                    <option value="24" {{ (old('duration', $duration ?? '')) == '24' ? 'selected' : '' }}>24 Jam</option>
                                </select>
                            </div>
                            <!-- Jadwal Mingguan -->
                            <div class="mb-3">
                                <label class="form-label">Jadwal Mingguan: <span id="weekly_hours">0.00</span> jam per minggu</label>
                                {{-- <small class="text-muted d-block mb-2">Batas maksimal: <span id="weekly_limit">0.00</span> jam per minggu</small> --}}
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
                                                    <input type="time" class="form-control" name="schedule_details[{{ $hariMap[$day] }}][start]" placeholder="Waktu Mulai" step="3600" min="07:00" max="23:00">
                                                    @error("schedule_details.$day.start")
                                                        <div class="text-danger">{{ $message }}</div>
                                                    @enderror
                                                    <input type="time" class="form-control mt-2" name="schedule_details[{{ $hariMap[$day] }}][end]" placeholder="Waktu Selesai" step="3600" min="07:00" max="23:00">
                                                    @error("schedule_details.$day.end")
                                                        <div class="text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            
                            <!-- Jumlah Bayar untuk Member -->
                            <div class="mb-3">
                                <label for="amount_paid_member" class="form-label">Total Harga (Member)</label>
                                <input type="text" class="form-control" id="amount_paid_member" name="amount_paid" readonly>
                            </div>
                            <!-- DP (Down Payment) untuk Member -->
                            <div class="mb-3">
                                <label for="dp_amount_member" class="form-label">Jumlah DP/Bayar Lunas </label>
                                <input type="number" class="form-control" id="dp_amount_member" name="dp_amount" placeholder="Masukkan jumlah DP minimal 50%">
                            </div>
                            <!-- Sisa Bayar untuk Member -->
                            <div class="mb-3">
                                <label for="remaining_amount_member" class="form-label">Sisa Bayar</label>
                                <input type="text" class="form-control" id="remaining_amount_member" name="remaining_amount" readonly>
                            </div>
                        </div>                        
                        
                        {{-- <!-- Upload Bukti Transfer -->
                        <div class="mb-3">
                            <label for="proof_of_payment" class="form-label">Upload Bukti Transfer</label>
                            <input type="file" class="form-control" id="proof_of_payment" name="proof_of_payment" accept="image/*">
                        </div>  --}}
                        
                        <!-- Tombol Submit -->
                        <button type="submit" class="btn btn-primary">Pesan Sekarang</button> 
                    </form>
                </div>
            </div>
        </div>
        <div class="col-7">
            <div class="card mb-4">                
                <div class="card-body">
                    <!-- Form Pilih Tanggal -->
                    <div class="card-header d-flex justify-content-between mb-0">
                        <div class="form-container">
                            <form id="date-form">
                                <label for="date">Pilih Tanggal:</label>
                                <input type="date" id="date" name="date" value="{{ $selectedDate }}" required>
                                <button class="btn btn-primary" type="submit">Lihat Jadwal</button>
                            </form>
                        </div>
                    </div>
                    <!-- Container untuk menampilkan jadwal -->
                    <div id="jadwal-container">
                        @include('jadwal')
                    </div>

                </div>
            </div>        

        </div>
    </div>
       
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // === VARIABEL GLOBAL ===
            const rateMember = 20000;
            const ratePerHour = 25000;
            let maxWeeklyHours = 0;
            

            // === FUNGSI UTAMA ===
            
            // Toggle booking type
            function toggleBookingType() {
                const type = document.getElementById('booking_type')?.value;
                const regular = document.getElementById('regular_booking');
                const member = document.getElementById('member_booking');
                
                if (type === 'member') {
                    regular.style.display = 'none';
                    member.style.display = 'block';
                    
                    // Disable regular inputs
                    document.querySelectorAll('#regular_booking input').forEach(input => {
                        input.disabled = true;
                    });
                    
                    // Enable member inputs  
                    document.querySelectorAll('#member_booking input').forEach(input => {
                        input.disabled = false;
                    });
                } else {
                    regular.style.display = 'block';
                    member.style.display = 'none';
                    
                    // Enable regular inputs
                    document.querySelectorAll('#regular_booking input').forEach(input => {
                        input.disabled = false;
                    });
                    
                    // Disable member inputs
                    document.querySelectorAll('#member_booking input').forEach(input => {
                        input.disabled = true;
                    });
                }
            }

            // Hitung waktu selesai
            function calculateEndTime() {
                const startTime = document.getElementById('start_time')?.value;
                const duration = parseInt(document.getElementById('duration')?.value);
                const endTimeInput = document.getElementById('end_time');

                if (!startTime || !duration || !endTimeInput) return;

                const [hours] = startTime.split(':');
                let endHour = parseInt(hours) + duration;
                
                // Kurangi 1 menit untuk menghindari tabrakan jadwal
                endHour--;
                if (endHour > 22) endHour = 22;
                
                endTimeInput.value = `${endHour.toString().padStart(2, '0')}:59`;
            }

            // Update opsi durasi
            function updateDurationOptions() {
                const startTime = document.getElementById('start_time')?.value;
                const durationSelect = document.getElementById('duration');

                if (!startTime || !durationSelect) return;

                const [hours] = startTime.split(':');
                const startHour = parseInt(hours);
                const maxDuration = 23 - startHour;
                
                // Disable opsi yang melebihi batas
                Array.from(durationSelect.options).forEach(option => {
                    const duration = parseInt(option.value);
                    option.disabled = duration > maxDuration;
                });
            }

            // Hitung harga reguler
            function calculateTotalPrice() {
                const duration = parseInt(document.getElementById('duration')?.value) || 0;
                const amountInput = document.getElementById('amount_paid');
                
                if (duration > 0 && amountInput) {
                    const totalPrice = duration * ratePerHour;
                    amountInput.value = `Rp ${totalPrice.toLocaleString('id-ID')}`;
                }
            }

            // Hitung harga member
            function calculateMemberPrice() {
                const totalHours = parseFloat(document.getElementById('total_hours')?.value) || 0;
                const amountInput = document.getElementById('amount_paid_member');
                
                if (amountInput) {
                    const totalPrice = totalHours * rateMember;
                    amountInput.value = `Rp ${totalPrice.toLocaleString('id-ID')}`;
                }
            }

            // Hitung sisa bayar reguler
            function calculateRemaining() {
                const amountInput = document.getElementById('amount_paid');
                const dpInput = document.getElementById('dp_amount');
                const remainingInput = document.getElementById('remaining_amount');
                
                if (amountInput && dpInput && remainingInput) {
                    const totalPrice = parseFloat(amountInput.value.replace(/[^0-9]/g, '')) || 0;
                    const dpAmount = parseFloat(dpInput.value) || 0;
                    const remaining = totalPrice - dpAmount;
                    remainingInput.value = `Rp ${remaining.toLocaleString('id-ID')}`;
                }
            }

            // Hitung sisa bayar member
            function calculateRemainingMember() {
                const amountInput = document.getElementById('amount_paid_member');
                const dpInput = document.getElementById('dp_amount_member');
                const remainingInput = document.getElementById('remaining_amount_member');
                
                if (amountInput && dpInput && remainingInput) {
                    const totalPrice = parseFloat(amountInput.value.replace(/[^0-9]/g, '')) || 0;
                    const dpAmount = parseFloat(dpInput.value) || 0;
                    const remaining = totalPrice - dpAmount;
                    remainingInput.value = `Rp ${remaining.toLocaleString('id-ID')}`;
                }
            }

            // Hitung jam mingguan member
            function calculateWeeklyHours() {
                const totalHours = parseFloat(document.getElementById('total_hours')?.value) || 0;
                maxWeeklyHours = totalHours / 4;
                const weeklySpan = document.getElementById('weekly_hours');
                if (weeklySpan) weeklySpan.textContent = maxWeeklyHours.toFixed(2);
            }
            
            // Validasi waktu member
            function validateMemberSchedule() {
                // Implementasi validasi member jika diperlukan
            }

            // === EVENT LISTENERS ===
            
            // Toggle booking type
            const bookingType = document.getElementById('booking_type');
            if (bookingType) {
                bookingType.addEventListener('change', toggleBookingType);
            }

            // Regular booking events
            const startTime = document.getElementById('start_time');
            const duration = document.getElementById('duration');
            const endTime = document.getElementById('end_time');
            const dpAmount = document.getElementById('dp_amount');
            const dpAmountMember = document.getElementById('dp_amount_member');
            const totalHours = document.getElementById('total_hours');

            if (startTime) {
                startTime.addEventListener('change', function() {
                    calculateEndTime();
                    calculateTotalPrice();
                    updateDurationOptions();
                });
                startTime.min = '07:00';
                startTime.max = '22:00';
                startTime.step = '3600';
            }

            if (duration) {
                duration.addEventListener('change', function() {
                    calculateEndTime();
                    calculateTotalPrice();
                });
            }

            if (dpAmount) {
                dpAmount.addEventListener('input', calculateRemaining);
            }

            if (dpAmountMember) {
                dpAmountMember.addEventListener('input', calculateRemainingMember);
            }

            if (totalHours) {
                totalHours.addEventListener('input', function() {
                    calculateWeeklyHours();
                    calculateMemberPrice();
                });
            }

            // Checkbox hari member
            document.querySelectorAll('.day-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function () {
                    const timeRange = this.closest('.form-check')?.querySelector('.time-range');
                    if (timeRange) {
                        timeRange.style.display = this.checked ? 'block' : 'none';
                    }
                });
            });
            
            // Set default duration jika ada start time
            setTimeout(function() {
                if (startTime?.value && duration && !duration.value) {
                    duration.value = '1';
                    calculateEndTime();
                    calculateTotalPrice();
                }
            }, 100);

            // Inisialisasi toggle booking type
            toggleBookingType();
        });
    </script>

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