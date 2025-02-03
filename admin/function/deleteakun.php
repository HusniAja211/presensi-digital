<?php
require "../database/config.php";

// Cek jika parameter 'nis' ada di URL
if (isset($_GET['nis'])) {
    $nisg = $_GET['nis']; // Ambil NIS dari URL

    // Siapkan query DELETE untuk menghapus akun berdasarkan NIS
    $query = "DELETE FROM akun WHERE nis = ?";
    
    // Menyiapkan statement
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("s", $nisg); // Bind parameter dengan tipe data string
        if ($stmt->execute()) {
            // Jika berhasil dihapus, arahkan kembali ke halaman sebelumnya menggunakan JavaScript
            echo "<script>alert('Akun berhasil dihapus!'); window.history.back();</script>";
        } else {
            // Jika gagal, arahkan kembali ke halaman sebelumnya menggunakan JavaScript
            echo "<script>alert('Gagal menghapus akun!'); window.history.back();</script>";
        }
    } else {
        // Jika statement gagal disiapkan, arahkan kembali ke halaman sebelumnya menggunakan JavaScript
        echo "<script>alert('Terjadi kesalahan!'); window.history.back();</script>";
    }
} else {
    // Jika tidak ada parameter nis di URL, arahkan kembali ke halaman sebelumnya menggunakan JavaScript
    echo "<script>window.history.back();</script>";
}
?>
