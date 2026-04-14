<?php
session_start();
include "../config/koneksi.php";

$username = trim($_POST['usr']);
$input_password = $_POST['psw'];
$role = trim(strtolower($_POST['role'])); // normalisasi

$sql = "SELECT * FROM user_account WHERE user_id = ? AND LOWER(rl) = LOWER(?)";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ss", $username, $role);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);

if ($row) {
    // rate limit per user dalam session: maksimal 5 percobaan, lock 5 menit
    $key = $row['user_id'];
    if (!isset($_SESSION['login_attempts'][$key])) {
        $_SESSION['login_attempts'][$key] = [
            'count' => 0,
            'first_try' => time()
        ];
    }

    $attempt = &$_SESSION['login_attempts'][$key];

    // reset counter kalau sudah lewat 5 menit sejak first_try
    if (time() - $attempt['first_try'] > 300) { // 300 detik = 5 menit
        $attempt['count'] = 0;
        $attempt['first_try'] = time();
    }

    if ($attempt['count'] >= 5) {
        header("Location: ../gates/login.php?error=Terlalu banyak percobaan. Coba lagi setelah 5 menit.");
        exit;
    }
    
    if (password_verify($input_password, $row['password_hash'])) {

        if (!isset($row['status']) || $row['status'] !== 'verified') {
            header("Location: ../gates/login.php?error=Login gagal. Akun belum diverifikasi");
            exit;
        }
        // rehash jika policy berubah
        if (password_needs_rehash($row['password_hash'], PASSWORD_DEFAULT)) {
            $newHash = password_hash($input_password, PASSWORD_DEFAULT);
            $updateStmt = mysqli_prepare($conn, "UPDATE user_account SET password_hash = ? WHERE user_id = ?");
            mysqli_stmt_bind_param($updateStmt, "ss", $newHash, $username);
            mysqli_stmt_execute($updateStmt);
            // optional: bisa cek error tapi nggak wajib sekarang
        }

        session_regenerate_id(true);
        $_SESSION['usr'] = $row['username'] ?? $row['user_id'];
        $_SESSION['role'] = $row['role'] ?? $row['rl'];
        // Ambil nama karyawan dari tabel karyawan
        $sql_nama = "SELECT nama, cabang FROM karyawan WHERE user_id = ?";
        $stmt_nama = mysqli_prepare($conn, $sql_nama);
        mysqli_stmt_bind_param($stmt_nama, "s", $username);
        mysqli_stmt_execute($stmt_nama);
        $result_nama = mysqli_stmt_get_result($stmt_nama);
        $row_nama = mysqli_fetch_assoc($result_nama);

        if ($row_nama) {
            $_SESSION['nama'] = $row_nama['nama'];
            $_SESSION['cabang'] = $row_nama['cabang'];
        }

        if ($role === "admin") {
            header("Location: ../pagesadmin/home.php");
            exit;
        } elseif ($role === "supervisor") {
            header("Location: ../pagessupervisor/home.php");
            exit;
        } else {
            header("Location: ../gates/login.php?error=Role tidak valid.");
            exit;
        }
    } else {
        header("Location: ../gates/login.php?error=Login gagal. Username atau Password salah.");
        exit;
    }
} else {
    header("Location: ../gates/login.php?error=Login gagal. Username atau Password salah.");
    exit;
}
