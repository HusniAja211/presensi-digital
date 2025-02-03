<?php
// Atur timezone ke Asia/Jakarta (Waktu Indonesia Barat)

// Memulai sesi dan memeriksa autentikasi
session_start();
require "../database/config.php";
require "../function/function.php";
require "../../vendor/autoload.php";

use Dompdf\Dompdf;

// Validasi session login
blmlogin(); 

// Ambil semua data dari tabel akun
$stmt = $conn->prepare("SELECT * FROM akun");
$stmt->execute();
$hasil = $stmt->get_result();

// Array untuk menyimpan data dari database
$dataAkun = [];
while ($row = $hasil->fetch_assoc()) {
    $filename = $row['pfp'];
    $filepath = "../../pfp/" . $filename;

    $dataAkun[] = [
        'nis' => $row['nis'],
        'email' => $row['email'],
        'nama' => $row['nama'],
        'kelas' => $row['kelas'],
        'pfp' => $filepath,
        'level' => $row['level']
    ];
}

// Ambil data hari ini
$hariini = aturTimezoneDanHari();

// Membuat instance Dompdf
$dompdf = new Dompdf();

// Konten HTML untuk PDF
$html = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Akun</title>
    <style>
        body { font-family: Arial, sans-serif; }
        h1 { color: #3b82f6; text-align: center; }
        h3 { color: #555; text-align: center; }
        .tanggal { text-align: center; font-size: 14px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 8px; text-align: center; border: 1px solid #ddd; font-size: 12px; }
        th { background-color: #3b82f6; color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        img { border-radius: 50%; width: 30px; height: 30px; }
    </style>
</head>
<body>
    <h1>LAPORAN DATA AKUN</h1>
    <div class="tanggal">
        <h3>Tanggal</h3>
        <p>' . htmlspecialchars($hariini) . '</p>
    </div>
    <table>
        <thead>
            <tr>
                <th>NIS</th>
                <th>Email</th>
                <th>Nama</th>
                <th>Kelas</th>
                <th>Gambar Profil</th>
                <th>Level</th>
            </tr>
        </thead>
        <tbody>';

foreach ($dataAkun as $data) {
    $html .= '<tr>
                <td>' . htmlspecialchars($data['nis']) . '</td>
                <td>' . htmlspecialchars($data['email']) . '</td>
                <td>' . htmlspecialchars($data['nama']) . '</td>
                <td>' . htmlspecialchars($data['kelas']) . '</td>
                <td><img src="' . $data['pfp'] . '" alt="Profil"></td>
                <td>' . htmlspecialchars($data['level']) . '</td>
              </tr>';
}

$html .= '
        </tbody>
    </table>
</body>
</html>';

// Load HTML ke Dompdf
$dompdf->loadHtml($html);

// Mengatur ukuran kertas dan orientasi
$dompdf->setPaper('A4', 'portrait');

// Render HTML ke PDF
$dompdf->render();

// Tentukan nama file dengan menggunakan variabel $hariini
$filename = 'Laporan_Data_Akun_' . str_replace(' ', '_', $hariini) . '.pdf';
$savePath = __DIR__ . '/../laporan/laporan_data_akun/' . $filename;

// Buat direktori jika belum ada
if (!file_exists(__DIR__ . '/../laporan/laporan_data_akun')) {
    mkdir(__DIR__ . '/../laporan/laporan_data_akun', 0777, true);
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