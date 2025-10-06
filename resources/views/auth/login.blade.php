<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Penyewaan PB Illverd</title>
    <link href="{{asset('assets/vendor/css/core.css')}}" rel="stylesheet">
    <link href="{{asset('assets/vendor/css/theme-default.css')}}" rel="stylesheet">
</head>
@if(session('login_required'))
    <div class="alert alert-warning text-center">
        {{ session('login_required') }}
    </div>
@endif
<body class="bg-light d-flex justify-content-center align-items-center vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm rounded-3">
                    <div class="card-body p-4">
                        <div class="card">
                            <img width="400" height="80"  src="{{ asset('assets/img/bagus1.png')}}"">                            
                        </div>
                        <h4 class="text-center mb-2">Selamat Datang di PB ILLVERD</h4>
                        <h4 class="text-center mb-1">Login</h4>
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if (session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif
                        <form action="{{ route('login') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" name="email" id="email" class="form-control" placeholder="Masukkan email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan password" required>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                    <label class="form-check-label" for="remember">
                                        Ingat Saya
                                    </label>
                                </div>
                                <a href="#" class="text-decoration-none text-primary">Lupa Password?</a>
                            </div>                            
                            <button type="submit" class="btn btn-primary w-100">Login</button>                                                     
                        </form>
                        <p class="text-center mt-2 mb-2">
                            Belum punya akun? <a href="{{ route('register') }}" class="text-decoration-none text-primary">Daftar Sekarang</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('form');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');

            form.addEventListener('submit', function (e) {
                if (!emailInput.value || !passwordInput.value) {
                    e.preventDefault();
                    alert('Email dan password tidak boleh kosong.');
                }
            });
        });
    </script>
</body>
</html>