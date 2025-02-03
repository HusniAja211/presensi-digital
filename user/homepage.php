<?php
session_start();
require "function/function.php";
sdhlogin(); // Cek login
require "../admin/database/config.php";

// Ambil data dashboard
$data = getSessionData($conn);

if (isset($data['error'])) {
    die($data['error']); // Tampilkan error jika ada
}

// Ambil data
$nama = $data['nama'];
$nis = $data['nis'];
$hari_sekarang = $data['hari_sekarang'];
$statistics = $data['statistics'];
$profile_picture = $data['profile_picture'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LogOnTime Dashboard</title>
    <style>
        html, body {
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            background-color: #fff;
            font-family: Arial, sans-serif;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        body {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            box-sizing: border-box;
        }

        .header {
            background-color: #4A73E0;
            width: 100%;
            padding: 20px 30px;
            color: white;
            font-size: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-sizing: border-box;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            color: white;
        }

        .account-info {
            display: flex;
            align-items: center;
            cursor: pointer;
            position: relative;
        }

        .account-name {
            font-size: 18px;
            color: white;
            margin-right: 8px;
        }

        .icon-akun {
            width: 60px;
            height: 60px;
            border-radius: 100%;
            object-fit: cover;
        }

        .dropdown {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            background-color: white;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            z-index: 999;
            width: 120px;
        }

        .dropdown a {
            display: block;
            padding: 8px;
            font-size: 14px;
            color: black;
            text-decoration: none;
            text-align: left;
        }

        .dropdown a:hover {
            background-color: red;
            color: white;
            font-weight: bold;
        }

        .hamburger {
            display: none;
            font-size: 24px;
            cursor: pointer;
            color: white;
        }

        .content {
            width: 100%;
            max-width: 1200px;
            text-align: center;
            margin-top: 50px;
            flex-grow: 1;
        }

        .welcome-text {
            font-size: 26px;
        }

        .date-time {
            margin-top: 10px;
            font-size: 22px;
            color: #666;
        }

        .status-cards {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
            gap: 20px;
            flex-wrap: wrap;
        }

        .card {
            flex: 1;
            background-color: white;
            border: 3px solid #ccc;
            border-radius: 15px;
            text-align: center;
            padding: 20px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
            min-width: 200px;
            max-width: 250px;
            box-sizing: border-box;
        }

        .card .label {
            font-size: 18px;
            margin-bottom: 10px;
        }

        .card .value {
            font-size: 36px;
            font-weight: bold;
        }

        .hadir {
            border-color: #a35ff8;
        }

        .izin {
            border-color: #00cc66;
        }

        .sakit {
            border-color: blue;
        }

        .alpha {
            border-color: red;
        }

        .btn-absen {
            margin-top: 50px;
            padding: 15px 30px;
            font-size: 22px;
            background-color: gray;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
        }

        .btn-absen:hover {
            background-color: #555;
        }

        .footer {
            background-color: #3A66E5;
            width: 100%;
            padding: 10px 30px;
            font-size: 16px;
            color: #ffffff;
            box-sizing: border-box;
            text-align: center;
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .account-info {
                display: none;
            }

            .hamburger {
                display: block;
            }

            .status-cards {
                flex-direction: column;
                align-items: center;
            }

            .card {
                width: 90%;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">LogOnTime</div>
        <div class="hamburger" onclick="toggleDropdown()"></div>
        <div class="account-info">
            <span class="account-name"><?= htmlspecialchars($nama); ?></span>
            <img src="<?= htmlspecialchars($profile_picture); ?>" 
                alt="Profile Image" 
                class="icon-akun" 
                onclick="toggleDropdown()">
            <div class="dropdown" id="profileDropdown">
                <a href="akun.php?nis=<?= htmlspecialchars ($nis); ?>">Akun</a>
                <a href="../logout.php">Keluar</a>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="welcome-text">
            <p>Selamat Datang, <?= htmlspecialchars($nama); ?>!</p>
        </div>
        <div class="date-time">
            <span id="current-date"><?= htmlspecialchars($hari_sekarang); ?></span> | Jam: <span id="current-time"></span>
        </div>

        <div class="status-cards">
            <div class="card hadir">
                <div class="label">Hadir</div>
                <div class="value"><?= $statistics['hadir'] ?? 0; ?></div>
            </div>
            <div class="card izin">
                <div class="label">Izin</div>
                <div class="value"><?= $statistics['izin'] ?? 0; ?></div>
            </div>
            <div class="card sakit">
                <div class="label">Sakit</div>
                <div class="value"><?= $statistics['sakit'] ?? 0; ?></div>
            </div>
            <div class="card alpha">
                <div class="label">Tidak Hadir</div>
                <div class="value"><?= $statistics['tidak hadir'] ?? 0; ?></div>
            </div>
        </div>

        <a href="riwayat_presensi.php"><button class="btn-absen">Riwayat Presensi</button></a>
    </div>

    <div class="footer">
        &copy; <?= date("Y"); ?> Husni Mubarak. All rights reserved.
    </div>

    <script>
        // Fungsi untuk menampilkan waktu
    function updateClock() {
        var now = new Date();
        var hours = now.getHours().toString().padStart(2, '0');
        var minutes = now.getMinutes().toString().padStart(2, '0');
        var seconds = now.getSeconds().toString().padStart(2, '0');
        
        document.getElementById('current-time').innerText = hours + ':' + minutes + ':' + seconds;
    }

    // Memperbarui waktu setiap detik
    setInterval(updateClock, 1000);

    // Memanggil fungsi sekali untuk menampilkan waktu segera setelah halaman dimuat
    updateClock();

    //dropdown
    // Fungsi untuk menampilkan dan menyembunyikan dropdown
    function toggleDropdown() {
        const dropdown = document.getElementById('profileDropdown');
        dropdown.style.display = (dropdown.style.display === 'block') ? 'none' : 'block';
    }

    // Fungsi untuk menutup dropdown jika pengguna mengklik di luar dropdown
    window.addEventListener('click', function(event) {
        const dropdown = document.getElementById('profileDropdown');
        const accountInfo = document.querySelector('.account-info');

        // Menutup dropdown jika klik terjadi di luar elemen account-info
        if (!accountInfo.contains(event.target)) {
            dropdown.style.display = 'none';
        }
    });
    </script>
</body>
</html>