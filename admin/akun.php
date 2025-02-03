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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akun <?= htmlspecialchars($userdata['nama']);?></title>
    <style>
        html, body {
            background-color: #4A73E0;
            height: 100%;
        }
        a {
            color: black;
            text-decoration: none;
        }
        nav, .div {
            display: flex;
            background: white;
            align-items: center;
            justify-content: space-between;
        }
        .div {
            width: 85%;
        }
        .nama {
            margin-left: 10px;
            color: #4A73E0;
        }
        ul {
            display: flex;
        }
        ul li {
            list-style: none;
            margin-right: 15px;
            cursor: pointer;
        }
        .account {
            display: flex;
            align-items: center;
            height: 100%;
            cursor: pointer;
            margin-right: 10px;
        }
        .fotoA {
            width: 40px;
            height: 40px;
            border-radius: 10px;
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
        .kembali, .ubah {
            height: 45px;
            font-size: 16px;
            font-weight: bold;
            background-color: #4a90e2; 
            color: white;
            border: none;
            border-radius: 5px; 
            cursor: pointer;
            margin-right: 10px; 
            transition: background-color 0.3s ease;
        }

        .kembali {
            background-color: #d9534f; 
        }

        .kembali:hover {
            background-color: #c9302c; 
        }

        .ubah:hover {
            background-color: #3a78c2; 
        }

        .Mdata {
            display: flex;
            flex-wrap: wrap; 
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
        .data input, .data select {
            width: 100%;
            height: 35px;
            border: 1px solid #000; /* Border hitam */
            border-radius: 5px;
            padding: 5px 10px;
            font-size: 16px;
            color: #333;
            background: #f9f9f9;
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
       <img class="fotoP" 
            src="<?= htmlspecialchars($profile_picture); ?>"
            alt="Foto Profil">
    </div>
    <div class="Mdata">
        <div class="data">
            <label>Nama</label>
            <input type="text" disabled value="<?= htmlspecialchars($userdata['nama']); ?>">
        </div>
        <div class="data">
            <label>Email</label>
            <input type="text" disabled value="<?= htmlspecialchars($userdata['email']); ?>">
        </div>
        <div class="data">
            <label>Kelas</label>
            <input type="text" disabled value="<?= htmlspecialchars($userdata['kelas']); ?>">
        </div>
        <div class="data">
            <label>Password</label>
            <input type="password" disabled value="********">
        </div>
        <div class="data">
            <label>Foto Diri</label>
            <input type="text" disabled value="<?= htmlspecialchars($userdata['pfp']); ?>">
        </div>
        <div class="data">
            <label>Level</label>
            <input type="text" disabled value="<?= htmlspecialchars($userdata['level']); ?>">
        </div>
    </div>
    <div class="operator">
        <a href="dashboard.php"><button class="kembali">Kembali</button></a>
        <a href="updateakun.php?nis=<?= $userdata['nis'];?>"><button class="ubah">Sunting Akun</button></a>
    </div>
</section>
</body>
</html> 