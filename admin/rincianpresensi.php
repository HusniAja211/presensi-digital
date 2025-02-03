<?php
session_start();
require "database/config.php";
require "function/function.php";

blmlogin(); // Validasi session login

$id = $_GET['id'];  // Get the ID from the URL

// Fetch the presensi detail data
$presensiDetail = getPresensiDetail($conn, $id);

// Check if the data was successfully retrieved
if ($presensiDetail) {
    // Assign variables for the details
    $email = $presensiDetail['email'];
    $nama = $presensiDetail['nama'];
    $fnis = $presensiDetail['fnis'];
    $kelas = $presensiDetail['kelas'];
    $hari = $presensiDetail['hari'];
    $jam = $presensiDetail['jam'];
    $status = $presensiDetail['status'];
    $bukti = $presensiDetail['bukti'];
} else {
    echo "Data not found!";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rincian Presensi - <?= htmlspecialchars($id); ?></title>
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
            border: 3px solid #e00d2ay;
            border-radius: 10px;
            width: 300px;
            height: auto;
            object-fit: cover;
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
            text-align: center; /* Center the text */
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
            <h1>Rincian Presensi</h1>
        </div>
        <di class="content">
            <?php if (!empty($bukti)): ?>
                <?php if ($status !== 'hadir'): ?>
                    <img src="<?= htmlspecialchars($bukti) ?>" alt="Bukti Presensi" />
                <?php else: ?>
                   <center><h3>Siswa Hadir</h3></center>
                <?php endif; ?>
            <?php else: ?>
                <p>Tidak ada bukti</p>
            <?php endif; ?>
            <h2><?= $id ?></h2> 
            <br>
            <p style="color: #3b82f6">Status</p>
            <p><?=$status?></p>

            <div class="info-section">
                <div>
                    <h3>Email</h3>
                    <p><?= $email ?></p>
                </div>
                <div>
                    <h3>Nama</h3>
                    <p><?= $nama ?></p>
                </div>
            </div>
            <div class="info-section">
                <div>
                    <h3>NIS</h3>
                    <p><?= $fnis ?></p>
                </div>
                <div>
                    <h3>Kelas</h3>
                    <p><?= $kelas ?></p>
                </div>
            </div>
            <div class="info-section">
                <div>
                    <h3>Hari</h3>
                    <p><?= $hari ?></p>
                </div>
                <div>
                    <h3>Jam</h3>
                    <p><?= $jam ?></p>
                </div>
            </div>

            <div class="back-btn">
                <button onclick="window.history.back()";>Kembali</button>
            </div>
        </div>
    </div>
</body>
</html>
