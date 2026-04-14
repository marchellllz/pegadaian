<?php
require_once __DIR__ . '/../vendor/autoload.php'; // Composer autoload
require_once __DIR__ . '/../config/koneksi.php';
date_default_timezone_set('Asia/Jakarta');
$id = isset($_GET['id_bayar']) ? urldecode($_GET['id_bayar']) : '';

// Siapkan mPDF
$mpdf = new \Mpdf\Mpdf(['format' => 'A4']);
$mpdf->SetTitle('Kwitansi_Pembayaran');

// Jika tidak ada id_bayar
if (empty($id)) {
    $html = '
    <div style="text-align:center; font-family:Arial; margin-top:100px;">
        <h2 style="color:#dc3545;">❌ Kwitansi Tidak Valid</h2>
        <p>Parameter <strong>id_bayar</strong> tidak ditemukan.</p>
    </div>';
    $mpdf->WriteHTML($html);
    $mpdf->Output('Kwitansi_Invalid.pdf', 'I');
    exit;
}

// Ambil data pembayaran + gadai
$stmt = $conn->prepare("
    SELECT b.*, g.nama_nasabah, g.jaminan, g.tanggal_masuk
    FROM bayar b
    JOIN gadai g ON b.no_gadai = g.no_gadai
    WHERE b.id_bayar = ?
");
$stmt->bind_param("s", $id);
$stmt->execute();
$res = $stmt->get_result();

if (!$row = $res->fetch_assoc()) {
    $html = '
    <div style="text-align:center; font-family:Arial; margin-top:100px;">
        <h2 style="color:#dc3545;">❌ Kwitansi Tidak Ditemukan</h2>
        <p>Data pembayaran dengan ID <strong>' . htmlspecialchars($id) . '</strong> tidak tersedia.</p>
    </div>';
    $mpdf->WriteHTML($html);
    $mpdf->Output('Kwitansi_Invalid.pdf', 'I');
    exit;
}

// Status pembayaran
if ($row['status_bayar'] === '-') {
    $status_text = 'Belum Diverifikasi';
    $status_color = '#ffc107';
} elseif ($row['status_bayar'] === 'x') {
    $status_text = 'Tidak Valid';
    $status_color = '#dc3545';
} elseif ($row['status_bayar'] === 'V') {
    $status_text = 'Terverifikasi';
    $status_color = '#28a745';
} else {
    $status_text = '-';
    $status_color = '#6c757d';
}

// HTML Kwitansi
$html = '
<style>
    body { font-family: Arial, sans-serif; font-size: 12pt; color:#333; }
    .header { text-align:center; border-bottom:2px solid #2c3e50; padding-bottom:12px; margin-bottom:20px; }
    .company-name { font-size:20pt; font-weight:bold; color:#2c3e50; }
    .title { font-size:18pt; font-weight:bold; margin:15px 0; color:#2c3e50; text-align:center; }
    .info-table, .payment-table { width:100%; border-collapse:collapse; margin-bottom:15px; }
    .info-table td, .payment-table td { padding:8px 12px; border:1px solid #dee2e6; }
    .info-table td:first-child, .payment-table td:first-child { font-weight:bold; width:30%; background:#f8f9fa; }
    .amount { font-size:14pt; font-weight:bold; color:#28a745; }
    .status-badge { padding:4px 10px; border-radius:12px; font-size:10pt; font-weight:bold; color:#fff; background:' . $status_color . '; }
    .proof { text-align:center; margin:20px 0; }
    .proof img { max-width:250px; max-height:150px; border:1px solid #ccc; border-radius:4px; }
    .footer-info { text-align:center; font-size:10pt; color:#666; margin-top:20px; border-top:1px solid #eee; padding-top:10px; }
</style>

<div class="header">
    <img src="../assets/logo.jpeg" width="90"><br>
    <div class="company-name">Sobat Gadai</div>
    <small>Jl. MT. Haryono No.613 & Jl. Majapahit No.22, Semarang</small><br>
    <small>Jl. Majapahit No.22, Pedurungan Lor, Kec. Pedurungan, Kota Semarang, Jawa Tengah 50192</small>
</div>

<div class="title">Kwitansi Pembayaran</div>

<table class="info-table">
    <tr><td>No. Kwitansi</td><td>' . htmlspecialchars($row['id_bayar']) . '</td><td>Tanggal Bayar</td><td>' . date('d F Y', strtotime($row['tanggal_bayar'])) . '</td></tr>
    <tr><td>No. Gadai</td><td>' . htmlspecialchars($row['no_gadai']) . '</td><td>Status Verifikasi</td><td><span class="status-badge">' . $status_text . '</span></td></tr>
    <tr><td>Nama Nasabah</td><td>' . htmlspecialchars($row['nama_nasabah']) . '</td><td>Barang Jaminan</td><td>' . htmlspecialchars($row['jaminan']) . '</td></tr>
</table>

<table class="payment-table">
    <tr><td>Metode Pembayaran</td><td>' . htmlspecialchars($row['metode_bayar']) . '</td></tr>
    <tr><td>Jumlah Dibayar</td><td class="amount">Rp ' . number_format($row['jumlah_bayar'], 0, ',', '.') . '</td></tr>
    <tr><td>Keterangan</td><td>' . (!empty($row['keterangan']) ? htmlspecialchars($row['keterangan']) : '-') . '</td></tr>
</table>

<div class="footer-info">
    Dicetak pada: ' . date('d F Y, H:i:s') . ' WIB<br>
    Kwitansi ini sah sebagai bukti pembayaran.
</div>
';

// Tambahkan watermark kalau status V
if ($row['status_bayar'] === 'V') {
    $mpdf->SetWatermarkText('VALID', 0.1);
    $mpdf->showWatermarkText = true;
}

$mpdf->WriteHTML($html);
$mpdf->Output('Kwitansi_' . $row['id_bayar'] . '.pdf', 'I');