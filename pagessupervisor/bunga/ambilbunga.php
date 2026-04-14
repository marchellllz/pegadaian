<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usr'])) {
    header('Location: ../gates/login.php');
    exit;
}
$username = $_SESSION['usr'];
$nama = $_SESSION['nama'];
$role = $_SESSION['role'];
include_once("../../config/koneksi.php");

$bunga_reguler = '';
$bunga_denda = '';

$query = mysqli_query($conn, "SELECT bunga, tarif FROM dfbunga");

while ($row = mysqli_fetch_assoc($query)) {
    if ($row['bunga'] === 'normal') {
        $bunga_reguler = $row['tarif'];
    } elseif ($row['bunga'] === 'denda') {
        $bunga_denda = $row['tarif'];
    } elseif($row['bunga'] === 'admin'){
        $bunga_admin = $row['tarif'];
    }
}