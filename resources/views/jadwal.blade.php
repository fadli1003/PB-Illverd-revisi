
        <div class="table-responsive text-wrap card-body">
            <h4 class="text-center">Jadwal untuk Tanggal {{ \Carbon\Carbon::parse($selectedDate)->format('d M Y') }}</h4>
            <table class="table">
                <thead>
                    <tr>
                        <th class="col-4 text-center" style="font-size: 14px">Waktu</th>
                        @foreach ($fields as $field)
                            <th style="text-align: center; font-size: 14px">{{ $field->name }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @foreach ($timeSlots as $time)
                        <tr class="text-center">
                            <td>{{ substr($time, 0, 5) }}</td>
                            @foreach ($fields as $field)
                                <td>
                                    @php                                        
                                        $slotDateTime = \Carbon\Carbon::parse($selectedDate . ' ' . $time);
                                        $now = \Carbon\Carbon::now();
                                        $isSlotPassed = $slotDateTime < $now;
                                                                          
                                        // Cek pemesanan regular
                                        $regularBookings = $filteredBookings
                                            ->where('field_id', $field->id)
                                            ->where('booking_type', 'regular')
                                            ->where('start_time', '<=', $time)
                                            ->where('end_time', '>', $time);
                    
                                        // Cek pemesanan member
                                        $memberBookings = $filteredBookings
                                            ->where('field_id', $field->id)
                                            ->where('booking_type', 'member')
                                            ->where('valid_until', '>=', $selectedDate) // Pastikan dalam rentang aktif
                                            ->filter(function ($booking) use ($selectedDate, $time) {
                                                if (isset($booking->schedule_details)) {
                                                    $dayOfWeek = \Carbon\Carbon::parse($selectedDate)->format('l'); // Hari dalam bahasa Inggris
                                                    foreach ($booking->schedule_details as $day => $timeRange) {
                                                        if ($day === $dayOfWeek && $time >= $timeRange['start'] && $time < $timeRange['end']) {
                                                            return true;
                                                        }
                                                    }
                                                }
                                                return false;
                                            });                                        
                                    @endphp
                    
                                    @if ($isSlotPassed)
                                        <!-- Slot waktu sudah lewat -->
                                        <div class="unavailable">
                                            <span class="badge bg-label-secondary" style="pointer-events: none;">Tidak Tersedia</span>
                                        </div>
                                    @elseif ($regularBookings->isEmpty() && $memberBookings->isEmpty())
                                        <!-- Slot waktu tersedia -->
                                        <div class="available">
                                            <a class="badge bg-label-primary" href="{{ route('bookings.create', [
                                                'field_id' => $field->id,
                                                'booking_date' => $selectedDate,
                                                'start_time' => substr($time, 0, 5),
                                                'end_time' => \Carbon\Carbon::createFromFormat('H:i:s', $time)->addHour()->format('H:i')
                                            ]) }}">Pesan</a>
                                        </div>                                    
                                    @else
                                        <!-- Slot waktu sudah dipesan -->
                                        <div class="booked">
                                            @if (!$regularBookings->isEmpty())
                                                <span class="badge bg-label-warning">Dipesan </span>
                                            @endif
                                            @if (!$memberBookings->isEmpty())
                                                <span class="badge bg-label-warning">Dipesan (member)</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    
    <div class="card">
        <div class="table-responsive text-wrap card-body">
            <h3 class="text-center">Jadwal Member</h3>
            <table class="table text-center">
                <thead>
                    <tr>
                        <th>Hari</th>
                        <th>Waktu Mulai</th>
                        <th>Waktu Selesai</th>
                        <th>Berlaku Hingga</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($filteredBookings as $booking)
                        @if ($booking->booking_type === 'member' && isset($booking->schedule_details))
                            @foreach ($booking->schedule_details as $day => $timeRange)
                                <tr>
                                    <td>{{ $day }}</td>
                                    <td>{{ $timeRange['start'] }}</td>
                                    <td>{{ $timeRange['end'] }}</td>
                                    <td>{{ \Carbon\Carbon::parse($booking->valid_until)->format('d M Y') }}</td>
                                </tr>
                            @endforeach
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <script>
        document.getElementById('date-form').addEventListener('submit', function (e) {
            e.preventDefault(); // Mencegah form submit default

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
