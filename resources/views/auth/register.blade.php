<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Penyewaan PB Illverd</title>
    <link href="{{asset('assets/vendor/css/core.css')}}" rel="stylesheet">
    <link href="{{asset('assets/vendor/css/theme-default.css')}}" rel="stylesheet">
</head>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm rounded-3">
                    <div class="card-body p-4">
                        <div class="card">
                            <img width="400" height="80"  src="{{ asset('assets/img/bagus1.png')}}"">                            
                        </div>
                        <h4 class="text-center mb-2">Daftar Akun PB ILLVERD</h4>
                        <h4 class="text-center mb-1">Register</h4>
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
                        <form action="{{ route('register') }}" method="POST">
                            @csrf
                            <div class="mb-2">
                                <label for="name" class="form-label">Nama</label>
                                <input type="text" name="name" id="name" class="form-control" placeholder="Masukkan nama" required>
                            </div>
                            <div class="mb-2">
                                <label for="no_Hp" class="form-label">No Handphone</label>
                                <input type="tect" name="no_Hp" id="no_Hp" class="form-control" placeholder="Masukkan nomor handphone" required>
                            </div>
                            <div class="mb-2">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" name="email" id="email" class="form-control" placeholder="Masukkan email" required>
                            </div>
                            <div class="mb-2">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan password" required>
                            </div>
                            <div class="mb-2">
                                <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Konfirmasi password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Daftar</button>
                        </form>
                        <p class="text-center mt-2">
                            Sudah punya akun? <a href="{{ route('login') }}" class="text-decoration-none text-primary">Login</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('form');
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('password_confirmation');

            form.addEventListener('submit', function (e) {
                if (password.value !== confirmPassword.value) {
                    e.preventDefault();
                    alert('Password tidak cocok!');
                }
            });
        });
    </script>
</body>
</html>