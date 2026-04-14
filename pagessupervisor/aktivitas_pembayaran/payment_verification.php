<?php
include_once("../../config/koneksi.php");
session_start();

if (!isset($_SESSION['usr']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'supervisor') {
    header('Location: ../../gates/login.php');
    exit;
}

$id_bayar = $_GET['id'] ?? '';
if (!$id_bayar) {
    header('Location: home_payment.php');
    exit;
}
date_default_timezone_set('Asia/Jakarta');
$username = $_SESSION['usr'];

// Start transaction
mysqli_begin_transaction($conn);

try {
    // Ambil data bayar (yang akan diverifikasi)
    $stmt = $conn->prepare("SELECT no_gadai, jumlah_bayar, keterangan FROM bayar WHERE id_bayar = ?");
    $stmt->bind_param("s", $id_bayar);
    $stmt->execute();
    $dataBayar = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$dataBayar) {
        throw new Exception("Data bayar tidak ditemukan");
    }

    $no_gadai = $dataBayar['no_gadai'];
    $jumlah_bayar = (float)$dataBayar['jumlah_bayar'];
    $keterangan = $dataBayar['keterangan'];

    // Ambil data gadai (tagihan asli: nilai + bunga + denda)
    $stmt = $conn->prepare("SELECT nilai_taksir, bunga, denda, biaya_adm FROM gadai WHERE no_gadai = ?");
    $stmt->bind_param("s", $no_gadai);
    $stmt->execute();
    $dataGadai = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$dataGadai) {
        throw new Exception("Data gadai tidak ditemukan");
    }

    $nilai = (float)$dataGadai['nilai_taksir'];
    $bunga = (float)$dataGadai['bunga'];
    $denda = (float)$dataGadai['denda'];
    $biayaadm = (float)$dataGadai['biaya_adm'];
    $total_tagihan = $nilai + $bunga + $denda +$biayaadm;

    // Update status bayar (menandakan pembayaran ini sudah diverifikasi)
    $stmt = $conn->prepare("UPDATE bayar SET status_bayar = 'V' WHERE id_bayar = ?");
    $stmt->bind_param("s", $id_bayar);
    $stmt->execute();
    $stmt->close();

    // HITUNG TOTAL YANG SUDAH DIBAYAR untuk no_gadai ini (hanya yang sudah 'V')
    $stmt = $conn->prepare("SELECT COALESCE(SUM(jumlah_bayar), 0) AS total_paid FROM bayar WHERE no_gadai = ? AND status_bayar = 'V'");
    $stmt->bind_param("s", $no_gadai);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $total_paid = (float)($row['total_paid'] ?? 0);
    $remaining = $total_tagihan - $total_paid; // sisa tagihan (bisa negatif kalau kelebihan bayar)

    // Putusin status gadai berdasarkan total_paid
    if ($remaining <= 0 && strtolower(trim($keterangan)) === 'pelunasan') {
        // lunas
        $stmt = $conn->prepare("UPDATE gadai SET status = 'lunas', denda_total = denda_total + ?, denda = 0 WHERE no_gadai = ?");
        $stmt->bind_param("ds", $denda, $no_gadai);
        $stmt->execute();
        $stmt->close();

        // update tabel verifikasi jika ada
        $stmt = $conn->prepare("UPDATE gadai_confirmed SET status_verifikasi = 'lunas' WHERE no_gadai = ?");
        $stmt->bind_param("s", $no_gadai);
        $stmt->execute();
        $stmt->close();

        $aktivitas = "Verifikasi pembayaran (lunas) : " . $no_gadai;
    } else if (strtolower(trim($keterangan)) === 'perpanjang') {
        // Keterangan adalah 'Perpanjang' -> perpanjang tanggal_keluar +30 hari dan update saldo = bunga + bunga
        // Mengambil bunga sudah dilakukan di atas ($bunga)
        $bungaKey = 'normal';
        $stmt = $conn->prepare("SELECT tarif FROM dfbunga WHERE bunga = ? LIMIT 1");
        $stmt->bind_param("s", $bungaKey);
        $stmt->execute();
        $resBunga = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $adminKey = 'admin';
        $stmt = $conn->prepare("SELECT tarif FROM dfbunga WHERE bunga = ? LIMIT 1");
        $stmt->bind_param("s", $adminKey);
        $stmt->execute();
        $resAdmin = $stmt->get_result()->fetch_assoc();
        $stmt->close();


        if (!$resBunga || !isset($resBunga['tarif'])) {
            throw new Exception("Tarif bunga normal tidak ditemukan di tabel dfbunga");
        }

        $tarif = (float)$resBunga['tarif']; // contoh: 5 => 5%
        $adminKey = (float)$resAdmin['tarif']; // contoh: 5 => 5%

        // Hitung bunga baru berdasarkan nilai_taksir * tarif / 100
        $hitungbungabaru = ($nilai * $tarif) / 100.0;
        $hitungadminbaru = ($nilai * $adminKey) / 100.0; 

        // new saldo = existing bunga (amount) + hitungbungabaru
        $new_saldo = $bunga + $hitungbungabaru;

        // new admin = existing admin + admin baru
        $new_admin = $biayaadm + $hitungadminbaru;

        // Update tanggal_keluar +30 hari dan saldo
        $stmt = $conn->prepare("UPDATE gadai SET tanggal_keluar = DATE_ADD(tanggal_keluar, INTERVAL 30 DAY), bunga = ?, denda_total = denda_total + ?, denda = 0 , biaya_adm = ? WHERE no_gadai = ?");
        $stmt->bind_param("ddds", $new_saldo, $denda, $new_admin, $no_gadai);
        $stmt->execute();
        $stmt->close();

        $aktivitas = "Verifikasi pembayaran (Perpanjangan) " .$no_gadai;
    }
    else if(strtolower(trim($keterangan)) === 'klblelang' || strtolower(trim($keterangan)) === 'klbbayar'){
        $aktivitas = "Verifikasi pengembalian : " .$no_gadai;
    }
    else {
        // masih sisa tagihan (parsial)
        $aktivitas = "Verifikasi pembayaran (Perpanjangan) - sisa Rp " . number_format($remaining, 0, ",", ".");
    }

    // Insert log dengan info total_paid dan sisa
    $tanggal = date("Y-m-d H:i:s");
    $stmt = $conn->prepare("INSERT INTO log_bayar (id_bayar, aktivitas, tanggal, operated) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $id_bayar, $aktivitas, $tanggal, $username);
    $stmt->execute();
    $stmt->close();

    // Commit transaction
    mysqli_commit($conn);

} catch (Exception $e) {
    mysqli_rollback($conn);
    // Jangan keluarkan pesan db mentah-mentah di production, ini untuk debugging lokal
    die("Error: " . $e->getMessage());
}

header("Location: home_payment.php");
exit;
?>