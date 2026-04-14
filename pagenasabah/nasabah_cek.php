<?php
// pagenasabah/nasabah_cek.php
require_once __DIR__ . '/../config/koneksi.php';
session_start();
$no_gadai = $_GET['no_gadai'] ?? '';
$nohp     = $_GET['nohp'] ?? '';
$captcha  = $_GET['captcha'] ?? '';
if ($no_gadai === '' || $nohp === '') {
  echo "<div class='alert alert-warning'>No Gadai dan No HP wajib diisi.</div>";
  exit;
}
// cek captcha
if (!isset($_SESSION['captcha']) || strtolower($captcha) !== strtolower($_SESSION['captcha'])) {
  echo "<div class='alert alert-danger'>Captcha salah, silakan coba lagi.</div>";
  exit;
}

// 1) Ambil semua data dari GADAI + NOHP dari nasabah_gadai (verifikasi pemilik)
$sqlG = "
SELECT 
  g.no_gadai, g.nomor_nasabah, g.nama_nasabah, g.cabang, g.jenis, g.jaminan,
  g.tanggal_masuk, g.tanggal_keluar, g.nilai, g.nilai_taksir ,g.bunga, g.biaya_adm, g.denda, g.denda_total ,g.status,
  ng.nama_nasabah AS nama_nasabah_ng, ng.nohp
FROM gadai g
LEFT JOIN nasabah_gadai ng ON g.nomor_nasabah = ng.nomor_nasabah
WHERE g.no_gadai = ? AND ng.nohp = ?
LIMIT 1
";
$stmt = $conn->prepare($sqlG);
$stmt->bind_param('ss', $no_gadai, $nohp);
$stmt->execute();
$res = $stmt->get_result();
$g = $res->fetch_assoc();
$stmt->close();

if (!$g) {
  echo "<div class='alert alert-danger'>Data tidak ditemukan atau No HP tidak cocok.</div>";
  exit;
}

// 2) Hitung total bayar dari tabel BAYAR
$sqlSum = "SELECT COALESCE(SUM(jumlah_bayar),0) AS total_bayar FROM bayar WHERE no_gadai = ? AND status_bayar ='V'";
$stmt2 = $conn->prepare($sqlSum);
$stmt2->bind_param('s', $no_gadai);
$stmt2->execute();
$res2 = $stmt2->get_result();
$tot = $res2->fetch_assoc();
$stmt2->close();

$total_tagihan = (float)$g['nilai_taksir'] + (float)$g['bunga'] + (float)$g['biaya_adm'] + (float)$g['denda'] + (float)$g['denda_total'];
$total_bayar   = (float)($tot['total_bayar'] ?? 0);
$sisa_tagihan  = max(0, $total_tagihan - $total_bayar);

// 3) Cetak HTML (lempar ke nasabah_page.php)
?>
<div class="card p-3 mb-3">
  <h5>Data Gadai</h5>
  <div class="row">
    <div class="col-md-6">
      <p class="mb-1"><strong>No Gadai:</strong> <?= htmlspecialchars($g['no_gadai']) ?></p>
      <p class="mb-1"><strong>Nomor Nasabah:</strong> <?= htmlspecialchars((string)$g['nomor_nasabah']) ?></p>
      <p class="mb-1"><strong>Nama Nasabah:</strong> <?= htmlspecialchars($g['nama_nasabah_ng'] ?: $g['nama_nasabah']) ?></p>
      <p class="mb-1"><strong>No HP:</strong> <?= htmlspecialchars($g['nohp']) ?></p>
      <p class="mb-1"><strong>Cabang:</strong> <?= htmlspecialchars($g['cabang']) ?></p>
      <p class="mb-1"><strong>Jenis:</strong> <?= htmlspecialchars($g['jenis']) ?></p>
      <p class="mb-1"><strong>Jaminan:</strong> <?= htmlspecialchars($g['jaminan']) ?></p>
    </div>
    <div class="col-md-6">
      <p class="mb-1"><strong>Tanggal Masuk:</strong> <?= htmlspecialchars($g['tanggal_masuk']) ?></p>
      <p class="mb-1"><strong>Jatuh Tempo:</strong> <?= htmlspecialchars($g['tanggal_keluar']) ?></p>
      <p class="mb-1"><strong>Nilai Pasar:</strong> Rp <?= number_format((float)$g['nilai'], 0, ',', '.') ?></p>
      <p class="mb-1"><strong>Nilai Taksir:</strong> Rp <?= number_format((float)$g['nilai_taksir'], 0, ',', '.') ?></p>
      <p class="mb-1"><strong>Bunga:</strong> Rp <?= number_format((float)$g['bunga'], 0, ',', '.') ?></p>
      <p class="mb-1"><strong>Biaya Admin:</strong> Rp <?= number_format((float)$g['biaya_adm'], 0, ',', '.') ?></p>
      <p class="mb-1"><strong>Denda Aktif:</strong> Rp <?= number_format((float)$g['denda'], 0, ',', '.') ?></p>
      <p class="mb-1"><strong>Denda Total:</strong> Rp <?= number_format((float)$g['denda_total'], 0, ',', '.') ?></p>
      <p class="mb-1"><strong>Status:</strong> <?= htmlspecialchars($g['status']) ?></p>
    </div>
  </div>
  <hr>
  <p class="mb-1"><strong>Total Tagihan (nilai taksir+bunga+admin+denda):</strong> Rp <?= number_format($total_tagihan, 0, ',', '.') ?></p>
  <p class="mb-1"><strong>Sudah Dibayar:</strong> Rp <?= number_format($total_bayar, 0, ',', '.') ?></p>
  <p class="mb-1"><strong>Sisa Tagihan:</strong> <span class="fw-bold">Rp <?= number_format($sisa_tagihan, 0, ',', '.') ?></span></p>

  <!-- Catatan Deadline -->
  <div class="alert alert-warning mt-3 mb-2">
    <i class="bi bi-exclamation-triangle-fill me-1"></i>
    <strong>Catatan:</strong> Keterlambatan pembayaran hanya diperkenankan <span class="fw-bold">maksimal 7 (tujuh) hari sejak tanggal jatuh tempo</span>.
    Apabila melewati batas tersebut, barang gadai akan <span class="text-danger fw-bold">dilelang</span>.
    Denda keterlambatan dikenakan sebesar <span class="text-danger fw-bold">1% dari nilai pinjaman per hari</span> setelah tanggal jatuh tempo.
    Untuk perpanjangan, debitur wajib melunasi biaya bunga, denda (jika ada), serta biaya administrasi. Jumlah pembayaran yang ditagihkan merupakan akumulasi dari periode sebelumnya ditambah periode perpanjangan.
  </div>

  <div class="mt-2">
    <button class="btn btn-info me-2" onclick="loadBayar('<?= htmlspecialchars($g['no_gadai']) ?>')">Riwayat Pembayaran</button>
  </div>
</div>

<div id="riwayatBayar" class="mb-3"></div>
<div id="detailBayar"></div>