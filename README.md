# Implementasi algoritma levensthein
fungsi aplikasi ini adalah mengkoreksi kesalahan ejaaan , contoh kenaps -> kenapa atau ssya sedang minnm ->  saya sedang minum
projek beberapa bulan lalu di tahun 2021, menggunakan codeigniter 4

import dulu list data kata bahasa indonesia di file koreksiejaan.sql
.env set seperti dibawah ini hanya bagian ini yg tidak di comment 
> database.default.hostname = localhost
> database.default.database = koreksiejaan
> database.default.username = root
> database.default.password = 
> database.default.DBDriver = MySQLi

jika masih belum bisa koneksi, coba set port mysqlnya ke 3306, soalnya di source code saya menggunakan port 3309
> root/app/Config/Database.php
> diarray $default coba ganti key 'port' ke 3306

buka cmd/terminal di folder root lalu jalankan dengan menggunakan :
> php spark serve ✨
akes di http://localhost:8080

aplikasi dapat menerima document dengan upload document bertipe docx dan pdf, atau langsung input text di bagian input text 

*** masih banyak kekurangan
*** note: algoritma bawaan PHP lebih akurat daripada algoritma di source code ini
*** terima kasih
