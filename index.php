<?php
session_start();

if (isset($_SESSION['login'])) {
    echo "<script>
        alert('KAMU SUDAH LOGIN!');
        window.history.back();
    </script>";
    exit();
    session_unset();
    session_destroy();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presensi Digital</title>
    <style>
        /* Reset dasar */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body, html {
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            overflow-x: hidden; /* Prevent horizontal scroll */
        }

        /* Bagian header */
        .header {
            background-color: #4E73DF;
            color: white;
            padding: 10px 20px;
            text-align: center;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap; /* Allows items to wrap on smaller screens */
        }

        .header h1 {
            font-size: 24px;
            margin: 0;
            flex: 1; /* Allows the title to take available space */
            text-align: left;
        }

        .header a {
            color: white;
            text-decoration: none;
            font-size: 18px;
            margin-right: 10px;
            flex-shrink: 0; /* Prevents the link from shrinking */
        }

        .header a:hover {
            color: black;
        }

        /* Bagian konten gambar sebagai background */
        .content {
            background-image: url('gambar/landingpage (index).png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            flex: 1;
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* Bagian footer */
        .footer {
            background-color: #4E73DF;
            color: white;
            padding: 10px 20px;
            text-align: center;
            font-size: 14px;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .header h1 {
                font-size: 20px;
                text-align: center;
            }

            .header a {
                font-size: 16px;
                margin: 5px 0;
            }

            .footer {
                font-size: 12px;
            }
        }

        @media (max-width: 480px) {
            .header {
                flex-direction: column;
                text-align: center;
                padding: 15px;
            }

            .header h1 {
                font-size: 18px;
                margin-bottom: 5px;
            }

            .header a {
                font-size: 14px;
                margin: 0;
            }

            .content {
                background-size: contain;
                padding: 20px;
                height: 60vh; /* Ensures background image is visible on smaller screens */
            }

            .footer {
                font-size: 10px;
                padding: 8px 15px;
            }
        }
    </style>
</head>
<body>

    <!-- Bagian header -->
    <div class="header">
        <h1>Presensi Digital</h1>
        <a id="masuk" href="login.php">Masuk</a>
    </div>

    <!-- Bagian konten gambar sebagai background -->
    <div class="content">
    </div>

    <!-- Bagian footer -->
    <div class="footer">
        &copy; 2024 Husni Mubarak. All Rights Reserved
    </div>

</body>
</html>
