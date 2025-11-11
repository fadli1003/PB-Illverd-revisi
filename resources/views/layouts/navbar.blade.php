<nav class="navbar" id="navbar">
    <div class="navbar-nav" id="navbar-collapse">
        <div class="nav-logo gradient-text">
            <a class="logo" href="{{ route('home') }}">PB Illverd</a>
        </div>
        <div class="nav-menu  items-center content-center">
            <button class="menu-toggle navbar-toggler" id="menuToggle" type="button" title="Menu">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="menu-svg">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3.75 6.75h16.5M3.75 12h16.5M12 17.25h8.25" />
                </svg>
            </button>
            <div class="block_login"></div>
            <div class="auth-div d-flex items-center">
                @auth
                    @if (in_array(Auth::user()->role, ['pelanggan', 'member']))
                        <li class="position-relative notif">
                            <a class="nav-link" href="{{ route('notif') }}" title="Notifikasi">
                                <i class="bx bx-bell"></i>
                                @php
                                    $tomorrow = \Carbon\Carbon::tomorrow()->toDateString();
                                    $count = auth()->user()->bookings()->whereDate('booking_date', $tomorrow)->count();
                                @endphp

                                @if ($count > 0)
                                    <span
                                        class="position-absolute  start-80 translate-middle badge-notif rounded-pill bg-danger">
                                        {{ $count }}
                                    </span>
                                @endif
                            </a>
                        </li>
                    @endif
                    <!-- Jika pengguna sudah login -->
                    <li class="nav-item navbar-dropdown dropdown-user dropdown">
                        <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                            <div class="avatar avatar-online">
                                <img src="{{ Auth()->user()->foto_profile ? asset('storage/' . Auth()->user()->foto_profile) : asset('assets/img/profile.jpg') }}"
                                    alt class="rounded-circle" width="35px" height="35px" title="Profile" />
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end p-6">
                            <li>
                                <a class="dropdown-item" href="{{ route('profile') }}">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0 me-3">
                                            <div class="avatar avatar-online">
                                                <img src="{{ Auth()->user()->foto_profile ? asset('storage/' . Auth()->user()->foto_profile) : asset('assets/img/profile.jpg') }}"
                                                    alt class="rounded-circle" />
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 profile-info">
                                            <span class="fw-semibold d-block mb-1">{{ Auth()->user()->name }}</span>
                                            <small class="text-muted">{{ Auth()->user()->role }}</small>
                                        </div>
                                    </div>
                                </a>
                            </li>

                            <li>
                                <div class="dropdown-divider mb-2"></div>
                            </li>
                            <li class="link-logout mb-2">
                                <button class="logout-btn" type="submit" onclick="bukaModalLogout()"  id="logout-link2">
                                    <i class="bx bx-power-off me-2"></i>
                                    <span class="align-middle">Log Out</span>
                                </button>
                            </li>
                        </ul>
                    </li>
                @else
                    <li class="nav-item">
                        <a href="{{ url('/login') }}" class="nav-link"><i class="bx bx-user"></i> Login<span class="border-b"></span></a>
                    </li>
                @endauth
            </div>
        </div>
        @if (session('success'))
            <div class="alert alert-dark">
                {{ session('success') }}
            </div>
        @endif
        <div class="collapse navbar-collapse" id="navbarResponsiv">
            <ul class="nav-ul " style="font-weight:500">
                <li class="li-cus">
                    @unless (Auth::user() && Auth::user()->role === 'admin')
                        <a class="nav-link" href="{{ route('home') }}#home" data-section="home">Home<span class="border-b"></span></a>
                    @endunless
                </li>
                {{-- <li class="li-cus">
                    <a class="nav-link" href="{{ route('home') }}#tentangKami">About<span class="border-b"></span></a>
                </li> --}}
                <li class="li-cus">
                    <a class="nav-link" href="{{ route('home') }}#jadwal">Jadwal<span class="border-b"></span></a>
                </li>
                <li class="li-cus">
                    <a class="nav-link" href="{{ route('home') }}#hargaSewa">Pricelist<span class="border-b"></span></a>
                </li>
                @auth
                    @if (in_array(Auth::user()->role, ['pelanggan', 'member']))
                        <li class="li-cus">
                            <a class="nav-link {{ request()->routeIs('book') ? 'border-b-active' : '' }}" href="{{ route('book') }}">Pemesanan<span class="border-b"></span></a>
                        </li>
                        <li class="li-cus">
                            <a class="nav-link {{ request()->routeIs('pindah_jadwal') ? 'border-b-active' : '' }}" href="{{ route('pindah_jadwal') }}">Pindah<span class="border-b"></span></a>
                        </li>
                        <li class="li-cus">
                            <a class="nav-link {{ request()->routeIs('riwayat') ? 'border-b-active' : '' }}" href="{{ route('riwayat') }}">Riwayat<span class="border-b"></span></a>
                        </li>
                        <li class="position-relative">
                            <a class="nav-link {{ request()->routeIs('notif') ? 'active-page' : '' }}" href="{{ route('notif') }}" title="Notifikasi">
                                <i class="bx bx-bell"></i>
                                @php
                                    $tomorrow = \Carbon\Carbon::tomorrow()->toDateString();
                                    $count = auth()->user()->bookings()->whereDate('booking_date', $tomorrow)->count();
                                @endphp

                                @if ($count > 0)
                                    <span
                                        class="position-absolute start-80 translate-middle badge rounded-pill bg-danger">
                                        {{ $count }}
                                    </span>
                                @endif
                            </a>
                        </li>
                    @elseif(Auth::user()->role === 'admin')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('kelola_jadwal') ? 'border-b-active' : '' }}" href="{{ route('kelola_jadwal') }}">Kelola Jadwal<span class="border-b"></span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link position-relative {{ request()->routeIs('pesanan_masuk') ? 'border-b-active' : '' }}" href="{{ route('pesanan_masuk') }}">Pesanan Masuk<span class="border-b"></span>
                                @php
                                    $today = \Carbon\Carbon::today()->format('Y-m-d');
                                    $pendingBookings = \App\Models\Booking::where('status', 'approved')
                                        ->whereDate('created_at', $today)
                                        ->count();
                                @endphp
                                @if ($pendingBookings > 0)
                                    <span
                                        class="badge-notif rounded-pill bg-danger position-absolute  end-0 translate-middle">{{ $pendingBookings }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('pengajuan') ? 'border-b-active' : '' }}" href="{{ route('pengajuan') }}">Pengajuan<span class="border-b"></span>
                                @php
                                    $pengajuan = \App\Models\Booking::whereIn('status', ['pembatalan'])->count();
                                @endphp
                                @if ($pengajuan > 0)
                                    <span
                                        class="badge-notif rounded-pill bg-danger position-absolute end-0 translate-middle">{{ $pengajuan }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('laporan') ? 'border-b-active' : '' }}" href="{{ route('laporan') }}">Laporan<span class="border-b"></span></a>
                        </li>
                    @endif
                    <!-- Jika pengguna sudah login -->
                    <li class="nav-item navbar-dropdown dropdown-user dropdown">
                        <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);"
                            data-bs-toggle="dropdown">
                            <div class="avatar avatar-online">
                                <img src="{{ Auth()->user()->foto_profile ? asset('storage/' . Auth()->user()->foto_profile) : asset('assets/img/profile.jpg') }}"
                                    alt class="rounded-circle" width="35px" height="35px" />
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end p-3">
                            <li>
                                <a class="dropdown-item" href="{{ route('profile') }}">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0 me-3">
                                            <div class="avatar avatar-online">
                                                <img src="{{ Auth()->user()->foto_profile ? asset('storage/' . Auth()->user()->foto_profile) : asset('assets/img/profile.jpg') }}"
                                                    alt class="rounded-circle" />
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 profile-info">
                                            <span class="fw-semibold d-block mb-1">{{ Auth()->user()->name }}</span>
                                            <small class="text-muted">{{ Auth()->user()->role }}</small>
                                        </div>
                                    </div>
                                </a>
                            </li>

                            <li>
                                <div class="dropdown-divider mb-2"></div>
                            </li>
                            <li class="link-logout mb-3">                               
                                <button class="logout-btn" type="submit" onclick="bukaModalLogout()" id="logout-link2">
                                    <i class="bx bx-power-off me-2"></i>
                                    <span class="align-middle">Log Out</span>
                                </button>                                
                            </li>
                        </ul>
                    </li>
                @else
                    <div class="block_login"></div>
                    <li class="nav-item">
                        <a href="{{ url('/login') }}" class="nav-link"><i class="bx bx-user"></i> Login<span class="border-b"></span></a>
                    </li>
                @endauth
            </ul>

        </div>

        <div class="nav-overlay hidden slide-bot" id="navbarOverlay">
            <ul class="nav-ul mt-0" style="font-weight:500">
                <li class="nav-link-sm">
                    @unless (Auth::user() && Auth::user()->role === 'admin')
                        <a class="nav-link" href="{{ route('home') }}#home">Home<span class="border-b"></span></a>
                    @endunless
                </li>
                <li class="nav-link-sm">
                    <a class="nav-link" href="{{ route('home') }}#jadwal">Jadwal<span class="border-b"></span></a>
                </li>
                <li class="nav-link-sm">
                    <a class="nav-link" href="{{ route('home') }}#hargaSewa">Pricelist<span class="border-b"></span></a>
                </li>
                @auth
                    @if (in_array(Auth::user()->role, ['pelanggan', 'member']))
                        <li class="nav-link-sm">
                            <a class="nav-link {{ request()->routeIs('book') ? 'border-b-active' : '' }}" href="{{ route('book') }}">Pemesanan<span class="border-b"></span></a>
                        </li>
                        <li class="nav-link-sm">
                            <a class="nav-link {{ request()->routeIs('pindah_jadwal') ? 'border-b-active' : '' }}" href="{{ route('pindah_jadwal') }}">Pindah<span class="border-b"></span></a>
                        </li>
                        <li class="nav-link-sm">
                            <a class="nav-link {{ request()->routeIs('riwayat') ? 'border-b-active' : '' }}" href="{{ route('riwayat') }}">Riwayat<span class="border-b"></span></a>
                        </li>
                    @elseif(Auth::user()->role === 'admin')
                        <li class="nav-link-sm">
                            <a class="nav-link {{ request()->routeIs('kelola_jadwal') ? 'border-b-active' : '' }}" href="{{ route('kelola_jadwal') }}">Kelola Jadwal<span class="border-b"></span></a>
                        </li>
                        <li class="nav-link-sm">
                            <a class="nav-link position-relative {{ request()->routeIs('pesanan_masuk') ? 'border-b-active' : '' }}" href="{{ route('pesanan_masuk') }}">Pesanan
                                Masuk<span class="border-b"></span>
                                @php
                                    $today = \Carbon\Carbon::today()->format('Y-m-d');
                                    $pendingBookings = \App\Models\Booking::where('status', 'approved')
                                        ->whereDate('created_at', $today)
                                        ->count();
                                @endphp
                                @if ($pendingBookings > 0)
                                    <span
                                    class="badge-notif rounded-pill bg-danger position-absolute  end-0 translate-middle badge-notif">
                                    {{ $pendingBookings }}</span>
                                @endif
                            </a>
                        </li>

                        <li class="nav-link-sm">
                            <a class="nav-link {{ request()->routeIs('pengajuan') ? 'border-b-active' : '' }}" href="{{ route('pengajuan') }}">Pengajuan<span class="border-b"></span>
                                @php
                                    $pengajuan = \App\Models\Booking::whereIn('status', ['pembatalan'])->count();
                                @endphp
                                @if ($pengajuan > 0)
                                    <span
                                    class="badge-notif rounded-pill bg-danger position-absolute translate-middle">
                                    {{ $pengajuan }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-link-sm">
                            <a class="nav-link {{ request()->routeIs('laporan') ? 'border-b-active' : '' }}" href="{{ route('laporan') }}">Laporan<span class="border-b"></span></a>
                        </li>
                    @endif
                @endauth
                <span class="btn-close" id="close">
                    X
                </span>
            </ul>

        </div>
    </div>
    <div id="logoutModal" class="hidden content-center justify-center">
        <div class="">
            <h3 class="mb-2">Konfirmasi Logout</h3>
            <p class="mb-4">Apakah Anda yakin ingin keluar?</p>
            <div class="d-flex">
                <button id="cancelLogout" class="px-4 py-2">
                    Batal
                </button>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button id="confirmLogout" class="px-4 py-2">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
    
</nav>

<script>
    document.getElementById('menuToggle').addEventListener('click', (e) => {
        e.stopPropagation(); // mencegah klik menutup overlay saat membuka
        document.getElementById('navbarOverlay').classList.toggle('hidden');
    });
    document.getElementById('close').addEventListener('click', () => {
        document.getElementById('navbarOverlay').classList.add('hidden');
    });

    const navMenu = document.getElementById('navbarOverlay');
    const menu = document.getElementById('menuToggle');
        document.addEventListener('click', (e) => {
            if (!navMenu.contains(e.target) && e.target !== menu) {
                navMenu.classList.add('hidden');
            }
        });

    function bukaModalLogout() {
        document.getElementById('logoutModal').classList.remove('hidden');
    }
    function tutupModalLogout() {
        document.getElementById('logoutModal').classList.add('hidden');
    }

    document.getElementById('cancelLogout').addEventListener('click', tutupModalLogout);
    document.getElementById('confirmLogout').addEventListener('click', () => {
    // Contoh redirect (jika pakai client-side routing seperti React Router):
    // window.location.href = '/login';

    // Jika pakai Laravel + session:
    // window.location.href = '/logout';
    tutupModalLogout();
    });
    document.getElementById('logoutModal').addEventListener('click', (e) => {
    if (e.target.id === 'logoutModal') {
        tutupModalLogout();
    }
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
