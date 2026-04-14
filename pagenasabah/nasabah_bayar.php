<?php
require_once __DIR__ . '/../config/koneksi.php';

$no_gadai = $_GET['no_gadai'] ?? '';

if ($no_gadai == '') {
    echo "<div class='alert alert-warning'>No Gadai tidak ditemukan.</div>";
    exit;
}

// Ambil semua pembayaran berdasar no_gadai
$sql = "SELECT id_bayar, no_gadai, tanggal_bayar, jumlah_bayar, keterangan 
        FROM bayar 
        WHERE no_gadai = ? 
        ORDER BY tanggal_bayar DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $no_gadai);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<div class='alert alert-info'>Belum ada pembayaran untuk No Gadai ini.</div>";
    exit;
}

echo "<ul class='list-group'>";

while ($row = $result->fetch_assoc()) {
    $id_bayar_encoded = urlencode($row['id_bayar']);
    $id_bayar = htmlspecialchars($row['id_bayar'], ENT_QUOTES, 'UTF-8');
    $tanggal = htmlspecialchars($row['tanggal_bayar'], ENT_QUOTES, 'UTF-8');
    $jumlah_formatted = number_format($row['jumlah_bayar'], 0, ',', '.');
    $keterangan = htmlspecialchars($row['keterangan']);
    
    echo <<<HTML
    <li class="list-group-item d-flex justify-content-between align-items-center">
        <div>
            <strong>ID Bayar:</strong> {$id_bayar} <br>
            <small>Tanggal: {$tanggal}</small><br>
            <small>Jumlah: Rp {$jumlah_formatted}</small><br>
            <small>Keterangan : {$keterangan}</small>
        </div>
         <div>
            <a href="pagenasabah/kwitansi.php?id_bayar={$id_bayar_encoded}" class="btn btn-primary btn-sm" target="_blank">
                <i class="fas fa-receipt"></i> Lihat Kwitansi
            </a>
        </div>
    </li>
HTML;
}

echo "</ul>";
$stmt->close();
?>