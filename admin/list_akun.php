<?php
session_start();
require "database/config.php";
require "function/function.php";

blmlogin(); // Validasi session login

// Ambil data dashboard
$data = getSessionData($conn);

// Ambil data
$nama = htmlspecialchars($data['nama']);
$nis = htmlspecialchars($data['nis']);
$profile_picture = getProfilePicture($conn, $nis);

// Mengambil parameter pencarian dari query string dengan sanitasi
$searchEmail = isset($_GET['email']) ? htmlspecialchars(trim($_GET['email'])) : '';
$searchNIS = isset($_GET['nis']) ? htmlspecialchars(trim($_GET['nis'])) : '';
$searchNama = isset($_GET['nama']) ? htmlspecialchars(trim($_GET['nama'])) : '';
$searchLevel = isset($_GET['level']) ? htmlspecialchars(trim($_GET['level'])) : '';

// Pagination
$limit = 5; // Jumlah data per halaman
$page = max(1, isset($_GET['page']) ? (int)$_GET['page'] : 1); 
$offset = ($page - 1) * $limit; 

// Mengambil total data
$totalData = getTotalData($conn, $searchEmail, $searchNIS, $searchNama, $searchLevel);
$totalPages = ceil($totalData['total'] / $limit); 

// Mengambil data untuk halaman saat ini
$result = getData($conn, $searchEmail, $searchNIS, $searchNama, $searchLevel, $limit, $offset);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akun Pengguna</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
        }

        a{
            text-decoration: none;
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
            margin-left: 250px; /* Pastikan margin-left sesuai dengan lebar sidebar */
            margin-top: 60px; /* Menjaga jarak dengan navbar */
        }

        .navbar {
            background-color: #3b82f6;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            top: 0;
            left: 250px;
            right: 0;
            z-index: 1000;
            color: white;
        }

        .navbar h1 {
            color: white;
            text-decoration: none; /* Pastikan tidak ada garis bawah */
            margin: 0; /* Menghapus margin default */
            border: none; /* Hapus garis border jika ada */
        }

        .navbar a {
            text-decoration: none; /* Pastikan tidak ada garis bawah pada link */
            color: white; /* Warna teks untuk link */
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

        .dropdown {
            display: none; /* Sembunyikan dropdown secara default */
            position: absolute; /* Posisi absolut untuk dropdown */
            top: 40px; /* Jarak dropdown dari atas */
            right: 0; /* Jarak dropdown dari sisi kanan */
            background-color: white; /* Latar belakang dropdown */
            border: 1px solid #ccc; /* Batas dropdown */
            border-radius: 5px; /* Sudut melingkar */
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); /* Bayangan dropdown */
            z-index: 1000; /* Pastikan dropdown di atas elemen lain */
        }

        .dropdown a {
            display: block; /* Menampilkan item dropdown sebagai blok */
            padding: 10px; /* Jarak dalam untuk item dropdown */
            color: #333; /* Warna teks item dropdown */
            text-decoration: none; /* Hapus garis bawah */
        }

        .dropdown a:hover {
            background-color: red; /* Background merah saat hover */
            color: white; /* Warna font putih saat hover */
            font-weight: bold; /* Font bold saat hover */
        }

        h2 {
            text-align: center;
            margin: 0; /* Mengatur margin untuk menjaga konsistensi */
            margin-bottom: 20px;
        }

        .search-container {
            display: flex;
            justify-content: center; /* Rata tengah */
            margin-bottom: 20px; /* Jarak bawah */
        }

        .search-container input,
        .search-container select {
            padding: 10px; /* Jarak dalam */
            margin-right: 10px; /* Jarak kanan */
            border: 1px solid #ccc; /* Warna batas */
            border-radius: 5px; /* Sudut melingkar */
            width: 150px; /* Lebar tetap untuk input */
        }

        .search-button {
            padding: 10px 20px; /* Jarak dalam atas-bawah dan kiri-kanan */
            background-color: #3b82f6; /* Warna latar belakang tombol */
            color: white; /* Warna teks */
            border: none; /* Hapus batas */
            border-radius: 5px; /* Sudut melingkar */
            cursor: pointer; /* Kursor pointer saat hover */
            transition: background-color 0.3s; /* Transisi halus untuk efek hover */
        }

        .search-button:hover {
            background-color: #357ae8; /* Warna lebih gelap saat hover */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
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

        tr:hover {
            background-color: #808080;
            color: #ffff;
        }

        #sunting{
            color: #57f754 ;
        }

        #sunting:hover{
        }

        #hapus{
            color: #e3090d;
        }

        .pagination {
            text-align: center;
            margin-top: 20px;
        }

        .pagination a {
            margin: 0 5px;
            padding: 8px 12px;
            border: 1px solid #3b82f6;
            color: #3b82f6;
            text-decoration: none;
            border-radius: 5px;
        }

        .pagination a.active {
            background-color: #3b82f6;
            color: white;
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
            border: none; /* Hapus garis border jika ada */
            box-shadow: none; /* Hapus bayangan jika ada */
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar" id="sidebar">
            <ul>
                <li class="menu-item" onclick="location.href='registerasi.php'">Pendaftaran Akun</li>
                <li class="menu-item" onclick="location.href='list_akun.php'">Akun Terdaftar</li>
                <li class="menu-item" onclick="location.href='daftar_presensi.php'">Daftar Presensi</li>
                <li class="menu-item" id="pdf" onclick="location.href='pdf/pdfakun.php'">Buat Laporan PDF</li>
            </ul>
        </div>
        
        <div class="main-content" id="main-content">
            <div class="navbar">
                <a href="dashboard.php"><h1>LogOnTime Dashboard</h1></a>
                <div class="account-info">
                    <p class="nama-akun"><?= htmlspecialchars($nama); ?></p>
                    <img src="<?= htmlspecialchars($profile_picture) ?>"
                    class="icon-akun" 
                    onclick="toggleDropdown()" 
                    alt="Profile Picture">
                    <div class="dropdown" id="profileDropdown">
                        <a href="akun.php?nis=<?= $nis; ?>">Akun</a>
                        <a href="../logout.php">Keluar</a>
                    </div>
                </div>
            </div>

            <h2>Data Akun Terdaftar</h2>
            <div class="search-container">
                <form method="GET">
                    <input type="text" name="email" placeholder="Search by Email" value="<?= $searchEmail; ?>">
                    <input type="text" name="nis" placeholder="Search by NIS" value="<?= $searchNIS; ?>">
                    <input type="text" name="nama" placeholder="Search by Nama" value="<?= $searchNama; ?>">
                    <select name="level">
                        <option value="">All Levels</option>
                        <option value="admin" <?= ($searchLevel == 'admin') ? 'selected' : ''; ?>>Admin</option>
                        <option value="user" <?= ($searchLevel == 'user') ? 'selected' : ''; ?>>User</option>
                    </select>
                    <button type="submit" class="search-button">Search</button>
                </form>
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
                        <th>Opsi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['nis']); ?></td>
                            <td><?= htmlspecialchars($row['email']); ?></td>
                            <td><?= htmlspecialchars($row['nama']); ?></td>
                            <td><?= htmlspecialchars($row['kelas']); ?></td>
                            <td><?= htmlspecialchars($row['pfp']); ?></td>
                            <td><?= htmlspecialchars($row['level']); ?></td>
                            <td>
                            <a href="updateakun.php?nis=<?= htmlspecialchars($row['nis']); ?>" id="sunting">Sunting</a>
                            |
                            <a href="function/deleteakun.php?nis=<?= htmlspecialchars($row['nis']); ?>" onclick="return confirm('Yakin ingin menghapus akun ini?');" id="hapus"> Hapus </a>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <div class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?= $i; ?>&email=<?= urlencode($searchEmail); ?>&nis=<?= urlencode($searchNIS); ?>&nama=<?= urlencode($searchNama); ?>&level=<?= urlencode($searchLevel); ?>" class="<?= ($page == $i) ? 'active' : ''; ?>">
                        <?= $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        </div>
    </div>

    <div class="footer">
        &copy; <?= date("Y"); ?> Husni Mubarak. All rights reserved.
    </div>
    <script>
    // Script untuk menangani klik pada ikon akun
    function toggleDropdown() {
        const dropdown = document.getElementById('profileDropdown');
        dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
    }

    // Menutup dropdown jika pengguna mengklik di luar
    window.onclick = function(event) {
        if (!event.target.matches('.icon-akun')) {
            const dropdowns = document.getElementsByClassName("dropdown");
            for (let i = 0; i < dropdowns.length; i++) {
                dropdowns[i].style.display = "none";
            }
        }
    }
    </script>
</body>
</html>