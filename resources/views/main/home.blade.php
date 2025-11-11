@extends('layouts.main')

@section('content')
<section class="home card mb-4 section mt-4" id="home" style="background-image: url('/assets/img/backgrounds/bg.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat; border-radius: 10px;">
    <div class="text-center">
        <div class="card-body">
            <div class="mb-4 ">
                <h1 class="uppercase header-h1" style="color: chartreuse; text-shadow: 2px 2px 4px #000000;">Selamat Datang di Penyewaan PB ILLVERD</h2>
            </div>
            <div class="header-h3">
                <h3 style="color: chartreuse; text-shadow: 2px 2px 4px #000000;">Silahkan Pilih Menu yang Anda Inginkan</h3>
            </div>
            <a class="link-pesan text-uppercase" href="{{route('book')}}">Pesan Sekarang</a>
            <div class="mt-6">
                <a class="link-jadwal text-uppercase" href="#jadwal">Lihat Jadwal</a>
            </div>
        </div>
    </div>
</section>
<section class="card mb-4 section" id="jadwal">
    <div class="card-header d-flex justify-content-between mb-2">
        <div class="form-container">
            <form id="date-form">
                <label class="home-jadwal" for="date">Pilih Tanggal:</label>
                <input class="input-tggl" type="date" id="date" name="date" value="{{ $selectedDate }}" required>
                <button class="jadwal-btn" type="submit">Lihat Jadwal</button>
            </form>
        </div>
    </div>
    <div class="card-body">
        <!-- Container untuk menampilkan jadwal -->
        <div id="jadwal-container">
            @include('main.jadwal')
        </div>
    </div>
</section>
<section class="card mb-4 section" id="hargaSewa">
    <div class="card-body">
        @include('main.hargaSewa')
    </div>
</section>
<section class="card mb-4 section" id="tentangKami">
    <div class="card-body">
        @include('main.tentangKami')
    </div>
</section>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const sections = document.querySelectorAll('.section');
    const navLinks = document.querySelectorAll('.nav-link');

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const activeId = entry.target.id;

                // Hapus class active dari semua link
                navLinks.forEach(link => link.classList.remove('border-b-active'));

                // Tambahkan class active ke link yang sesuai
                document.querySelector(`.nav-link[href="#${activeId}"]`)?.classList.add('border-b-active');
            }
        });
    }, {
        threshold: 0.5 // 50% section terlihat
    });

    sections.forEach(section => observer.observe(section));
});
</script>
@endsection
