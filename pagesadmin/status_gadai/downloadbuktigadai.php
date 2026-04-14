<?php
require_once __DIR__ . "../../../vendor/autoload.php"; // path ke autoload mpdf
include "../../config/koneksi.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['usr']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../gates/login.php');
    exit;
}
if (!isset($_GET['id'])) {
    die("No Gadai tidak ditemukan.");
}
date_default_timezone_set('Asia/Jakarta');

$petugas = $_SESSION['nama'] ?? 'Petugas';
$noGadai = $_GET['id'];

// Ambil data gadai
$sql = "
SELECT 
    g.no_gadai,
    g.nama_nasabah,
    g.cabang,
    g.jenis,
    g.jaminan,
    g.tanggal_masuk,
    g.tanggal_keluar,
    g.nilai,
    g.nilai_taksir,
    g.bunga,
    g.biaya_adm,

    k.nama AS nama_supervisor

FROM gadai g
LEFT JOIN aktivitas_kerja ak 
    ON ak.aktivitas = CONCAT('Verifikasi Gadai ', g.no_gadai)
LEFT JOIN karyawan k 
    ON k.user_id = ak.operated

WHERE g.no_gadai = ?
LIMIT 1
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $noGadai);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Data tidak ditemukan.");
}
$data = $result->fetch_assoc();
$namaSupervisor = $data['nama_supervisor'] ?? '-';
// HTML untuk PDF - Optimized for single page
$html = '
<style>
    body { 
        font-family: Arial, sans-serif; 
        font-size: 9pt; 
        line-height: 1.2;
        margin: 0;
        padding: 15px;
        color: #333;
    }
    
    .header { 
        text-align: center; 
        border-bottom: 2px solid #2c3e50; 
        padding-bottom: 8px;
        margin-bottom: 15px; 
    }
    
    .header img {
        margin-bottom: 3px;
    }
    
    .header h2 { 
        margin: 3px 0; 
        color: #2c3e50;
        font-size: 14pt;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .header .address {
        font-size: 7pt;
        color: #666;
        line-height: 1.2;
        margin-top: 4px;
    }
    
    .document-title {
        text-align: center;
        margin: 12px 0;
        padding: 6px;
        background-color: #f8f9fa;
        border: 1px solid #2c3e50;
        border-radius: 4px;
    }
    
    .document-title h3 {
        margin: 0;
        color: #2c3e50;
        font-size: 12pt;
        font-weight: bold;
        text-transform: uppercase;
    }
    
    .info { 
        margin: 15px 0;
    }
    
    .info table { 
        width: 100%; 
        border-collapse: collapse;
        margin: 0;
        border: 1px solid #ddd;
    }
    
    .info tr:nth-child(even) {
        background-color: #f8f9fa;
    }
    
    .info td { 
        padding: 6px 10px; 
        vertical-align: top;
        border-bottom: 1px solid #e9ecef;
        font-size: 9pt;
    }
    
    .info td:first-child {
        font-weight: bold;
        color: #2c3e50;
        width: 30%;
        background-color: rgba(44, 62, 80, 0.05);
    }
    
    .monetary {
        font-weight: bold;
        color: #27ae60;
        font-size: 9pt;
    }
    
    .notes {
        margin: 15px 0;
        padding: 8px;
        background-color: #fff3cd;
        border: 1px solid #ffeaa7;
        border-radius: 4px;
        font-size: 8pt;
        line-height: 1.3;
    }
    
    .notes strong {
        color: #856404;
    }
    
    .footer-section {
        margin-top: 40px;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 20px;
        width: 100%;
    }
    
    .signature-box {
        flex: 1;
        text-align: center;
        padding: 15px 10px;
        border: 1px solid #333;
        border-radius: 4px;
        background-color: #ffffff;
        min-height: 100px;
    }
    
    .signature-line {
        border-top: 1px solid #333;
        margin: 25px 10px 5px 10px;
        padding-top: 5px;
    }
    
    .signature-title {
        font-size: 11px;
        color: #666;
        margin-bottom: 50px;
        font-weight: normal;
    }
    
    .signature-name {
        font-weight: bold;
        color: #2c3e50;
        font-size: 9pt;
    }
    
    .print-date {
        font-size: 7pt;
        color: #999;
        text-align: center;
        margin-top: 15px;
        border-top: 1px solid #eee;
        padding-top: 5px;
    }
</style>

<div class="header">
    <img src="../../assets/logo.jpg" width="60">
    <h2>Sobat Gadai</h2>
    <div class="address">
        Jl. MT. Haryono No.613, Karangkidul, Kec. Semarang Tengah, Kota Semarang, Jawa Tengah 50136<br>
        Jl. Majapahit No.22, Pedurungan Lor, Kec. Pedurungan, Kota Semarang, Jawa Tengah 50192
    </div>
</div>

<div class="document-title">
    <h3>Surat Bukti Gadai</h3>
</div>

<div class="info">
    <table>
        <tr>
            <td>No. Gadai</td>
            <td><strong>' . htmlspecialchars($data['no_gadai']) . '</strong></td>
        </tr>
        <tr>
            <td>Nama Nasabah</td>
            <td>' . htmlspecialchars($data['nama_nasabah']) . '</td>
        </tr>
        <tr>
            <td>Cabang</td>
            <td>' . htmlspecialchars($data['cabang']) . '</td>
        </tr>
        <tr>
            <td>Jenis Gadai</td>
            <td>' . htmlspecialchars($data['jenis']) . '</td>
        </tr>
        <tr>
            <td>Barang Jaminan</td>
            <td>' . htmlspecialchars($data['jaminan']) . '</td>
        </tr>
        <tr>
            <td>Tanggal Masuk</td>
            <td>' . date('d F Y', strtotime($data['tanggal_masuk'])) . '</td>
        </tr>
        <tr>
            <td>Tanggal Jatuh Tempo</td>
            <td>' . ($data['tanggal_keluar'] ? date('d F Y', strtotime($data['tanggal_keluar'])) : "<em>Belum Ditentukan</em>") . '</td>
        </tr>
        <tr>
            <td>Nilai Pasar</td>
            <td class="monetary">Rp ' . number_format($data['nilai'], 0, ",", ".") . '</td>
        </tr>
        <tr>
            <td>Nilai Pinjaman</td>
            <td class="monetary">Rp ' . number_format($data['nilai_taksir'], 0, ",", ".") . '</td>
        </tr>
        <tr>
            <td>Bunga</td>
            <td class="monetary">Rp ' . number_format($data['bunga'], 0, ",", ".") . '</td>
        </tr>
        <tr>
            <td>Biaya Admin</td>
            <td class="monetary">Rp ' . number_format($data['biaya_adm'], 0, ",", ".") . '</td>
        </tr>
    </table>
</div>

<div class="notes">
    <strong>Catatan Penting:</strong> Keterlambatan pembayaran hanya diperbolehkan maksimal <strong>7 hari</strong> sejak tanggal jatuh tempo. 
    Setelah itu, barang akan <strong>dilelang</strong>. Denda dikenakan sebesar <strong>1% per hari</strong> dari nilai pinjaman. 
    Untuk perpanjangan, wajib melunasi bunga, biaya admin dan denda (jika ada). Biaya bunga dan admin akan ditagihkan kembali dengan jumlah yang sama jika melakukan perpanjangan.
</div>

<div class="footer-section">
    <div class="signature-box">
        <div class="signature-title">Petugas yang Melayani</div>
        <div class="signature-line">
            <div class="signature-name">' . htmlspecialchars($petugas) . '</div>
        </div>
    </div>

    <div class="signature-box">
        <div class="signature-title">Supervisor</div>
        <div class="signature-line">
            <div class="signature-name">' . htmlspecialchars($namaSupervisor) . '</div>
        </div>
    </div>

    <div class="signature-box">
        <div class="signature-title">Nasabah yang Mengajukan</div>
        <div class="signature-line">
            <div class="signature-name">' . htmlspecialchars($data['nama_nasabah']) . '</div>
        </div>
    </div>
</div>

<div class="print-date">
    Dokumen dicetak pada: ' . date('d F Y, H:i:s') . ' WIB
</div>
';

// Load mPDF dengan konfigurasi optimal untuk single page
$config = [
    'mode' => 'utf-8',
    'format' => 'A4',
    'margin_left' => 15,
    'margin_right' => 15,
    'margin_top' => 15,
    'margin_bottom' => 15,
    'margin_header' => 0,
    'margin_footer' => 0,
    'orientation' => 'P'
];

$mpdf = new \Mpdf\Mpdf($config);

// Set auto page break off untuk kontrol penuh
$mpdf->SetAutoPageBreak(false);

$mpdf->WriteHTML($html);

// Set nama file dan download
$filename = 'Bukti_Gadai_' . $data['no_gadai'] . '_' . date('Ymd_His') . '.pdf';
$mpdf->Output($filename, 'D'); // 'D' untuk download langsung
exit;
