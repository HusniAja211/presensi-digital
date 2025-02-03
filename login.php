<?php
session_start(); // Memulai session

if (isset($_SESSION['login'])) {
    echo "<script>
        alert('KAMU SUDAH LOGIN!');
        window.history.back();
    </script>";
    exit();
}

require "admin/database/config.php";

// Memeriksa apakah tombol login sudah ditekan atau belum
if (isset($_POST['submit'])) {
    // Mengambil data dari form POST dan sanitasi input
    $nis = htmlspecialchars($_POST['nis']);
    $password = $_POST['password']; // Ambil password yang diinputkan user

    // Melakukan pemeriksaan NIS menggunakan prepared statement
    $stmt = $conn->prepare("SELECT email, nama, nis, kelas, pfp, level, password FROM akun WHERE nis = ?");
    $stmt->bind_param("s", $nis); // Ikat parameter NIS
    $stmt->execute();
    $resultcek = $stmt->get_result();

    if ($resultcek->num_rows > 0) {
        $row = $resultcek->fetch_assoc();
        $hashedpassword = $row['password']; // Ambil password dari database

        if (password_verify($password, $hashedpassword)) {
            $_SESSION['login'] = true;
            $_SESSION['akun'] = [
                'emailses' => $row['email'],
                'namases' => $row['nama'],
                'nisses' => $row['nis'],
                'kelasses' => $row['kelas'],
                'pfpses' => $row['pfp'],
                'levelses' => $row['level']
            ];

            // Menentukan halaman berdasarkan level
            $redirectUrl = ($_SESSION['akun']['levelses'] === 'admin') ? 'admin/dashboard.php' : 'user/homepage.php';
            echo "<script>
                alert('Selamat Datang " . $_SESSION['akun']['namases'] . "! Level: " . $_SESSION['akun']['levelses'] . "');
                window.location.href = '$redirectUrl';
            </script>";
            exit();
        } else {
            echo "<script>alert('Password salah!'); window.location.href = 'login.php';</script>";
        }
    } else {
        echo "<script>alert('NIS tidak ditemukan!'); window.location.href = 'login.php';</script>";
    }

    // Tutup statement setelah selesai
    $stmt->close();
    // Menutup koneksi setelah selesai
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
            background-color: #4A73E0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .login-container {
            background-color: white;
            width: 400px;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .login-container h2 {
            margin-bottom: 30px;
            font-size: 24px;
        }
        .input-field {
            width: 100%;
            padding: 10px 15px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 30px;
            font-size: 16px;
            outline: none;
        }
        .login-btn {
            width: 100%;
            padding: 10px;
            background-color: #4A73E0;
            border: none;
            border-radius: 30px;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }
        .login-btn:hover {
            background-color: #3A5DC9;
        }
        .forgot-password {
            display: block;
            margin-top: 15px;
            font-size: 14px;
            color: #4A73E0;
            text-decoration: none;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .login-container {
                width: 80%;
                padding: 30px;
            }
            .login-container h2 {
                font-size: 22px;
            }
            .input-field {
                font-size: 15px;
            }
            .login-btn {
                font-size: 15px;
            }
            .forgot-password {
                font-size: 13px;
            }
        }

        @media (max-width: 480px) {
            .login-container {
                width: 90%;
                padding: 20px;
            }
            .login-container h2 {
                font-size: 20px;
                margin-bottom: 20px;
            }
            .input-field {
                padding: 8px 10px;
                font-size: 14px;
            }
            .login-btn {
                font-size: 14px;
                padding: 8px;
            }
            .forgot-password {
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Selamat Datang!</h2>
        <form action="" method="POST">
            <input type="text" class="input-field" name="nis" placeholder="Masukkan NIS Anda" inputmode="numeric" pattern="\d{4}" title="NIS harus terdiri dari 4 digit angka" maxlength="4" autocomplete="off" required>
            <input type="password" class="input-field" name="password" placeholder="Masukkan Password Anda" minlength="8" maxlength="8" autocomplete="off" required>
            <button type="submit" class="login-btn" name="submit">Masuk</button>
            <a href="forgetpass.php" class="forgot-password">Lupa Password?</a>
        </form>
    </div>
</body>
</html>