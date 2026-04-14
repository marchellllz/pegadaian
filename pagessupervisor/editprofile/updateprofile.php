<?php
session_start();
if (!isset($_SESSION['usr']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'supervisor') {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

include_once("../../config/koneksi.php");

$user_id = $_SESSION['usr'];
$nama = $_POST['nama'];
$nohp = $_POST['nohp'];
$alamat = $_POST['alamat'];
$jkel = $_POST['jkel'];
$agama = $_POST['agama'];

// Prepare statement
$stmt = mysqli_prepare($conn, "UPDATE karyawan SET nama = ?, nohp = ?, alamat = ?, jkel = ?, agama = ? WHERE user_id = ?");
if (!$stmt) {
    header("Location: editprofile.php?status=prepare_failed");
    exit;
}

// Bind param
if (!mysqli_stmt_bind_param($stmt, "ssssss", $nama, $nohp, $alamat, $jkel, $agama, $user_id)) {
    header("Location: editprofile.php?status=bind_failed");
    exit;
}

// Execute
if (!mysqli_stmt_execute($stmt)) {
    header("Location: editprofile.php?status=execute_failed");
    exit;
}

header("Location: editprofile.php?status=success");
exit;
?>