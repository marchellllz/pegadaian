<?php
include_once("../../config/koneksi.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['usr'])) {
    header('Location: ../gates/login.php');
    exit;
}
// Ambil cabang dari session
$cabang = $_SESSION['cabang'] ?? '';

// Ambil parameter GET
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Query pencarian
$searchSql = "WHERE cabang = '" . mysqli_real_escape_string($conn, $cabang) . "'";
if ($search !== '') {
    $searchSafe = mysqli_real_escape_string($conn, $search);
    $searchSql .= " AND (nomor_nasabah LIKE '%$searchSafe%' OR nama_nasabah LIKE '%$searchSafe%')";
}

// Ambil data nasabah
$result = mysqli_query($conn, "
    SELECT * FROM nasabah_gadai 
    $searchSql
");

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

echo json_encode([
    'status' => 'success',
    'data' => $data
]);