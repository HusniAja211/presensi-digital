<?php
session_start();
require "function/function.php";
sdhlogin(); // Cek login
require "../admin/database/config.php";

$id = $_GET['id'] ?? null; // Ambil ID dari GET
if (!$id) {
    die("ID tidak ditemukan.");
}

$data = getDetailPresensi($conn, $id);

if (!$data) {
    die("Data tidak ditemukan untuk ID: " . htmlspecialchars($id));
}

// Gunakan data yang diambil
$email = $data['email'];
$nama = $data['nama'];
$nis = $data['fnis'];
$kelas = $data['kelas'];
$hari = $data['hari'];
$jam = $data['jam'];
$status = $data['status'];

$bukti = getBukti ($conn, $id);

$data2 = getSessionData($conn);
$profile_picture = $data2['profile_picture'];




?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rincian Akun - <?= htmlspecialchars($nama); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        
        .container {
            max-width: 900px;
            margin: 50px auto;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background-color: #3b82f6;
            color: white;
            text-align: center;
            padding: 1.5rem;
        }

        .header h1 {
            margin: 0;
            font-size: 2rem;
        }

        .content {
            padding: 2rem;
        }

        .content img {
            display: block;
            margin: 0 auto;
            border-radius: 50%;
            width: 300px;
            height: 300px; 
            object-fit: cover; 
        }

        .bukti img {
            display: block;
            margin: 0 auto;
            width: 20%; /* Menyesuaikan lebar kontainer */
            height: auto; /* Menjaga rasio gambar */
            border-radius: 0%; /* Pastikan gambar bukti kotak */
            border: 2px #000 solid;
        }

        .content h2 {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 1.5rem;
            color: #333;
        }

        .content p {
            margin: 1rem 0;
            font-size: 1.2rem;
            color: #555;
            text-align: center;
            font-weight: bold;
        }

        .info-section {
            display: flex;
            justify-content: space-between;
            margin-top: 2rem;
        }

        .info-section div {
            width: 48%;
        }

        .info-section h3 {
            font-size: 1.2rem;
            margin-bottom: 1rem;
            color: #007bff;
        }

        .info-section p {
            font-size: 0.9rem;
            color: #666;
            line-height: 1.5;
        }

        .back-btn {
            display: block;
            text-align: center;
            margin-top: 2rem;
        }

        .back-btn button {
            text-decoration: none;
            color: #fff;
            background-color: #3b82f6;
            padding: 0.7rem 2rem;
            border-radius: 5px;
            transition: background-color 0.2s ease;
        }

        .back-btn a:hover {
            background-color: #0056b3;
        }

        @media (max-width: 768px) {
            .info-section {
                flex-direction: column;
            }

            .info-section div {
                width: 100%;
                margin-bottom: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Rincian Akun <?= htmlspecialchars($nama); ?></h1>
        </div>

        <div class="content">
                <img src="<?= htmlspecialchars($profile_picture) ?>" 
                alt="Bukti Presensi">
            <br>
            <p style="color: #3b82f6">Status</p>
            <p><?=$status?></p>

            <div class="info-section">
                <div>
                    <h3> <center>Email</center></h3>
                    <p><?= $email ?></p>
                </div>
                <div>
                    <h3><center>Nama</h3>
                    <p><?= $nama ?></p>
                </div>
            </div>
            <div class="info-section">
                <div>
                    <h3> <center>NIS</center></h3>
                    <p><?= $nis ?></p>
                </div>
                <div>
                    <h3><center>Kelas</h3>
                    <p><?= $kelas ?></p>
                </div>
            </div>
            <div class="info-section">
                <div>
                    <h3> <center>Hari</center></h3>
                    <p><?= $hari ?></p>
                </div>
                <div>
                    <h3><center>Jam</h3>
                    <p><?= $jam ?></p>
                </div>
            </div>

            <div class="bukti">
                <div>
                    <h3 style="color: #007bff;"> <center>Bukti</center></h3>
                    <img src="<?= htmlspecialchars($bukti); ?>"
                     alt="">
                </div>
            </div>

            <div class="back-btn">
                <button onclick="window.history.back()";>Kembali</button>
            </div>
        </div>
    </div>
</body>
</html>
