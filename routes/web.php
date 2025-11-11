<?php

use App\Http\Controllers\ProfileController;
use App\Models\Booking;
use App\Models\Membership;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RiwayatController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PindahController;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

//Halaman statis
Route::get('/', [HomeController::class, 'home'])->name('home');
// Route::get('/login', function (){return view('login');})->name('login'); //tidak perlu karena sudah di set default oleh laravel breeze pada auth.php
// Route::post('/register', [AuthController::class, 'register'])->name('register');
// Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

//pelanggan dan member
Route::middleware(['auth', 'role:pelanggan,member'])->group(function () {    
    //pengajuan pindah jadwal
    Route::get('/bookings/pindah/{id}', [PindahController::class, 'formPindah'])->name('pindah');
    Route::put('/bookings/update_pindah_jadwal/{id}', [PindahController::class, 'pengajuanPindahRegular'])->name('update_pindah');
    Route::get('/bookings/pindah_Jadwal', [PindahController::class, 'index'])->name('pindah_jadwal');
    Route::get('/riwayat_pemesanan', [RiwayatController::class, 'index'])->name('riwayat');    
    Route::put('/bookings/batalkan_pengajuan/{id}', [RiwayatController::class, 'batalkanPengajuan'])->name('batalkan_pengajuan');
    Route::get('/riwayat/{id}/cetak', [RiwayatController::class, 'cetak'])->name('cetak');
    Route::get('/notifications', [HomeController::class, 'notifikasi'])->name('notif');
    //profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'ubahProfile'])->name('editProfile');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/hapus_akun', [ProfileController::class, 'destroy'])->name('profile.hapus');
    Route::post('/profile/tukar_password', [AuthController::class, 'passwordReset'])->name('tukar_sandi');
});
//member
Route::middleware(['auth', 'role:member'])->group(function () {
    Route::put('/bookings/cancel/{id}', [RiwayatController::class, 'cancel'])->name('bookings.cancel');
    Route::get('/bookings/membership/{id}', [RiwayatController::class, 'extend'])->name('memberExtend');
    Route::put('/bookings/membershipUpdate/{id}', [RiwayatController::class, 'pengajuanMembership'])->name('pengajuanMembership');
    Route::put('/bookings/update_pindah_member/{id}', [PindahController::class, 'updatePindahMember'])->name('update_pindah_member');
    Route::get('/bookings/pindah_member/{id}', [PindahController::class, 'formPindahMember'])->name('pindah_member');
});
//admin
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/pesanan_masuk', [AdminController::class, 'pesananMasuk'])->name('pesanan_masuk');
    Route::get('/admin/pengajuan', [AdminController::class, 'pengajuan'])->name('pengajuan');
    Route::get('/admin/kelola_jadwal', [AdminController::class, 'kelolaJadwal'])->name('kelola_jadwal');
    Route::post('/admin/setujui-pesanan/{id}', [BookingController::class, 'approveOrder'])->name('setujuiPesanan');
    Route::post('/admin/pesanan_masuk/{id}', [AdminController::class, 'tolakOrder'])->name('tolakPesanan');
    Route::delete('/admin/hapus-pesanan/{id}', [AdminController::class, 'deleteOrder'])->name('tolakPesanan');
    Route::get('/admin/edit_pesanan/{id}', [AdminController::class, 'edit'])->name('edit');
    Route::put('/admin/form_edit_pesanan/{id}', [AdminController::class, 'editPesananRegular'])->name('editPesananRegular');
    Route::put('/admin/pesanan_member_updated/{id}', [AdminController::class, 'editPesananMember'])->name('editPesananMember');
    Route::get('/admin/laporan', [HomeController::class, 'laporan'])->name('laporan');    
    Route::get('/get-pending-bookings', [BookingController::class, 'getPendingBookings'])->name('get.pending');
    // Route untuk menyetujui dan tolak pembatalan
    Route::put('/admin/pengajuan/setujui-pembatalan/{id}', [AdminController::class, 'setujuiCancel'])->name('setujui_cancel');
    Route::put('/admin/pengajuan/tolak-pembatalan/{id}', [AdminController::class, 'tolakCancel'])->name('tolak_cancel');
    Route::put('/admin/setujui_pindah/{id}', [AdminController::class, 'setujuiPindah'])->name('setujui_pindah');
    Route::put('/admin/tolak_pindah/{id}', [AdminController::class, 'tolakPindah'])->name('tolak_pindah');
    // Route untuk menyetujui/tolak perpanjangan member
    Route::put('/admin/setujui_perpanjangan_member/{id}', [AdminController::class, 'perpanjangMember'])->name('perpanjang_member');
    Route::put('/admin/tolak_perpanjangan/{id}', [AdminController::class, 'tolakPerpanjangan'])->name('tolak_perpanjangan');
});
Route::middleware(['auth', 'role:pelanggan,member,admin'])->group(function () {
    Route::get('/pemesanan', [BookingController::class, 'index'])->name('book');
    Route::get('/bookings/create', [BookingController::class, 'create'])->name('bookings.create'); //dari jadwal home
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store'); //dari jadwal pemesanan
    Route::get('/get-jadwal', [JadwalController::class, 'getJadwal'])->name('get-jadwal');
});

// payment midtrans
Route::middleware('auth')->group(function () {
    Route::get('/payment/{booking}', [PaymentController::class, 'showPaymentPage'])->name('payment.show');
    Route::post('/payment/create', [PaymentController::class, 'createPayment'])->name('payment.create');
});
// Route untuk callback (opsional, bisa arahkan ke halaman umum)
Route::get('/payment/success', function () { return view('payments.success'); })->name('payment.success');
Route::get('/payment/unfinish', function () { return view('payments.unfinish'); })->name('payment.unfinish');
Route::get('/payment/error', function () { return view('payments.error'); })->name('payment.error');

require __DIR__.'/auth.php';