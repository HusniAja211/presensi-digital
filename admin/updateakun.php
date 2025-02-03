<?php
session_start();
require "database/config.php";
require "function/function.php";
blmlogin();

// Periksa apakah ada GET atau POST parameter untuk NIS
if (isset($_GET['nis'])) {
    $nisg = $_GET['nis'];
} elseif (isset($_POST['nishidden'])) { // Gunakan 'nishidden' dari POST
    $nisg = $_POST['nishidden'];
} else {
    echo "<script>
        alert('Parameter NIS tidak ditemukan.');
        window.history.back();
    </script>";
    exit;
}

// Ambil data pengguna berdasarkan NIS
$userdata = getDatabaseData($conn, $nisg);
$path = "../pfp/";
$fileName = $userdata['pfp'];
$profile_picture = $path . $fileName;

// Jika form disubmit, proses data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $nishidden = $_POST['nishidden'];
    $updateFields = [];
    $params = [];

    // Proses nama
    if (!empty($_POST['nama'])) {
        $updateFields[] = "nama = ?";
        $params[] = $_POST['nama'];
    }

    // Proses email
    if (!empty($_POST['email'])) {
        $updateFields[] = "email = ?";
        $params[] = $_POST['email'];
    }

    // Proses kelas
    if (!empty($_POST['kelas'])) {
        $updateFields[] = "kelas = ?";
        $params[] = $_POST['kelas'];
    }

    // Proses password
    if (!empty($_POST['password'])) {
        $updateFields[] = "password = ?";
        $params[] = password_hash($_POST['password'], PASSWORD_DEFAULT);
    }

    // Proses file upload
    $upfile = $_FILES['upfile'];
    if ($upfile['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $upfile['tmp_name'];
        $fileName = $upfile['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png'];

        if (in_array($fileExtension, $allowedExtensions)) {
            $newFileName = $nishidden . '_' . $userdata['nama'] . '.' . $fileExtension;
            $uploadDir = '../pfp/';
            $destPath = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $updateFields[] = "pfp = ?";
                $params[] = $newFileName;
            } else {
                echo "<script>alert('Gagal mengunggah file.'); window.history.back();</script>";
                exit;
            }
        } else {
            echo "<script>alert('Format file tidak didukung.'); window.history.back();</script>";
            exit;
        }
    }

    // Jika tidak ada field yang diisi
    if (empty($updateFields)) {
        echo "<script>alert('Tidak ada data yang diubah.'); window.history.back();</script>";
        exit;
    }

    // Tambahkan NIS untuk WHERE klausa
    $params[] = $nishidden;

    // Bangun query
    $query = "UPDATE akun SET " . implode(', ', $updateFields) . " WHERE nis = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);

    // Eksekusi query
    if ($stmt->execute()) {
        echo "<script>alert('Data berhasil diperbarui.'); window.location.href = 'list_akun.php';</script>";
    } else {
        echo "<script>alert('Terjadi kesalahan saat memperbarui data.'); window.history.back();</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akun | <?= htmlspecialchars ($userdata['nama']);?></title>
    <style>
        html, body{
            background-color: #4A73E0;
            height:100%;
        }
        a{
            color:black;
            text-decoration:none;
        }
        nav, .div{
            display:flex;
            background:white;
            align-items:center;
            justify-content:space-between;
        }
        .div{
            width:85%;
        }
        .nama{
            margin-left:10px;
            color: #4A73E0;
        }
        ul{
            display:flex;
        }
        ul li{
            list-style:none;
            margin-right:15px;
            cursor:pointer;
        }
        .account{
            display:flex;
            align-items:center;
            height:100%;
            cursor:pointer;
            margin-right:10px;
        }
        .fotoA{
            width: 40px;
            height:40px;
            border-radius:10px;
        }
        section {
            background: rgba(255, 255, 255, 0.8);
            padding: 30px 40px;
            margin-top: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            font-family: Verdana, sans-serif;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }
        .profil {
            display: flex;
            flex-direction: column;
            align-items: center;
            font-size: 22px;
            color: #333;
        }
        .fotoP {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin-bottom: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            object-fit: cover;
        }
        .out{
            position:absolute;
            background:white;
            margin-left: 1340px;
            margin-top:150px;
            border:1px solid black;
            width: 55px;
            padding:20px 45px ;
            display: none;
            opacity: 0;
            transition: opacity 0.5s ease;
        }

        .kembali, .ubah {
            height: 45px;
            font-size: 16px;
            font-weight: bold;
            background-color: #4a90e2; /* Menggunakan warna yang sama */
            color: white;
            border: none;
            border-radius: 5px; /* Sesuaikan border-radius dengan input */
            cursor: pointer;
            margin-right: 10px; /* Untuk memberi jarak dengan tombol lainnya */
            transition: background-color 0.3s ease;
        }

        .kembali {
            background-color: #d9534f; /* Warna tombol kembali */
        }

        .kembali:hover {
            background-color: #c9302c; /* Warna saat hover */
        }

        .ubah:hover {
            background-color: #3a78c2; /* Warna saat hover untuk tombol sunting */
        }

        .Mdata {
            display: flex;
            flex-wrap: wrap; /* Tambahkan ini */
            justify-content: space-around;
            flex-direction: row;
            gap: 20px;
            margin-top: 20px;
        }
        
        .data {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
            max-width: 250px;
            margin-right: 10px;
        }

        .data label {
            font-size: 14px;
            color: #555;
            margin-bottom: 5px;
        }
        .data input {
            width: 100%;
            height: 35px;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 5px 10px;
            font-size: 16px;
            color: #333;
            background: #f9f9f9;
        }
        .data input {
            background: #f0f0f0;
            color: #777;
        }
        .operator {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
<section>
    <div class="profil">
        <img class="fotoP" src="<?= htmlspecialchars($profile_picture); ?>" alt="Foto Profil">
        <p><?= htmlspecialchars($userdata['nis']); ?></p>
    </div>

    <form action="updateakun.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="nishidden" value="<?= htmlspecialchars($nisg); ?>">
        <div class="Mdata">
            <div class="data">
                <label>Nama</label>
                <input type="text" name="nama" value="<?= htmlspecialchars($userdata['nama']); ?>">
            </div>
            <div class="data">
                <label>Email</label>
                <input type="text" name="email" value="<?= htmlspecialchars($userdata['email']); ?>" autocomplete="off">
            </div>
            <div class="data">
                <label>Kelas</label>
                <input type="text" name="kelas" value="<?= htmlspecialchars($userdata['kelas']); ?>">
            </div>
            <div class="data">
                <label>Password</label>
                <input type="password" name="password" minlength="8" maxlength="8" autocomplete="off">
            </div>
            <div class="data">
                <label>Foto Diri</label>
                <input type="file" name="upfile" accept=".jpg, .jpeg, .png">
            </div>
            <div class="data">
                <label>Level</label>
                <input type="text" name="level" value="<?= htmlspecialchars($userdata['level']); ?>">
            </div>
        </div>
        <div class="operator">
            <button class="ubah" type="submit" name="submit">Selesai Menyunting</button>
            <button class="kembali" type="button" onclick="window.history.back();">Kembali</button>
        </div>
    </form>
</section>
</body>
</html>
