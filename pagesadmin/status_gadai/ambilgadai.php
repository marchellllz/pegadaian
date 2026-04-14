<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usr'])) {
    header('Location: ../gates/login.php');
    exit;
}
$cabang = $_SESSION['cabang'] ?? '';
// Ambil koneksi dan dendaRate dari file pemanggil
// $conn, $dendaRate sudah tersedia

// Setup variabel denda
$maxDays = 7; // Maksimal hari denda sebelum dilelang

// Ambil kata kunci dan filter dari GET
$keyword = isset($_GET['q']) ? trim($_GET['q']) : '';
$statusGadai = isset($_GET['status']) ? trim($_GET['status']) : '';
$statusVerifikasi = isset($_GET['status_verifikasi']) ? trim($_GET['status_verifikasi']) : '';
$filterYear = isset($_GET['tahun']) ? trim($_GET['tahun']) : date("y"); // default tahun sekarang

// Pagination setup
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

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

// --- 3) Hitung total rows untuk pagination ---
$countSql = "
    SELECT COUNT(*) AS total 
    FROM gadai g
    LEFT JOIN gadai_confirmed gc ON g.no_gadai = gc.no_gadai
    WHERE 1=1
";

if ($keyword !== '') {
    $escaped = $conn->real_escape_string($keyword);
    $countSql .= " AND g.nama_nasabah LIKE '%{$escaped}%'";
}

if ($statusGadai !== '') {
    $escapedStatus = $conn->real_escape_string($statusGadai);
    $countSql .= " AND g.status = '{$escapedStatus}'";
}

if ($statusVerifikasi !== '') {
    $escapedStatus = $conn->real_escape_string($statusVerifikasi);
    $countSql .= " AND gc.status_verifikasi = '{$escapedStatus}'";
}
if ($filterYear !== '') {
    $escapedStatus = $conn->real_escape_string($filterYear);
    $countSql .= " AND SUBSTRING(g.no_gadai, 7, 2) = '{$escapedStatus}'";
}
// Tambahkan filter cabang kalau bukan pusat
if (!empty($cabang) && strtolower($cabang) !== 'mataram') {
    $escapedCbg = $conn->real_escape_string($cabang);
    $countSql .= " AND g.cabang = '{$escapedCbg}'";
}
$resCount = $conn->query($countSql);
$totalRows = ($resCount && $resCount->num_rows > 0) ? $resCount->fetch_assoc()['total'] : 0;
$totalPages = ceil($totalRows / $limit);

// --- 4) Ambil data untuk ditampilkan ---
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
    $escaped = $conn->real_escape_string($keyword);
    $sql .= " AND g.nama_nasabah LIKE '%{$escaped}%'";
}

if ($statusGadai !== '') {
    $escapedStatus = $conn->real_escape_string($statusGadai);
    $sql .= " AND g.status = '{$escapedStatus}'";
}

if ($statusVerifikasi !== '') {
    $escapedStatus = $conn->real_escape_string($statusVerifikasi);
    $sql .= " AND gc.status_verifikasi = '{$escapedStatus}'";
}

if ($filterYear !== '') {
    $escapedStatus = $conn->real_escape_string($filterYear);
    $sql .= " AND SUBSTRING_INDEX(SUBSTRING_INDEX(g.no_gadai, '/', -2), '/', 1) = '{$escapedStatus}'";
}
// Filter cabang kalau bukan pusat
if (!empty($cabang) && strtolower($cabang) !== 'mataram') {
    $escapedCbg = $conn->real_escape_string($cabang);
    $sql .= " AND g.cabang = '{$escapedCbg}'";
}
$sql .= " ORDER BY g.tanggal_masuk DESC LIMIT $limit OFFSET $offset";

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

if (!$result) {
    echo "<tr><td colspan='17' class='text-danger'>Gagal mengambil data.</td></tr>";
    return;
}

while ($row = $result->fetch_assoc()):
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

    $total = $nilaitaksir + $bunga + $biayaadm + $denda + $denda_total;
    $totalBayar = floatval($row['total_bayar']);

    // Sisa tagihan = 0 untuk status 'ditolak' atau 'pending'
    if ($status === 'ditolak' || $status === 'pending') {
        $sisaTagihan = 0;
    } else {
        $sisaTagihan = $total - $totalBayar;
    }

    // Tentukan class CSS berdasarkan status
    $rowClass = '';
    if ($status === 'dilelang') {
        $rowClass = 'table-danger';
    } elseif ($status === 'lunas') {
        $rowClass = 'table-info';
    } elseif ($status === 'pending') {
        $rowClass = 'table-warning';
    } elseif ($status === 'ditolak') {
        $rowClass = 'table-secondary';
    }
?>
    <tr class="<?= $rowClass ?>">
        <td><?= htmlspecialchars($row['no_gadai']) ?></td>
        <td><?= htmlspecialchars($row['nomor_nasabah']) ?></td>
        <td><?= htmlspecialchars($row['nama_nasabah']) ?></td>
        <td><?= htmlspecialchars($row['jenis']) ?></td>
        <td><?= htmlspecialchars($row['jaminan']) ?></td>
        <td><?= htmlspecialchars($row['tanggal_masuk']) ?></td>
        <td><?= htmlspecialchars($row['tanggal_keluar'] ?? '-') ?></td>
        <td><?= number_format($nilai, 2, ',', '.') ?></td>
        <td><?= number_format($nilaitaksir, 2, ',', '.') ?></td>
        <td><?= number_format($bunga, 2, ',', '.') ?></td>
        <td><?= number_format($biayaadm, 2, ',', '.') ?></td>
        <td><?= number_format($denda_total, 2, ',', '.') ?></td>
        <td class="<?= $denda > 0 ? 'text-warning fw-bold' : '' ?>">
            <?= number_format($denda, 2, ',', '.') ?>
            <?php if ($denda > 0): ?>
                <small class="d-block text-muted">
                    (<?= $dendaRate ?>% × <?= $usiaDenda ?> hari)
                    <?php if ($status === 'lunas' || $status === 'dilelang'): ?>
                        <span class="badge bg-secondary">FINAL</span>
                    <?php endif; ?>
                </small>
            <?php endif; ?>
        </td>
        <td><?= $usia ?></td>
        <td>
            <?php if ($status === 'lunas'): ?>
                <?php $usiaDenda = $usiaDenda; // biar fix di titik terakhir 
                ?>
                <small class="d-block text-success">
                    <i>Lunas saat <?= $usiaDenda ?> hari denda</i>
                </small>
            <?php elseif ($usiaDenda > 0): ?>
                <span class="<?= ($usiaDenda >= $maxDays) ? 'text-white bg-danger px-2 py-1 rounded' : 'text-warning fw-bold' ?>">
                    <?= $usiaDenda ?> / <?= $maxDays ?> hari
                </span>
                <?php if ($usiaDenda >= $maxDays): ?>
                    <small class="d-block text-danger">MAX REACHED</small>
                <?php endif; ?>
            <?php else: ?>
                <span class="text-muted">0 hari</span>
            <?php endif; ?>
        </td>
        <td><?= number_format($total, 2, ',', '.') ?></td>
        <td>
            <?php if ($status === 'ditolak' || $status === 'pending'): ?>
                <span class="text-muted">-</span>
                <small class="d-block text-muted"><?= $status === 'pending' ? 'Belum disetujui' : 'Ditolak' ?></small>
            <?php else: ?>
                <?= number_format($sisaTagihan, 2, ',', '.') ?>
                <?php if ($sisaTagihan <= 0 && $status === 'lunas'): ?>
                    <small class="d-block text-success">LUNAS</small>
                <?php elseif ($sisaTagihan > 0): ?>
                    <small class="d-block text-danger">Belum lunas</small>
                <?php endif; ?>
            <?php endif; ?>
        </td>
        <td>
            <span class="badge bg-<?php
                                    switch ($status) {
                                        case 'pending':
                                            echo 'warning';
                                            break;
                                        case 'diterima':
                                            echo 'success';
                                            break;
                                        case 'ditolak':
                                            echo 'danger';
                                            break;
                                        case 'lunas':
                                            echo 'primary';
                                            break;
                                        case 'dilelang':
                                            echo 'dark';
                                            break;
                                        default:
                                            echo 'secondary';
                                    }
                                    ?>">
                <?= strtoupper($row['status']) ?>
            </span>
            <?php if ($status === 'dilelang'): ?>
                <small class="d-block text-danger mt-1">Auto-lelang</small>
            <?php endif; ?>
        </td>
        <td><?= htmlspecialchars($row['status_verifikasi'] ?? '-') ?></td>
        <td><?= htmlspecialchars($row['catatan'] ?? '-') ?></td>
        <td>
            <a href="downloadbuktigadai.php?id=<?= urlencode($row['no_gadai']) ?>"
                class="btn btn-sm btn-primary" target="_blank">
                <i class="fas fa-download"></i> Bukti
            </a>
        </td>
    </tr>
<?php endwhile;

// Close prepared statement if it was created
if (isset($stmtSelect)) {
    $stmtSelect->close();
}
?>