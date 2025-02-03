<?php

// Validasi session login
function blmlogin() {
    if (!isset($_SESSION['login'])) {
        echo "<script>
            alert('Kamu Belum Login!');
            window.location.href = '../login.php';
        </script>";
        exit;
    }

    $level = $_SESSION['akun']['levelses'];

    // Membatasi akses halaman hanya untuk admin
    if ($level !== 'admin') {
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

    $akun = $_SESSION['akun'];
    $nis = $akun['nisses'];

    // Ambil informasi lainnya
    $profile_picture = getProfilePicture($conn, $nis);
    $hari_sekarang = aturTimezoneDanHari();
    $statistics = getPresensiStatistics($conn);

    // Gabungkan semua data
    return array_merge([
        'email' => $akun['emailses'],
        'nis' => $akun['nisses'],
        'nama' => $akun['namases'],
        'kelas' => $akun['kelasses'],
        'level' => $akun['levelses'],
        'profile_picture' => $profile_picture,
        'hari_sekarang' => $hari_sekarang,
    ], $statistics);
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
        // Mendefinisikan variabel
        $nis = $data['nis'];
        $email = $data['email'];
        $nama = $data['nama'];
        $kelas = $data['kelas'];
        $level = $data['level'];
        $pfp = $data['pfp'];
    } else {
        // Jika tidak ada data, beri nilai null pada variabel
        $nis = null;
        $email = null;
        $nama = null;
        $kelas = null;
        $level = null;
        $pfp = null;
    }

    // Tutup statement
    $stmt->close();

    // Kembalikan data dalam bentuk array
    return [
        'nis' => $nis,
        'email' => $email,
        'nama' => $nama,
        'kelas' => $kelas,
        'level' => $level,
        'pfp' => $pfp
    ];
}

function getPresensiDetail($conn, $id) {
    // Prepare the SQL query to select all the relevant data for the specific ID
    $stmt = $conn->prepare("SELECT * FROM presensi_siswa WHERE id = ?");
    
    if ($stmt === false) {
        die('Query preparation failed: ' . $conn->error);
    }

    // Bind the id parameter and execute the query
    $stmt->bind_param("s", $id);
    $stmt->execute();
    
    // Get the result
    $result = $stmt->get_result();
    
    // Check if a record was found
    if ($result && $result->num_rows > 0) {
        // Fetch the data from the result set
        $row = $result->fetch_assoc();

        // Assign the retrieved data to variables
        $email = $row['email'];
        $nama = $row['nama'];
        $fnis = $row['fnis'];
        $kelas = $row['kelas'];
        $hari = $row['hari'];
        $jam = $row['jam'];
        $status = $row['status'];
        $fileName = $row['bukti'];

        $path = '../user/bukti_presensi/';
        $bukti = $path . $fileName;
        
        // Return the data as an array or directly use these variables as needed
        return [
            'email' => $email,
            'nama' => $nama,
            'fnis' => $fnis,
            'kelas' => $kelas,
            'hari' => $hari,
            'jam' => $jam,
            'status' => $status,
            'bukti' => $bukti,  // Corrected to only return 'bukti' once
        ];
    } else {
        // If no result found, return null
        return null;
    }

    // Close the statement
    $stmt->close();
    
}

// Fungsi untuk mengambil jumlah pengguna, admin, dan total presensi
function getPresensiStatistics($conn) {
    $counts = [];
    $stmt = $conn->prepare("SELECT 
                                (SELECT COUNT(*) FROM akun WHERE level = 'user') AS jumlah_pengguna,
                                (SELECT COUNT(*) FROM akun WHERE level = 'admin') AS jumlah_admin,
                                (SELECT COUNT(*) FROM presensi_siswa) AS total_presensi");
    $stmt->execute();
    $stmt->bind_result($counts['jumlah_pengguna'], $counts['jumlah_admin'], $counts['total_presensi']);
    $stmt->fetch();
    $stmt->close();
    return $counts;
}

// Fungsi untuk mengambil gambar profil
function getProfilePicture($conn, $nis) {
    $stmt = $conn->prepare("SELECT pfp FROM akun WHERE nis = ?");
    $stmt->bind_param("s", $nis);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    // Fetch hasil query
    if ($row = $result->fetch_assoc()) {
        $fileName = $row['pfp'];
        $path = "../pfp/";
        $pfp = $path . $fileName;
        return $pfp; // Return path lengkap
    } else {
        // Jika tidak ada hasil, kembalikan default gambar profil
        return "../pfp/defaultpfp.jpg"; // Path ke gambar profil default
    }
}


// Fungsi untuk mengatur timezone dan mendapatkan hari dalam bahasa Indonesia
function aturTimezoneDanHari() {
    date_default_timezone_set('Asia/Jakarta');
    $hari_indonesia = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
    $bulan_indonesia = ['01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April', '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus', '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'];

    return $hari_indonesia[date("l")] . ", " . date("j") . " " . $bulan_indonesia[date("m")] . " " . date("Y");
}

// Fungsi untuk mendapatkan total data dengan pencarian
function getTotalData($conn, $searchEmail, $searchNIS, $searchNama, $searchLevel) {
    $query = "SELECT COUNT(*) as total FROM akun WHERE 
        email LIKE ? AND 
        nis LIKE ? AND 
        nama LIKE ? AND 
        (level = ? OR ? = '')";

    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    // Mengikat parameter
    $likeEmail = "%$searchEmail%";
    $likeNIS = "%$searchNIS%";
    $likeNama = "%$searchNama%";

    $stmt->bind_param("sssss", $likeEmail, $likeNIS, $likeNama, $searchLevel, $searchLevel);
    if ($stmt->execute() === false) {
        die("Error executing query: " . $stmt->error);
    }

    $result = $stmt->get_result();
    return $result->fetch_assoc(); 
}

// Fungsi untuk mengambil data untuk halaman saat ini dengan pencarian dan pagination
function getData($conn, $searchEmail, $searchNIS, $searchNama, $searchLevel, $limit, $offset) {
    $query = "SELECT * FROM akun WHERE 
        email LIKE ? AND 
        nis LIKE ? AND 
        nama LIKE ? AND 
        (level = ? OR ? = '') 
        LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    $likeEmail = "%$searchEmail%";
    $likeNIS = "%$searchNIS%";
    $likeNama = "%$searchNama%";

    $stmt->bind_param("ssssiis", $likeEmail, $likeNIS, $likeNama, $searchLevel, $searchLevel, $limit, $offset);
    if ($stmt->execute() === false) {
        die("Error executing query: " . $stmt->error);
    }

    return $stmt->get_result(); 
}

