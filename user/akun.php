<?php
session_start();
require "function/function.php";
sdhlogin(); // Cek login
require "../admin/database/config.php";

// Ambil data dari GET
if (isset($_GET['nis']) && !empty($_GET['nis'])) {
    $nisg = htmlspecialchars($_GET['nis']); // Hindari injeksi XSS
} else {
    die("Parameter NIS tidak ditemukan.");
}

$data = getDatabaseData($conn, $nisg);

// Gunakan data yang diambil
$emaild = $data['email'];
$nisd = $data['nis']; 
$namad = $data['nama'];
$varcharpfp = $data['pfp'];
$kelasd = $data['kelas'];
$leveld = $data['level'];

$data2 = getSessionData($conn);
$profile_picture = $data2['profile_picture'];
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akun -- <?= $namad;?></title>
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

        /* When .out is shown */
        .out.show {
        display: block;
        opacity: 1;
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
        .data input:disabled {
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
        <img class="fotoP" 
        src="<?= htmlspecialchars ($profile_picture); ?>">
        <p><?= $namad ?></p>
        <p><?= $nisd ?></p>
    </div>
    <div class="Mdata">
        <div class="data">
            <label>Nama</label>
            <input type="text" disabled value="<?= $namad ?>">
        </div>
        <div class="data">
            <label>Email</label>
            <input type="text" disabled value="<?= $emaild ?>">
        </div>
        <div class="data">
            <label>Kelas</label>
            <input type="text" disabled value="<?= $kelasd ?>">
        </div>
        <div class="data">
            <label>Password</label>
            <input type="password" disabled value="********">
        </div>
        <div class="data">
            <label>Foto Diri</label>
            <input type="text" disabled value="<?= $varcharpfp ?>">
        </div>
        <div class="data">
            <label>Level</label>
            <input type="text" disabled value="<?= $leveld ?>">
        </div>
    </div>
    <div class="operator">
        <a href="homepage.php"><button class="kembali">Kembali</button></a>
        <a href="updateakun.php?nis=<?= htmlspecialchars($nisd); ?>"><button class="ubah">Sunting Akun</button></a>
    </div>
</section>
</body>
</html>