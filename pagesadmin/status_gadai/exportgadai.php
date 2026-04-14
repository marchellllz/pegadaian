<?php
session_start();
include "../../config/koneksi.php";
require "../../vendor/autoload.php"; // autoload composer
date_default_timezone_set('Asia/Jakarta');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (!isset($_SESSION['usr']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../gates/login.php');
    exit;
}

// Ambil tarif denda default dari tabel dfbunga
$dendaRate = 1.0; // fallback default 1%
$maxDays = 7; // Maksimal hari denda sebelum dilelang
$resDenda = $conn->query("SELECT tarif FROM dfbunga WHERE bunga='denda' LIMIT 1");
if ($resDenda && $resDenda->num_rows > 0) {
    $rowDenda = $resDenda->fetch_assoc();
    $dendaRate = floatval($rowDenda['tarif']);
}

// --- 1) Update otomatis status menjadi 'dilelang' untuk yang telat lebih dari 7 hari ---
// KECUALI yang sudah lunas
$updateStatusSql = "
    UPDATE gadai
    SET status = 'dilelang'
    WHERE status = 'diterima'
      AND status != 'lunas'
      AND tanggal_keluar IS NOT NULL
      AND DATEDIFF(CURDATE(), tanggal_keluar) > ?
";
if ($stmtStatusUpdate = $conn->prepare($updateStatusSql)) {
    $stmtStatusUpdate->bind_param("i", $maxDays);
    $stmtStatusUpdate->execute();
    $stmtStatusUpdate->close();
}

// --- 2) Update otomatis kolom denda di DB untuk yang telat dan belum lunas ---
// Denda dihitung per hari maksimal 7 hari, HANYA untuk status 'diterima'
$updateSql = "
    UPDATE gadai
    SET denda = (
        nilai_taksir * ? * LEAST(DATEDIFF(CURDATE(), tanggal_keluar), ?) / 100
    )
    WHERE status = 'diterima'
      AND tanggal_keluar IS NOT NULL
      AND CURDATE() > tanggal_keluar
";
if ($stmtUpdate = $conn->prepare($updateSql)) {
    $stmtUpdate->bind_param("di", $dendaRate, $maxDays);
    $stmtUpdate->execute();
    $stmtUpdate->close();
}

// Filter GET
$keyword = isset($_GET['q']) ? trim($_GET['q']) : '';
$statusGadai = isset($_GET['status']) ? trim($_GET['status']) : '';
$statusVerifikasi = isset($_GET['status_verifikasi']) ? trim($_GET['status_verifikasi']) : '';
$filterYear = isset($_GET['tahun']) ? trim($_GET['tahun']) : date("y"); // default tahun sekarang
$cabang = $_SESSION['cabang'] ?? '';

// Query data dengan usia_denda
$sql = "
    SELECT 
        g.*,
        gc.status_verifikasi,
        gc.catatan,
        DATEDIFF(CURDATE(), g.tanggal_masuk) AS usia_hari,
        CASE 
            WHEN g.status = 'dilelang' THEN ?
            WHEN g.status = 'lunas' THEN 0 
            WHEN g.tanggal_keluar IS NOT NULL AND CURDATE() > g.tanggal_keluar 
            THEN LEAST(DATEDIFF(CURDATE(), g.tanggal_keluar), ?) 
            ELSE 0 
        END AS usia_denda,
        IFNULL((
            SELECT SUM(jumlah_bayar) 
            FROM bayar b 
            WHERE b.no_gadai = g.no_gadai AND b.status_bayar = 'V'
        ), 0) AS total_bayar
    FROM gadai g
    LEFT JOIN gadai_confirmed gc ON g.no_gadai = gc.no_gadai
    WHERE 1=1
";

if ($keyword !== '') {
    $sql .= " AND g.nama_nasabah LIKE '%" . $conn->real_escape_string($keyword) . "%'";
}
if ($statusGadai !== '') {
    $sql .= " AND g.status = '" . $conn->real_escape_string($statusGadai) . "'";
}
if ($statusVerifikasi !== '') {
    $sql .= " AND gc.status_verifikasi = '" . $conn->real_escape_string($statusVerifikasi) . "'";
}
if ($filterYear !== '') {
    $sql .= " AND SUBSTRING(g.no_gadai, 7, 2) = '" . $conn->real_escape_string($filterYear) . "'";
}
// Tambahkan filter cabang kalau bukan pusat
if (!empty($cabang) && strtolower($cabang) !== 'mataram') {
    $sql .= " AND g.cabang = '" . $conn->real_escape_string($cabang) . "'";
}
$sql .= " ORDER BY g.tanggal_masuk DESC";

// Prepare and execute query
if ($stmtSelect = $conn->prepare($sql)) {
    $stmtSelect->bind_param("ii", $maxDays, $maxDays);
    $stmtSelect->execute();
    $result = $stmtSelect->get_result();
} else {
    // Fallback jika prepare gagal
    $sql = str_replace('?', $maxDays, $sql);
    $result = $conn->query($sql);
}

// Buat spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Header kolom
$headers = [
    'Nomor Gadai', 'Nomor Nasabah', 'Nama', 'Jenis', 'Jaminan',
    'Tanggal Masuk', 'Tanggal Jatuh Tempo', 'Nilai', 'Nilai Taksir', 'Bunga', 'Biaya Admin', 'Denda Total','Denda Aktif',
    'Usia (Hari)', 'Usia Denda (Hari)', 'Total Tagihan', 'Sisa Tagihan', 
    'Status', 'Status Verifikasi', 'Catatan Supervisor'
];

$col = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($col . '1', $header);
    $col++;
}

// Isi data
$rowNum = 2;
while ($row = $result->fetch_assoc()) {
    $nilai = floatval($row['nilai']);
    $nilaitaksir = floatval($row['nilai_taksir']);
    $bunga = floatval($row['bunga']);
    $biayaadm = floatval($row['biaya_adm']);
    $denda_total = floatval($row['denda_total']);
    $denda = floatval($row['denda']);
    $usia_db = isset($row['usia_hari']) ? intval($row['usia_hari']) : 0;
    $usiaDenda = intval($row['usia_denda']);
    $status = strtolower(trim($row['status']));

    $usia = $usia_db;

    // Untuk status 'lunas', hitung usia denda berdasarkan denda yang tersimpan
    if ($status === 'lunas' && $denda > 0) {
        // Kalkulasi mundur: berapa hari denda berdasarkan jumlah denda yang ada
        $usiaDenda = round(($denda / $nilai) * 100 / $dendaRate);
        // Pastikan tidak melebihi batas maksimal
        $usiaDenda = min($usiaDenda, $maxDays);
    }

    // Hitung total dan sisa tagihan
    $total = $nilaitaksir + $bunga + $denda + $biayaadm;
    $totalBayar = floatval($row['total_bayar']);
    
    // Sisa tagihan = 0 untuk status 'ditolak' atau 'pending'
    if ($status === 'ditolak' || $status === 'pending') {
        $sisaTagihan = 0;
    } else {
        $sisaTagihan = $total - $totalBayar;
    }

    // Format usia denda untuk Excel
    $usiaDendaText = $usiaDenda;
    if ($status === 'lunas' && $usiaDenda > 0) {
        $usiaDendaText = $usiaDenda . " (saat lunas)";
    } elseif ($status === 'dilelang') {
        $usiaDendaText = $usiaDenda . " (max reached)";
    } elseif ($status === 'pending' || $status === 'ditolak') {
        $usiaDendaText = "-";
    }

    $sheet->fromArray([
        $row['no_gadai'],
        $row['nomor_nasabah'],
        $row['nama_nasabah'],
        $row['jenis'],
        $row['jaminan'],
        $row['tanggal_masuk'],
        $row['tanggal_keluar'] ?? '-',
        $nilai,
        $nilaitaksir,
        $bunga,
        $biayaadm,
        $denda_total,
        $denda,
        $usia,
        $usiaDendaText,
        $total,
        ($status === 'ditolak' || $status === 'pending') ? 0 : $sisaTagihan,
        strtoupper($row['status']),
        $row['status_verifikasi'] ?? '-',
        $row['catatan'] ?? '-'
    ], null, "A{$rowNum}");
    $rowNum++;
}

// Auto size kolom
foreach (range('A', 'T') as $c) { // Update sampai kolom Q karena ada 17 kolom
    $sheet->getColumnDimension($c)->setAutoSize(true);
}

// Styling header
$headerRange = 'A1:T1';
$sheet->getStyle($headerRange)->getFont()->setBold(true);
$sheet->getStyle($headerRange)->getFill()
    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
    ->getStartColor()->setRGB('366092');
$sheet->getStyle($headerRange)->getFont()->getColor()->setRGB('FFFFFF');

// Close prepared statement if it was created
if (isset($stmtSelect)) {
    $stmtSelect->close();
}

// Output file
$filename = 'data_gadai_' . date('Ymd_His') . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;