<?php
session_start();
if (!isset($_SESSION['usr']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../gates/login.php');
    exit;
}

$username = $_SESSION['usr'];
$nama = $_SESSION['nama'];
$role = $_SESSION['role'];

include_once("../../config/koneksi.php");

// Pastikan ada parameter id
if (!isset($_GET['id'])) {
    header("Location: datanasabah.php?pesan=ID tidak ditemukan");
    exit;
}

$id = $_GET['id'];

// Ambil data nasabah
$stmt = $conn->prepare("SELECT nama_nasabah, ibu_kandung, alamat, nohp, jenis_id, nomor_id FROM nasabah_gadai WHERE nomor_nasabah = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: datanasabah.php?pesan=Nasabah tidak ditemukan");
    exit;
}

$data = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Nasabah</title>
    <link rel="stylesheet" href="../../bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --light-green: #e8f5e8;
            --medium-green: #c8e6c9;
            --dark-green: #4caf50;
            --darker-green: #388e3c;
            --warning-green: #689f38;
        }
        
        body {
            background: linear-gradient(135deg, var(--light-green) 0%, var(--medium-green) 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .main-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        
        .form-control:focus {
            border-color: var(--dark-green);
            box-shadow: 0 0 0 0.2rem rgba(76, 175, 80, 0.25);
        }
        
        .form-select:focus {
            border-color: var(--dark-green);
            box-shadow: 0 0 0 0.2rem rgba(76, 175, 80, 0.25);
        }
        
        .btn-warning-custom {
            background: linear-gradient(135deg, var(--warning-green) 0%, var(--darker-green) 100%);
            border: none;
            padding: 12px 30px;
            font-weight: 500;
            color: white;
            transition: all 0.3s ease;
        }
        
        .btn-warning-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(104, 159, 56, 0.3);
            color: white;
        }
        
        .form-label {
            color: var(--darker-green);
            font-weight: 500;
            margin-bottom: 8px;
        }
        
        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 16px;
            transition: all 0.3s ease;
            background-color: rgba(255, 255, 255, 0.9);
        }
        
        .form-control:hover, .form-select:hover {
            border-color: var(--medium-green);
        }
        
        .alert {
            border: none;
            border-radius: 10px;
            font-weight: 500;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
        }
        
        .logo-container {
            background: white;
            border-radius: 50%;
            padding: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            display: inline-block;
        }
        
        .page-title {
            color: var(--darker-green);
            font-weight: 600;
            margin-bottom: 30px;
        }
        
        .input-group-text {
            background: var(--light-green);
            border-color: #e9ecef;
            color: var(--darker-green);
        }
        
        .cancel-link {
            color: var(--darker-green);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .cancel-link:hover {
            color: var(--dark-green);
            text-decoration: underline;
        }
        
        .edit-badge {
            background: linear-gradient(135deg, var(--warning-green) 0%, var(--darker-green) 100%);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            margin-left: 10px;
        }
        
        .form-control[readonly] {
            background-color: #f8f9fa;
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-11 col-sm-10 col-md-8 col-lg-6 col-xl-5">
                <div class="card main-card rounded-4 border-0">
                    <div class="card-body p-4 p-md-5">
                        <!-- Logo Section -->
                        <div class="text-center mb-4">
                            <div class="logo-container">
                                <img src="../../assets/logo.jpeg" alt="logo.jpeg" class="img-fluid" style="max-width: 80px;">
                            </div>
                        </div>
                        
                        <!-- Page Title -->
                        <h2 class="text-center page-title">
                            <i class="fas fa-user-edit me-2"></i>Form Edit Nasabah
                            <span class="edit-badge">
                                <i class="fas fa-pencil-alt me-1"></i>EDIT
                            </span>
                        </h2>
                        
                        <!-- Alert Messages -->
                        <?php if (isset($_GET['pesan'])): ?>
                            <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <div><?= htmlspecialchars($_GET['pesan']) ?></div>
                            </div>
                        <?php elseif (isset($_GET['sukses'])): ?>
                            <div class="alert alert-success d-flex align-items-center mb-4" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                <div><?= htmlspecialchars($_GET['sukses']) ?></div>
                            </div>
                        <?php endif; ?>

                        <!-- Form -->
                        <form action="prosesupdate.php" method="post">
                            <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">

                            <!-- Info ID -->
                            <div class="mb-4">
                                <div class="alert alert-info d-flex align-items-center py-2" role="alert">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <small>Mengedit data nasabah dengan ID: <strong>#<?= htmlspecialchars($id) ?></strong></small>
                                </div>
                            </div>

                            <!-- Nama Nasabah -->
                            <div class="mb-4">
                                <label class="form-label">
                                    <i class="fas fa-user me-1"></i>Nama Nasabah
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text border-0">
                                        <i class="fas fa-user-tag"></i>
                                    </span>
                                    <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($data['nama_nasabah']) ?>" placeholder="Masukkan nama lengkap" required>
                                </div>
                            </div>

                            <!-- Ibu Kandung -->
                            <div class="mb-4">
                                <label class="form-label">
                                    <i class="fas fa-user me-1"></i>Ibu Kandung
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text border-0">
                                        <i class="fas fa-user-tag"></i>
                                    </span>
                                    <input type="text" name="ibu_kandung" class="form-control" value="<?= htmlspecialchars($data['ibu_kandung']) ?>" placeholder="Nama ibu kandung" required>
                                </div>
                            </div>

                            <!-- Alamat -->
                            <div class="mb-4">
                                <label class="form-label">
                                    <i class="fas fa-map-marker-alt me-1"></i>Alamat
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text border-0">
                                        <i class="fas fa-home"></i>
                                    </span>
                                    <input type="text" name="alamat" class="form-control" value="<?= htmlspecialchars($data['alamat']) ?>" placeholder="Masukkan alamat lengkap" required>
                                </div>
                            </div>

                            <!-- Nomor Ponsel -->
                            <div class="mb-4">
                                <label class="form-label">
                                    <i class="fas fa-phone me-1"></i>Nomor Ponsel
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text border-0">
                                        <i class="fas fa-mobile-alt"></i>
                                    </span>
                                    <input type="number" name="nohp" class="form-control" value="<?= htmlspecialchars($data['nohp']) ?>" placeholder="08xxxxxxxxxx" required>
                                </div>
                            </div>
                            
                            <!-- Jenis ID -->
                            <div class="mb-4">
                                <label class="form-label">
                                    <i class="fas fa-id-card me-1"></i>Jenis ID
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text border-0">
                                        <i class="fas fa-list"></i>
                                    </span>
                                    <select name="jenis_id" class="form-select" required>
                                        <option value="ktp" <?= $data['jenis_id'] === 'ktp' ? 'selected' : '' ?>>KTP (Kartu Tanda Penduduk)</option>
                                        <option value="kk" <?= $data['jenis_id'] === 'kk' ? 'selected' : '' ?>>KK (Kartu Keluarga)</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Nomor ID -->
                            <div class="mb-4">
                                <label class="form-label">
                                    <i class="fas fa-hashtag me-1"></i>Nomor ID
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text border-0">
                                        <i class="fas fa-barcode"></i>
                                    </span>
                                    <input type="text" name="nomor_id" class="form-control" value="<?= htmlspecialchars($data['nomor_id']) ?>" placeholder="Masukkan nomor identitas" required>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="d-grid gap-2 mb-4">
                                <button type="submit" class="btn btn-warning-custom btn-lg rounded-3">
                                    <i class="fas fa-save me-2"></i>Update Data Nasabah
                                </button>
                            </div>
                            
                            <!-- Cancel Link -->
                            <div class="text-center">
                                <a href="datanasabah.php" class="cancel-link">
                                    <i class="fas fa-arrow-left me-1"></i>Kembali ke Data Nasabah
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>