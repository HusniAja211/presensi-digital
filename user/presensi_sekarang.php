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
$hari_sekarang = $data['hari_sekarang'];
$profile_picture = $data['profile_picture'];
$email = $data['email'];
$nis = $data['nis']; 
$nama = $data['nama'];
$kelas = $data['kelas'];
$level = $data['level'];

// Mendapatkan tanggal hari ini
date_default_timezone_set('Asia/Jakarta');
$hari = date("Y-m-d");
$jamsekarang = date("H:i");

// Mendefinisikan waktu presensi
$pawal = "06:30"; // waktu mulai presensi
$pakhir = "24:00"; // waktu selesai presensi
$bolehpresensi = ($jamsekarang >= $pawal && $jamsekarang <= $pakhir);

// Logika presensi
if (isset($_POST['kirim'])) {
    // Mengambil data dari form
    $status = $_POST['status'];
    $bukti = '';

    // Pengecekan file yang diupload
    if (isset($_FILES['bukti']) && $_FILES['bukti']['error'] == UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['bukti']['tmp_name'];
        $fileName = $_FILES['bukti']['name']; // Nama asli file
        $fileType = pathinfo($fileName, PATHINFO_EXTENSION); // Ekstensi file

        // Validasi jenis file
        $allowedFileTypes = array('jpg', 'jpeg', 'png');
        if (in_array($fileType, $allowedFileTypes)) {
            // Nama unik untuk file yang diupload (menggunakan $nis dan $hari)
            $uniqueFileName = $nis . '_' . $hari . '.' . $fileType; // Format idpresensi_NIS_Hari.ext
            $uploadDir = 'bukti_presensi/'; // Direktori untuk menyimpan file
            $destination = $uploadDir . $uniqueFileName;

            if (move_uploaded_file($fileTmpPath, $destination)) {
                $bukti = $uniqueFileName; // Simpan nama file ke variabel bukti
            } else {
                echo "<script>alert('Gagal mengunggah bukti!');</script>";
            }
        } else {
            echo "<script>alert('Format file tidak valid!');</script>"; 
        }
    }

    // Cek apakah pengguna sudah presensi hari ini
    $check_stmt = $conn->prepare("SELECT COUNT(*) FROM presensi_siswa WHERE fnis = ? AND hari = ?");
    $check_stmt->bind_param("ss", $nis, $hari);
    $check_stmt->execute();
    $check_stmt->bind_result($count);
    $check_stmt->fetch();
    $check_stmt->close();

    // Simpan data ke database hanya jika dalam waktu yang ditentukan dan belum presensi
    if ($bolehpresensi) {
        if ($count > 0) {
            echo "<script>
            alert('Anda sudah presensi hari ini!');
            window.location.href = 'riwayat_presensi.php';
            </script>";
        } else {
            $insert_stmt = $conn->prepare("INSERT INTO presensi_siswa (email, fnis, nama, hari, status, bukti) VALUES (?, ?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("ssssss", $email, $nis, $nama, $hari, $status, $bukti);
            
            if ($insert_stmt->execute()) {
                echo "<script>alert('Presensi berhasil dicatat!'); window.location.href = 'riwayat_presensi.php';</script>";
            } else {
                echo "<script>alert('Gagal mencatat presensi!');</script>";
            }
            $insert_stmt->close();
        }
    } else {
        echo "<script>alert('Waktu presensi sudah ditutup!');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presensi <?= htmlspecialchars($nama) . ' ' . htmlspecialchars($hari_sekarang); ?></title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Roboto', sans-serif;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(135deg, #4a90e2, #5b82d6);
            color: #333;
        }

        .container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
        }

        .form-box {
            background-color: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h2 {
            text-align: center;
            background-color: #5b82d6;
            color: #fff;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .input-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-size: 0.9rem;
            color: #555;
        }

        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 1rem;
            background-color: #f9f9f9;
            transition: all 0.3s;
        }

        input:focus, select:focus {
            border-color: #5b82d6;
            outline: none;
            box-shadow: 0 0 5px rgba(91, 130, 214, 0.3);
        }

        .button-group {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        button {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: bold;
            transition: background-color 0.3s, transform 0.2s;
            cursor: pointer;
        }

        button[type="button"] {
            background-color: #f44336;
            color: white;
        }

        button[type="button"]:hover {
            background-color: #d32f2f;
        }

        button[type="submit"] {
            background-color: #5b82d6;
            color: #fff;
        }

        button[type="submit"]:hover {
            background-color: #3a5ba0;
            transform: scale(1.05);
        }

        .end-message {
            text-align: center;
            background-color: #ffcccb;
            color: #b22222;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            margin-top: 20px;
            animation: slideIn 0.4s ease;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        a {
            text-decoration: none;
        }

        a button {
            display: inline-block;
            width: 100%;
        }

    </style>
</head>
<body>
<div class="container">
    <?php if($bolehpresensi): ?>
        <div class="form-box">
            <h2>Presensi Sekarang</h2>
            <form action="presensi_sekarang.php" method="post" enctype="multipart/form-data">
                <div class="input-group">
                    <label for="email">Email</label>
                    <input type="text" id="email" name="email" value="<?= $email; ?>" required readonly>
                </div>
                <div class="input-group">
                    <label for="nis">NIS</label>
                    <input type="text" id="nis" name="nis" value="<?= $nis; ?>" required readonly>
                </div>
                <div class="input-group">
                    <label for="nama">Nama</label>
                    <input type="text" id="nama" name="nama" value="<?= $nama; ?>" required readonly>
                </div>
                <div class="input-group">
                    <label for="hari">Hari</label>
                    <input type="text" id="hari" name="hari" value="<?= htmlspecialchars($hari_sekarang); ?>" required readonly>
                </div>
                <div class="input-group">
                    <label for="status">Keterangan</label>
                    <select id="status" name="status" required onchange="toggleInput()">
                        <option value="" disabled selected>Status</option>
                        <option value="1">Hadir</option>
                        <option value="2">Izin</option>
                        <option value="3">Sakit</option>
                    </select>
                </div>
                <div class="input-group" id="keteranganInput" style="display: none;">
                    <label for="bukti">Bukti</label>
                    <input type="file" id="bukti" name="bukti" accept="iamge/png, image/jpeg">
                </div>
                <div class="button-group">
                    <a href="riwayat_presensi.php"><button type="button">Kembali</button></a>
                    <button type="submit" name="kirim">Kirim</button>
                </div>
            </form>
        </div>
    <?php else: ?>
        <div class="end-message">
            <p>Waktu Presensi Belum Dimulai <br> atau Telah Selesai</p>
            <p>Pukul: <?= $jamsekarang; ?></p>
            <p>Tanggal: <?= htmlspecialchars($hari_sekarang); ?></p>
            <a href="riwayat_presensi.php"><button>Kembali</button></a>
        </div>
    <?php endif; ?>
</div>
<script>
    function toggleInput() {
        const statusSelect = document.getElementById('status');
        const keteranganInput = document.getElementById('keteranganInput');
        if (statusSelect.value === '2' || statusSelect.value === '3') {
            keteranganInput.style.display = 'block';
        } else {
            keteranganInput.style.display = 'none';
            document.getElementById('bukti').value = ''; // Reset input
        }
    }
</script>
</body>
</html>
