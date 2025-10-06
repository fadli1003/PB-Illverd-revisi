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
                            <!-- Waktu Mulai -->
                            <div class="mb-3">
                                <label for="start_time" class="form-label">Waktu Mulai</label>
                                <input type="time" class="form-control" id="start_time" name="start_time"
                                    value="{{ old('start_time', $start_time ?? '') }}" >
                            </div>
                            <!-- Waktu Selesai -->
                            <div class="mb-3">
                                <label for="end_time" class="form-label">Waktu Selesai</label>
                                <input type="time" class="form-control" id="end_time" name="end_time"
                                    value="{{ old('end_time', $end_time ?? '') }}" >
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
                                    placeholder="Contoh: 12 jam" min="12" oninput="calculateWeeklyHours()">
                            </div>
                            <!-- Jadwal Mingguan -->
                            <div class="mb-3">
                                <label class="form-label">Jadwal Mingguan: <span id="weekly_hours">0.00</span> jam per minggu</label>
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
    
    </script>
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