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
    
    <div class="card h4 shadow mb-2">
            <div class="card-header row">
                <a class="card-title mt-0 mb-1 col-sm-9 text-center">
                    Halo Admin, Selamat Datang di Manajemen Pemesanan!
                </a>
                <button type="button" class="btn btn-primary1 col-sm-3" data-bs-toggle="modal" data-bs-target="#modalCreate">
                    <i class="tf-icons bx bx-plus-circle"></i>
                    Tambahkan Pemesanan
                </button>
            </div>
    </div>
    
    @if($bookings->isEmpty())
        <div class="card mb-1 mt-1">
            <div class="card-body text-center">
                <h4>Tidak Ada Pesanan Masuk</h4>
            </div>
        </div>
    @else
        <div class="card mb-1">
            <div class="card-body text-center">
                <h3>Pesanan Masuk</h3>
            </div>
        </div>
        <div class="card mb-3 mt-0 table-responsive">   
                <table class="table text-center">
                    <thead>
                        <tr>
                            <th>Nama Pemesan</th>
                            <th>Email</th>
                            <th>Tggl Pemesanan</th>
                            <th>Lapangan</th>
                            <th>Jam Sewa</th>
                            <th>Total Harga</th>
                            <th>DP</th>
                            <th>Sisa Bayar</th>                    
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bookings as $booking)
                            <tr>
                                <td>{{ $booking->user->name ?? 'Tidak Diketahui' }}</td>
                                <td>{{ $booking->user->email ?? 'Tidak Diketahui'}}</td>
                                <td>{{ $booking->user->updated_at}}</td>
                                <td>{{ $booking->field_id }}</td>
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
                                                    @if (!$loop->last),@endif 
                                                @endforeach
                                            @else
                                                <span class="text-danger">Jadwal tidak tersedia</span>
                                            @endif
                                        @else
                                            {{ $booking->booking_date}} jam {{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}
                                        @endif
                                </td>
                                <td>Rp {{ number_format($booking->amount_paid, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($booking->dp_amount, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($booking->remaining_amount, 0, ',', '.') }}</td>
                                
                                <td>
                                    @if($booking->status === 'pending')
                                        <span class="badge bg-warning">Pending</span>
                                    @elseif($booking->status === 'approved')
                                        <span class="badge bg-success">Berhasil</span>
                                    @elseif($booking->status === 'pembatalan')
                                        <span class="badge bg-danger">Mengajukan Pembatalan</span>
                                    @elseif($booking->status === 'membership')
                                        <span class="badge bg-danger">Mengajukan Perpanjangan</span>
                                    @else
                                        <span class="badge bg-secondary">{{$booking->status}}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
        </div>
    @endif
    <!-- Modal untuk create -->
    <div class="modal fade" id="modalCreate" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
                <form class="modal-content" action="{{ Route('bookings.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalCreateTitle">Buat Pemesanan Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
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
                            <!-- Waktu Mulai -->
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
                        <!-- Tombol Submit -->
                        <button type="submit" class="btn btn-primary">Pesan Sekarang</button> 
                    </div>                   
                </form>
        </div>
    </div>

    {{-- <script>
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
    
        function calculateWeeklyHours() {
            // Ambil nilai total_hours dari input
            const totalHoursInput = document.getElementById('total_hours');
            const totalHours = parseFloat(totalHoursInput.value);

            // Hitung jadwal mingguan (total_hours dibagi 4)
            const weeklyHours = totalHours ? (totalHours / 4).toFixed(2) : '0.00';

            // Update nilai jadwal mingguan di halaman
            document.getElementById('weekly_hours').textContent = weeklyHours;
        }
    
        var ratePerHour = 25000;
        var rateMember = 20000; 
        
        document.getElementById('start_time').addEventListener('change', calculateTotalPrice);
        document.getElementById('end_time').addEventListener('change', calculateTotalPrice);
        document.getElementById('dp_amount').addEventListener('input', calculateRemaining);

        // Perhitungan untuk Pemesanan Member
        document.getElementById('total_hours')?.addEventListener('input', calculateMemberPrice);

        function calculateMemberPrice() {
            const totalHours = parseFloat(document.getElementById('total_hours').value) || 0;
            const totalPrice = totalHours * rateMember;

            document.getElementById('amount_paid_member').value = `Rp ${totalPrice.toLocaleString('id-ID')}`;
        }
        
        function calculateTotalPrice() {
            const startTime = document.getElementById('start_time').value;
            const endTime = document.getElementById('end_time').value;

            if (startTime && endTime) {
                const start = new Date(`2000-01-01T${startTime}`);
                const end = new Date(`2000-01-01T${endTime}`);

                if (end > start) {
                    const durationInMinutes = (end - start) / (1000 * 60); // Selisih dalam menit
                    const durationInHours = durationInMinutes / 60; // Konversi ke jam
                    const totalPrice = durationInHours * ratePerHour;

                    document.getElementById('amount_paid').value = `Rp ${totalPrice.toLocaleString('id-ID')}`;
                }
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
    
    </script> --}}
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