<?php
session_start();
include "../../config/koneksi.php";
if (!isset($_SESSION['usr']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../gates/login.php');
    exit;
}

$username = $_SESSION['usr'];
$cbg = $_SESSION['cabang'];
$id = $_POST['id'];
$nama = $_POST['nama'];
$ibu = $_POST['ibu_kandung'];
$alamat = $_POST['alamat'];
$nohp = $_POST['nohp'];
$jenis_id = $_POST['jenis_id'];
$no_id = $_POST['nomor_id'];


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

$jml_transaksi = 0;
$conn->begin_transaction();
try {
    // 1. Insert ke nasabah
    $stmt = $conn->prepare("
        UPDATE nasabah_gadai 
        SET nama_nasabah = ?, ibu_kandung = ?,alamat = ?, nohp = ?, jenis_id = ?, nomor_id = ?
        WHERE nomor_nasabah = ?
    ");
    $stmt->bind_param("ssssssi", $nama, $ibu, $alamat, $nohp, $jenis_id, $no_id_sensored, $id);
    if (!$stmt->execute()) {
        throw new Exception("Gagal update: " . $stmt->error);
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
    $aktivitas = 'Edit Data Nasabah ' . $nama;
    $stmt1->bind_param("ssss", $aktivitas, $datetime, $username, $cbg);
    if (!$stmt1->execute()) {
        throw new Exception("Gagal insert aktivitas: " . $stmt1->error);
    }
    $stmt1->close();


    // Commit kalau semua sukses
    $conn->commit();
    header("Location: form_updatenasabah.php?sukses=Pendaftaran berhasil. Silakan login.");
    exit;
} catch (Exception $e) {
    // Rollback kalau ada error
    $conn->rollback();
    error_log("[Signup Error] " . $e->getMessage());
    header("Location: form_updatenasabah.php?pesan=Gagal mengubah. Silakan coba lagi!");
    exit;
}
$conn->close();
