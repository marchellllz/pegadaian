<?php
session_start();
include "../config/koneksi.php";

$user_id = $_POST['usr'];
$nama = $_POST['nama'];
$nohp = $_POST['nohp'];
$alamat = $_POST['alamat'];
$jkel = $_POST['jkel'];
$agama = $_POST['agama'];
$role = $_POST['role']; // akan disimpan ke kolom rl
$password = $_POST['psw']; // nanti bisa ganti password_hash()
$cabang = $_POST['cabang'];
$konfirmasi_password = $_POST['psw_cfm'];

// Validasi password
if ($password !== $konfirmasi_password) {
    header("Location: signup.php?pesan=Password dan konfirmasi tidak cocok.");
    exit;
}

// Hash password dengan algoritma default (bcrypt)
$password_hash = password_hash($konfirmasi_password, PASSWORD_DEFAULT);

// Cek apakah user_id sudah ada
$cek = $conn->prepare("SELECT user_id FROM user_account WHERE user_id = ?");
$cek->bind_param("s", $user_id);
$cek->execute();
$cek->store_result();

if ($cek->num_rows > 0) {
    header("Location: signup.php?pesan=User ID sudah digunakan.");
    exit;
}
$cek->close();

$conn->begin_transaction();
try {
    // 1. Insert ke karyawan
    $stmt = $conn->prepare("
        INSERT INTO karyawan (user_id, nama, nohp, alamat, jkel, agama, rl, cabang)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("ssssssss", $user_id, $nama, $nohp, $alamat, $jkel, $agama, $role, $cabang);
    if (!$stmt->execute()) {
        throw new Exception("Gagal insert karyawan: " . $stmt->error);
    }
    $stmt->close();

    // 2. Insert ke user_account
    $stmt1 = $conn->prepare("
        INSERT INTO user_account (user_id, password_hash, rl, status, cabang)
        VALUES (?, ?, ?, ?, ?)
    ");
    $status_default = 'pending';
    $stmt1->bind_param("sssss", $user_id, $password_hash, $role, $status_default, $cabang);
    if (!$stmt1->execute()) {
        throw new Exception("Gagal insert user_account: " . $stmt1->error);
    }
    $stmt1->close();

    // Commit kalau semua sukses
    $conn->commit();
    header("Location: signup.php?sukses=Pendaftaran berhasil. Silakan Verifikasi.");
    exit;

} catch (Exception $e) {
    // Rollback kalau ada error
    $conn->rollback();
    error_log("[Signup Error] " . $e->getMessage());
    header("Location: signup.php?pesan=Gagal mendaftar. Silakan coba lagi.");
    exit;
}
$conn->close();
