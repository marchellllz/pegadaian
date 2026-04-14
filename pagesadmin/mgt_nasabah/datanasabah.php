<?php
session_start();
if (!isset($_SESSION['usr']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../gates/login.php');
    exit;
}

$username = $_SESSION['usr'];
$nama = $_SESSION['nama'];
$role = $_SESSION['role'];
$cabang = $_SESSION['cabang'];
include_once("../../config/koneksi.php");

// Pagination Setup
$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Hitung total
$totalQuery = "
    SELECT COUNT(*) as total 
    FROM nasabah_gadai 
    WHERE 1=1
";

// Filter cabang biar cabang lain nggak ikut nimbrung
if ($_SESSION['cabang'] !== 'mataram') {
    $cabang = $conn->real_escape_string($_SESSION['cabang']);
    $totalQuery .= " AND cabang = '$cabang'";
}

$totalResult = mysqli_query($conn, $totalQuery);
$total = mysqli_fetch_assoc($totalResult)['total'];
$totalPages = ceil($total / $limit);

// Ambil data nasabah
$sql = "
    SELECT *
    FROM nasabah_gadai
    WHERE 1=1
";

// Filter cabang biar cabang lain nggak ikut nimbrung
if ($_SESSION['cabang'] !== 'mataram') {
    $cabang = $conn->real_escape_string($_SESSION['cabang']);
    $sql .= " AND cabang = '$cabang'";
}

$sql .= " LIMIT $offset, $limit";

$result = mysqli_query($conn, $sql);
$dataNasabah = [];
while ($row = mysqli_fetch_assoc($result)) {
    $dataNasabah[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Nasabah - Sistem Gadai</title>
    <link rel="stylesheet" href="../../bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #a8e6cf 0%, #88d8a3 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .main-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .header-section {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border-radius: 20px 20px 0 0;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }
        
        .header-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }
        
        .stats-card {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
        }
        
        .search-container {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            border: 2px solid #e9ecef;
        }
        
        .btn-custom {
            border-radius: 25px;
            padding: 0.6rem 1.5rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }
        
        .table-container {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid #e9ecef;
        }
        
        .table thead th {
            background: linear-gradient(135deg, #343a40 0%, #495057 100%);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 1rem;
            border: none;
        }
        
        .table tbody tr:hover {
            background: rgba(40, 167, 69, 0.05);
            transform: scale(1.01);
            transition: all 0.2s ease;
        }
        
        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-color: #e9ecef;
        }
        
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }
        
        .pagination .page-link {
            border-radius: 10px;
            margin: 0 2px;
            border: 2px solid #28a745;
            color: #28a745;
            font-weight: 600;
        }
        
        .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border-color: #28a745;
        }
        
        .pagination .page-link:hover {
            background: #28a745;
            border-color: #28a745;
            color: white;
        }
        
        .fade-in {
            animation: fadeIn 0.6s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .icon-bounce {
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }
    </style>
</head>

<body>
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-11">
                <div class="main-container fade-in">
                    <!-- Header Section -->
                    <div class="header-section">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h1 class="display-5 fw-bold mb-2">
                                    <i class="fas fa-users me-3 icon-bounce"></i>
                                    Data Nasabah
                                </h1>
                                <p class="lead mb-0">Kelola data nasabah sistem gadai dengan mudah</p>
                            </div>
                            <div class="col-md-4">
                                <div class="stats-card p-3 text-center">
                                    <h3 class="fw-bold mb-1"><?= number_format($total) ?></h3>
                                    <p class="mb-0"><i class="fas fa-user-check me-2"></i>Total Nasabah</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Content Section -->
                    <div class="p-4">
                        <!-- Search Container -->
                        <div class="search-container">
                            <div class="row align-items-end">
                                <div class="col-md-8 mb-3 mb-md-0">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-search me-2"></i>Pencarian Nasabah
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0">
                                            <i class="fas fa-search text-muted"></i>
                                        </span>
                                        <input type="text" class="form-control border-start-0 ps-0" 
                                               placeholder="Cari berdasarkan Nomor atau Nama Nasabah..." 
                                               id="searchInput">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-success btn-custom flex-fill" id="searchBtn">
                                            <i class="fas fa-search me-2"></i>Cari
                                        </button>
                                        <button class="btn btn-outline-secondary btn-custom flex-fill" id="resetBtn" style="display: none;">
                                            <i class="fas fa-undo me-2"></i>Reset
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="form_tambahnasabah.php" class="btn btn-success btn-custom">
                                        <i class="fas fa-user-plus me-2"></i>Tambah Nasabah
                                    </a>
                                    <a href="aktivitaskerja.php" class="btn btn-info btn-custom">
                                        <i class="fas fa-chart-line me-2"></i>Lihat Aktivitas
                                    </a>
                                    <a href="../home.php" class="btn btn-secondary btn-custom ms-auto">
                                        <i class="fas fa-arrow-left me-2"></i>Kembali
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Dynamic Table (Hidden initially) -->
                        <div id="tabelNasabah" class="table-container mb-4" style="display: none;"></div>
                        <div id="pagination" class="text-center mb-4"></div>

                        <!-- Static Table -->
                        <div id="staticTableWrapper" class="table-container">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-hashtag me-2"></i>No</th>
                                        <th><i class="fas fa-user me-2"></i>Nama Nasabah</th>
                                        <th><i class="fas fa-user me-2"></i>Ibu Kandung</th>
                                        <th><i class="fas fa-map-marker-alt me-2"></i>Alamat</th>
                                        <th><i class="fas fa-phone me-2"></i>No HP</th>
                                        <th><i class="fas fa-id-card me-2"></i>Jenis ID</th>
                                        <th><i class="fas fa-barcode me-2"></i>Nomor ID</th>
                                        <th><i class="fas fa-exchange-alt me-2"></i>Transaksi</th>
                                        <th colspan="2" class="text-center"><i class="fas fa-cogs me-2"></i>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($dataNasabah) > 0): ?>
                                        <?php foreach ($dataNasabah as $i => $k): ?>
                                            <tr>
                                                <td>
                                                    <span class="badge bg-primary rounded-pill">
                                                        <?= htmlspecialchars($k['nomor_nasabah']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="fw-semibold"><?= htmlspecialchars($k['nama_nasabah']) ?></div>
                                                </td>
                                                <td><?= htmlspecialchars($k['ibu_kandung']) ?></td>
                                                <td><?= htmlspecialchars($k['alamat']) ?></td>
                                                <td>
                                                    <span class="text-success fw-semibold">
                                                        <i class="fas fa-phone-alt me-1"></i>
                                                        <?= htmlspecialchars($k['nohp']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">
                                                        <?= htmlspecialchars($k['jenis_id']) ?>
                                                    </span>
                                                </td>
                                                <td><?= htmlspecialchars($k['nomor_id']) ?></td>
                                                <td>
                                                    <span class="badge bg-warning text-dark fs-6">
                                                        <?= htmlspecialchars($k['jml_transaksi']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="form_updatenasabah.php?id=<?= urlencode($k['nomor_nasabah']) ?>"
                                                        class="btn btn-sm btn-outline-success btn-custom"
                                                        onclick="return confirm('Yakin ingin mengubah data nasabah ini?')">
                                                        <i class="fas fa-edit me-1"></i>Ubah
                                                    </a>
                                                </td>
                                                <td>
                                                    <form method="POST" action="gadai_activity/halamangadai.php" 
                                                          onsubmit="return confirm('Mulai proses gadai untuk nasabah ini?')" class="d-inline">
                                                        <input type="hidden" name="id" value="<?= htmlspecialchars($k['nomor_nasabah']) ?>">
                                                        <button type="submit" class="btn btn-primary btn-sm btn-custom">
                                                            <i class="fas fa-coins me-1"></i>Mulai Gadai
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="9" class="text-center py-5">
                                                <div class="text-muted">
                                                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                                    <h5>Belum ada data nasabah</h5>
                                                    <p>Silakan tambah nasabah baru untuk memulai</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                        <nav class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $page - 1 ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $page + 1 ?>">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function loadNasabah(page = 1) {
            const keyword = document.getElementById('searchInput').value.trim();
            fetch(`ambilnasabah.php?page=${page}&search=${encodeURIComponent(keyword)}`)
                .then(res => res.json())
                .then(res => {
                    if (res.status !== 'success') {
                        document.getElementById('tabelNasabah').innerHTML = '<p class="text-danger">Gagal memuat data.</p>';
                        return;
                    }

                    const data = res.data;
                    const offset = res.offset;
                    let html = `
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th><i class="fas fa-hashtag me-2"></i>Nomor</th>
                        <th><i class="fas fa-user me-2"></i>Nama Nasabah</th>
                        <th><i class="fas fa-user me-2"></i>Ibu Kandung</th>
                        <th><i class="fas fa-map-marker-alt me-2"></i>Alamat</th>
                        <th><i class="fas fa-phone me-2"></i>No HP</th>
                        <th><i class="fas fa-id-card me-2"></i>Jenis ID</th>
                        <th><i class="fas fa-barcode me-2"></i>Nomor ID</th>
                        <th><i class="fas fa-exchange-alt me-2"></i>Transaksi</th>
                        <th colspan="2" class="text-center"><i class="fas fa-cogs me-2"></i>Aksi</th>
                    </tr>
                </thead><tbody>`;

                    if (data.length === 0) {
                        html += `<tr><td colspan="9" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-search fa-3x mb-3 d-block"></i>
                                        <h5>Data tidak ditemukan</h5>
                                        <p>Coba gunakan kata kunci lain</p>
                                    </div>
                                </td></tr>`;
                    } else {
                        data.forEach((k, i) => {
                            html += `<tr>
                        <td><span class="badge bg-primary rounded-pill">${k.nomor_nasabah}</span></td>
                        <td><div class="fw-semibold">${k.nama_nasabah}</div></td>
                        <td><div class="fw-semibold">${k.ibu_kandung}</div></td>
                        <td>${k.alamat}</td>
                        <td><span class="text-success fw-semibold"><i class="fas fa-phone-alt me-1"></i>${k.nohp}</span></td>
                        <td><span class="badge bg-info">${k.jenis_id}</span></td>
                        <td>${k.nomor_id}</td>
                        <td><span class="badge bg-warning text-dark fs-6">${(typeof k.jml_transaksi !== 'undefined') ? k.jml_transaksi : 0}</span></td>
                        <td>
                            <a href="form_updatenasabah.php?id=${encodeURIComponent(k.nomor_nasabah)}"
                            class="btn btn-sm btn-outline-success btn-custom"
                            onclick="return confirm('Yakin ingin mengubah data nasabah ini?')">
                            <i class="fas fa-edit me-1"></i>Ubah
                            </a>
                        </td>
                        <td>
                            <form method="POST" action="gadai_activity/halamangadai.php" onsubmit="return confirm('Mulai proses gadai untuk nasabah ini?')" class="d-inline">
                                <input type="hidden" name="id" value="${k.nomor_nasabah}">
                                <button type="submit" class="btn btn-primary btn-sm btn-custom">
                                    <i class="fas fa-coins me-1"></i>Mulai Gadai
                                </button>
                            </form>
                        </td>
                    </tr>`;
                        });
                    }

                    html += '</tbody></table>';
                    document.getElementById('tabelNasabah').innerHTML = html;
                });
        }

        const searchBtn = document.getElementById('searchBtn');
        const resetBtn = document.getElementById('resetBtn');

        // Saat tombol CARI diklik
        searchBtn.addEventListener('click', function() {
            const keyword = document.getElementById('searchInput').value.trim();
            if (keyword === '') return;

            document.getElementById('staticTableWrapper').style.display = 'none';
            document.getElementById('tabelNasabah').style.display = 'block';
            loadNasabah(1);

            searchBtn.style.display = 'none';
            resetBtn.style.display = 'inline-block';
        });

        resetBtn.addEventListener('click', function() {
            document.getElementById('searchInput').value = '';
            document.getElementById('tabelNasabah').innerHTML = '';
            document.getElementById('tabelNasabah').style.display = 'none';
            document.getElementById('pagination').innerHTML = '';
            document.getElementById('staticTableWrapper').style.display = 'block';

            searchBtn.style.display = 'inline-block';
            resetBtn.style.display = 'none';
        });

        // Enter key untuk search
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchBtn.click();
            }
        });
    </script>

    <script src="../../bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>