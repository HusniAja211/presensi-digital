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


// Menghubungkan file koneksi database
require "database/config.php";

// Memeriksa apakah button sudah ditekan atau belum
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['regis'])) {
    // Mengambil dan membersihkan data dari form POST
    $email = $_POST['email'];
    $nama = $_POST['nama'];
    $nis = $_POST['nis'];
    $kelas = $_POST['kelas'];
    $level = $_POST['level'];
    $password = $_POST['password'];
    $cpassword = $_POST['cpassword'];

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Memeriksa apakah email dan nis sudah terpakai atau belum
    $stmt = $conn->prepare("SELECT nis, email FROM akun WHERE nis = ? OR email = ?");
    $stmt->bind_param("ss", $nis, $email);
    $stmt->execute();

    // Mengambil hasil query
    $resultcek = $stmt->get_result();

    // Menutup stmt
    $stmt->close();

    // Cek apakah ada email atau nis yang sudah terdaftar
    if ($resultcek->num_rows > 0) {
        echo "<script>
            alert('Email atau NIS sudah terdaftar!');
            window.location.href = 'admin/register.php';
            </script>";
        exit();
    }

    // Membandingkan apakah password dan cpassword sama
    if ($password !== $cpassword) {
        echo "<script>
            alert('Password dan Konfirmasi Password tidak cocok!');
            window.location.href = 'admin/register.php';
            </script>";
        exit();
    }

    // Memasukkan data akun baru ke database menggunakan prepared statement
    $stmt = $conn->prepare("INSERT INTO akun (email, nama, nis, kelas, password, level) VALUES(?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $email, $nama, $nis, $kelas, $hashedPassword, $level);

    if ($stmt->execute()) {
        // Jika berhasil memasukkan data
        echo "<script>
            alert('Pendaftaran Akun Berhasil!');
            window.location.href = 'registerasi.php';
            </script>";
    } else {
        // Jika gagal memasukkan data
        echo "<script>
            alert('Pendaftaran Akun Gagal!');
            window.location.href = 'register.php';
            </script>";
    }

    // Menutup stmt
    $stmt->close();

    // Menutup koneksi
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Akun</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: Arial, sans-serif;
            background-color: #4a78e0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            width: 400px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            background-color: #4a78e0;
            color: white;
            padding: 8px;
            border-radius: 10px;
            font-size: 18px;
        }
        label {
            margin-top: 8px;
            display: block;
            font-size: 14px;
        }
        input, select {
            width: 100%;
            padding: 8px;
            margin: 5px 0 12px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        input:focus, select:focus {
            outline: none;
            border-color: #4a78e0;
        }
        .buttons {
            display: flex;
            justify-content: space-between;
        }
        .btn {
            width: 48%;
            padding: 8px;
            background-color: #4a78e0;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
        }
        .btn:hover {
            background-color: #395bb5;
        }
        #kembali {
            background-color: #777;
        }
        #kembali:hover {
            background-color: #555;
        }
    </style>
</head>
<body>

<div class="container">
    <form class="register-form" method="POST">
        <h2>Pendaftaran Akun</h2>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" autocomplete="off" required>

        <label for="nama">Nama</label>
        <input type="text" id="nama" name="nama" autocomplete="off" required>

        <label for="nis">NIS</label>
        <input type="text" id="nis" name="nis" autocomplete="off" minlength="4" maxlength="4" required>

        <label for="kelas" name="kelas" required>Kelas</label>
        <select name="kelas" id="kelas">
        <option value="" disabled selected>Kelas Anda</option>
        <option value="1">XI RPL 1</option>
        <option value="2">XI RPL 2</option>
        <option value="3">Kelas Admin</option>
        </select>
        
        <label for="password">Password</label>
        <input type="password" id="password" name="password" minlength="8" maxlength="8" autocomplete="off" required>

        <label for="confirm_password">Konfirmasi Password</label>
        <input type="password" id="confirm_password" name="cpassword" autocomplete="off" minlength="8" maxlength="8" required>

        <label for="level">Level</label>
        <select id="level" name="level" required>
            <option value="" disabled selected>Pilih Level</option>
            <option value="1">admin</option>
            <option value="2">user</option>
        </select>

        <div class="buttons">
            <button type="button" class="btn" id="kembali">Kembali</button>
            <button type="submit" class="btn daftar-btn" name="regis">Daftar</button>
        </div>
    </form>
</div>

<script>
    document.getElementById('kembali').addEventListener('click', function() {
        window.location.href = 'dashboard.php';  // Mengarahkan ke dashboard.php
    });
</script>
</body>
</html>
