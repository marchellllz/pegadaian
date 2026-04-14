<?php
session_start();
if (!isset($_SESSION['usr']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../gates/login.php');
    exit;
}

$username = $_SESSION['usr'];
$nama = $_SESSION['nama'];
$role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Home</title>
    <link rel="stylesheet" href="../bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-light" style="background: linear-gradient(135deg, #a8e6cf 0%, #dcedc8 50%, #f1f8e9 100%) !important; min-height: 100vh;">

    <div class="container py-5">

        <!-- Header -->
        <div class="card border-0 shadow-lg mb-5" style="border-radius: 20px; background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div class="d-flex align-items-center mb-3 mb-md-0">
                        <!-- Logo -->
                        <div class="me-3">
                            <img src="../assets/logo.jpeg" alt="logo" style="width: 100px; height: auto;">
                        </div>
                        <!-- Selamat Datang dan Role -->
                        <div>
                            <h3 class="fw-bold mb-2 text-dark">
                                <i class="fas fa-user-cog text-primary me-2"></i>
                                Selamat Datang, <?php echo htmlspecialchars($nama); ?>
                            </h3>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-semibold">
                                    <i class="fas fa-shield-alt me-1"></i>
                                    <?php echo htmlspecialchars($role); ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Kanan: Logout dan Edit Profile -->
                    <div class="d-flex align-items-center">
                        <a href="../gates/logout.php" class="btn btn-outline-danger me-3 px-4 py-2 rounded-pill">
                            <i class="fas fa-sign-out-alt me-2"></i>Log Out
                        </a>
                        <a href="editprofile/editprofile.php" class="position-relative">
                            <img src="../assets/profil.jpeg" alt="profil" class="rounded-circle border border-3 border-primary"
                                style="width: 60px; height: 60px; object-fit: cover; transition: transform 0.3s ease;">
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">
                                <i class="fas fa-edit fa-xs"></i>
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Menu Dashboard -->
        <div class="row g-4">

            <!-- Nasabah -->
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card border-0 shadow h-100" style="border-radius: 20px; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px);">
                    <div class="card-body text-center py-5 position-relative">
                        <div class="mb-4">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center"
                                style="width: 80px; height: 80px;">
                                <i class="fas fa-users fa-2x text-primary"></i>
                            </div>
                        </div>
                        <h5 class="card-title fw-bold mb-3 text-dark">Manajemen Nasabah</h5>
                        <p class="text-muted mb-4">Kelola dan pantau data nasabah dalam sistem perusahaan</p>
                        <a href="mgt_nasabah/datanasabah.php" class="btn btn-primary px-5 py-3 rounded-pill fw-semibold">
                            <i class="fas fa-eye me-2"></i>Lihat Nasabah
                        </a>
                    </div>
                </div>
            </div>

            <!-- Pegadaian -->
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card border-0 shadow h-100" style="border-radius: 20px; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px);">
                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <div class="bg-info bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center"
                                style="width: 80px; height: 80px;">
                                <i class="fas fa-gem fa-2x text-info"></i>
                            </div>
                        </div>
                        <h5 class="card-title fw-bold mb-3 text-dark">Pegadaian</h5>
                        <p class="text-muted mb-4">Monitor dan kelola seluruh aktivitas transaksi pegadaian</p>
                        <a href="status_gadai/listgadai.php" class="btn btn-info px-5 py-3 rounded-pill fw-semibold text-white">
                            <i class="fas fa-handshake me-2"></i>Kelola Pegadaian
                        </a>
                    </div>
                </div>
            </div>

            <!-- Pembayaran -->
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card border-0 shadow h-100" style="border-radius: 20px; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px);">
                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center"
                                style="width: 80px; height: 80px;">
                                <i class="fas fa-credit-card fa-2x text-success"></i>
                            </div>
                        </div>
                        <h5 class="card-title fw-bold mb-3 text-dark">Pembayaran</h5>
                        <p class="text-muted mb-4">Catat dan verifikasi semua pembayaran dari nasabah</p>
                        <a href="payment/home_payment.php" class="btn btn-success px-5 py-3 rounded-pill fw-semibold">
                            <i class="fas fa-money-check-alt me-2"></i>Kelola Pembayaran
                        </a>
                    </div>
                </div>
            </div>

        </div>

        <!-- Footer Info -->
        <div class="text-center mt-5">
            <div class="card border-0 shadow-sm" style="border-radius: 15px; background: rgba(255, 255, 255, 0.8);">
                <div class="card-body py-3">
                    <small class="text-muted">
                        <i class="fas fa-shield-alt text-primary me-2"></i>
                        Dashboard Admin - Sobat Gadai
                    </small>
                </div>
            </div>
        </div>

    </div>

    <script src="../bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>