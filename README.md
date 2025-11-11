1. clone repository atau download file zip dari git.
2. setelah semua file dan folder diekstraks, masuk ke direktori root PB-Illverd-revisi. kemudian jalankan composer install pada terminal.
3. jalankan server local(xammp/laragon/etc.) untuk akses ke database mysql.
4. jalankan php artisan migrate pada terminal.
5. jalankan npm run dev.
6. buka terminal baru, dan jalankan perintah php artisan serve. web akan dapat diakses melalui link local host yang diberikan. umumnya berjalan pada http://127.0.0.1:8000.
7. untuk dapat menggunakan payment gateway, membutuhkan public server yang dapat diakses oleh pihak ketiga(midtrans).
