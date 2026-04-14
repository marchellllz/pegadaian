<?php
include_once("../../config/koneksi.php");
session_start();
if (!isset($_SESSION['usr']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'supervisor') {
    header('Location: ../../gates/login.php');
    exit;
}

if ($_SESSION['role'] !== 'supervisor') {
    header('Location: ../home.php');
    exit;
}

$id_bayar = $_GET['id'] ?? '';
if (!$id_bayar) {
    header('Location: home_payment.php');
    exit;
}

$username = $_SESSION['usr'];
$stmt = $conn->prepare("SELECT 1 FROM bayar WHERE id_bayar = ?");
$stmt->bind_param("i", $id_bayar);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    header('Location: home_payment.php');
    exit;
}
$stmt->close();
// Update status bayar
$stmt = $conn->prepare("UPDATE bayar SET status_bayar = 'x' WHERE id_bayar = ?");
$stmt->bind_param("s", $id_bayar);
$stmt->execute();
$stmt->close();

// ========== Insert ke tabel log_bayar ==========
date_default_timezone_set('Asia/Jakarta');
$aktivitas = "Tolak pembayaran dengan ID bayar : ". $id_bayar;
$tanggalLog = date("Y-m-d H:i:s");

$stmtLog = $conn->prepare("INSERT INTO log_bayar (id_bayar, aktivitas, tanggal, operated) VALUES (?, ?, ?, ?)");
$stmtLog->bind_param("ssss", $id_bayar, $aktivitas, $tanggalLog, $username);
if (!$stmtLog->execute()) {
    die("Error insert log: " . $stmtLog->error);
}
$stmtLog->close();
// Redirect balik
header("Location: home_payment.php");
exit;
?>