<?php
session_start();
include_once("../../config/koneksi.php");

// Cek apakah user sudah login
if (!isset($_SESSION['usr'])) {
    echo "Unauthorized access.";
    exit;
}

$user_id = $_SESSION['usr'];
$passwordBaru = $_POST['passwordBaru'] ?? '';
$konfirmasiPassword = $_POST['konfirmasiPassword'] ?? '';

// Cek apakah dua password cocok
if ($passwordBaru !== $konfirmasiPassword) {
    echo "<script>
        alert('Password dan konfirmasi tidak sama!');
        window.location.href = 'passwordbaru.php';
    </script>";
    exit;
}

// Hash password baru
$hashedPassword = password_hash($passwordBaru, PASSWORD_DEFAULT);

// Update password di database
$stmt = $conn->prepare("UPDATE user_account SET password_hash = ? WHERE user_id = ?");
$stmt->bind_param("ss", $hashedPassword, $user_id);

if ($stmt->execute()) {
    echo "<script>
        alert('Password berhasil diperbarui.');
        window.location.href = '../home.php'; // arahkan ke dashboard atau halaman lain
    </script>";
} else {
    echo "<script>
        alert('Gagal memperbarui password.');
        window.location.href = 'passwordbaru.php';
    </script>";
}

$stmt->close();
$conn->close();
?>