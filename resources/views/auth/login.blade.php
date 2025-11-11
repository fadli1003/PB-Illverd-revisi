<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penyewaan PB Illverd</title>
    {{-- <link href="{{asset('assets/vendor/css/core.css')}}" rel="stylesheet">
    <link href="{{asset('assets/vendor/css/theme-default.css')}}" rel="stylesheet"> --}}
    {{-- Tailwind --}}
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />
    @vite(['resources/css/app.css', 'resources/css/animations.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="assets/css/login.css">
    <style>
    input,
    select,
    textarea {
    accent-color: #0307fb; /* warna utama */
    color-scheme: dark light;
    }
    </style>
</head>
@if (session('login_required'))
    <div class="alert alert-warning text-center">
        {{ session('login_required') }}
    </div>
@endif

<body class="bg-gray-900 bg-opacity-[.995] flex min-h-screen">
    <div class="flex w-full flex-col items-center justify-center">
        <div class="back absolute top-[5%] left-[5%]">
            <a href="{{ route('home') }}" class="backButton">🏠Home</a><span class="text-gray-300">/</span><a href="{{ route('login') }}" class="backButton">Login</a>
        </div>
        <div class="flex pt-1 shadow-[0_2px_3px_rgba(0,0,255,.4)] rounded-lg justify-center bg-slate-900 w-[370px] h-[480px] max-[390px]:w-[93%]">
            <div class="card-body p-6 justify-self-center w-full">
                <div class="m-0 flex h-[90px]">
                    <img class="rounded-[4px] object-cover w-full h-full" src="{{ asset('assets/img/bagus.png') }}"">
                </div>
                <h4 class="text-xl font-bold text-center mt-2 text-[#FFD700]">Selamat Datang di PB ILLVERD</h4>
                <h4 class="text-lg text-center text-[#FFD700] font-semibold my-2">Login</h4>
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
                    <div class="mb-3 flex flex-col text-gray-300">
                        <label class="mb-[2px]" for="email">Email</label>
                        <input
                            class="rounded-md text-sm py-[6px] focus:border-[1.5px] focus:ring-0 focus:border-amber-500 bg-transparent"
                            type="email" name="email" id="email" placeholder="Masukkan email" required>
                    </div>
                    <div class="mb-1 flex flex-col text-gray-300">
                        <label class="mb-[2px]" for="password">Password</label>
                        <input
                            class="rounded-md text-sm py-[6px] focus:border-[1.5px] focus:ring-0 focus:border-amber-500 bg-transparent"
                            type="password" name="password" id="password" class="form-control"
                            placeholder="Masukan Password" required>
                    </div>
                    <div class="flex justify-between mx-1 mb-3">
                        <div class="form-check">
                            <input class=" w-3 h-3 focus:ring-0 bg-transparent rounded-sm" type="checkbox"
                                id="remember" name="remember">
                            <label class=" text-xs text-gray-300" for="remember">
                                Ingat Saya
                            </label>
                        </div>
                        <a href="#"
                            class="self-center text-xs text-blue-500 hover:text-blue-700 hover:underline">
                            Lupa Password?
                        </a>
                    </div>
                    <div class="w-full flex justify-center">
                    <button type="submit"
                        class="mt-1 ring-1 ring-amber-500 rounded-[4px] shadow-md hover:-translate-y-[1px] p-1 w-[98%] transition-all duration-[.4s] hover:bg-amber-500 text-gray-300 hover:text-[#3f3307] font-semibold"
                        >Login
                    </button>
                    </div>
                </form>
                <p class="text-center mt-4 mb-2 text-xs text-gray-300">
                    Belum punya akun?
                    <a href="{{ route('register') }}"
                        class=" text-blue-500 hover:underline hover:text-blue-700 transition-all duration-[.4s] ease">
                        Daftar Sekarang
                    </a>
                </p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');

            form.addEventListener('submit', function(e) {
                if (!emailInput.value || !passwordInput.value) {
                    e.preventDefault();
                    alert('Email dan password tidak boleh kosong.');
                }
            });
        });
    </script>
</body>

</html>
