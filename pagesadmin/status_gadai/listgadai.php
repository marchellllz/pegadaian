<?php
session_start();
include "../../config/koneksi.php";
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['usr']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../gates/login.php');
    exit;
}

// Ambil tarif denda default dari tabel dfbunga (fallback 2%)
$dendaRate = 1.0;
$resDenda = $conn->query("SELECT tarif FROM dfbunga WHERE bunga = 'denda' LIMIT 1");
if ($resDenda && $resDenda->num_rows > 0) {
    $rowDenda = $resDenda->fetch_assoc();
    $dendaRate = floatval($rowDenda['tarif']);
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List Gadai - Dashboard Admin</title>
    <link rel="stylesheet" href="../../bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .main-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            margin-top: 2rem;
            margin-bottom: 2rem;
        }

        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 20px 20px 0 0;
            padding: 2rem;
            margin: -15px -15px 2rem -15px;
        }

        .page-header h2 {
            font-weight: 700;
            margin: 0;
            font-size: 2.5rem;
        }

        .page-header .subtitle {
            opacity: 0.9;
            margin-top: 0.5rem;
        }

        .search-form {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 1.5rem;
            border: 2px solid #e9ecef;
            margin-bottom: 2rem;
        }

        .form-control,
        .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .btn {
            border-radius: 10px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            box-shadow: 0 4px 15px rgba(72, 187, 120, 0.3);
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(72, 187, 120, 0.4);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #718096 0%, #4a5568 100%);
            box-shadow: 0 4px 15px rgba(113, 128, 150, 0.3);
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(113, 128, 150, 0.4);
        }

        .action-buttons {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .table-container {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .table {
            margin-bottom: 0;
            border-radius: 10px;
            overflow: hidden;
        }

        .table thead th {
            background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
            color: white;
            font-weight: 600;
            border: none;
            padding: 1rem;
            font-size: 0.9rem;
        }

        .table tbody tr {
            transition: all 0.3s ease;
        }

        .table tbody tr:hover {
            background-color: #f7fafc;
            transform: scale(1.001);
        }

        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-color: #e2e8f0;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-diterima {
            background: #c6f6d5;
            color: #22543d;
        }

        .status-pending {
            background: #fed7c3;
            color: #c05621;
        }

        .status-ditolak {
            background: #fed7d7;
            color: #c53030;
        }

        .status-lunas {
            background: #bee3f8;
            color: #2c5282;
        }

        .status-dilelang {
            background: #e9d8fd;
            color: #553c9a;
        }

        .pagination {
            background: white;
            border-radius: 15px;
            padding: 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-top: 2rem;
        }

        .page-link {
            color: #667eea;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            margin: 0 0.2rem;
            transition: all 0.3s ease;
        }

        .page-link:hover,
        .page-item.active .page-link {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: #667eea;
            color: white;
            transform: translateY(-1px);
        }

        .currency {
            font-weight: 600;
            color: #2d3748;
        }

        .search-icon,
        .filter-icon,
        .download-icon,
        .back-icon {
            margin-right: 0.5rem;
        }

        @media (max-width: 768px) {
            .page-header {
                padding: 1.5rem;
            }

            .page-header h2 {
                font-size: 2rem;
            }

            .search-form {
                padding: 1rem;
            }

            .d-flex.gap-2 {
                flex-direction: column;
                gap: 0.5rem !important;
            }

            .table-container {
                overflow-x: auto;
            }
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="main-container">
                    <!-- Header -->
                    <div class="page-header">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-gem fa-2x me-3"></i>
                            <div>
                                <h2><i class="fas fa-list me-2"></i>Daftar Transaksi Gadai</h2>
                                <p class="subtitle mb-0">Kelola dan pantau semua transaksi gadai nasabah</p>
                            </div>
                        </div>
                    </div>

                    <div class="container-fluid px-4">
                        <!-- Search Form -->
                        <div class="search-form">
                            <form method="GET" action="listgadai.php" class="row g-3" role="search">
                                <div class="col-md-6">
                                    <label for="searchInput" class="form-label fw-bold">
                                        <i class="fas fa-search search-icon"></i>Cari Nasabah
                                    </label>
                                    <input class="form-control" type="search" name="q" id="searchInput"
                                        placeholder="Masukkan nama nasabah..."
                                        value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>">
                                </div>

                                <div class="col-md-2">
                                    <label for="statusSelect" class="form-label fw-bold">
                                        <i class="fas fa-filter filter-icon"></i>Status Verifikasi
                                    </label>
                                    <select name="status" id="statusSelect" class="form-select">
                                        <option value="">Semua Status Verifikasi</option>
                                        <option value="diterima" <?= (isset($_GET['status']) && $_GET['status'] === 'diterima') ? 'selected' : '' ?>>Diterima</option>
                                        <option value="pending" <?= (isset($_GET['status']) && $_GET['status'] === 'pending') ? 'selected' : '' ?>>Pending</option>
                                        <option value="ditolak" <?= (isset($_GET['status']) && $_GET['status'] === 'ditolak') ? 'selected' : '' ?>>Ditolak</option>
                                        <option value="lunas" <?= (isset($_GET['status']) && $_GET['status'] === 'lunas') ? 'selected' : '' ?>>Lunas</option>
                                        <option value="dilelang" <?= (isset($_GET['status']) && $_GET['status'] === 'dilelang') ? 'selected' : '' ?>>Dilelang</option>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-calendar-alt me-2"></i>Filter Tahun
                                    </label>
                                    <select class="form-select" name="tahun">
                                        <option value=''>Pilih Tahun</option>
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

                                <div class="col-md-2 d-flex align-items-end gap-2">
                                    <button class="btn btn-primary flex-fill" type="submit">
                                        <i class="fas fa-search me-1"></i>Cari
                                    </button>
                                    <?php if (!empty($_GET['q']) || !empty($_GET['status']) || !empty($_GET['tahun'])): ?>
                                        <a href="listgadai.php" class="btn btn-secondary">
                                            <i class="fas fa-undo me-1"></i>Reset
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>

                        <!-- Action Buttons -->
                        <div class="action-buttons">
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                <div class="me-auto">
                                    <h5 class="mb-0 text-muted">
                                        <i class="fas fa-cog me-2"></i>Panel Aksi
                                    </h5>
                                </div>

                                <!-- Tombol Download Excel -->
                                <form action="exportgadai.php" method="GET" class="d-inline">
                                    <input type="hidden" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                                    <input type="hidden" name="status" value="<?= htmlspecialchars($_GET['status'] ?? '') ?>">
                                    <input type="hidden" name="tahun" value="<?= htmlspecialchars($_GET['tahun'] ?? '') ?>">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-download download-icon"></i>Download Excel
                                    </button>
                                </form>

                                <!-- Tombol Kembali -->
                                <a href="../home.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left back-icon"></i>Kembali
                                </a>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="table-container">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th><i class="fas fa-hashtag me-1"></i>Nomor Gadai</th>
                                            <th><i class="fas fa-user me-1"></i>Nomor Nasabah</th>
                                            <th><i class="fas fa-id-card me-1"></i>Nama</th>
                                            <th><i class="fas fa-tags me-1"></i>Jenis</th>
                                            <th><i class="fas fa-gem me-1"></i>Jaminan</th>
                                            <th><i class="fas fa-calendar-plus me-1"></i>Tanggal Masuk</th>
                                            <th><i class="fas fa-calendar-minus me-1"></i>Tanggal Jatuh Tempo</th>
                                            <th><i class="fas fa-dollar-sign me-1"></i>Nilai Barang</th>
                                            <th><i class="fas fa-dollar-sign me-1"></i>Nilai Taksiran</th>
                                            <th><i class="fas fa-percentage me-1"></i>Bunga (Rp)</th>
                                            <th><i class="fas fa-receipt me-1"></i>Biaya Admin</th>
                                            <th><i class="fas fa-exclamation-triangle me-1"></i>Denda Total (Periode Lalu)</th>
                                            <th><i class="fas fa-exclamation-triangle me-1"></i>Denda Aktif(Rp)</th>
                                            <th><i class="fas fa-clock me-1"></i>Usia (Hari)</th>
                                            <th><i class="fas fa-calendar-times me-1"></i>Usia Denda</th>
                                            <th><i class="fas fa-calculator me-1"></i>Total Tagihan</th>
                                            <th><i class="fas fa-money-bill me-1"></i>Sisa Tagihan</th>
                                            <th><i class="fas fa-info-circle me-1"></i>Status Gadai</th>
                                            <th><i class="fas fa-check-circle me-1"></i>Status Verifikasi</th>
                                            <th><i class="fas fa-sticky-note me-1"></i>Catatan Supervisor</th>
                                            <th><i class="fas fa-cogs me-1"></i>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php include "ambilgadai.php"; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <?php if (isset($totalPages) && $totalPages > 1): ?>
                            <nav class="pagination">
                                <ul class="pagination justify-content-center mb-0">
                                    <li class="page-item <?= (!isset($page) || $page <= 1) ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?page=<?= max(1, ($page ?? 1) - 1) ?><?= isset($_GET['q']) ? '&q=' . urlencode($_GET['q']) : '' ?><?= isset($_GET['status']) ? '&status=' . urlencode($_GET['status']) : '' ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>

                                    <?php
                                    $currentPage = $page ?? 1;
                                    $startPage = max(1, $currentPage - 2);
                                    $endPage = min($totalPages, $currentPage + 2);

                                    for ($i = $startPage; $i <= $endPage; $i++):
                                    ?>
                                        <li class="page-item <?= ($i == $currentPage) ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?><?= isset($_GET['q']) ? '&q=' . urlencode($_GET['q']) : '' ?><?= isset($_GET['status']) ? '&status=' . urlencode($_GET['status']) : '' ?>">
                                                <?= $i ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <li class="page-item <?= (!isset($page) || $page >= $totalPages) ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?page=<?= min($totalPages, ($page ?? 1) + 1) ?><?= isset($_GET['q']) ? '&q=' . urlencode($_GET['q']) : '' ?><?= isset($_GET['status']) ? '&status=' . urlencode($_GET['status']) : '' ?>">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../../bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add loading animation when forms are submitted
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Loading...';
                    submitBtn.disabled = true;
                }
            });
        });

        // Add smooth scroll animation
        document.documentElement.style.scrollBehavior = 'smooth';
    </script>
</body>

</html>