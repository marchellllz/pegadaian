<?php
session_start();
include "../../config/koneksi.php";
date_default_timezone_set('Asia/Jakarta');

// Pastikan user login
if (!isset($_SESSION['usr']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'supervisor') {
    header('Location: ../../../gates/login.php');
    exit;
}

// Ambil username dari session
$username = $_SESSION['usr'];

// Ambil no_gadai dari parameter GET
if (!isset($_GET['no_gadai']) || empty($_GET['no_gadai'])) {
    echo "Nomor gadai tidak ditemukan!";
    exit;
}
$no_gadai = $_GET['no_gadai'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Proses Gadai</title>
    <link rel="stylesheet" href="../../../bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <style>
        :root {
            --light-green: #e8f5e8;
            --soft-green: #d4edda;
            --medium-green: #c3e6cb;
            --dark-green: #155724;
            --accent-green: #28a745;
        }
        
        body {
            background: linear-gradient(135deg, var(--light-green) 0%, #f8f9fa 100%);
            min-height: 100vh;
        }
        
        .main-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(40, 167, 69, 0.1);
            border: 1px solid var(--medium-green);
        }
        
        .header-section {
            background: linear-gradient(135deg, var(--accent-green), #20c997);
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 2rem;
            text-align: center;
        }
        
        .form-section {
            padding: 2.5rem;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--dark-green);
            margin-bottom: 0.75rem;
        }
        
        .form-control, .form-select {
            border: 2px solid var(--medium-green);
            border-radius: 10px;
            padding: 12px 15px;
            transition: all 0.3s ease;
            background-color: #f8fff8;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--accent-green);
            box-shadow: 0 0 0 0.25rem rgba(40, 167, 69, 0.15);
            background-color: white;
        }
        
        .form-control[readonly] {
            background-color: var(--soft-green);
            border-color: var(--medium-green);
        }
        
        .btn-custom-primary {
            background: linear-gradient(135deg, var(--accent-green), #20c997);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
            color: white;
        }
        
        .btn-custom-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
            background: linear-gradient(135deg, #218838, #1e7e34);
            color: white;
        }
        
        .btn-custom-secondary {
            background: var(--soft-green);
            border: 2px solid var(--medium-green);
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            color: var(--dark-green);
            transition: all 0.3s ease;
        }
        
        .btn-custom-secondary:hover {
            background: var(--medium-green);
            border-color: var(--accent-green);
            color: var(--dark-green);
            transform: translateY(-2px);
        }
        
        .form-floating > .form-control, 
        .form-floating > .form-select {
            height: calc(3.5rem + 2px);
            line-height: 1.25;
        }
        
        .form-floating > label {
            color: var(--dark-green);
            font-weight: 500;
        }
        
        .input-group-text {
            background-color: var(--soft-green);
            border: 2px solid var(--medium-green);
            color: var(--dark-green);
            font-weight: 600;
        }
        
        .alert-info-custom {
            background: var(--light-green);
            border: 1px solid var(--medium-green);
            color: var(--dark-green);
            border-radius: 10px;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="main-card">
                <!-- Header Section -->
                <div class="header-section">
                    <h2 class="mb-0">
                        <i class="fas fa-gem me-2"></i>
                        Form Proses Gadai
                    </h2>
                    <p class="mb-0 mt-2 opacity-75">Verifikasi dan Proses Transaksi Gadai</p>
                </div>

                <!-- Form Section -->
                <div class="form-section">
                    <form action="prosesgadai.php" method="POST" onsubmit="return confirm('Yakin ingin menyimpan verifikasi ini?')">
                        
                        <!-- Info Alert -->
                        <div class="alert alert-info-custom mb-4" role="alert">
                            <strong>Informasi:</strong> Pastikan semua data telah diverifikasi dengan benar sebelum menyimpan.
                        </div>

                        <!-- Nomor Gadai -->
                        <div class="form-floating mb-4">
                            <input type="text" class="form-control" id="no_gadai" name="no_gadai" 
                                   value="<?= htmlspecialchars($no_gadai) ?>" readonly placeholder="Nomor Gadai">
                            <label for="no_gadai">
                                <i class="fas fa-hashtag me-2"></i>Nomor Gadai
                            </label>
                        </div>

                        <!-- User ID -->
                        <div class="form-floating mb-4">
                            <input type="text" class="form-control" id="user_id" name="user_id" 
                                   value="<?= htmlspecialchars($username) ?>" readonly placeholder="User ID">
                            <label for="user_id">
                                <i class="fas fa-user me-2"></i>User ID (Supervisor)
                            </label>
                        </div>

                        <!-- Status Verifikasi -->
                        <div class="form-floating mb-4">
                            <select class="form-select" id="status_verifikasi" name="status_verifikasi" required>
                                <option value="">-- Pilih Status Verifikasi --</option>
                                <option value="pending">📋 Pending</option>
                                <option value="diterima">✅ Diterima</option>
                                <option value="ditolak">❌ Ditolak</option>
                                <option value="dilelang">🔨 Dilelang</option>
                                <option value="lunas">💰 Lunas</option>
                            </select>
                            <label for="status_verifikasi">
                                <i class="fas fa-check-circle me-2"></i>Status Verifikasi
                            </label>
                        </div>

                        <!-- Catatan -->
                        <div class="form-floating mb-4">
                            <textarea class="form-control" id="catatan" name="catatan" 
                                      style="height: 120px" placeholder="Catatan"></textarea>
                            <label for="catatan">
                                <i class="fas fa-sticky-note me-2"></i>Catatan Verifikasi
                            </label>
                        </div>

                        <!-- Action Buttons -->
                        <div class="row mt-5">
                            <div class="col-md-6 mb-3">
                                <a href="datagadai.php" class="btn btn-custom-secondary w-100">
                                    <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar
                                </a>
                            </div>
                            <div class="col-md-6 mb-3">
                                <button type="submit" class="btn btn-custom-primary w-100">
                                    <i class="fas fa-save me-2"></i>Simpan Verifikasi
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="../../../bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>

<!-- Font Awesome (optional, for icons) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

</body>
</html>