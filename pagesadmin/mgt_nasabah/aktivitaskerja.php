<?php
session_start();
if (!isset($_SESSION['usr']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../gates/login.php');
    exit;
}
$cbg = $_SESSION['cabang'];
include_once("../../config/koneksi.php");

// Ambil semua data, urutkan dari terbaru ke terlama
$sql = "SELECT * FROM aktivitas_kerja ";
// Filter cabang biar cabang lain nggak ikut nimbrung
if ($_SESSION['cabang'] !== 'mataram') {
    $cabang = $conn->real_escape_string($_SESSION['cabang']);
    $sql .= " WHERE cabang = '$cabang'";
}
$sql .= "ORDER BY tanggal DESC";
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
    <h3>Log Aktivitas Akun Nasabah</h3>
    <a href="datanasabah.php" class="btn btn-primary mb-3">Kembali</a>
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>No</th>
                <th>Aktivitas</th>
                <th>Tanggal</th>
                <th>ID Petugas</th>
                <th>Cabang</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= htmlspecialchars($row['no']) ?></td>
                    <td><?= htmlspecialchars($row['aktivitas']) ?></td>
                    <td><?= htmlspecialchars($row['tanggal']) ?></td>
                    <td><?= htmlspecialchars($row['operated']) ?></td>
                    <td><?= htmlspecialchars($row['cabang']) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>