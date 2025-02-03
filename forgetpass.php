<?php
session_start();
require "admin/database/config.php";

// Set default step if not set in the session
if (!isset($_SESSION['step'])) {
    $_SESSION['step'] = 1; // Default to step 1
}

// Step 1: Validate email
if (isset($_POST['valemail'])) {
    $email = $_POST['email'];
    $stmt = $conn->prepare("SELECT email FROM akun WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows > 0) {
        $_SESSION['valid_email'] = $email;
        $_SESSION['step'] = 2; // Move to step 2
    } else {
        echo "<script>alert('Email tidak ditemukan'); window.location.href = 'forgetpass.php';</script>";
    }
}

// Step 2: Update password
if (isset($_POST['update_password']) && $_SESSION['step'] == 2) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $email = $_SESSION['valid_email'];

    // Check if passwords match
    if ($new_password === $confirm_password) {
        if (strlen($new_password) === 8) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Update password
            $stmt = $conn->prepare("UPDATE akun SET password = ? WHERE email = ?");
            $stmt->bind_param("ss", $hashed_password, $email);
            
            if ($stmt->execute()) {
                unset($_SESSION['step'], $_SESSION['valid_email']);
                echo "<script>alert('Password berhasil diperbarui'); window.location.href = 'login.php';</script>";
                exit;
            } else {
                echo "<script>alert('Terjadi kesalahan saat memperbarui password');</script>";
            }
            $stmt->close();
        } else {
            echo "<script>alert('Password harus 8 karakter');</script>";
        }
    } else {
        echo "<script>alert('Password dan konfirmasi password tidak sama');</script>";
    }
}

// Handle "Cancel" button
if (isset($_POST['cancel'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemulihan Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #4A73E0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background-color: white;
            padding: 20px 40px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 320px;
        }
        .container h2 {
            margin-bottom: 20px;
            color: #4B89DC;
        }
        .input-container {
            margin-bottom: 20px;
        }
        .input-container input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }
        .button {
            background-color: #4A73E0;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .button:hover {
            background-color: #4B89DC;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Pemulihan Password</h2>
        <p>Atur Ulang Password</p>

        <?php if ($_SESSION['step'] == 1): ?>
            <form method="post">
                <div class="input-container">
                    <input type="email" name="email" autocomplete="off" placeholder="Masukkan email Anda">
                </div>
                <button type="submit" class="button" name="valemail">Kirim Tautan</button>
                <button type="button" class="button" onclick="window.location.href='login.php'">Kembali</button>
            </form>
        <?php elseif ($_SESSION['step'] == 2): ?>
            <form method="post">
                <div class="input-container">
                    <input type="password" name="new_password" placeholder="Password Baru (8 karakter)" minlength="8" maxlength="8">
                </div>
                <div class="input-container">
                    <input type="password" name="confirm_password" placeholder="Konfirmasi Password" minlength="8" maxlength="8">
                </div>
                <button type="submit" class="button" name="update_password">Perbarui Password</button>
                <button type="submit" class="button" name="cancel">Batal</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
