<?php
session_start();
if (!isset($_SESSION['usr']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'supervisor' || !isset($_SESSION['cabang'])) {
    header('Location: ../../gates/login.php');
    exit;
}

$username = $_SESSION['usr'];
$nama = $_SESSION['nama'];
$role = $_SESSION['role'];
$cabang = $_SESSION['cabang'];
include_once("../../config/koneksi.php");

// Mapping agama
$agamaList = [
    1 => 'Islam',
    2 => 'Katolik',
    3 => 'Kristen',
    4 => 'Hindu',
    5 => 'Buddha',
    6 => 'Konghucu'
];

// Pagination Setup
$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Hitung total
if ($_SESSION['cabang'] === 'mataram') {
    // pusat -> hitung semua
    $totalResult = mysqli_query($conn, "
        SELECT COUNT(*) as total 
        FROM karyawan
    ");
} else {
    // cabang -> hitung hanya cabangnya sendiri
    $cabang = mysqli_real_escape_string($conn, $_SESSION['cabang']);
    $totalResult = mysqli_query($conn, "
        SELECT COUNT(*) as total 
        FROM karyawan 
        WHERE cabang = '$cabang'
    ");
}
$total = mysqli_fetch_assoc($totalResult)['total'];
$totalPages = ceil($total / $limit);

// Ambil data karyawan dan status verifikasi
if ($_SESSION['cabang'] === 'mataram') {
    // pusat -> lihat semua cabang
    $result = mysqli_query($conn, "
        SELECT k.*, ua.status 
        FROM karyawan k 
        LEFT JOIN user_account ua ON k.user_id = ua.user_id 
        LIMIT $offset, $limit
    ");
} else {
    // cabang -> cuma lihat cabangnya sendiri
    $cabang = mysqli_real_escape_string($conn, $_SESSION['cabang']);
    $result = mysqli_query($conn, "
        SELECT k.*, ua.status 
        FROM karyawan k 
        LEFT JOIN user_account ua ON k.user_id = ua.user_id 
        WHERE k.cabang = '$cabang'
        LIMIT $offset, $limit
    ");
}
$dataKaryawan = [];
while ($row = mysqli_fetch_assoc($result)) {
    $dataKaryawan[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Karyawan - Management System</title>
    <link rel="stylesheet" href="../../bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-green: #28a745;
            --light-green: #d4edda;
            --dark-green: #155724;
            --accent-green: #20c997;
        }

        body {
            background: linear-gradient(135deg, #e8f5e8 0%, #c3e9d0 50%, #a8ddb5 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .main-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        .header-section {
            background: linear-gradient(135deg, var(--primary-green), var(--accent-green));
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
            right: -50%;
            width: 200%;
            height: 200%;
            background: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 10px,
                rgba(255, 255, 255, 0.05) 10px,
                rgba(255, 255, 255, 0.05) 20px
            );
            animation: float 20s infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        .search-card {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        .btn-custom {
            border-radius: 10px;
            padding: 10px 25px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, var(--primary-green), var(--accent-green));
            border: none;
            color: white;
        }

        .btn-secondary-custom {
            background: linear-gradient(135deg, #6c757d, #495057);
            border: none;
            color: white;
        }

        .btn-info-custom {
            background: linear-gradient(135deg, #17a2b8, #138496);
            border: none;
            color: white;
        }

        .table-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .table thead th {
            background: linear-gradient(135deg, var(--dark-green), var(--primary-green));
            color: white;
            border: none;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 15px 10px;
        }

        .table tbody tr {
            border-bottom: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .table tbody tr:hover {
            background: var(--light-green);
            transform: scale(1.01);
        }

        .table tbody td {
            padding: 15px 10px;
            vertical-align: middle;
        }

        .badge-custom {
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .btn-action {
            border-radius: 8px;
            padding: 6px 15px;
            font-size: 0.85rem;
            margin: 2px;
            transition: all 0.3s ease;
        }

        .btn-action:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
        }

        .pagination .page-link {
            border-radius: 10px;
            margin: 0 3px;
            border: none;
            color: var(--primary-green);
            font-weight: 600;
        }

        .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, var(--primary-green), var(--accent-green));
            border: none;
        }

        .alert-custom {
            border-radius: 15px;
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .form-control-custom {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }

        .form-control-custom:focus {
            border-color: var(--primary-green);
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }

        .stats-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.7));
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .icon-badge {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
    </style>
</head>

<body>
    <?php if (isset($_GET['reset'])): ?>
        <div class="container-fluid">
            <div class="alert <?= $_GET['reset'] === 'success' ? 'alert-success' : 'alert-danger' ?> alert-custom alert-dismissible fade show">
                <i class="fas <?= $_GET['reset'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle' ?> me-2"></i>
                <?= $_GET['reset'] === 'success' ? 'Password berhasil direset ke default' : 'Gagal mereset password.' ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>

    <div class="container-fluid px-4 py-4">
        <div class="main-container">
            <!-- Header Section -->
            <div class="header-section">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="mb-2">
                            <i class="fas fa-users me-3"></i>
                            Data Management Karyawan
                        </h1>
                        <p class="mb-0 opacity-75">Kelola data karyawan dan akses sistem</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="stats-card bg-white text-dark">
                            <div class="icon-badge bg-success text-white">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                            <h3 class="mb-1"><?= $total ?></h3>
                            <small class="text-muted">Total Karyawan</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-4">
                <!-- Search Section -->
                <div class="card search-card mb-4">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="fas fa-search text-muted"></i>
                                    </span>
                                    <input type="text" class="form-control form-control-custom border-start-0" 
                                           placeholder="Cari berdasarkan ID atau Nama Karyawan..." id="searchInput">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex gap-2 justify-content-md-end">
                                    <button class="btn btn-primary-custom btn-custom" id="searchBtn">
                                        <i class="fas fa-search me-2"></i>Cari
                                    </button>
                                    <button class="btn btn-secondary-custom btn-custom" id="resetBtn" style="display: none;">
                                        <i class="fas fa-undo me-2"></i>Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex flex-wrap gap-3 mb-4">
                    <a href="logmasuk.php" class="btn btn-info-custom btn-custom">
                        <i class="fas fa-history me-2"></i>Lihat Aktivitas
                    </a>
                    <a href="../home.php" class="btn btn-secondary-custom btn-custom">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
                    </a>
                    <a href="../../gates/signup.php" class="btn btn-primary-custom btn-custom">
                        <i class="fas fa-user-plus me-2"></i>Daftarkan Karyawan
                    </a>
                </div>

                <!-- Dynamic Table (Search Results) -->
                <div id="tabelKaryawan" class="table-container mb-4" style="display: none;"></div>
                <div id="pagination" class="text-center"></div>

                <!-- Static Table -->
                <div class="table-container" id="staticTableWrapper">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th><i class="fas fa-hashtag me-2"></i>No</th>
                                <th><i class="fas fa-id-card me-2"></i>User ID</th>
                                <th><i class="fas fa-user me-2"></i>Nama</th>
                                <th><i class="fas fa-phone me-2"></i>No HP</th>
                                <th><i class="fas fa-map-marker-alt me-2"></i>Alamat</th>
                                <th><i class="fas fa-venus-mars me-2"></i>Gender</th>
                                <th><i class="fas fa-pray me-2"></i>Agama</th>
                                <th><i class="fas fa-user-tag me-2"></i>Role</th>
                                <th colspan="3" class="text-center"><i class="fas fa-cogs me-2"></i>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($dataKaryawan) > 0): ?>
                                <?php foreach ($dataKaryawan as $i => $k): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($offset + $i + 1) ?></strong></td>
                                        <td><span class="badge bg-secondary"><?= htmlspecialchars($k['user_id']) ?></span></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="icon-badge bg-primary text-white me-2" style="width: 35px; height: 35px; font-size: 0.9rem;">
                                                    <?= strtoupper(substr($k['nama'], 0, 1)) ?>
                                                </div>
                                                <?= htmlspecialchars($k['nama']) ?>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($k['nohp']) ?></td>
                                        <td><?= htmlspecialchars($k['alamat']) ?></td>
                                        <td>
                                            <span class="badge <?= $k['jkel'] == 'L' ? 'bg-info' : 'bg-warning' ?> badge-custom">
                                                <i class="fas <?= $k['jkel'] == 'L' ? 'fa-mars' : 'fa-venus' ?> me-1"></i>
                                                <?= htmlspecialchars($k['jkel']) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($agamaList[$k['agama']] ?? 'Tidak diketahui') ?></td>
                                        <td>
                                            <span class="badge bg-dark badge-custom">
                                                <?= htmlspecialchars($k['rl']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($k['status'] === 'verified'): ?>
                                                <span class="badge bg-success badge-custom">
                                                    <i class="fas fa-check-circle me-1"></i>Terverifikasi
                                                </span>
                                            <?php else: ?>
                                                <button class="btn btn-warning btn-action" onclick="verifikasiAkun('<?= $k['user_id'] ?>', this)">
                                                    <i class="fas fa-user-check me-1"></i>Verifikasi
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <form method="POST" action="hapuskaryawan.php" onsubmit="return confirm('Yakin ingin menghapus?')" class="d-inline">
                                                <input type="hidden" name="id" value="<?= htmlspecialchars($k['user_id']) ?>">
                                                <button type="submit" class="btn btn-danger btn-action">
                                                    <i class="fas fa-trash me-1"></i>Hapus
                                                </button>
                                            </form>
                                        </td>
                                        <td>
                                            <form action="resetpass.php" method="POST" onsubmit="return confirm('Yakin reset password user ini ke default?')" class="d-inline">
                                                <input type="hidden" name="id" value="<?= htmlspecialchars($k['user_id']) ?>">
                                                <button type="submit" class="btn btn-warning btn-action">
                                                    <i class="fas fa-key me-1"></i>Reset
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="11" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="fas fa-inbox fa-3x mb-3"></i>
                                            <h5>Tidak ada data karyawan</h5>
                                            <p>Belum ada data karyawan yang terdaftar dalam sistem.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <script src="../../bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function verifikasiAkun(userId, button) {
            if (!confirm("Yakin ingin memverifikasi akun karyawan ini?")) return;

            fetch(`verifikasi.php?id=${userId}`)
                .then(response => {
                    if (!response.ok) throw new Error("Gagal menghubungi server");
                    return response.json();
                })
                .then(data => {
                    if (data.status === "success") {
                        // Ganti button jadi badge
                        button.parentElement.innerHTML = '<span class="badge bg-success badge-custom"><i class="fas fa-check-circle me-1"></i>Terverifikasi</span>';
                    } else {
                        alert("Gagal: " + data.message);
                    }
                })
                .catch(error => {
                    alert("Terjadi kesalahan: " + error.message);
                });
        }

        function loadKaryawan(page = 1) {
            const keyword = document.getElementById('searchInput').value.trim();
            fetch(`ambilkaryawan.php?page=${page}&search=${encodeURIComponent(keyword)}`)
                .then(res => res.json())
                .then(res => {
                    if (res.status !== 'success') {
                        document.getElementById('tabelKaryawan').innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>Gagal memuat data.</div>';
                        return;
                    }

                    const data = res.data;
                    const offset = res.offset;
                    let html = `
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-hashtag me-2"></i>No</th>
                                    <th><i class="fas fa-id-card me-2"></i>User ID</th>
                                    <th><i class="fas fa-user me-2"></i>Nama</th>
                                    <th><i class="fas fa-phone me-2"></i>No HP</th>
                                    <th><i class="fas fa-map-marker-alt me-2"></i>Alamat</th>
                                    <th><i class="fas fa-venus-mars me-2"></i>Gender</th>
                                    <th><i class="fas fa-pray me-2"></i>Agama</th>
                                    <th><i class="fas fa-user-tag me-2"></i>Role</th>
                                    <th colspan="3" class="text-center"><i class="fas fa-cogs me-2"></i>Aksi</th>
                                </tr>
                            </thead><tbody>`;

                    if (data.length === 0) {
                        html += `<tr><td colspan="11" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-search fa-3x mb-3"></i>
                                <h5>Tidak ada hasil pencarian</h5>
                                <p>Tidak ditemukan data karyawan dengan kata kunci "${keyword}"</p>
                            </div>
                        </td></tr>`;
                    } else {
                        data.forEach((k, i) => {
                            html += `<tr>
                                <td><strong>${offset + i + 1}</strong></td>
                                <td><span class="badge bg-secondary">${k.user_id}</span></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="icon-badge bg-primary text-white me-2" style="width: 35px; height: 35px; font-size: 0.9rem;">
                                            ${k.nama.charAt(0).toUpperCase()}
                                        </div>
                                        ${k.nama}
                                    </div>
                                </td>
                                <td>${k.nohp}</td>
                                <td>${k.alamat}</td>
                                <td>
                                    <span class="badge ${k.jkel === 'L' ? 'bg-info' : 'bg-warning'} badge-custom">
                                        <i class="fas ${k.jkel === 'L' ? 'fa-mars' : 'fa-venus'} me-1"></i>
                                        ${k.jkel}
                                    </span>
                                </td>
                                <td>${k.agama_nama}</td>
                                <td><span class="badge bg-dark badge-custom">${k.rl}</span></td>
                                <td>${
                                    k.status === 'verified'
                                    ? '<span class="badge bg-success badge-custom"><i class="fas fa-check-circle me-1"></i>Terverifikasi</span>'
                                    : `<button class="btn btn-warning btn-action" onclick="verifikasiAkun('${k.user_id}', this)"><i class="fas fa-user-check me-1"></i>Verifikasi</button>`
                                }</td>
                                <td>
                                    <form method="POST" action="hapuskaryawan.php" onsubmit="return confirm('Yakin ingin menghapus?')" class="d-inline">
                                        <input type="hidden" name="id" value="${k.user_id}">
                                        <button type="submit" class="btn btn-danger btn-action"><i class="fas fa-trash me-1"></i>Hapus</button>
                                    </form>
                                </td>
                                <td>
                                    <form method="POST" action="resetpass.php" onsubmit="return confirm('Yakin reset password user ini ke default?')" class="d-inline">
                                        <input type="hidden" name="id" value="${k.user_id}">
                                        <button type="submit" class="btn btn-warning btn-action"><i class="fas fa-key me-1"></i>Reset</button>
                                    </form>
                                </td>
                            </tr>`;
                        });
                    }

                    html += '</tbody></table>';
                    document.getElementById('tabelKaryawan').innerHTML = html;
                });
        }

        const searchBtn = document.getElementById('searchBtn');
        const resetBtn = document.getElementById('resetBtn');
        const searchInput = document.getElementById('searchInput');

        // Search functionality
        searchBtn.addEventListener('click', function() {
            const keyword = searchInput.value.trim();
            if (keyword === '') return;

            document.getElementById('staticTableWrapper').style.display = 'none';
            document.getElementById('tabelKaryawan').style.display = 'block';
            loadKaryawan(1);

            searchBtn.style.display = 'none';
            resetBtn.style.display = 'inline-block';
        });

        // Reset functionality
        resetBtn.addEventListener('click', function() {
            searchInput.value = '';
            document.getElementById('tabelKaryawan').innerHTML = '';
            document.getElementById('tabelKaryawan').style.display = 'none';
            document.getElementById('pagination').innerHTML = '';
            document.getElementById('staticTableWrapper').style.display = 'block';

            searchBtn.style.display = 'inline-block';
            resetBtn.style.display = 'none';
        });

        // Enter key search
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchBtn.click();
            }
        });
    </script>
</body>

</html>