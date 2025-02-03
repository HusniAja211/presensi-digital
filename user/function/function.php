<?php
// Fungsi untuk mengecek login
function sdhlogin() {
    if (!isset($_SESSION['login'])) {
        echo "<script> 
            alert('LOGIN DULU!');
            window.location.href = '../login.php';
        </script>";
        exit;
    }

    $level = $_SESSION['akun']['levelses'];

    // Membatasi akses halaman hanya untuk admin
    if ($level !== 'user') {
        echo "<script> 
            alert('Anda tidak memiliki akses ke halaman ini!');
            window.history.back();
        </script>";
        exit;
    }
}

// Fungsi untuk mendapatkan data dashboard
function getSessionData($conn) {
    if (!isset($_SESSION['akun'])) {
        return ['error' => 'Session tidak ditemukan. Silakan login terlebih dahulu.'];
    }

    // Ambil data dari session
    $akun = $_SESSION['akun'];
    $nis = $akun['nisses'];

    // Ambil informasi lainnya
    $profile_picture = getProfilePicture($conn, $nis);
    $hari_sekarang = aturTimezoneDanHari();
    $statistics = getPresensiStatistics($conn, $nis);

    // Gabungkan semua data
    return [
        'email' => $akun['emailses'],
        'nis' => $akun['nisses'],
        'nama' => $akun['namases'],
        'kelas' => $akun['kelasses'],
        'level' => $akun['levelses'],
        'profile_picture' => $profile_picture,
        'hari_sekarang' => $hari_sekarang,
        'statistics' => $statistics,
    ];
}


//fungsi untuk mengambil data dari database
function getDatabaseData($conn, $nisg) {
    $stmt = $conn->prepare("SELECT * FROM akun WHERE nis = ?");
    if (!$stmt) {
        die("Kesalahan prepare statement: " . $conn->error);
    }
    $stmt->bind_param("s", $nisg);
    $stmt->execute();
    $result = $stmt->get_result();

    // Jika data ditemukan
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc(); // Ambil data sebagai array asosiatif
    } else {
        $data = null; // Jika tidak ada data, kembalikan null
    }

    // Tutup statement dan koneksi
    $stmt->close();

    // Kembalikan data
    return $data;
}

//function untuk ambil detail presensi
function getDetailPresensi($conn, $id) {
    $stmt = $conn->prepare("SELECT * FROM presensi_siswa WHERE id = ?");
    if (!$stmt) {
        die("Kesalahan prepare statement: " . $conn->error);
    }

    $stmt->bind_param("i", $id);
    $stmt->execute();
    $hasilDetailPresensi = $stmt->get_result();

    // Jika data ditemukan
    if ($hasilDetailPresensi->num_rows > 0) {
        $data = $hasilDetailPresensi->fetch_assoc(); // Ambil data sebagai array asosiatif
    } else {
        $data = null; // Jika tidak ada data, kembalikan null
    }

    // Tutup statement
    $stmt->close();

    return $data; // Mengembalikan data
}


// Fungsi untuk mendapatkan path gambar profil
function getProfilePicture($conn, $nis) {

    $query = "SELECT pfp FROM akun WHERE nis = '$nis'";
    $result = mysqli_query($conn, $query);

    $path = "../pfp/";
    $default_pfp = "defaultpfp.png";

    if ($result && $row = mysqli_fetch_assoc($result)) {
        $pfp = $row['pfp'] ?: $default_pfp;
    } else {
        $pfp = $default_pfp;
    }

    return $path . $pfp;
}


// Fungsi untuk mendapatkan path gambar bukti
function getBukti($conn, $id) {

    $path = "bukti_presensi/";
    $bukti = "Tidak Ada Bukti"; // Nilai default jika tidak ditemukan

    // Menggunakan prepared statement untuk keamanan
    $stmt = $conn->prepare("SELECT bukti FROM presensi_siswa WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id); // Pastikan id bertipe integer
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $row = $result->fetch_assoc()) {
            // Jika ada data, gunakan nilai bukti atau default
            $bukti = $row['bukti'] ?: "Tidak Ada Bukti";
        }

        $stmt->close(); // Menutup statement
    } else {
        die("Kesalahan pada prepare statement: " . $conn->error); // Menangani kesalahan jika prepare gagal
    }

    return $path . $bukti; // Mengembalikan path lengkap
}


// Fungsi untuk mendapatkan statistik presensi berdasarkan status
function getPresensiStatistics($conn, $nis) {
    $statuses = ['hadir', 'izin', 'sakit', 'tidak hadir'];
    $statistics = [];

    foreach ($statuses as $status) {
        $query = "SELECT COUNT(*) AS jumlah FROM presensi_siswa WHERE fnis = '$nis' AND status = '$status'";
        $result = mysqli_query($conn, $query);
        $row = $result ? mysqli_fetch_assoc($result) : ['jumlah' => 0];
        $statistics[$status] = $row['jumlah'];
    }

    return $statistics;
}

// Fungsi untuk mengatur timezone dan mendapatkan hari dalam bahasa Indonesia
function aturTimezoneDanHari() {
    date_default_timezone_set('Asia/Jakarta');

    $hari_indonesia = [
        'Sunday' => 'Minggu',
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu'
    ];

     // Daftar nama bulan dalam bahasa Indonesia
     $bulan_indonesia = [
        '01' => 'Januari',
        '02' => 'Februari',
        '03' => 'Maret',
        '04' => 'April',
        '05' => 'Mei',
        '06' => 'Juni',
        '07' => 'Juli',
        '08' => 'Agustus',
        '09' => 'September',
        '10' => 'Oktober',
        '11' => 'November',
        '12' => 'Desember'
    ];

    // Mendapatkan hari, tanggal, bulan, dan tahun
    $hari = $hari_indonesia[date("l")];
    $tanggal = date("j");
    $bulan = $bulan_indonesia[date("m")];
    $tahun = date("Y");

    return "$hari, $tanggal $bulan $tahun";
}

// Menyimpan hasil dalam variabel
$hariini = aturTimezoneDanHari();
