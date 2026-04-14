<?php
session_start();
if (!isset($_SESSION['usr']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'supervisor') {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

include_once("../../config/koneksi.php");

$username = $_SESSION['usr'];
$nama = $_SESSION['nama'];
$role = $_SESSION['role'];

$bunga_reguler = $_POST['bunga_reguler'];
$bunga_denda   = $_POST['bunga_denda'];
$bunga_admin   = $_POST['bunga_admin'];
// Validasi input
if (!is_numeric($bunga_reguler) || !is_numeric($bunga_denda)) {
    header('Location: bunga.php?status=invalid');
    exit;
}

// Prepare update statement
$stmt = mysqli_prepare($conn, "UPDATE dfbunga SET tarif = ? WHERE bunga = ?");
if (!$stmt) {
    header("Location: bunga.php?status=prepare_failed");
    exit;
}

// Update bunga normal
$bunga_label = "normal";
mysqli_stmt_bind_param($stmt, "ds", $bunga_reguler, $bunga_label);
mysqli_stmt_execute($stmt);

// Update bunga denda
$bunga_label = "denda";
mysqli_stmt_bind_param($stmt, "ds", $bunga_denda, $bunga_label);
mysqli_stmt_execute($stmt);

// Update bunga denda
$bunga_label = "admin";
mysqli_stmt_bind_param($stmt, "ds", $bunga_admin, $bunga_label);
mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);
header('Location: bunga.php?status=success');

exit;
