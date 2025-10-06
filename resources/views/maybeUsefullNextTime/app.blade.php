<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PB Illverd</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        
        .navbar-brand, .nav-link {
            color: rgb(248, 243, 243) !important;
        }
        .login-btn {
            margin-right: auto;
        }
        /* Styling navbar, menu, dan submenu */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: right;
            background-color: #000000;
            max-height: 70px;
            color: rgba(255, 255, 255, 0.395);
            padding: 10px 20px;
            font-family: Arial, sans-serif;
        }
        .menu {
            list-style: none;
            display: flex;
            align-content: center;
            gap: 20px;
        }

        .menu a {
            text-decoration: none;
            color: white;
            padding: 10px 15px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .menu a:hover {
            background-color: #555;
            border-radius: 5px;
        }

        .submenu {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background-color: #444;
            list-style: none;
            padding: 10px 0;
            border-radius: 5px;
            min-width: 150px;
            opacity: 0;
            transform: translateY(-10px);
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .submenu a {
            display: block;
            padding: 10px 20px;
            color: white;
        }

        .submenu a:hover {
            background-color: #666;
        }

        .dropdown:hover .submenu {
            display: block;
        }
        
    </style>    
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg ">
                <div class="collapse navbar-collapse" id="navbarNav">
                    <div class="menu">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('home') }}">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('tentangKami') }}">Tentang Kami</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('jadwal') }}">Jadwal</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('pricelist') }}">Harga Sewa</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('lokasi') }}">Lokasi</a>
                        </li>
                            
                    </div>
                    <div class="menu">
                        @auth
                            @if (in_array(Auth::user()->role, ['pelanggan', 'member']))
                                <div class="menu">
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('book') }}">Pemesanan</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('pindah_jadwal') }}">Pindah Jadwal</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('riwayat') }}">Riwayat</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('profile') }}">Profile</a>
                                    </li>
                                </div>
                            @elseif(Auth::user()->role === 'admin')
                                <ul class="navbar-nav">
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('kelola_jadwal') }}">Kelola Jadwal</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('pesanan_masuk') }}">Pesanan Masuk</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('pengajuan') }}">Pengajuan</a>
                                    </li>
                                </ul>                            
                            @endif
                            <!-- Jika pengguna sudah login -->
                            <li class="nav-item navbar-dropdown dropdown-user dropdown" >
                                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                                    <div class="avatar avatar-online">
                                        <img src="{{ asset('assets/img/avatars/1.png') }}" alt class="rounded-circle" width="40px" height="40px" />
                                    </div>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" >
                                    <li>
                                        <a class="dropdown-item" href="#">
                                            <div class="d-flex">
                                                <div class="flex-shrink-0 me-3">
                                                    <div class="avatar avatar-online">
                                                        <img src="{{ asset('assets/img/avatars/1.png') }}" alt class="rounded-circle" />
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
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
                                        <form action="{{ route('logout') }}" method="post">
                                            @csrf
                                            <button class="dropdown-item" type="submit">
                                                <i class="bx bx-power-off me-2"></i>
                                                <span class="align-middle">Log Out</span>
                                            </button>
                                        </form>
                                    </li>
                                    
                                </ul>
                            </li>
                        @else
                            <!-- Jika pengguna belum login -->
                            <a href="{{ url('/login') }}" class="btn btn-outline-light me-2" >Login</a>
                            <a href="{{ url('/register') }}" class="btn btn-outline-light">Register</a>
                        @endauth
                        @if(session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif
                    </div>               
                </div>
            </div>
        </nav>
    </header>

    <main class="container mt-4">
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>