@extends('layouts.main')

@section('content')
<div class="card mb-4 shadow" id="home" style="background-image: url('/assets/img/lapangan bulu tangkis.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat; border-radius: 10px;">
    <div class="text-center">
        <div class="card-body mt-5">
            <div class="mb-5 mt-4">        
                <h1 style="color: chartreuse; text-shadow: 2px 2px 4px #000000;">Selamat Datang di Penyewaan Lapangan PB ILLVERD</h2>                
            </div>
            <div class="mb-5">        
                <h3 style="color: chartreuse; text-shadow: 2px 2px 4px #000000;">Silahkan Pilih Menu yang Anda Inginkan</h3>                
            </div>
            <a class="btn btn-info btn-xl text-uppercase" href="{{route('book')}}">Pesan Sekarang</a>
            <div class="mt-5 mb-5">
                <a class="btn btn-warning btn-xl text-uppercase" href="#jadwal">Lihat Jadwal</a>
            </div>
        </div>
    </div>
</div>
<div class="card mb-4" id="jadwal">
    <div class="card-body">
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
<div class="card mb-4" id="hargaSewa">
    <div class="card-body">
        @include('main.hargaSewa')
    </div>
</div>
<div class="card mb-4" id="tentangKami">
    <div class="card-body">
        @include('main.tentangKami')
    </div>
</div>
@endsection