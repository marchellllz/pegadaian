<?php
include_once("../../config/koneksi.php");
session_start();
if (!isset($_SESSION['usr']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'supervisor' || !isset($_SESSION['cabang'])) {
    header('Location: ../../gates/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Payment - Supervisor Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --light-bg: #ecf0f1;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .main-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
            margin-bottom: 20px;
        }

        .header-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border-radius: 20px 20px 0 0;
            padding: 2rem;
            margin: -15px -15px 2rem -15px;
        }

        .header-title {
            font-size: 2rem;
            font-weight: 700;
            margin: 0;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .header-subtitle {
            opacity: 0.9;
            margin: 0;
            font-weight: 300;
        }

        .action-buttons .btn {
            border-radius: 25px;
            font-weight: 600;
            padding: 10px 20px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .action-buttons .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }

        .filter-section {
            background: rgba(255, 255, 255, 0.8);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--card-shadow);
        }

        .filter-title {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .form-select:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        .payment-card {
            border: none;
            border-radius: 15px;
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
            overflow: hidden;
            background: white;
        }

        .payment-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .card-header-custom {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: 3px solid var(--secondary-color);
            padding: 1rem 1.25rem;
        }

        .card-id {
            color: var(--primary-color);
            font-weight: 700;
            font-size: 1.1rem;
        }

        .card-info {
            padding: 0.5rem 0;
            margin: 0;
            color: #6c757d;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card-info i {
            width: 16px;
            color: var(--secondary-color);
        }

        .card-status {
            font-weight: 700;
            padding: 8px 15px;
            border-radius: 20px;
            color: white;
            text-align: center;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .status-belum {
            background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);
        }

        .status-tidak {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        }

        .status-valid {
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
        }

        .detail-btn {
            background: linear-gradient(135deg, var(--secondary-color) 0%, #2980b9 100%);
            border: none;
            border-radius: 20px;
            color: white;
            font-weight: 600;
            padding: 8px 20px;
            transition: all 0.3s ease;
        }

        .detail-btn:hover {
            background: linear-gradient(135deg, #2980b9 0%, #1f4e79 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border-radius: 10px 10px 0 0;
        }

        .modal-title {
            font-weight: 700;
        }

        .modal-body {
            padding: 2rem;
        }

        .detail-item {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 12px 15px;
            margin-bottom: 10px;
            border-left: 4px solid var(--secondary-color);
        }

        .detail-label {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 5px;
        }

        .detail-value {
            color: #495057;
            margin: 0;
        }

        .proof-image {
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            max-height: 300px;
            object-fit: cover;
        }

        .loading-spinner {
            text-align: center;
            padding: 3rem;
            color: var(--secondary-color);
        }

        .no-data {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }

        .no-data i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #dee2e6;
        }

        .action-section {
            background: rgba(248, 249, 250, 0.8);
            padding: 1rem 2rem;
            border-top: 1px solid #e9ecef;
        }

        .btn-verify {
            background: linear-gradient(135deg, var(--success-color) 0%, #229954 100%);
            border: none;
            border-radius: 20px;
            color: white;
            font-weight: 600;
            padding: 8px 20px;
            transition: all 0.3s ease;
        }

        .btn-verify:hover {
            background: linear-gradient(135deg, #229954 0%, #1e8449 100%);
            transform: translateY(-2px);
            color: white;
        }

        .btn-reject {
            background: linear-gradient(135deg, var(--danger-color) 0%, #c0392b 100%);
            border: none;
            border-radius: 20px;
            color: white;
            font-weight: 600;
            padding: 8px 20px;
            transition: all 0.3s ease;
        }

        .btn-reject:hover {
            background: linear-gradient(135deg, #c0392b 0%, #a93226 100%);
            transform: translateY(-2px);
            color: white;
        }

        @media (max-width: 768px) {
            .header-section {
                padding: 1.5rem;
            }

            .header-title {
                font-size: 1.5rem;
            }

            .action-buttons .btn {
                margin-bottom: 10px;
                width: 100%;
            }
        }
    </style>
</head>

<body class="p-3">

    <div class="container-fluid">
        <div class="main-container p-4">
            <!-- Header Section -->
            <div class="header-section">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                    <div>
                        <h1 class="header-title"><i class="fas fa-credit-card me-2"></i>Daftar Pembayaran</h1>
                        <p class="header-subtitle">Kelola dan verifikasi pembayaran gadai</p>
                    </div>
                    <div class="action-buttons d-flex flex-column flex-md-row mt-3 mt-md-0">
                        <a href="logbayar.php" class="btn btn-info me-2">
                            <i class="fas fa-history me-2"></i>Aktivitas
                        </a>
                        <a href="../home.php" class="btn btn-light">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="filter-section">
                <h5 class="filter-title">
                    <i class="fas fa-filter"></i>Filter Pembayaran
                </h5>
                <div class="row">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label for="filterBulan" class="form-label fw-semibold">
                            <i class="fas fa-calendar-alt me-2"></i>Bulan
                        </label>
                        <select id="filterBulan" class="form-select">
                            <option value="">-- Semua Bulan --</option>
                            <option value="01">Januari</option>
                            <option value="02">Februari</option>
                            <option value="03">Maret</option>
                            <option value="04">April</option>
                            <option value="05">Mei</option>
                            <option value="06">Juni</option>
                            <option value="07">Juli</option>
                            <option value="08">Agustus</option>
                            <option value="09">September</option>
                            <option value="10">Oktober</option>
                            <option value="11">November</option>
                            <option value="12">Desember</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="filterTahun" class="form-label fw-semibold">
                            <i class="fas fa-calendar me-2"></i>Tahun
                        </label>
                        <select id="filterTahun" class="form-select">
                            <option value="">-- Semua Tahun --</option>
                            <?php
                            $tahunSekarang = date('Y');
                            for ($t = $tahunSekarang; $t >= 2022; $t--) {
                                echo "<option value='$t'>$t</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Payment List -->
            <div id="payment-list" class="row g-4">
                <!-- Loading spinner initially -->
                <div class="col-12 loading-spinner">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3">Memuat data pembayaran...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detail -->
    <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-file-invoice me-2"></i>Detail Pembayaran</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body" id="detailContent">
                    <!-- Detail pembayaran akan dimuat lewat AJAX -->
                </div>
                <div class="action-section">
                    <div class="d-flex gap-2">
                        <button id="verifikasiBtn" class="btn btn-verify" style="display: none;">
                            <i class="fas fa-check me-2"></i>Verifikasi
                        </button>
                        <button id="tolakBtn" class="btn btn-reject" style="display: none;">
                            <i class="fas fa-times me-2"></i>Tolak
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <a id="downloadBtn" class="btn btn-success" href="#" target="_blank">
                        <i class="fas fa-download me-2"></i>Download Kwitansi
                    </a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showLoading() {
            document.getElementById('payment-list').innerHTML = `
                <div class="col-12 loading-spinner">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3">Memuat data pembayaran...</p>
                </div>
            `;
        }

        function loadPayments() {
            showLoading();

            const bulan = document.getElementById('filterBulan').value;
            const tahun = document.getElementById('filterTahun').value;

            let url = 'payment_list.php';
            let params = [];

            if (bulan) params.push('bulan=' + encodeURIComponent(bulan));
            if (tahun) params.push('tahun=' + encodeURIComponent(tahun));

            if (params.length > 0) {
                url += '?' + params.join('&');
            }

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    const list = document.getElementById('payment-list');
                    list.innerHTML = '';

                    if (data.length === 0) {
                        list.innerHTML = `
                            <div class="col-12 no-data">
                                <i class="fas fa-inbox"></i>
                                <h5>Tidak Ada Data</h5>
                                <p class="text-muted">Tidak ada data pembayaran yang ditemukan dengan filter yang dipilih.</p>
                            </div>
                        `;
                        return;
                    }

                    data.forEach(row => {
                        let statusClass = '';
                        let statusText = '';
                        let statusIcon = '';

                        if (row.status_bayar === '-') {
                            statusClass = 'status-belum';
                            statusText = 'Belum Diverifikasi';
                            statusIcon = 'fas fa-clock';
                        } else if (row.status_bayar === 'x') {
                            statusClass = 'status-tidak';
                            statusText = 'Tidak Valid';
                            statusIcon = 'fas fa-times';
                        } else if (row.status_bayar === 'V') {
                            statusClass = 'status-valid';
                            statusText = 'Terverifikasi';
                            statusIcon = 'fas fa-check';
                        }

                        list.innerHTML += `
                            <div class="col-lg-4 col-md-6">
                                <div class="card payment-card h-100">
                                    <div class="card-header-custom">
                                        <h6 class="card-id mb-0">
                                            <i class="fas fa-hashtag me-2"></i>ID: ${row.id_bayar}
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="card-info">
                                            <i class="fas fa-barcode"></i>
                                            <strong>No. Gadai:</strong> ${row.no_gadai}
                                        </p>
                                        <p class="card-info">
                                            <i class="fas fa-user"></i>
                                            <strong>Nasabah:</strong> ${row.nama_nasabah ? row.nama_nasabah : row.no_gadai}
                                        </p>
                                        <p class="card-info">
                                            <i class="fas fa-calendar-day"></i>
                                            <strong>Tanggal:</strong> ${row.tanggal_bayar}
                                        </p>
                                        <div class="d-flex justify-content-center mb-3">
                                            <span class="card-status ${statusClass}">
                                                <i class="${statusIcon} me-2"></i>${statusText}
                                            </span>
                                        </div>
                                        <div class="text-center">
                                            <button class="btn detail-btn" onclick="lihatDetail('${row.id_bayar}')">
                                                <i class="fas fa-eye me-2"></i>Lihat Detail
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    const list = document.getElementById('payment-list');
                    list.innerHTML = `
                        <div class="col-12 no-data">
                            <i class="fas fa-exclamation-triangle text-warning"></i>
                            <h5>Terjadi Kesalahan</h5>
                            <p class="text-muted">Gagal memuat data pembayaran. Silakan refresh halaman.</p>
                        </div>
                    `;
                });
        }

        function lihatDetail(id_bayar) {
            // Show loading in modal
            document.getElementById('detailContent').innerHTML = `
                <div class="text-center p-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3">Memuat detail pembayaran...</p>
                </div>
            `;

            new bootstrap.Modal(document.getElementById('detailModal')).show();

            fetch('payment_detail.php?id=' + encodeURIComponent(id_bayar))
                .then(res => res.json())
                .then(row => {
                    let statusBadge = '';
                    if (row.status_bayar === '-') {
                        statusBadge = '<span class="badge bg-secondary fs-6"><i class="fas fa-clock me-2"></i>Belum Diverifikasi</span>';
                    } else if (row.status_bayar === 'x') {
                        statusBadge = '<span class="badge bg-danger fs-6"><i class="fas fa-times me-2"></i>Tidak Valid</span>';
                    } else if (row.status_bayar === 'V') {
                        statusBadge = '<span class="badge bg-success fs-6"><i class="fas fa-check me-2"></i>Terverifikasi</span>';
                    }

                    let html = `
                        <div class="row">
                            <div class="col-md-6">
                                <div class="detail-item">
                                    <div class="detail-label"><i class="fas fa-hashtag me-2"></i>ID Bayar</div>
                                    <p class="detail-value">${row.id_bayar}</p>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label"><i class="fas fa-barcode me-2"></i>No Gadai</div>
                                    <p class="detail-value">${row.no_gadai}</p>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label"><i class="fas fa-calendar-day me-2"></i>Tanggal Bayar</div>
                                    <p class="detail-value">${row.tanggal_bayar}</p>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label"><i class="fas fa-money-bill-wave me-2"></i>Jumlah Bayar</div>
                                    <p class="detail-value">Rp ${parseInt(row.jumlah_bayar).toLocaleString('id-ID')}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="detail-item">
                                    <div class="detail-label"><i class="fas fa-credit-card me-2"></i>Metode Bayar</div>
                                    <p class="detail-value">${row.metode_bayar}</p>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label"><i class="fas fa-sticky-note me-2"></i>Keterangan</div>
                                    <p class="detail-value">${row.keterangan || 'Tidak ada keterangan'}</p>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label"><i class="fas fa-info-circle me-2"></i>Status</div>
                                    <p class="detail-value">${statusBadge}</p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <h6 class="mb-3"><i class="fas fa-image me-2"></i>Bukti Pembayaran</h6>
                            <div class="text-center">
                                <img src="../../${row.bukti_bayar}" class="img-fluid proof-image" alt="Bukti Bayar" style="max-width: 100%; height: auto;">
                            </div>
                        </div>
                    `;

                    document.getElementById('detailContent').innerHTML = html;

                    // Atur tombol Verifikasi dan Tolak
                    const verifBtn = document.getElementById('verifikasiBtn');
                    const tolakBtn = document.getElementById('tolakBtn');

                    if (row.status_bayar === '-') {
                        verifBtn.style.display = 'inline-block';
                        tolakBtn.style.display = 'inline-block';

                        verifBtn.onclick = function(e) {
                            e.preventDefault();
                            if (confirm("Yakin ingin memverifikasi pembayaran ini?")) {
                                window.location.href = 'payment_verification.php?id=' + encodeURIComponent(row.id_bayar);
                            }
                        };

                        tolakBtn.onclick = function(e) {
                            e.preventDefault();
                            if (confirm("Yakin ingin menolak pembayaran ini?")) {
                                window.location.href = 'payment_reject.php?id=' + encodeURIComponent(row.id_bayar);
                            }
                        };
                    } else {
                        verifBtn.style.display = 'none';
                        tolakBtn.style.display = 'none';
                    }

                    document.getElementById('downloadBtn').href = 'payment_download.php?id=' + encodeURIComponent(row.id_bayar);
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('detailContent').innerHTML = `
                        <div class="text-center p-4">
                            <i class="fas fa-exclamation-triangle text-warning fs-1"></i>
                            <h5 class="mt-3">Terjadi Kesalahan</h5>
                            <p class="text-muted">Gagal memuat detail pembayaran.</p>
                        </div>
                    `;
                });
        }

        // Event listeners untuk filter
        document.getElementById('filterBulan').addEventListener('change', loadPayments);
        document.getElementById('filterTahun').addEventListener('change', loadPayments);

        // Load data pertama kali
        document.addEventListener('DOMContentLoaded', function() {
            loadPayments();
        });
    </script>

</body>

</html>