<?php
session_start();
if (!isset($_SESSION['usr']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'supervisor') {
    header('Location: ../../gates/login.php');
    exit;
}

include_once("../../config/koneksi.php");

// Ambil semua data, urutkan dari terbaru ke terlama
$sql = "SELECT * FROM log_bayar ORDER BY tanggal DESC"; 
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Log Bayar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <h3>Log Aktivitas Pembayaran</h3>
    <a href="home_payment.php" class="btn btn-primary mb-3">Kembali</a>
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>No</th>
                <th>ID Bayar</th>
                <th>Aktivitas</th>
                <th>Tanggal</th>
                <th>ID Petugas</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= htmlspecialchars($row['no']) ?></td>
                    <td><?= htmlspecialchars($row['id_bayar']) ?></td>
                    <td><?= htmlspecialchars($row['aktivitas']) ?></td>
                    <td><?= htmlspecialchars($row['tanggal']) ?></td>
                    <td><?= htmlspecialchars($row['operated']) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>