<?php
session_start();
include "../../../config/koneksi.php";
date_default_timezone_set('Asia/Jakarta');

// Ambil datetime sekarang
$datetime = date('Y-m-d H:i:s');

// Cek method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Akses tidak diizinkan");
}

$username = $_SESSION['usr'];
$cabang = $_SESSION['cabang'];
// Ambil data dari form
$nomor_nasabah  = intval($_POST['nomor_nasabah']);
$name = trim($_POST['nama_nasabah']);
$jenis          = trim($_POST['jenis']);
$jaminan        = trim($_POST['jaminan']);
$tanggal_masuk  = $_POST['tanggal_masuk'];
$tanggal_keluar = $_POST['tanggal_keluar'] ?: null;
$nilai          = floatval($_POST['nilai']);
$nilai_taksir   = floatval($_POST['nilai_taksir']);
$bunga          = floatval($_POST['jumlah_bunga']);
$admin          = floatval($_POST['biaya_adm']);
$denda          = floatval($_POST['denda']);
$status         = trim($_POST['status']);

// ===== Generate no_gadai otomatis =====
$currentMonth = date('m', strtotime($tanggal_masuk));
$currentYear  = date('Y', strtotime($tanggal_masuk));
$todayFormat  = date('d/m/y', strtotime($tanggal_masuk));

// Cari no urut terakhir bulan ini
$sqlLast = "
    SELECT no_gadai 
    FROM gadai 
    WHERE MONTH(tanggal_masuk) = ? 
      AND YEAR(tanggal_masuk) = ?
    ORDER BY no_gadai DESC 
    LIMIT 1
";
$stmtLast = $conn->prepare($sqlLast);
$stmtLast->bind_param("ii", $currentMonth, $currentYear);
$stmtLast->execute();
$resultLast = $stmtLast->get_result();

if ($resultLast && $resultLast->num_rows > 0) {
    $last = $resultLast->fetch_assoc()['no_gadai'];
    $parts = explode('/', $last);
    $urut = intval(end($parts)) + 1;
} else {
    $urut = 1;
}
$stmtLast->close();

$map = [
    'mataram'   => 'SG11',
    'majapahit' => 'SG12',
    'ambarawa'  => 'SG13',
];
$key = strtolower(trim($_SESSION['cabang']));
$kode_cabang = $map[$key] ?? 'SG00'; // default kalau ga ketemu
$no_gadai = $kode_cabang.'/'.$todayFormat . '/' . $urut;

// Simpan ke tabel gadai
$stmt = $conn->prepare("
    INSERT INTO gadai 
    (no_gadai, nomor_nasabah, nama_nasabah, cabang, jenis, jaminan, tanggal_masuk, tanggal_keluar, nilai, nilai_taksir ,bunga, biaya_adm, denda, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "sissssssddddds",
    $no_gadai,
    $nomor_nasabah,
    $name,
    $cabang,
    $jenis,
    $jaminan,
    $tanggal_masuk,
    $tanggal_keluar,
    $nilai,
    $nilai_taksir,
    $bunga,
    $admin,
    $denda,
    $status
);

if ($stmt->execute()) {
    // Update jumlah transaksi nasabah
    $updateStmt = $conn->prepare("
        UPDATE nasabah_gadai
        SET jml_transaksi = jml_transaksi + 1
        WHERE nomor_nasabah = ?
    ");
    $updateStmt->bind_param("i", $nomor_nasabah);
    $updateStmt->execute();
    $updateStmt->close();

    // Catat aktivitas kerja
    $aktivitas = "Tambah Gadai ". $name;
    $logStmt = $conn->prepare("
        INSERT INTO aktivitas_kerja (aktivitas, tanggal, operated, cabang)
        VALUES (?, ?, ?, ?)
    ");
    $logStmt->bind_param("ssss", $aktivitas, $datetime, $username, $cabang);
    $logStmt->execute();
    $logStmt->close();

    echo "<script>
        alert('Transaksi berhasil disimpan!');
        window.location.href = '../datanasabah.php';
    </script>";
} else {
    echo "<script>
        alert('Gagal menyimpan data: " . addslashes($stmt->error) . "');
        window.history.back();
    </script>";
}
