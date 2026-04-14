<?php
session_start();
if (!isset($_SESSION['usr']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'supervisor') {
    header('Location: ../../gates/login.php');
    exit;
}

$username = $_SESSION['usr'];
$nama = $_SESSION['nama'];
$role = $_SESSION['role'];
include "../../config/koneksi.php";

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(["status" => "error", "message" => "ID tidak ditemukan"]);
    exit;
}

$id = $_GET['id'];

// Update status jadi 'verified'
$query = mysqli_query($conn, "UPDATE user_account SET status = 'verified' WHERE user_id = '$id'");

if ($query) {
    // Update log operated
    $udt = mysqli_query($conn, "UPDATE logmasuk SET operated = '$username' WHERE user_id = '$id' AND activity = 'Diverifikasi'");

    if ($udt) {
        echo json_encode(["status" => "success", "message" => "Akun berhasil diverifikasi dan log diperbarui"]);
    } else {
        echo json_encode(["status" => "success", "message" => "Akun berhasil diverifikasi, tapi gagal memperbarui log"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Gagal memverifikasi akun"]);
}
exit;
