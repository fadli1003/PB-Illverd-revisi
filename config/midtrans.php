<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Midtrans Configuration
    |--------------------------------------------------------------------------
    |
    | Di sini Anda dapat menentukan konfigurasi untuk Midtrans API.
    | Anda bisa mendapatkan credentials ini dari Merchant Dashboard Midtrans Anda.
    |
    */

    /**
     * Server Key dari Midtrans Dashboard
     * Digunakan untuk autentikasi server-side (private key).
     * JANGAN DITAMPILKAN di sisi client (browser).
     */
    'server_key' => env('MIDTRANS_SERVER_KEY'),

    /**
     * Client Key dari Midtrans Dashboard
     * Digunakan untuk inisialisasi Snap JS di sisi client (browser).
     * Relatif aman untuk ditampilkan di sisi client, tapi lebih baik tidak hardcode.
     */
    'client_key' => env('MIDTRANS_CLIENT_KEY'),

    /**
     * Environment Mode
     * Set ke true untuk production, false untuk sandbox.
     */
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),

    /**
     * Enable sanitization
     * Sanitasi otomatis input data transaksi.
     */
    'is_sanitized' => env('MIDTRANS_IS_SANITIZED', true),

    /**
     * Enable 3D-Secure
     * Aktifkan fitur keamanan 3D Secure.
     */
    'is_3ds' => env('MIDTRANS_IS_3DS', true),

];