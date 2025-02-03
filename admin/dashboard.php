<?php
session_start();
require "database/config.php";
require "function/function.php";

blmlogin(); // Validasi session login

// Ambil data dashboard
$data = getSessionData($conn);

if (isset($data['error'])) {
    die($data['error']);
}

// Ambil data
$nama = htmlspecialchars($data['nama']);
$nis = htmlspecialchars($data['nis']);
$hari_sekarang = htmlspecialchars($data['hari_sekarang']);
$profile_picture = getProfilePicture($conn, $nis);

// Handle the statistics data safely
$jumlahPengguna = isset($data['jumlah_pengguna']) ? $data['jumlah_pengguna'] : 0;
$jumlahAdmin = isset($data['jumlah_admin']) ? $data['jumlah_admin'] : 0;
$totalPresensi = isset($data['total_presensi']) ? $data['total_presensi'] : 0;


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LogOnTime Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
        }

        .container {
            display: flex;
            height: 100vh;
        }

        .sidebar {
            background-color: #3b82f6;
            width: 250px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100%;
            color: white;
        }

        .sidebar h2 {
            text-align: center;
            font-size: 24px;
            margin-bottom: 30px;
            font-weight: bold;
            text-transform: uppercase;
            color: #fff;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .menu-item {
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            text-align: center;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s, border-color 0.3s;
            color: white;
            border: 2px solid transparent;
        }

        .sidebar ul li:hover {
            background-color: #3B5BB2;
        }

        #pdf:hover {
            background-color: #ed1831;
            color: white;
            font-style: bold;
        }

        .main-content {
            flex-grow: 1;
            padding: 20px;
            margin-left: 250px; /* Menggeser konten sesuai lebar sidebar */
        }

        .navbar {
            background-color: #3b82f6;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            top: 0;
            left: 250px; /* Sesuaikan dengan sidebar */
            right: 0;
            z-index: 1000;
            color: white;
        }

        .navbar a {
            text-decoration: none;
            color: inherit;
        }

        .navbar h1 {
            margin: 0;
            color: white;
        }

        .account-info {
            display: flex;
            align-items: center;
            position: relative;
        }
        .nama-akun {
            font-size: 18px;
            margin-right: 10px;
            color: white;
        }
        .icon-akun {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            object-fit: cover;
            border: 2px solid #ccc;
        }

        .header {
            text-align: center;
            margin-top: 60px; /* Tambahkan margin atas untuk menghindari tumpang tindih dengan navbar */
            margin-bottom: 20px;
        }

        h2 {
            text-align: center;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        .info-boxes {
            display: flex;
            justify-content: center;
            gap: 20px;
        }

        .box {
            width: 150px;
            height: 100px;
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            box-shadow: 3px 3px 10px rgba(0, 0, 0, 0.1);
            background-color: white;
            border: 1px solid #ddd;
        }

        .box-users { border-left: 5px solid #4f46e5; }
        .box-admins { border-left: 5px solid #10b981; }
        .box-presensi { border-left: 5px solid #ef4444; }
        .box p { font-size: 14px; color: gray; }
        .box h3 { font-size: 32px; margin-top: 5px; }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        th, td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }

        th {
            background-color: #3b82f6;
            color: white;
        }

        tbody tr:hover {
            background-color: #f1f1f1;
        }

        .footer {
            background-color: #3b82f6;
            color: white;
            padding: 10px 20px;
            text-align: center;
            font-size: 14px;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
        }

        /* Dropdown Menu Styles */
        .dropdown {
            display: none; /* Sembunyikan dropdown secara default */
            position: absolute; /* Menggunakan absolute positioning */
            top: 100%; /* Menempatkan dropdown di bawah ikon */
            right: 0; /* Posisi di sebelah kanan */
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            z-index: 999;
        }

        .dropdown a {
            display: block;
            padding: 10px;
            color: black;
            text-decoration: none;
            text-align: left;
        }

        .dropdown a:hover {
            background-color: red; /* Background merah saat hover */
            color: white; /* Warna font putih saat hover */
            font-weight: bold; /* Font bold saat hover */
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <ul>
                <li class="menu-item" onclick="location.href='registerasi.php'">Pendaftaran Akun</li>
                <li class="menu-item" onclick="location.href='list_akun.php'">Akun Terdaftar</li>
                <li class="menu-item" onclick="location.href='daftar_presensi.php'">Daftar Presensi</li>
                <!-- <li class="menu-item" id="pdf" onclick="location.href='#'">Buat Laporan PDF</li> -->
            </ul>
        </div>

        <div class="main-content">
            <div class="navbar">
                <a href="dashboard.php"><h1>LogOnTime Dashboard</h1></a>
                <div class="account-info">
                    <p class="nama-akun"><?= $nama; ?></p>
                    <img src="<?= $profile_picture; ?>" 
                         class="icon-akun" 
                         onclick="toggleDropdown()" 
                         alt="Profile Image">
                    <div class="dropdown" id="profileDropdown">
                        <a href="akun.php?nis=<?= urlencode($nis); ?>">Akun</a>
                        <a href="../logout.php">Keluar</a>
                    </div>
                </div>
            </div>

            <div class="header">
                <h2>Selamat Datang, <?= $nama; ?>!</h2>
                <p>Hari <?= $hari_sekarang; ?></p>
                <p>Jam <span id="jam_sekarang"></span></p>
            </div>

            <div class="info-boxes">
                <div class="box box-users">
                    <p>Jumlah Pengguna</p>
                    <h3><?= $jumlahPengguna; ?></h3>
                </div>
                <div class="box box-admins">
                    <p>Jumlah Admin</p>
                    <h3><?= $jumlahAdmin; ?></h3>
                </div>
                <div class="box box-presensi">
                    <p>Total Presensi</p>
                    <h3><?= $totalPresensi; ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        &copy; <?= date("Y"); ?> Husni Mubarak. All rights reserved.
    </div>

    <script>
        // Toggle dropdown visibility
        function toggleDropdown() {
            var dropdown = document.getElementById('profileDropdown');
            dropdown.style.display = (dropdown.style.display === 'block') ? 'none' : 'block';
        }

        // Close dropdown when clicking outside
        window.addEventListener('click', function(event) {
            var dropdown = document.getElementById('profileDropdown');
            var accountInfo = document.querySelector('.account-info');
            if (!accountInfo.contains(event.target)) {
                dropdown.style.display = 'none';
            }
        });

        // Update the current time
        function updateDateTime() {
            const now = new Date();
            let hours = String(now.getHours()).padStart(2, '0');
            let minutes = String(now.getMinutes()).padStart(2, '0');
            let seconds = String(now.getSeconds()).padStart(2, '0');
            const formattedTime = `${hours}:${minutes}:${seconds}`;
            document.getElementById('jam_sekarang').textContent = formattedTime;
        }

        // Update time every second
        setInterval(updateDateTime, 1000);
        updateDateTime(); // Initial time update
    </script>
</body>
</html>