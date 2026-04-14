<?php
include "../../config/koneksi.php";
session_start();
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['usr']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'supervisor') {
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

// Ambil keyword & filter status
$keyword = isset($_GET['q']) ? trim($_GET['q']) : '';
$filterStatus = isset($_GET['status']) ? trim($_GET['status']) : '';
$filterYear = isset($_GET['tahun']) ? trim($_GET['tahun']) : date("y"); // default tahun sekarang
$cabang = $_SESSION['cabang'] ?? '';
// Pagination setup
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Query count
$countSql = "SELECT COUNT(*) AS total FROM gadai g WHERE 1=1";
if ($keyword !== '') {
    $countSql .= " AND g.nama_nasabah LIKE '%" . $conn->real_escape_string($keyword) . "%'";
}
if ($filterStatus !== '') {
    $countSql .= " AND g.status = '" . $conn->real_escape_string($filterStatus) . "'";
}
if ($filterYear !== '') {
    $countSql .= " AND SUBSTRING(g.no_gadai, 7, 2) = '" . $conn->real_escape_string($filterYear) . "'";
}
// Filter cabang (kecuali pusat)
if (!empty($cabang) && strtolower($cabang) !== 'mataram') {
    $countSql .= " AND g.cabang = '" . $conn->real_escape_string($cabang) . "'";
}
$countRes = $conn->query($countSql);
$totalRows = ($countRes && $countRes->num_rows > 0) ? $countRes->fetch_assoc()['total'] : 0;
$totalPages = ceil($totalRows / $limit);

// Query data
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

// Filter cabang (kalau bukan pusat)
if ($_SESSION['cabang'] !== 'mataram') {
    $cabang = $conn->real_escape_string($_SESSION['cabang']);
    $sql .= " AND g.cabang = '$cabang'";
}
if ($keyword !== '') {
    $sql .= " AND g.nama_nasabah LIKE '%" . $conn->real_escape_string($keyword) . "%'";
}
if ($filterStatus !== '') {
    $sql .= " AND g.status = '" . $conn->real_escape_string($filterStatus) . "'";
}
if ($filterYear !== '') {
    $escapedStatus = $conn->real_escape_string($filterYear);
    $sql .= " AND SUBSTRING_INDEX(SUBSTRING_INDEX(g.no_gadai, '/', -2), '/', 1) = '{$escapedStatus}'";
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
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proses Gadai - Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --bs-primary-rgb: 13, 110, 253;
            --shadow-light: 0 2px 10px rgba(0, 0, 0, 0.1);
            --shadow-medium: 0 4px 20px rgba(0, 0, 0, 0.15);
            --border-radius: 12px;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .main-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-medium);
            margin: 20px;
            padding: 0;
            overflow: hidden;
        }

        .header-section {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 30px;
            border-radius: var(--border-radius) var(--border-radius) 0 0;
        }

        .header-title {
            font-size: 2.2rem;
            font-weight: 700;
            margin: 0;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .header-subtitle {
            opacity: 0.9;
            margin-top: 8px;
        }

        .content-section {
            padding: 30px;
        }

        .info-card {
            background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-light);
            color: white;
        }

        .info-card .card-body {
            padding: 25px;
        }

        .info-card h6 {
            font-weight: 600;
            margin-bottom: 15px;
            font-size: 1.1rem;
        }

        .info-card ul li {
            margin-bottom: 8px;
            padding-left: 5px;
        }

        .search-filter-card {
            background: white;
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-light);
            margin-bottom: 25px;
        }

        .search-filter-card .card-body {
            padding: 25px;
        }

        .form-control,
        .form-select {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .btn {
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .btn-success {
            background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #636e72 0%, #2d3436 100%);
        }

        .table-container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-light);
            overflow: hidden;
        }

        .table {
            margin: 0;
            font-size: 0.9rem;
        }

        .table thead th {
            background: linear-gradient(135deg, #2d3436 0%, #636e72 100%);
            color: white;
            border: none;
            padding: 15px 12px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }

        .table tbody td {
            padding: 15px 12px;
            vertical-align: middle;
            border-color: #f1f3f4;
        }

        /* Status Row Colors - Sesuai Permintaan */
        .status-lunas {
            background-color: #cce7ff !important;
            /* Biru muda */
        }

        .status-pending {
            background-color: #fff3cd !important;
            /* Kuning */
        }

        .status-ditolak {
            background-color: #f8d7da !important;
            /* Merah muda */
        }

        .status-diterima {
            background-color: #d1e7dd !important;
            /* Hijau muda */
        }

        .status-dilelang {
            background-color: #e2e3e5 !important;
            /* Abu-abu */
        }

        .badge {
            font-size: 0.75rem;
            padding: 8px 12px;
            border-radius: 6px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .denda-warning {
            color: #dc3545;
            font-weight: bold;
        }

        .denda-danger {
            color: #fff;
            background: linear-gradient(135deg, #e17055 0%, #d63031 100%);
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .pagination {
            justify-content: center;
            margin-top: 30px;
        }

        .page-link {
            border: 2px solid #e9ecef;
            color: #667eea;
            padding: 10px 15px;
            margin: 0 3px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .page-link:hover {
            background: #667eea;
            border-color: #667eea;
            color: white;
            transform: translateY(-2px);
        }

        .page-item.active .page-link {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: #667eea;
        }

        .stats-row {
            background: linear-gradient(135deg, #fdcb6e 0%, #e17055 100%);
            color: white;
            padding: 20px;
            border-radius: var(--border-radius);
            margin-bottom: 25px;
            box-shadow: var(--shadow-light);
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            display: block;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-top: 5px;
        }

        @media (max-width: 768px) {
            .main-container {
                margin: 10px;
            }

            .header-section,
            .content-section {
                padding: 20px;
            }

            .header-title {
                font-size: 1.8rem;
            }

            .table-responsive {
                font-size: 0.8rem;
            }
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="main-container">
            <!-- Header Section -->
            <div class="header-section">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="header-title">
                            <i class="fas fa-gem me-3"></i>
                            Manajemen Gadai
                        </h1>
                        <p class="header-subtitle mb-0">
                            <i class="fas fa-chart-line me-2"></i>
                            Dashboard Proses Transaksi Gadai
                        </p>
                    </div>
                    <div class="text-end">
                        <div class="badge bg-light text-dark fs-6 p-3">
                            <i class="fas fa-calendar-alt me-2"></i>
                            <?= date('d F Y') ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Section -->
            <div class="content-section">
                <!-- Statistics Row -->
                <div class="stats-row">
                    <div class="row">
                        <?php
                        // Hitung statistik
                        $statsQuery = "
                        SELECT 
                        status,
                        COUNT(*) as jumlah,
                        SUM(nilai) as total_nilai
                        FROM gadai
                        WHERE 1=1
                        ";

                        // Filter cabang biar cabang lain nggak ikut nimbrung
                        if ($_SESSION['cabang'] !== 'mataram') {
                            $cabang = $conn->real_escape_string($_SESSION['cabang']);
                            $statsQuery .= " AND cabang = '$cabang'";
                        }

                        $statsQuery .= " GROUP BY status";
                        $statsResult = $conn->query($statsQuery);
                        $stats = [];
                        while ($row = $statsResult->fetch_assoc()) {
                            $stats[$row['status']] = $row;
                        }
                        ?>
                        <div class="col-md-2 col-6">
                            <div class="stat-item">
                                <span class="stat-number"><?= $stats['pending']['jumlah'] ?? 0 ?></span>
                                <div class="stat-label">Pending</div>
                            </div>
                        </div>
                        <div class="col-md-2 col-6">
                            <div class="stat-item">
                                <span class="stat-number"><?= $stats['diterima']['jumlah'] ?? 0 ?></span>
                                <div class="stat-label">Diterima</div>
                            </div>
                        </div>
                        <div class="col-md-2 col-6">
                            <div class="stat-item">
                                <span class="stat-number"><?= $stats['lunas']['jumlah'] ?? 0 ?></span>
                                <div class="stat-label">Lunas</div>
                            </div>
                        </div>
                        <div class="col-md-2 col-6">
                            <div class="stat-item">
                                <span class="stat-number"><?= $stats['ditolak']['jumlah'] ?? 0 ?></span>
                                <div class="stat-label">Ditolak</div>
                            </div>
                        </div>
                        <div class="col-md-2 col-6">
                            <div class="stat-item">
                                <span class="stat-number"><?= $stats['dilelang']['jumlah'] ?? 0 ?></span>
                                <div class="stat-label">Dilelang</div>
                            </div>
                        </div>
                        <div class="col-md-2 col-6">
                            <div class="stat-item">
                                <span class="stat-number"><?= $totalRows ?></span>
                                <div class="stat-label">Total</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info Denda Card -->
                <div class="card info-card mb-4">
                    <div class="card-body">
                        <h6><i class="fas fa-info-circle me-2"></i>Informasi Denda & Status:</h6>
                        <div class="row">
                            <div class="col-md-8">
                                <ul class="mb-0">
                                    <li><strong>Denda:</strong> <?= $dendaRate ?>% per hari setelah tanggal jatuh tempo (hanya untuk status <strong>DITERIMA</strong>)</li>
                                    <li><strong>Maksimal denda:</strong> <?= $maxDays ?> hari (<?= $dendaRate * $maxDays ?>%)</li>
                                    <li>Setelah <?= $maxDays ?> hari, status otomatis berubah menjadi <strong>DILELANG</strong></li>
                                    <li>Status <strong>LUNAS</strong> dan <strong>DILELANG</strong>: Denda tidak bertambah lagi</li>
                                </ul>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex flex-column gap-2">
                                    <div class="d-flex align-items-center">
                                        <div class="status-lunas rounded p-2 me-2" style="width: 30px; height: 20px;"></div>
                                        <small>Lunas</small>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <div class="status-diterima rounded p-2 me-2" style="width: 30px; height: 20px;"></div>
                                        <small>Diterima</small>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <div class="status-pending rounded p-2 me-2" style="width: 30px; height: 20px;"></div>
                                        <small>Pending</small>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <div class="status-ditolak rounded p-2 me-2" style="width: 30px; height: 20px;"></div>
                                        <small>Ditolak</small>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <div class="status-dilelang rounded p-2 me-2" style="width: 30px; height: 20px;"></div>
                                        <small>Dilelang</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Search & Filter Card -->
                <div class="card search-filter-card">
                    <div class="card-body">
                        <form method="GET" action="">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-search me-2"></i>Cari Nasabah
                                    </label>
                                    <input class="form-control" type="search" name="q"
                                        placeholder="Masukkan nama nasabah..."
                                        value="<?= htmlspecialchars($keyword) ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-filter me-2"></i>Filter Status
                                    </label>
                                    <select class="form-select" name="status">
                                        <option value="">-- Semua Status --</option>
                                        <option value="pending" <?= $filterStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="diterima" <?= $filterStatus === 'diterima' ? 'selected' : '' ?>>Diterima</option>
                                        <option value="ditolak" <?= $filterStatus === 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
                                        <option value="lunas" <?= $filterStatus === 'lunas' ? 'selected' : '' ?>>Lunas</option>
                                        <option value="dilelang" <?= $filterStatus === 'dilelang' ? 'selected' : '' ?>>Dilelang</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-calendar-alt me-2"></i>Filter Tahun
                                    </label>
                                    <select class="form-select" name="tahun">
                                        <?php
                                        $currentYear = date("y"); // contoh: 2025 -> '25'
                                        $startYear = 24; // mulai dari tahun 2025
                                        for ($y = $startYear; $y <= $currentYear; $y++) {
                                            $selected = ($filterYear == $y) ? 'selected' : '';
                                            echo "<option value='$y' $selected>20$y</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold text-transparent">Action</label>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-primary flex-fill" type="submit">
                                            <i class="fas fa-search me-2"></i>Filter
                                        </button>
                                        <?php if ($keyword !== '' || $filterStatus !== '' || $filterYear !== ''): ?>
                                            <a href="datagadai.php" class="btn btn-secondary">
                                                <i class="fas fa-times me-2"></i>Reset
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex gap-3 mb-4">
                    <form action="exportgadai.php" method="GET">
                        <input type="hidden" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                        <input type="hidden" name="status" value="<?= htmlspecialchars($_GET['status'] ?? '') ?>">
                        <input type="hidden" name="tahun" value="<?= htmlspecialchars($_GET['tahun'] ?? '') ?>">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-file-excel me-2"></i>Download Excel
                        </button>
                    </form>
                    <a href="aktivitaskerja.php" class="btn btn-info btn-custom">
                        <i class="fas fa-chart-line me-2"></i>Lihat Aktivitas
                    </a>
                    <a href="../home.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
                    </a>
                </div>

                <!-- Data Table -->
                <div class="table-container">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-hashtag me-2"></i>No. Gadai</th>
                                    <th><i class="fas fa-id-card me-2"></i>No. Nasabah</th>
                                    <th><i class="fas fa-user me-2"></i>Nama</th>
                                    <th><i class="fas fa-tag me-2"></i>Jenis</th>
                                    <th><i class="fas fa-shield-alt me-2"></i>Jaminan</th>
                                    <th><i class="fas fa-calendar-plus me-2"></i>Tgl Masuk</th>
                                    <th><i class="fas fa-calendar-minus me-2"></i>Tgl Jatuh Tempo</th>
                                    <th><i class="fas fa-money-bill me-2"></i>Nilai Barang</th>
                                    <th><i class="fas fa-money-bill me-2"></i>Nilai Taksir</th>
                                    <th><i class="fas fa-percentage me-2"></i>Bunga</th>
                                    <th><i class="fas fa-receipt me-2"></i>Biaya Admin</th>
                                    <th><i class="fas fa-exclamation-triangle me-2"></i>Denda Total (Periode Lalu)</th>
                                    <th><i class="fas fa-exclamation-triangle me-2"></i>Denda Aktif</th>
                                    <th><i class="fas fa-clock me-2"></i>Usia</th>
                                    <th><i class="fas fa-hourglass-half me-2"></i>Usia Denda</th>
                                    <th><i class="fas fa-calculator me-2"></i>Total</th>
                                    <th><i class="fas fa-balance-scale me-2"></i>Sisa</th>
                                    <th><i class="fas fa-flag me-2"></i>Status</th>
                                    <th><i class="fas fa-cogs me-2"></i>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()):
                                    $nilai = floatval($row['nilai']);
                                    $nilaitaksir = floatval($row['nilai_taksir']);
                                    $bunga = floatval($row['bunga']);
                                    $biayaadm = floatval($row['biaya_adm']);
                                    $denda_total = floatval($row['denda_total']);
                                    $denda = floatval($row['denda']);
                                    $usia = intval($row['usia_hari']);
                                    $usiaDenda = intval($row['usia_denda']);
                                    $status = strtolower(trim($row['status']));

                                    // Hitung total dan sisa tagihan
                                    $total = $nilaitaksir + $bunga + $denda + $denda_total + $biayaadm;
                                    $totalBayar = floatval($row['total_bayar']);

                                    // Untuk status 'lunas', hitung usia denda berdasarkan denda yang tersimpan
                                    if ($status === 'lunas' && $denda > 0) {
                                        // Kalkulasi mundur: berapa hari denda berdasarkan jumlah denda yang ada
                                        $usiaDenda = round(($denda / $nilai) * 100 / $dendaRate);
                                        // Pastikan tidak melebihi batas maksimal
                                        $usiaDenda = min($usiaDenda, $maxDays);
                                    }

                                    // Sisa tagihan = 0 untuk status 'ditolak' atau 'pending'
                                    if ($status === 'ditolak' || $status === 'pending') {
                                        $sisaTagihan = 0;
                                    } else {
                                        $sisaTagihan = $total - $totalBayar;
                                    }

                                    // Tentukan class CSS berdasarkan status
                                    $rowClass = 'status-' . $status;
                                ?>
                                    <tr class="<?= $rowClass ?>">
                                        <td class="fw-bold"><?= htmlspecialchars($row['no_gadai']) ?></td>
                                        <td><?= htmlspecialchars($row['nomor_nasabah']) ?></td>
                                        <td class="fw-semibold"><?= htmlspecialchars($row['nama_nasabah']) ?></td>
                                        <td><?= htmlspecialchars($row['jenis']) ?></td>
                                        <td><?= htmlspecialchars($row['jaminan']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($row['tanggal_masuk'])) ?></td>
                                        <td><?= $row['tanggal_keluar'] ? date('d/m/Y', strtotime($row['tanggal_keluar'])) : '-' ?></td>
                                        <td class="fw-bold text-primary">Rp <?= number_format($nilai, 0, ',', '.') ?></td>
                                        <td class="fw-bold text-primary">Rp <?= number_format($nilaitaksir, 0, ',', '.') ?></td>
                                        <td>Rp <?= number_format($bunga, 0, ',', '.') ?></td>
                                        <td>Rp <?= number_format($biayaadm, 0, ',', '.') ?></td>
                                        <td>Rp <?= number_format($denda_total, 0, ',', '.') ?></td>
                                        <td>
                                            <span class="<?= $denda > 0 ? 'denda-warning' : '' ?>">
                                                Rp <?= number_format($denda, 0, ',', '.') ?>
                                            </span>
                                            <?php if ($denda > 0): ?>
                                                <small class="d-block text-muted">
                                                    (<?= $dendaRate ?>% × <?= $usiaDenda ?> hari)
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?= $usia ?> hari</span>
                                        </td>
                                        <td>
                                            <?php if ($usiaDenda > 0): ?>
                                                <span class="<?= $usiaDenda >= $maxDays ? 'denda-danger' : 'badge bg-warning text-dark' ?>">
                                                    <?= $usiaDenda ?> / <?= $maxDays ?> hari
                                                </span>
                                                <?php if ($usiaDenda >= $maxDays): ?>
                                                    <small class="d-block text-danger fw-bold">MAX REACHED</small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="badge bg-light text-dark">0 hari</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="fw-bold">Rp <?= number_format($total, 0, ',', '.') ?></td>
                                        <td>
                                            <?php if ($status === 'ditolak' || $status === 'pending'): ?>
                                                <span class="text-muted">-</span>
                                                <small class="d-block text-muted">
                                                    <?= $status === 'pending' ? 'Belum disetujui' : 'Ditolak' ?>
                                                </small>
                                            <?php else: ?>
                                                <span class="fw-bold <?= $sisaTagihan > 0 ? 'text-danger' : 'text-success' ?>">
                                                    Rp <?= number_format($sisaTagihan, 0, ',', '.') ?>
                                                </span>
                                                <?php if ($sisaTagihan <= 0 && $status === 'lunas'): ?>
                                                    <small class="d-block text-success fw-bold">
                                                        <i class="fas fa-check-circle me-1"></i>LUNAS
                                                    </small>
                                                <?php elseif ($sisaTagihan > 0): ?>
                                                    <small class="d-block text-danger">
                                                        <i class="fas fa-exclamation-circle me-1"></i>Belum lunas
                                                    </small>
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
                                                                            echo 'secondary';
                                                                            break;
                                                                        default:
                                                                            echo 'dark';
                                                                    }
                                                                    ?>">
                                                <i class="fas fa-<?php
                                                                    switch ($status) {
                                                                        case 'pending':
                                                                            echo 'clock';
                                                                            break;
                                                                        case 'diterima':
                                                                            echo 'check';
                                                                            break;
                                                                        case 'ditolak':
                                                                            echo 'times';
                                                                            break;
                                                                        case 'lunas':
                                                                            echo 'check-double';
                                                                            break;
                                                                        case 'dilelang':
                                                                            echo 'gavel';
                                                                            break;
                                                                        default:
                                                                            echo 'question';
                                                                    }
                                                                    ?> me-1"></i>
                                                <?= strtoupper($row['status']) ?>
                                            </span>
                                            <?php if ($status === 'dilelang'): ?>
                                                <small class="d-block text-danger mt-1 fw-bold">
                                                    <i class="fas fa-robot me-1"></i>Auto-lelang
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($status !== 'dilelang'): ?>
                                                <a href="form_prosesgadai.php?no_gadai=<?= urlencode($row['no_gadai']) ?>"
                                                    class="btn btn-success btn-sm">
                                                    <i class="fas fa-cog me-1"></i>Proses
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted small">
                                                    <i class="fas fa-ban me-1"></i>Tidak dapat diproses
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <nav aria-label="Pagination">
                        <ul class="pagination">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $page - 1 ?><?= $keyword !== '' ? '&q=' . urlencode($keyword) : '' ?><?= $filterStatus !== '' ? '&status=' . urlencode($filterStatus) : '' ?>">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php
                            $start = max(1, $page - 2);
                            $end = min($totalPages, $page + 2);

                            if ($start > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=1<?= $keyword !== '' ? '&q=' . urlencode($keyword) : '' ?><?= $filterStatus !== '' ? '&status=' . urlencode($filterStatus) : '' ?>">1</a>
                                </li>
                                <?php if ($start > 2): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php for ($i = $start; $i <= $end; $i++): ?>
                                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?><?= $keyword !== '' ? '&q=' . urlencode($keyword) : '' ?><?= $filterStatus !== '' ? '&status=' . urlencode($filterStatus) : '' ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($end < $totalPages): ?>
                                <?php if ($end < $totalPages - 1): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $totalPages ?><?= $keyword !== '' ? '&q=' . urlencode($keyword) : '' ?><?= $filterStatus !== '' ? '&status=' . urlencode($filterStatus) : '' ?>"><?= $totalPages ?></a>
                                </li>
                            <?php endif; ?>

                            <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $page + 1 ?><?= $keyword !== '' ? '&q=' . urlencode($keyword) : '' ?><?= $filterStatus !== '' ? '&status=' . urlencode($filterStatus) : '' ?>">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>

                <!-- Footer Info -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card border-0 bg-light">
                            <div class="card-body py-3">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Menampilkan <?= min($limit, $totalRows - $offset) ?> dari <?= $totalRows ?> data
                                    <?php if ($keyword !== ''): ?>
                                        | Pencarian: "<?= htmlspecialchars($keyword) ?>"
                                    <?php endif; ?>
                                    <?php if ($filterStatus !== ''): ?>
                                        | Filter: <?= strtoupper($filterStatus) ?>
                                    <?php endif; ?>
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 bg-light">
                            <div class="card-body py-3 text-end">
                                <small class="text-muted">
                                    <i class="fas fa-clock me-2"></i>
                                    Last Updated: <?= date('d F Y, H:i:s') ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS for Enhanced Interactions -->
    <script>
        // Add smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Add loading state to buttons
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Loading...';

                    setTimeout(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }, 3000);
                }
            });
        });

        // Add hover effects to table rows
        document.querySelectorAll('tbody tr').forEach(row => {
            row.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.01)';
                this.style.transition = 'all 0.2s ease';
                this.style.boxShadow = '0 4px 15px rgba(0,0,0,0.1)';
            });

            row.addEventListener('mouseleave', function() {
                this.style.transform = 'scale(1)';
                this.style.boxShadow = 'none';
            });
        });

        // Auto-refresh data setiap 5 menit
        setInterval(function() {
            const url = new URL(window.location);
            url.searchParams.set('refresh', Date.now());

            // Show notification
            const notification = document.createElement('div');
            notification.className = 'alert alert-info alert-dismissible fade show position-fixed';
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            notification.innerHTML = `
                <i class="fas fa-sync-alt fa-spin me-2"></i>
                Data sedang diperbarui...
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(notification);

            setTimeout(() => {
                location.reload();
            }, 2000);
        }, 300000); // 5 menit

        // Add search suggestions (jika diperlukan)
        const searchInput = document.querySelector('input[name="q"]');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                if (this.value.length >= 2) {
                    // Implement search suggestions here if needed
                    console.log('Searching for:', this.value);
                }
            });
        }
    </script>

    <?php
    // Close prepared statement if it was created
    if (isset($stmtSelect)) {
        $stmtSelect->close();
    }
    ?>

</body>

</html>