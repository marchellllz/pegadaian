<?php
session_start();
include "../../config/koneksi.php";
if (!isset($_SESSION['usr']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../gates/login.php');
    exit;
}

$username = $_SESSION['usr'];

$nama = $_POST['nama'];
$alamat = $_POST['alamat'];
$ibu = $_POST['ibu_kandung'];
$nohp = $_POST['nohp'];
$jenis_id = $_POST['jenis_id'];
$no_id = $_POST['nomor_id'];
$cbg = $_SESSION['cabang'];

function sensorNomorID($id)
{
    $len = strlen($id);
    if ($len <= 6) {
        // Kalau kurang dari / sama dengan 6 digit, sensor semua kecuali 1 digit awal & 1 akhir
        return substr($id, 0, 1) . str_repeat('*', $len - 2) . substr($id, -1);
    }
    return substr($id, 0, 3) . str_repeat('*', $len - 6) . substr($id, -3);
}

$no_id_asli = $no_id; // simpan kalau perlu di tempat lain
$no_id_sensored = sensorNomorID($no_id);

// Cek apakah nasabah sudah ada
$cek = $conn->prepare("
    SELECT nomor_id 
    FROM nasabah_gadai 
    WHERE jenis_id = ? AND nomor_id = ? 
");
$cek->bind_param("ss", $jenis_id, $no_id_sensored);
$cek->execute();
$cek->store_result();
if ($cek->num_rows > 0) {
    header("Location: form_tambahnasabah.php?pesan=Nasabah dengan ID ini sudah ada");
    exit;
}
$cek->close();

$jml_transaksi = 0;
$conn->begin_transaction();
try {
    // 1. Insert ke nasabah
    $stmt = $conn->prepare("
        INSERT INTO nasabah_gadai (nama_nasabah, ibu_kandung, alamat, nohp, cabang, jenis_id, nomor_id, jml_transaksi)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("sssssssi", $nama, $ibu, $alamat, $nohp, $cbg, $jenis_id, $no_id_sensored, $jml_transaksi);
    if (!$stmt->execute()) {
        throw new Exception("Gagal insert: " . $stmt->error);
    }
    $stmt->close();

    // 2. Insert ke aktivitas kerja
    // Pastikan timezone sesuai
    date_default_timezone_set('Asia/Jakarta');

    // Ambil datetime sekarang
    $datetime = date('Y-m-d H:i:s');
    $stmt1 = $conn->prepare("
        INSERT INTO aktivitas_kerja (aktivitas, tanggal, operated, cabang)
        VALUES (?, ?, ?, ?)
    ");
    $aktivitas = 'Tambah Data Nasabah '. $nama;
    $stmt1->bind_param("ssss", $aktivitas, $datetime, $username, $cbg);
    if (!$stmt1->execute()) {
        throw new Exception("Gagal insert aktivitas: " . $stmt1->error);
    }
    $stmt1->close();


    // Commit kalau semua sukses
    $conn->commit();
    header("Location: form_tambahnasabah.php?sukses=Nasabah berhasil ditambahkan!");
    exit;
} catch (Exception $e) {
    // Rollback kalau ada error
    $conn->rollback();
    error_log("[Signup Error] " . $e->getMessage());
    header("Location: form_tambahnasabah.php?pesan=Gagal mendaftar. Silakan coba lagi!");
    exit;
}
$conn->close();
