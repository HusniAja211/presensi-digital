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
$profile_picture = $data['profile_picture'];

// Mengambil data presensi dari database menggunakan prepared statement
$stmt = $conn->prepare("SELECT id, fnis, nama, status, hari, jam, bukti FROM presensi_siswa WHERE fnis = ? ORDER BY hari DESC");
$stmt->bind_param("s", $nis);
$stmt->execute(); // Execute the statement
$result = $stmt->get_result(); // Get the result set
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
            overflow-x: hidden; /* Prevent horizontal scrolling */
            background-color: #fff;
            font-family: Arial, sans-serif;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        body {
            display: flex;
            flex-direction: column;
            justify-content: space-between; /* Ensure header and footer stay in place */
            align-items: center;
            min-height: 100vh;
            box-sizing: border-box;
        }

        a{
            color: blue;
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

        .header .logo {
            font-size: 24px;
            font-weight: bold;
            color: white;
        }

        .header a{
            text-decoration: none;
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
            margin-right: 8px; /* Sesuaikan margin agar lebih dekat */
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
            width: 120px; /* Perkecil lebar dropdown */
        }

        .dropdown a {
            display: block;
            padding: 8px; /* Kurangi padding agar lebih kecil */
            font-size: 14px; /* Perkecil ukuran font */
            color: black;
            text-decoration: none;
            text-align: left;
        }

        .dropdown a:hover {
            background-color: red;
            color: white;
            font-weight: bold;
        }

        .content {
            text-align: center;
            margin: 40px;
            flex: 1; /* Allow content to grow and fill available space */
            width: 100%; /* Ensure it takes the full width */
            max-width: 900px; /* Restrict the width for readability */
        }

        .attendance-table {
            width: 100%; /* Make table responsive */
            border-collapse: collapse;
            border: 1px solid #000;
            margin-top: 20px;
        }

        .attendance-table th, .attendance-table td {
            border: 1px solid #000;
            padding: 10px;
            text-align: center;
        }

        .attendance-table th {
            background-color: #4A73E0;
            color: white;
        }

        .presensi-btn {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #4A73E0;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        h2 {
            margin-bottom: 20px;
            font-size: 24px;
        }

        .footer {
            background-color: #4A73E0;
            width: 100%;
            padding: 10px 30px;
            font-size: 16px;
            color: #ffffff;
            box-sizing: border-box;
            position: relative; 
            text-align: center;
        }
    </style>
</head>
<body>
<div class="header">
    <a href="homepage.php"><div class="logo">LogOnTime</div></a>
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
        <h2>Riwayat Presensi</h2>
        <table class="attendance-table">
            <thead>
                <tr>
                    <th>NIS</th>
                    <th>Nama</th>
                    <th>Status</th>
                    <th>Hari</th>
                    <th>Jam</th>
                    <th>Bukti</th>
                    <th>Detail</th> <!-- Kolom Detail ditambahkan -->
                </tr>
            </thead>
            <tbody>
    <?php
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                <td>{$row['fnis']}</td>
                <td>{$row['nama']}</td>
                <td>{$row['status']}</td>
                <td>{$row['hari']}</td>
                <td>{$row['jam']}</td>
                <td>{$row['bukti']}</td> 
                <td><a href='detail_presensi.php?id={$row['id']}'>Lihat Rincian</a></td> 
                </tr>";
        }
    } else {
        echo "<tr><td colspan='8'>Tidak ada data presensi</td></tr>";
    }
    ?>
</tbody>

        </table>
        <a href="presensi_sekarang.php"><button class="presensi-btn">Presensi Sekarang</button></a>
    </div>

    <div class="footer">
        &copy; <?= date("Y"); ?> Husni Mubarak. All rights reserved.
    </div>

    <script>
          //dropdown menu
        // Update the time every second
        setInterval(updateDateTime, 1000);

        // Initialize the time on page load
        updateDateTime();

        //dropdown
        function toggleDropdown() {
        var dropdown = document.getElementById('profileDropdown');
        dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        }

        window.onclick = function(event) {
            var dropdown = document.getElementById('profileDropdown');
            if (!event.target.closest('.account-info')) {
                dropdown.style.display = 'none';
            }
        }
    </script>

</body>
</html>
