<?php
session_start();
include "../../config/koneksi.php";

// Cek login
if (!isset($_SESSION['usr']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'supervisor') {
    header("Location: ../gates/login.php");
    exit;
}

$username = $_SESSION['usr'];

if (!isset($_POST['id'])) {
    header("Location: datakaryawan.php?msg=invalid_id");
    exit;
}

$id = $_POST['id'];

if($id == $username){
    header("Location: datakaryawan.php?msg=Tidak dapat menghapus akses sendiri");
    exit;
}
// Cek apakah ID karyawan tersebut ada
$cek = mysqli_query($conn, "SELECT * FROM user_account WHERE user_id = '$id'");
if (mysqli_num_rows($cek) === 0) {
    header("Location: datakaryawan.php?msg=not_found");
    exit;
}

// Hapus user
$deleteUser = mysqli_query($conn, "DELETE FROM user_account WHERE user_id = '$id'");

if ($deleteUser) {
    // Update log terbaru (yang dibuat oleh TRIGGER) untuk tambahkan 'operated'
    $udt = mysqli_query($conn, "
        UPDATE logmasuk 
        SET operated = '$username' 
        WHERE user_id = '$id' 
          AND activity = 'Karyawan dihapus'");

    header("Location: datakaryawan.php?msg=deleted");
} else {
    header("Location: datakaryawan.php?msg=error");
}
exit;
?>