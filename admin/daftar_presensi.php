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

$nama = htmlspecialchars($data['nama']);
$nis = htmlspecialchars($data['nis']);
$profile_picture = getProfilePicture($conn, $nis);

// Pagination dan Search
$limit = 5; // Jumlah data per halaman
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1; // Mendapatkan nomor halaman, minimal 1
$offset = ($page - 1) * $limit;

$searchNIS = isset($_GET['nis']) ? htmlspecialchars($_GET['nis']) : '';
$searchEmail = isset($_GET['email']) ? htmlspecialchars($_GET['email']) : '';
$searchNama = isset($_GET['nama']) ? htmlspecialchars($_GET['nama']) : '';
$searchKelas = isset($_GET['kelas']) ? htmlspecialchars($_GET['kelas']) : '';
$searchTanggal = isset($_GET['searchTanggal']) ? $_GET['searchTanggal'] : '';
$searchStatus = isset($_GET['status']) ? htmlspecialchars($_GET['status']) : '';

// Query Total Data
$totalQuery = "SELECT COUNT(*) as total FROM presensi_siswa WHERE 
    (fnis LIKE ? OR email LIKE ? OR nama LIKE ? OR kelas LIKE ? OR hari LIKE ? OR status LIKE ?)";
$searchPatternNIS = '%' . $searchNIS . '%';
$searchPatternEmail = '%' . $searchEmail . '%';
$searchPatternNama = '%' . $searchNama . '%';
$searchPatternKelas = '%' . $searchKelas . '%';
$searchPatternDate = '%' . $searchTanggal . '%';
$searchPatternStatus = '%' . $searchStatus . '%';

$stmt = $conn->prepare($totalQuery);
$stmt->bind_param('ssssss', $searchPatternNIS, $searchPatternEmail, $searchPatternNama, $searchPatternKelas, $searchPatternDate, $searchPatternStatus);
$stmt->execute();
$totalResult = $stmt->get_result();
$totalData = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalData / $limit);

// Query Data
$dataQuery = "
    SELECT id, email, nama, fnis, kelas, hari, jam, status, bukti 
    FROM presensi_siswa 
    WHERE (fnis LIKE ? OR email LIKE ? OR nama LIKE ? OR kelas LIKE ? OR hari LIKE ? OR status LIKE ?)
    ORDER BY hari DESC 
    LIMIT ?, ?";
$stmt = $conn->prepare($dataQuery);
$stmt->bind_param('ssssssii', $searchPatternNIS, $searchPatternEmail, $searchPatternNama, $searchPatternKelas, $searchPatternDate, $searchPatternStatus, $offset, $limit);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Presensi</title>
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

        .search-container input{
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
            font-size: 14px; /* Mengurangi ukuran font dalam tabel */
        }

        th, td {
            padding: 10px; /* Mengurangi padding */
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

        .pagination {
            text-align: center;
            margin-top: 20px;
        }

        .pagination a {
            margin: 0 5px;
            padding: 6px 10px; /* Memperkecil padding pada pagination */
            border: 1px solid #3b82f6;
            color: #3b82f6;
            text-decoration: none;
            border-radius: 5px;
        }

        #sunting{
            color: #57f754 ;
        }

        #sunting:hover{
        }

        #hapus{
            color: #e3090d;
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
                <li class="menu-item" id="pdf" onclick="location.href='pdf/pdfpresensi.php'">Buat Laporan PDF</li>
                <li class="menu-item" onclick="location.href='pdf/pdfpresensi.php'">Rekap Presensi</li>
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

            <h2>Data Presensi Tercatat</h2>
            <div class="search-container">
                <form method="GET">
                    <input type="text" name="nis" placeholder="Cari Berdasarkan NIS" value="<?= $searchNIS; ?>">
                    <input type="email" name="email" placeholder="Cari Berdasarkan Email" value="<?= $searchEmail; ?>">
                    <input type="text" name="nama" placeholder="Cari Berdasarkan Nama" value="<?= $searchNama; ?>">
                    <input type="text" name="kelas" placeholder="Cari Berdasarkan Kelas" value="<?= $searchKelas; ?>">
                    <input type="date" name="tanggal" placeholder="Cari Berdasarkan Tanggal" value="<?= $searchTanggal; ?>">
                    <input type="text" name="status" placeholder="Cari Berdasarkan Status" value="<?= $searchStatus; ?>">
                    
                    <button type="submit" class="search-button">Cari</button>
                    <!-- <button class="search-button" onclick="">Rekap Presensi Hari Ini</button> -->
                </form>
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
                    <th>Bukti</th>
                    <th>Rincian Presensi</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']); ?></td>
                        <td><?= htmlspecialchars($row['fnis']); ?></td>
                        <td><?= htmlspecialchars($row['email']); ?></td>
                        <td><?= htmlspecialchars($row['nama']); ?></td>
                        <td><?= htmlspecialchars($row['kelas']); ?></td>
                        <td><?= htmlspecialchars($row['hari']); ?></td>
                        <td><?= htmlspecialchars($row['jam']); ?></td>
                        <td><?= htmlspecialchars($row['status']); ?></td>
                        <td><?= htmlspecialchars($row['bukti']); ?></td>                   
                        <td>        
                            <a href="rincianpresensi.php?id=<?= htmlspecialchars($row['id']); ;?>" id="rincian" style="color: #0084ff">Rincian Presensi</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>

            <div class="pagination">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?page=<?= $i; ?>&nis=<?= urlencode($searchNIS); ?>&email=<?= urlencode($searchEmail); ?>&nama=<?= urlencode($searchNama); ?>&kelas=<?= urlencode($searchKelas); ?>&searchTanggal=<?= urlencode($searchTanggal); ?>&status=<?= urlencode($searchStatus); ?>">
            <?= $i; ?>
        </a>
    <?php endfor; ?>
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