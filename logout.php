<?php
// Memulai sesi untuk dapat mengakses data sesi yang sudah ada
session_start();

// Menghapus semua variabel sesi yang ada
session_unset(); // Menghapus semua data yang tersimpan dalam sesi

// Menghancurkan sesi yang aktif
session_destroy(); // Menghancurkan sesi, menghapus semua data terkait sesi

// Mengalihkan pengguna ke halaman login setelah logout
header("Location: index.php"); // Mengirim header untuk mengalihkan ke halaman login
exit(); // Menghentikan eksekusi skrip setelah pengalihan untuk mencegah eksekusi kode selanjutnya
?>