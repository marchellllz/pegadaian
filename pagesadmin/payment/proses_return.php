<?php
include_once("../../config/koneksi.php");
session_start();
if (!isset($_SESSION['usr']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../gates/login.php');
    exit;
}

$username = $_SESSION['usr'];
$nama     = $_SESSION['nama'];
$role     = $_SESSION['role'];
$cabang   = $_SESSION['cabang'];
// Ambil data dari form
$id_bayar_input = $_POST['id_bayar'];
$no_gadai       = $_POST['no_gadai'];
$tanggal_bayar  = $_POST['tanggal_bayar'];
$jumlah_bayar   = $_POST['jumlah_bayar'];
$metode_bayar   = $_POST['metode_bayar'];
$keterangan     = $_POST['keterangan'];
$status_bayar   = '-'; // default

// ======== Format id_bayar jadi 3 digit belakang ========
$parts = explode("/", $id_bayar_input);
if (count($parts) === 4) {
    // Pastikan bagian terakhir jadi 3 digit
    $parts[3] = str_pad($parts[3], 3, '0', STR_PAD_LEFT);
    $id_bayar = implode("/", $parts);
} else {
    // Kalau gak sesuai format, tetep pake input tapi pastikan tanpa spasi
    $id_bayar = trim($id_bayar_input);
}

// Buat versi tanpa slash untuk nama file
$idBayarClean = str_replace("/", "", $id_bayar);

// ========== Upload bukti bayar ==========
$uploadDir = "../../assets/buktibayar/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Ambil ekstensi file asli
$imageFileType = strtolower(pathinfo($_FILES['bukti_bayar']['name'], PATHINFO_EXTENSION));

// Nama file: id_bayar_clean.ext
$namaFile = $idBayarClean . "." . $imageFileType;
$targetFile = $uploadDir . $namaFile;

// Validasi file
$allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
if (!in_array($imageFileType, $allowedTypes)) {
    die("Format file tidak diizinkan.");
}

if (!move_uploaded_file($_FILES['bukti_bayar']['tmp_name'], $targetFile)) {
    die("Gagal mengunggah file.");
}

// Simpan path relatif
$bukti_bayar_path = $uploadDir . $namaFile;

if (isset($_POST['keterangan']) && 
    ($_POST['keterangan'] === 'Klblelang' || $_POST['keterangan'] === 'Klbbayar')) {
    $jumlah_bayar = -abs($jumlah_bayar);
}

// ========== Insert ke tabel bayar ==========
$stmt = $conn->prepare("INSERT INTO bayar 
    (id_bayar, no_gadai, cabang,tanggal_bayar, jumlah_bayar, metode_bayar, bukti_bayar, keterangan, status_bayar) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssdssss", 
    $id_bayar, 
    $no_gadai,
    $cabang, 
    $tanggal_bayar, 
    $jumlah_bayar, 
    $metode_bayar, 
    $bukti_bayar_path, 
    $keterangan, 
    $status_bayar
);

if (!$stmt->execute()) {
    die("Error insert bayar: " . $stmt->error);
}
$stmt->close();

// ========== Insert ke tabel log_bayar ==========
date_default_timezone_set('Asia/Jakarta');
$aktivitas = "Mencatat pengembalian ".$no_gadai;
$tanggalLog = date("Y-m-d H:i:s");

$stmtLog = $conn->prepare("INSERT INTO log_bayar (id_bayar, aktivitas, tanggal, operated) VALUES (?, ?, ?, ?)");
$stmtLog->bind_param("ssss", $id_bayar, $aktivitas, $tanggalLog, $username);
if (!$stmtLog->execute()) {
    die("Error insert log: " . $stmtLog->error);
}
$stmtLog->close();

// Redirect setelah sukses
echo "<script>
        alert('Transaksi berhasil disimpan!');
        window.location.href = 'home_payment.php';
    </script>";
exit;
?>