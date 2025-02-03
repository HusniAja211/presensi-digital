<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "p_presensi";

// Membuat koneksi
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Periksa koneksi
// if ($conn->connect_error) {
//     die("Gagal Menghubungkan: " . $conn->connect_error);
// }
// echo "Berhasil Menghubungkan";
?>