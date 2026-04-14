<?php
session_start();
include "../../config/koneksi.php";

if (!isset($_SESSION['usr'])) {
    die("Akses ditolak!");
}

$username = $_SESSION['usr'];
$cabang = $_SESSION['cabang'];
// Pastikan time_zone MySQL jadi WIB
$conn->query("SET time_zone = '+07:00'");

// Ambil data dari form
$no_gadai = isset($_POST['no_gadai']) ? trim($_POST['no_gadai']) : '';
$status_verifikasi = isset($_POST['status_verifikasi']) ? trim($_POST['status_verifikasi']) : '';
$catatan = isset($_POST['catatan']) ? trim($_POST['catatan']) : '';

// Validasi sederhana
if ($no_gadai === '' || $status_verifikasi === '') {
    die("Data tidak lengkap!");
}

// --- 1) Update status di tabel gadai ---
if ($status_verifikasi === 'diterima') {
    $status_gadai = 'diterima';
} elseif ($status_verifikasi === 'ditolak') {
    $status_gadai = 'ditolak';
} elseif ($status_verifikasi === 'lunas') {
    $status_gadai = 'lunas';
 }elseif ($status_verifikasi === 'dilelang') {
    $status_gadai = 'dilelang';
}
else {
    $status_gadai = 'pending';
}

$stmtUpdate = $conn->prepare("UPDATE gadai SET status = ? WHERE no_gadai = ?");
$stmtUpdate->bind_param("ss", $status_gadai, $no_gadai);
$stmtUpdate->execute();
$stmtUpdate->close();

// --- 2) Insert ke tabel gadai_confirmed ---
// --- 2) Insert/Update ke tabel gadai_confirmed ---
// Cek apakah no_gadai sudah ada
$cekStmt = $conn->prepare("SELECT COUNT(*) FROM gadai_confirmed WHERE no_gadai = ?");
$cekStmt->bind_param("s", $no_gadai);
$cekStmt->execute();
$cekStmt->bind_result($ada);
$cekStmt->fetch();
$cekStmt->close();

if ($ada > 0) {
    // Sudah ada → update
    $stmtCU = $conn->prepare("
        UPDATE gadai_confirmed 
        SET user_id = ?, status_verifikasi = ?, catatan = ?, tanggal_verifikasi = NOW()
        WHERE no_gadai = ?
    ");
    $stmtCU->bind_param("ssss", $username, $status_verifikasi, $catatan, $no_gadai);
} else {
    // Belum ada → insert
    $stmtCU = $conn->prepare("
        INSERT INTO gadai_confirmed 
        (no_gadai, user_id, status_verifikasi, catatan, tanggal_verifikasi) 
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmtCU->bind_param("ssss", $no_gadai, $username, $status_verifikasi, $catatan);
}
$stmtCU->execute();
$stmtCU->close();

// --- 3) Insert ke tabel aktivitas_kerja ---
$aktivitas = "Verifikasi Gadai ". $no_gadai;
$stmtLog = $conn->prepare("
    INSERT INTO aktivitas_kerja (aktivitas, tanggal, operated, cabang) 
    VALUES (?, NOW(), ?, ?)
");
$stmtLog->bind_param("sss", $aktivitas, $username, $cabang);
$stmtLog->execute();
$stmtLog->close();

// --- Redirect balik ---
header("Location: datagadai.php?msg=Proses verifikasi berhasil");
exit;