<?php
include_once("../../config/koneksi.php");
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usr'])) {
    header('Location: ../gates/login.php');
    exit;
}
// Ambil parameter GET
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Mapping agama
$agamaList = [
    1 => 'Islam',
    2 => 'Katolik',
    3 => 'Kristen',
    4 => 'Hindu',
    5 => 'Buddha',
    6 => 'Konghucu'
];

// Query pencarian
$searchSql = '';
if ($search !== '') {
    $searchSafe = mysqli_real_escape_string($conn, $search);
    $searchSql = "WHERE k.user_id LIKE '%$searchSafe%' OR k.nama LIKE '%$searchSafe%'";
}

// Ambil data karyawan
$result = mysqli_query($conn, "
    SELECT k.*, ua.status 
    FROM karyawan k 
    LEFT JOIN user_account ua ON k.user_id = ua.user_id 
    $searchSql
");

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $row['agama_nama'] = $agamaList[$row['agama']] ?? 'Tidak diketahui';
    $data[] = $row;
}

echo json_encode([
    'status' => 'success',
    'data' => $data
]);