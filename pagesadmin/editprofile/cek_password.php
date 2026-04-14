<?php
session_start();
header('Content-Type: application/json');

// Cek apakah user sudah login
if (!isset($_SESSION['usr'])) {
    echo json_encode(['status' => 'unauthorized']);
    exit;
}

// Cek apakah password dikirim
if (!isset($_POST['passwordLama'])) {
    echo json_encode(['status' => 'invalid_request']);
    exit;
}

include_once("../../config/koneksi.php");

$username = $_SESSION['usr'];
$passwordInput = $_POST['passwordLama'];

// Ambil hash password dari database
$stmt = mysqli_prepare($conn, "SELECT password_hash FROM user_account WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $hashPassword);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

if (!$hashPassword) {
    echo json_encode(['status' => 'not_found']);
    exit;
}

// Verifikasi password input dengan hash dari DB
if (password_verify($passwordInput, $hashPassword)) {
    echo json_encode(['status' => 'valid']);
} else {
    echo json_encode(['status' => 'invalid']);
}
?>