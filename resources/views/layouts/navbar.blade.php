        <nav class="sticky-top container-xxl navbar-expand-xl navbar-detached bg-navbar-theme"
            id="layout-navbar">
            <div class="d-flex" id="navbar-collapse" >
                <div class="navbar-nav flex-row align-items-center">  
                    <li class="nav-item d-flex">                
                        <a class="nav-link" href="{{ route('home') }}">
                            <img src="{{ asset('assets/img/logo.png') }}" class="app-brand-logo" width="120" height="64"
                            alt=""/>
                        </a>                                   
                    </li>
                </div>
                <ul class="navbar-nav align-self-center ms-auto">
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
                        <img src="{{ asset('assets/img/free/svg/basic/bxs-menu.svg') }}" width="40" height="40">                                
                    </button>
                </ul>                
                @if(session('success'))
                    <div class="alert alert-dark">
                        {{ session('success') }}
                    </div>
                @endif
                <div class="collapse navbar-collapse" id="navbarResponsive">
                    <ul class="navbar-nav align-items-end ms-auto">                  
                        <li class="nav-item">
                            @unless(Auth::user() && Auth::user()->role === 'admin')
                                <a class="nav-link" style="color:  rgb(69, 29, 161);" href="{{ route('home') }}#home">Home</a>
                            @endunless
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" style="color:  rgb(28, 26, 26)" href="{{ route('home') }}#tentangKami">About</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" style="color:  rgb(28, 26, 26)" href="{{ route('home') }}#jadwal">Jadwal</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" style="color:  rgb(28, 26, 26)" href="{{ route('home') }}#hargaSewa">Harga Sewa</a>
                        </li>                                              
                            @auth
                                @if (in_array(Auth::user()->role, ['pelanggan', 'member']))
                                    <ul class="navbar-nav align-items-center">
                                        <li class="nav-item">
                                            <a class="nav-link" style="color:rgb(28, 26, 26);" href="{{ route('book') }}">Pemesanan</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" style="color:rgb(28, 26, 26);" href="{{ route('pindah_jadwal') }}">Pindah Jadwal</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" style="color:rgb(28, 26, 26);" href="{{ route('riwayat') }}">Riwayat</a>
                                        </li>
                                        <li class="nav-item position-relative">
                                            <a class="nav-link" href="{{ route('notif') }}" title="Notifikasi">
                                                <i class="bx bx-bell"></i>                                                
                                                <!-- Tampilkan badge jumlah notifikasi jika ada -->
                                                @php
                                                    $tomorrow = \Carbon\Carbon::tomorrow()->toDateString();
                                                    $count = auth()->user()->bookings()
                                                                        ->whereDate('booking_date', $tomorrow)
                                                                        ->count();
                                                @endphp

                                                @if($count > 0)
                                                    <span class="position-absolute top-0 start-80 translate-middle badge rounded-pill bg-danger">
                                                        {{ $count }}
                                                    </span>
                                                @endif
                                            </a>
                                        </li>
                                    </ul>
                                @elseif(Auth::user()->role === 'admin')
                                    <ul class="navbar-nav">
                                        <li class="nav-item">
                                            <a class="nav-link"  style="color:rgb(28, 26, 26);" href="{{ route('kelola_jadwal') }}">Kelola Jadwal</a>
                                        </li>                                        
                                        <li class="nav-item">
                                            <a class="nav-link position-relative"" style="color:rgb(28, 26, 26);" href="{{ route('pesanan_masuk') }}">Pesanan Masuk</a>
                                        </li>
                                        <!-- Notifikasi -->
                                        <li class="nav-item">
                                            <a href="#" class="nav-link position-relative">                                           
                                                @php
                                                    $today = \Carbon\Carbon::today()->format('Y-m-d');
                                                    $pendingBookings = \App\Models\Booking::where('status', 'approved')
                                                                                        ->whereDate('created_at', $today)
                                                                                        ->count();
                                                @endphp
                                                @if ($pendingBookings > 0)
                                                    <span class="badge rounded-pill bg-danger position-absolute top-0 end-0 translate-middle">{{ $pendingBookings }}</span>
                                                @endif
                                            </a>                                            
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" style="color:rgb(28, 26, 26);" href="{{ route('pengajuan') }}">Pengajuan</a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="#" class="nav-link position-relative">                                           
                                                @php
                                                    $pengajuan = \App\Models\Booking::whereIn('status', ['pembatalan'])->count();
                                                @endphp
                                                @if ($pengajuan > 0)
                                                    <span class="badge rounded-pill bg-danger position-absolute top-0 end-0 translate-middle">{{ $pendingBookings }}</span>
                                                @endif
                                            </a>                                            
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" style="color:rgb(28, 26, 26);" href="{{ route('laporan') }}">Laporan</a>
                                        </li>
                                    </ul>                            
                                @endif
                                <!-- Jika pengguna sudah login -->
                                <li class="nav-item navbar-dropdown dropdown-user dropdown" >
                                    <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                                        <div class="avatar avatar-online">
                                            <img src="{{ Auth()->user()->foto_profile ? asset('storage/'.Auth()->user()->foto_profile) : asset('assets/img/profile.jpg') }}" alt class="rounded-circle" width="40px" height="40px" />
                                        </div>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end" >
                                        <li>
                                            <a class="dropdown-item" href="{{route('profile')}}">
                                                <div class="d-flex">
                                                    <div class="flex-shrink-0 me-3">
                                                        <div class="avatar avatar-online">
                                                            <img src="{{ Auth()->user()->foto_profile ? asset('storage/'.Auth()->user()->foto_profile) : asset('assets/img/profile.jpg') }}" alt class="rounded-circle" />
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1" >
                                                        <span class="fw-semibold d-block">{{ Auth()->user()->name }}</span>
                                                        <small class="text-muted">{{ Auth()->user()->role }}</small>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                    
                                        <li>
                                            <div class="dropdown-divider"></div>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" id="logout-link">
                                                <i class="bx bx-power-off me-2"></i>
                                                <span class="align-middle">Log Out</span>
                                            </a>
                                        </li>
                                        
                                    </ul>
                                </li>
                            @else
                                <li class="nav-item">
                                    <a href="{{ url('/login') }}" class="nav-link" style="color:rgb(28, 26, 26);" >Login</a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ url('/register') }}" class="nav-link" style="color:rgb(28, 26, 26);">Register</a>
                                </li>
                            @endauth                                                               
                    </ul>
                </div>
            </div>
            <!-- SweetAlert2 -->
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <!-- Modal Logout -->
            <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h6 class="modal-title" id="logoutModalLabel">Konfirmasi Logout</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            Apakah Anda yakin ingin logout?
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <form action="{{ route('logout') }}" method="POST" id="logout-form">
                                @csrf
                                <button type="submit" class="btn btn-danger">Logout</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
        <script>
            // Collapse responsive navbar when toggler is visible
            const navbarToggler = document.body.querySelector('.navbar-toggler');
            const responsiveNavItems = [].slice.call(
                document.querySelectorAll('#navbarResponsive .nav-link')
            );

            // Fungsi untuk menampilkan modal saat tombol logout diklik
                document.getElementById('logout-link').addEventListener('click', function(e) {
                    e.preventDefault(); // Mencegah redirect otomatis

                    Swal.fire({
                        title: 'Anda yakin ingin logout?',
                        text: "Anda akan keluar dari akun.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Ya, logout',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById('logout-form').submit();
                        }
                    });
                });
                            
            function updateNotificationCount() {
                fetch('/get-pending-bookings')
                    .then(response => response.json())
                    .then(data => {
                        document.querySelector('.notification-badge').textContent = data.count;
                    });
            }
            
            window.onload = updateNotificationCount;
        </script>