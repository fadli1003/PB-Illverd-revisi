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
                                    value="{{ old('start_time') }}" 
                                    min="07:00" max="23:00" step="3600">
                            </div>

                            <!-- Durasi Sewa -->
                            <div class="mb-3">
                                <label for="duration" class="form-label">Durasi Sewa (jam)</label>
                                <select id="duration" name="duration" class="form-control" >
                                    <option value="">-- Pilih Durasi --</option>
                                    <option value="1">1 Jam</option>
                                    <option value="2">2 Jam</option>
                                    <option value="3">3 Jam</option>
                                    <option value="4">4 Jam</option>
                                    <option value="5">5 Jam</option>
                                    <option value="6">6 Jam</option>
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
                            </div>
                        </div>
                        <!-- Formulir Tambahan untuk Member -->
                        <div id="member_booking" class="booking-form" style="display: none;">
                            <!-- Total Jam Sewa (per Bulan) -->
                            <div class="mb-3">
                                <label for="total_hours" class="form-label">Total Jam Sewa (per Bulan):</label>
                                <input type="number" class="form-control" id="total_hours" name="total_hours" 
                                    placeholder="Contoh: 12 jam" min="12" step="1">
                                <small class="text-muted">Minimal 12 jam per bulan</small>
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
                        <!-- Dropdown No. Rek -->
                        <div class="mb-3" id="rek-container">
                            <label for="bank_account" class="form-label">Nomor Rekening</label>
                            <select id="bank_account" name="bank_account" class="form-select">
                                <option value="">-- Pilih Rekening --</option>
                                <option value="1234567890">BCA - 1234567890</option>
                                <option value="9876543210">BRI - 9876543210</option>
                                <option value="0987654321">Mandiri - 0987654321</option>
                            </select>
                        </div>
                        <!-- Upload Bukti Transfer -->
                        <div class="mb-3">
                            <label for="proof_of_payment" class="form-label">Upload Bukti Transfer</label>
                            <input type="file" class="form-control" id="proof_of_payment" name="proof_of_payment" accept="image/*">
                        </div>
                        <!-- Tombol Submit -->
                        <button type="submit" id="pesanSekarang" class="btn btn-primary">Pesan Sekarang</button>                
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
        document.getElementById('booking_type').addEventListener('change', function () {
            const regularBooking = document.getElementById('regular_booking');
            const memberBooking = document.getElementById('member_booking');

            if (this.value === 'member') {
                regularBooking.style.display = 'none';
                memberBooking.style.display = 'block';

                // Hapus atribut required dari input total_hours
                document.getElementById('total_hours').removeAttribute('required');
            } else {
                regularBooking.style.display = 'block';
                memberBooking.style.display = 'none';

                // Hapus atribut required dari input total_hours
                document.getElementById('total_hours').removeAttribute('required');
            }
        });
    
        document.getElementById('start_time').addEventListener('change', calculateEndTime);
        document.getElementById('duration').addEventListener('change', calculateEndTime);
        document.getElementById('start_time').addEventListener('change', updateDurationOptions);

        function calculateEndTime() {
            const startTime = document.getElementById('start_time').value;
            const duration = parseInt(document.getElementById('duration').value);

            if (!startTime || !duration) return;

            const [hours] = startTime.split(':');
            let endHour = parseInt(hours) + duration;
            endHour--;

            if (endHour > 22) {
                endHour = 22;
            }

            const endTime = `${endHour.toString().padStart(2, '0')}:59`;
            document.getElementById('end_time').value = endTime;
        }

        function updateDurationOptions() {
            const startTime = document.getElementById('start_time').value;
            const durationSelect = document.getElementById('duration');

            if (!startTime) {
                // Reset semua opsi jika tidak ada jam mulai
                Array.from(durationSelect.options).forEach(option => {
                    option.disabled = false;
                });
                return;
            }

            // Ambil jam dari waktu mulai
            const [hours] = startTime.split(':');
            const startHour = parseInt(hours);

            // Hitung durasi maksimal yang masih aman (maksimal waktu selesai: 22:59)
            const maxEndHour = 23; // Waktu selesai maksimal
            const maxDuration = maxEndHour - startHour;

            // Nonaktifkan opsi durasi yang melebihi batas
            Array.from(durationSelect.options).forEach(option => {
                const duration = parseInt(option.value);
                if (duration > maxDuration) {
                    option.disabled = true;
                } else {
                    option.disabled = false;
                }
            });

            // Jika durasi saat ini tidak valid, reset ke nilai terkecil yang valid
            const currentDuration = parseInt(document.getElementById('duration').value);
            if (currentDuration > maxDuration) {
                document.getElementById('duration').value = maxDuration || 1;
            }

            // Hitung waktu selesai setelah mengubah durasi
            calculateEndTime();
        }

        window.onload = function () {
            const startTime = document.getElementById('start_time').value;
            const duration = document.getElementById('duration').value;
            if (startTime && duration) {
                calculateEndTime();
            }
        };
    
        
        var rateMember = 20000; 
        var ratePerHour = 25000;

        document.getElementById('start_time').addEventListener('change', calculateTotalPrice);
        document.getElementById('end_time').addEventListener('change', calculateTotalPrice);
        document.getElementById('dp_amount').addEventListener('input', calculateRemaining);
        document.getElementById('duration').addEventListener('change', calculateTotalPrice);
        // Perhitungan untuk Pemesanan Member
        document.getElementById('total_hours')?.addEventListener('input', calculateMemberPrice);

        function calculateMemberPrice() {
            const totalHours = parseFloat(document.getElementById('total_hours').value) || 0;
            const totalPrice = totalHours * rateMember;

            document.getElementById('amount_paid_member').value = `Rp ${totalPrice.toLocaleString('id-ID')}`;
        }
        
        function calculateTotalPrice() {
            const duration = parseInt(document.getElementById('duration').value) || 0;
            
            
            if (duration > 0) {
                const totalPrice = duration * ratePerHour;
                document.getElementById('amount_paid').value = `Rp ${totalPrice.toLocaleString('id-ID')}`;
            } else {
                document.getElementById('amount_paid').value = '';
            }
        
        }

        // Hitung sisa bayar berdasarkan DP
        document.getElementById('dp_amount')?.addEventListener('input', calculateRemaining);
        document.getElementById('dp_amount_member')?.addEventListener('input', calculateRemainingMember);

        function calculateRemaining() {
            const totalPrice = parseFloat(document.getElementById('amount_paid').value.replace(/[^0-9]/g, '')) || 0;
            const dpAmount = parseFloat(document.getElementById('dp_amount').value) || 0;
            const remainingAmount = totalPrice - dpAmount;

            document.getElementById('remaining_amount').value = `Rp ${remainingAmount.toLocaleString('id-ID')}`;
        }

        function calculateRemainingMember() {
            const totalPrice = parseFloat(document.getElementById('amount_paid_member').value.replace(/[^0-9]/g, '')) || 0;
            const dpAmountMember = parseFloat(document.getElementById('dp_amount_member').value) || 0;
            const remainingAmountMember = totalPrice - dpAmountMember;

            document.getElementById('remaining_amount_member').value = `Rp ${remainingAmountMember.toLocaleString('id-ID')}`;
        }
        // Hitung total harga saat halaman dimuat
        window.onload = function () {
            if (document.getElementById('start_time')?.value && document.getElementById('end_time')?.value) {
                calculateTotalPrice();
            }            
            if (document.getElementById('dp_amount')?.value) {
                calculateRemaining();
            }            
        };        
    
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
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const startTimeInput = document.getElementById('start_time');
            const pesanSekarangButton = document.getElementById('pesanSekarang'); // atau sesuaikan ID tombolnya
            
            if (startTimeInput) {
                // Set atribut batasan
                startTimeInput.min = '07:00';
                startTimeInput.max = '23:00';
                startTimeInput.step = '3600';
            }
            
            // Validasi saat tombol pesan sekarang diklik
            if (pesanSekarangButton && startTimeInput) {
                pesanSekarangButton.addEventListener('click', function(e) {
                    const timeValue = startTimeInput.value;
                    
                    // Jika waktu belum diisi
                    if (!timeValue) {
                        e.preventDefault();
                        alert('Harap pilih waktu mulai');
                        startTimeInput.focus();
                        return false;
                    }
                    
                    // Validasi range waktu
                    const [hours] = timeValue.split(':').map(Number);
                    if (hours < 7 || hours > 23) {
                        e.preventDefault();
                        alert('Waktu harus antara 07:00-23:00');
                        startTimeInput.focus();
                        return false;
                    }
                    
                    // Jika valid, form akan submit secara normal
                });
            }
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
    {{-- <script>
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
                
                // Reset semua input waktu
                document.querySelectorAll('.time-range').forEach(div => {
                    div.style.display = 'none';
                });
                document.querySelectorAll('.day-checkbox').forEach(cb => {
                    cb.checked = false;
                });
                document.querySelectorAll('input[type="time"]').forEach(input => {
                    input.value = '';
                });
                
                updateCurrentWeeklyHours();
            }

            // Fungsi untuk membatasi input waktu ke kelipatan 1 jam
            function restrictToHourSteps(input) {
                input.addEventListener('input', function() {
                    let time = this.value;
                    if (time) {
                        let [hours] = time.split(':');
                        this.value = `${hours}:00`;
                    }
                });
            }

            // // Fungsi untuk menghitung total jam mingguan saat ini
            // function getCurrentWeeklyHours() {
            //     let totalMinutes = 0;

            //     checkboxes.forEach(checkbox => {
            //         if (checkbox.checked) {
            //             const day = checkbox.value;
            //             const timeRangeDiv = checkbox.closest('.form-check').querySelector('.time-range');
            //             const startInput = timeRangeDiv.querySelector('input[name$="[start]"]');
            //             const endInput = timeRangeDiv.querySelector('input[name$="[end]"]');

            //             const start = startInput.value;
            //             const end = endInput.value;

            //             if (start && end) {
            //                 const [startHour] = start.split(':').map(Number);
            //                 const [endHour] = end.split(':').map(Number);

            //                 if (endHour > startHour) {
            //                     totalMinutes += (endHour - startHour) * 60;
            //                 }
            //             }
            //         }
            //     });

            //     return totalMinutes / 60;
            // }

            // Fungsi untuk memperbarui tampilan jam mingguan dan membatasi input
            function updateCurrentWeeklyHours() {
                const currentHours = getCurrentWeeklyHours();
                const remainingHours = maxWeeklyHours - currentHours;
                
                // Update tampilan
                document.getElementById('current_weekly_hours').textContent = currentHours.toFixed(2);
                document.getElementById('remaining_weekly_hours').textContent = remainingHours.toFixed(2);

                // Batasi checkbox jika sudah mencapai batas
                const allCheckboxes = document.querySelectorAll('.day-checkbox');
                allCheckboxes.forEach(checkbox => {
                    const timeRangeDiv = checkbox.closest('.form-check').querySelector('.time-range');
                    const startInput = timeRangeDiv ? timeRangeDiv.querySelector('input[name$="[start]"]') : null;
                    const endInput = timeRangeDiv ? timeRangeDiv.querySelector('input[name$="[end]"]') : null;

                    if (!checkbox.checked) {
                        // Jika belum dicentang, cek apakah masih ada sisa jam
                        if (remainingHours < 1) {
                            checkbox.disabled = true;
                        } else {
                            checkbox.disabled = false;
                        }
                    } else {
                        // Jika sudah dicentang, aktifkan input
                        if (startInput && endInput) {
                            startInput.disabled = false;
                            endInput.disabled = false;
                        }
                    }
                });
            }

            // Event listener untuk total jam bulanan
            totalHoursInput.addEventListener('input', calculateWeeklyHours);

            // Setup event listener untuk setiap checkbox hari
            checkboxes.forEach(checkbox => {
                const day = checkbox.value;
                const timeRangeDiv = checkbox.closest('.form-check').querySelector('.time-range');
                const startInput = timeRangeDiv ? timeRangeDiv.querySelector('input[name$="[start]"]') : null;
                const endInput = timeRangeDiv ? timeRangeDiv.querySelector('input[name$="[end]"]') : null;

                // Terapkan pembatasan kelipatan 1 jam
                if (startInput) {
                    restrictToHourSteps(startInput);
                    startInput.min = '07:00';
                    startInput.max = '23:00';
                }
                if (endInput) {
                    restrictToHourSteps(endInput);
                    endInput.min = '07:00';
                    endInput.max = '23:00';
                }

                // Toggle waktu input saat checkbox di klik
                checkbox.addEventListener('change', function () {
                    if (timeRangeDiv) {
                        timeRangeDiv.style.display = this.checked ? 'block' : 'none';
                        if (!this.checked) {
                            if (startInput) startInput.value = '';
                            if (endInput) endInput.value = '';
                        }
                    }
                    updateCurrentWeeklyHours();
                });

                // Batasi waktu selesai berdasarkan waktu mulai
                if (startInput) {
                    startInput.addEventListener('change', function () {
                        const start = this.value;
                        if (start && endInput) {
                            const [startHour] = start.split(':').map(Number);
                            const maxEndHour = Math.min(23, startHour + Math.floor(maxWeeklyHours));
                            
                            endInput.min = start;
                            endInput.max = `${String(maxEndHour).padStart(2, '0')}:00`;
                            
                            // Reset end time jika tidak valid
                            if (endInput.value && endInput.value <= start) {
                                endInput.value = '';
                            }
                        }
                        updateCurrentWeeklyHours();
                    });
                }

                // Update total jam saat waktu selesai berubah
                if (endInput) {
                    endInput.addEventListener('change', function() {
                        updateCurrentWeeklyHours();
                    });
                }
            });

            // Tambahkan elemen untuk menampilkan informasi jam
            const weeklyLabel = document.querySelector('label[for="weekly_hours"]').parentElement;
            weeklyLabel.innerHTML += `
                <div class="mt-2">
                    <small class="text-muted">
                        Jam terpakai: <span id="current_weekly_hours">0.00</span> | 
                        Sisa jam: <span id="remaining_weekly_hours">${maxWeeklyHours.toFixed(2)}</span>
                    </small>
                </div>
            `;
        });
    </script> --}}
    {{-- <script>
        document.addEventListener('DOMContentLoaded', function () {
            const checkboxes = document.querySelectorAll('.day-checkbox');
            const weeklyHoursSpan = document.getElementById('weekly_hours');

            // Fungsi untuk membatasi waktu dalam kelipatan 1 jam
            function restrictToHourSteps(input) {
                input.addEventListener('input', function() {
                    let time = this.value;
                    if (time) {
                        let [hours, minutes] = time.split(':');
                        minutes = '00'; // Selalu set ke 00 untuk kelipatan 1 jam
                        this.value = `${hours}:${minutes}`;
                    }
                });
            }

            checkboxes.forEach(checkbox => {
                const day = checkbox.value;
                const timeRangeDiv = checkbox.closest('.form-check').querySelector('.time-range');
                const startTimeInput = timeRangeDiv.querySelector('.start-time');
                const endTimeInput = timeRangeDiv.querySelector('.end-time');

                // Terapkan pembatasan kelipatan 1 jam
                restrictToHourSteps(startTimeInput);
                restrictToHourSteps(endTimeInput);

                // Toggle waktu input saat checkbox di klik
                checkbox.addEventListener('change', function () {
                    timeRangeDiv.style.display = this.checked ? 'block' : 'none';
                    if (!this.checked) {
                        startTimeInput.value = '';
                        endTimeInput.value = '';
                        updateWeeklyHours();
                    }
                });

                // Batasi waktu selesai berdasarkan waktu mulai
                startTimeInput.addEventListener('change', function () {
                    const start = this.value;
                    if (start) {
                        const [startHour] = start.split(':').map(Number);
                        const maxEndHour = Math.min(23, startHour + 16); // maksimal 16 jam
                        
                        // Set min dan max untuk end time
                        endTimeInput.min = start;
                        endTimeInput.max = `${String(maxEndHour).padStart(2, '0')}:00`;
                        
                        // Reset end time jika tidak valid
                        if (endTimeInput.value && endTimeInput.value <= start) {
                            endTimeInput.value = '';
                        }
                    } else {
                        endTimeInput.min = '07:00';
                        endTimeInput.max = '23:00';
                    }
                    updateWeeklyHours();
                });

                // Update total jam saat waktu selesai berubah
                endTimeInput.addEventListener('change', updateWeeklyHours);
            });

            function updateWeeklyHours() {
                let totalMinutes = 0;

                checkboxes.forEach(checkbox => {
                    if (checkbox.checked) {
                        const day = checkbox.value;
                        const timeRangeDiv = checkbox.closest('.form-check').querySelector('.time-range');
                        const startInput = timeRangeDiv.querySelector('.start-time');
                        const endInput = timeRangeDiv.querySelector('.end-time');

                        const start = startInput.value;
                        const end = endInput.value;

                        if (start && end) {
                            const [startHour, startMin] = start.split(':').map(Number);
                            const [endHour, endMin] = end.split(':').map(Number);

                            const startDate = new Date(0, 0, 0, startHour, startMin);
                            const endDate = new Date(0, 0, 0, endHour, endMin);

                            if (endDate > startDate) {
                                const diffMs = endDate - startDate;
                                totalMinutes += diffMs / (1000 * 60);
                            }
                        }
                    }
                });

                const totalHours = (totalMinutes / 60).toFixed(2);
                weeklyHoursSpan.textContent = totalHours;
            }

            // Inisialisasi batasan waktu awal
            document.querySelectorAll('input[type="time"]').forEach(input => {
                input.min = '07:00';
                input.max = '23:00';
            });
        });
    </script> --}}
    <script>
        document.getElementById('date-form').addEventListener('submit', function (e) {
            e.preventDefault();

            let date = document.getElementById('date').value;

            fetch('/get-jadwal?date=' + date)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('jadwal-container').innerHTML = html;
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        });        
    </script>
@endsection