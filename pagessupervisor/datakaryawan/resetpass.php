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
include_once("../../config/koneksi.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['id'])) {
    $userId = $_POST['id'];
    $defaultPassword = 'adm123456';
    $hashedPassword = password_hash($defaultPassword, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE user_account SET password_hash = ? WHERE user_id = ?");
    $stmt->bind_param("ss", $hashedPassword, $userId);

    if ($stmt->execute()) {
        // Update log operated
        $udt = mysqli_query($conn, "UPDATE logmasuk SET operated = '$username' WHERE user_id = '$id' AND activity = 'Ubah Password'");

        if ($udt) {
            echo json_encode(["status" => "success", "message" => "Password direset dan log diperbarui"]);
        } else {
            echo json_encode(["status" => "success", "message" => "Password direset, tapi gagal memperbarui log"]);
        }
        header("Location: datakaryawan.php?reset=success");
        exit;
    } else {
        header("Location: datakaryawan.php?reset=fail");
        exit;
    }
}

header("Location: datakaryawan.php");
exit;
