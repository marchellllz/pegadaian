<?php
require_once __DIR__ . '/../../vendor/autoload.php'; // Composer autoload
include_once("../../config/koneksi.php");
session_start();
date_default_timezone_set('Asia/Jakarta');
if (!isset($_SESSION['usr']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../gates/login.php');
    exit;
}

$id = $_GET['id'] ?? '';
if ($id == '') {
    die("ID tidak ditemukan.");
}

// Ambil data pembayaran + data gadai
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
    die("Data tidak ditemukan.");
}

// Tentukan status verifikasi
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
    body { 
        font-family: Arial, sans-serif; 
        font-size: 12pt; 
        line-height: 1.4;
        margin: 0;
        padding: 15px;
        color: #333;
    }
    
    .header { 
        text-align: center; 
        border-bottom: 2px solid #2c3e50;
        padding-bottom: 12px;
        margin-bottom: 20px;
    }
    
    .header img {
        margin-bottom: 8px;
    }
    
    .company-name {
        font-size: 20pt;
        font-weight: bold;
        color: #2c3e50;
        margin: 6px 0;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .address {
        font-size: 10pt;
        color: #666;
        line-height: 1.4;
        margin: 5px 0;
    }
    
    .title { 
        font-size: 18pt; 
        font-weight: bold;
        color: #2c3e50;
        margin: 15px 0 12px 0;
        text-transform: uppercase;
        letter-spacing: 1px;
        background-color: #f8f9fa;
        padding: 8px;
        border: 1px solid #2c3e50;
        border-radius: 4px;
    }
    
    .info-section {
        background-color: #fff;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        overflow: hidden;
        margin: 15px 0;
    }
    
    .info-table { 
        width: 100%; 
        border-collapse: collapse; 
        margin: 0;
    }
    
    .info-table tr:nth-child(even) {
        background-color: #f8f9fa;
    }
    
    .info-table tr:nth-child(odd) {
        background-color: #ffffff;
    }
    
    .info-table td { 
        padding: 8px 12px; 
        vertical-align: top;
        border-bottom: 1px solid #e9ecef;
        font-size: 11pt;
    }
    
    .info-table td:first-child {
        font-weight: bold;
        color: #2c3e50;
        width: 22%;
        background-color: rgba(44, 62, 80, 0.05);
    }
    
    .payment-section {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 12px;
        margin: 18px 0;
    }
    
    .payment-table {
        width: 100%;
        border-collapse: collapse;
        background-color: white;
        border-radius: 4px;
        overflow: hidden;
    }
    
    .payment-table td {
        padding: 10px 15px;
        border-bottom: 1px solid #dee2e6;
        font-size: 11pt;
    }
    
    .payment-table td:first-child {
        background-color: #e9ecef;
        font-weight: bold;
        color: #2c3e50;
        width: 35%;
    }
    
    .payment-table tr:last-child td {
        border-bottom: none;
    }
    
    .amount {
        font-size: 14pt;
        font-weight: bold;
        color: #28a745;
    }
    
    .status-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 10pt;
        font-weight: bold;
        color: white;
        background-color: ' . $status_color . ';
    }
    
    .proof-section {
        text-align: center;
        margin: 20px 0;
        padding: 15px;
        background-color: #f8f9fa;
        border-radius: 4px;
        border: 1px dashed #dee2e6;
    }
    
    .proof-image {
        max-width: 250px;
        max-height: 150px;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .proof-caption {
        font-size: 10pt;
        color: #666;
        margin-top: 8px;
        font-style: italic;
    }
    
    .signature-section {
        margin-top: 35px;
        page-break-inside: avoid;
    }
    
    .signature-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .signature-cell {
        text-align: center;
        padding: 15px;
        width: 50%;
    }
    
    .signature-box {
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 18px;
        background-color: #f8f9fa;
        margin: 0 8px;
    }
    
    .signature-title {
        font-weight: bold;
        color: #2c3e50;
        margin-bottom: 35px;
        font-size: 11pt;
    }
    
    .signature-line {
        border-top: 1px solid #333;
        margin-top: 12px;
        padding-top: 5px;
        font-size: 10pt;
        color: #666;
    }
    
    .footer-info {
        text-align: center;
        font-size: 10pt;
        color: #999;
        margin-top: 20px;
        padding-top: 12px;
        border-top: 1px solid #eee;
    }
</style>

<div class="header">
    <img src="../../assets/logo.jpeg" width="100">
    <div class="company-name">Sobat Gadai</div>
    <div class="address">
        Jl. MT. Haryono No.613, Karangkidul, Kec. Semarang Tengah, Kota Semarang, Jawa Tengah 50136<br>
        Jl. Majapahit No.22, Pedurungan Lor, Kec. Pedurungan, Kota Semarang, Jawa Tengah 50192
    </div>
</div>

<div class="title">Kwitansi Pembayaran</div>

<div class="info-section">
    <table class="info-table">
        <tr>
            <td>No. Kwitansi</td>
            <td>' . htmlspecialchars($row['id_bayar']) . '</td>
            <td>Tanggal Bayar</td>
            <td>' . date('d F Y', strtotime($row['tanggal_bayar'])) . '</td>
        </tr>
        <tr>
            <td>No. Gadai</td>
            <td>' . htmlspecialchars($row['no_gadai']) . '</td>
            <td>Status Verifikasi</td>
            <td><span class="status-badge">' . $status_text . '</span></td>
        </tr>
        <tr>
            <td>Nama Nasabah</td>
            <td>' . htmlspecialchars($row['nama_nasabah']) . '</td>
            <td>Barang Jaminan</td>
            <td>' . htmlspecialchars($row['jaminan']) . '</td>
        </tr>
    </table>
</div>

<div class="payment-section">
    <table class="payment-table">
        <tr>
            <td>Metode Pembayaran</td>
            <td>' . htmlspecialchars($row['metode_bayar']) . '</td>
        </tr>
        <tr>
            <td>Jumlah Dibayar</td>
            <td class="amount">Rp ' . number_format($row['jumlah_bayar'], 0, ',', '.') . '</td>
        </tr>
        <tr>
            <td>Keterangan</td>
            <td>' . (!empty($row['keterangan']) ? htmlspecialchars($row['keterangan']) : '<em>-</em>') . '</td>
        </tr>
    </table>
</div>

<div class="proof-section">
    <img src="'.$row['bukti_bayar'] . '" class="proof-image">
    <div class="proof-caption">Bukti Transfer Pembayaran</div>
</div>

<div class="signature-section">
    <table class="signature-table">
        <tr>
            <td class="signature-cell">
                <div class="signature-box">
                    <div class="signature-title">Penerima</div><br>
                    <div class="signature-line">( Nasabah )</div>
                </div>
            </td>
            <td class="signature-cell">
                <div class="signature-box">
                    <div class="signature-title">Mengetahui</div><br>
                    <div class="signature-line">( Admin )</div>
                </div>
            </td>
        </tr>
    </table>
</div>

<div class="footer-info">
    Dokumen dicetak pada: ' . date('d F Y, H:i:s') . ' WIB<br>
    Kwitansi ini sah sebagai bukti pembayaran yang telah diverifikasi
</div>
';

// Generate PDF
$mpdf = new \Mpdf\Mpdf(['format' => 'A4']);
$mpdf->SetTitle('Kuitansi_' . $row['id_bayar']);

// Tambahkan watermark LUNAS kalau status verifikasi V
if ($row['status_bayar'] === 'V') {
    $mpdf->SetWatermarkText('VALID', 0.1); // transparansi 0.1
    $mpdf->showWatermarkText = true;
}

$mpdf->WriteHTML($html);
$mpdf->Output('Kwitansi_' . $row['id_bayar'] . '.pdf', 'D');