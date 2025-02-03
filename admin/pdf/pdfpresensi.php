<?php
session_start();
require "../database/config.php";
require "../function/function.php";

use Dompdf\Dompdf;
use Dompdf\Options;

require "../../vendor/autoload.php";

// Validasi session login
blmlogin(); 

// Query untuk mengambil semua data dari tabel presensi_siswa
$stmt = $conn->prepare("SELECT * FROM presensi_siswa");
$stmt->execute();
$result = $stmt->get_result();

$hariini = aturTimezoneDanHari();

// Inisialisasi Dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true); // Jika ada gambar dari URL eksternal
$dompdf = new Dompdf($options);

// Buat HTML untuk diubah menjadi PDF
$html = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Data Presensi Siswa</title>
    <style>
        body { font-family: Arial, sans-serif; }
        h1 { color: #3b82f6; text-align: center; }
        h3 { color: #555; text-align: center; }
        .tanggal { text-align: center; font-size: 14px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 8px; text-align: center; border: 1px solid #ddd; font-size: 12px; }
        th { background-color: #3b82f6; color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; }
    </style>
</head>
<body>
    <h1>LAPORAN DATA PRESENSI SISWA</h1>
    <div class="tanggal">
        <h3>Tanggal</h3>
        <p>' . $hariini . '</p>
    </div>
    <table>
        <thead>
            <tr>
                <th>ID Presensi</th>
                <th>NIS</th>
                <th>Email</th>
                <th>Nama</th>
                <th>Kelas</th>
                <th>Hari</th>
                <th>Jam</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>';

// Tambahkan data ke tabel
while ($row = $result->fetch_assoc()) {
    $html .= '<tr>
        <td>' . htmlspecialchars($row['id']) . '</td>
        <td>' . htmlspecialchars($row['fnis']) . '</td>
        <td>' . htmlspecialchars($row['email']) . '</td>
        <td>' . htmlspecialchars($row['nama']) . '</td>
        <td>' . htmlspecialchars($row['kelas']) . '</td>
        <td>' . htmlspecialchars($row['hari']) . '</td>
        <td>' . htmlspecialchars($row['jam']) . '</td>
        <td>' . htmlspecialchars($row['status']) . '</td>
    </tr>';
}

$html .= '
        </tbody>
    </table>
</body>
</html>';

// Menutup statement dan koneksi
$stmt->close();
$conn->close();

// Load HTML ke Dompdf
$dompdf->loadHtml($html);

// Mengatur ukuran kertas dan orientasi
$dompdf->setPaper('A4', 'portrait');

// Render HTML ke PDF
$dompdf->render();

// Tentukan nama file dengan menggunakan variabel $hariini
$filename = 'Laporan_Presensi_Siswa_' . str_replace(' ', '_', $hariini) . '.pdf';
$savePath = __DIR__ . '/../laporan/laporan_presensi_siswa/' . $filename;

// Buat direktori jika belum ada
if (!file_exists(__DIR__ . '/../laporan/laporan_presensi_siswa')) {
    mkdir(__DIR__ . '/../laporan/laporan_presensi_siswa', 0777, true);
}

// Simpan file PDF ke direktori server
file_put_contents($savePath, $dompdf->output());

// Atur header untuk download otomatis
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($savePath));

// Baca file dan keluarkan ke browser
readfile($savePath);
exit;
?>
