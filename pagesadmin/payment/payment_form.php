<?php
include_once("../../config/koneksi.php");
session_start();
if (!isset($_SESSION['usr']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../gates/login.php');
    exit;
}

$username = $_SESSION['usr'];
$nama = $_SESSION['nama'];
$role = $_SESSION['role'];
$cabang = $_SESSION['cabang'];

// Mapping cabang → kode
$kodeCabang = '';
switch (strtolower($cabang)) {
    case 'mataram':
        $kodeCabang = 'SG11';
        break;
    case 'majapahit':
        $kodeCabang = 'SG12';
        break;
    case 'ambarawa':
        $kodeCabang = 'SG13';
        break;
    default:
        $kodeCabang = 'SG00'; // fallback kalau cabang tidak dikenali
        break;
}
// ====== AUTO GENERATE ID BAYAR (reset tiap bulan) ======
$hari  = date('d');
$bulan = date('m');
$tahun = date('Y');

// Cari nomor urut terakhir untuk BULAN & TAHUN ini (apa pun harinya)
$patternMonthly = $kodeCabang . '/__/' . $bulan . '/' . $tahun . '/%'; // '__/mm/YYYY/%' → 2 underscore = 2 digit hari

$stmt = $conn->prepare("
    SELECT CAST(RIGHT(id_bayar, 3) AS UNSIGNED) AS last_seq
    FROM bayar
    WHERE id_bayar LIKE ?
    ORDER BY last_seq DESC
    LIMIT 1
");
$stmt->bind_param('s', $patternMonthly);
$stmt->execute();
$res = $stmt->get_result();

$next = 1;
if ($row = $res->fetch_assoc()) {
    $next = ((int)$row['last_seq']) + 1;
}
$stmt->close();

// Bentuk ID final untuk hari ini
$id_bayar = $kodeCabang . '/' . $hari . '/' . $bulan . '/' . $tahun . '/' . str_pad($next, 3, '0', STR_PAD_LEFT);

// ambil data gadai yang perlu dibayar
$sqlGadai = "SELECT no_gadai FROM gadai WHERE (status = 'diterima' OR status = 'dilelang')";

// Filter cabang kalau bukan pusat
if (!empty($cabang) && strtolower($cabang) !== 'mataram') {
    $sqlGadai .= " AND cabang = '" . $conn->real_escape_string($cabang) . "'";
}

$gadaiResult = mysqli_query($conn, $sqlGadai);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pembayaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --light-green: #d4edda;
            --green: #28a745;
            --dark-green: #1e7e34;
            --accent-green: #c3e6cb;
        }
        
        body {
            background: linear-gradient(135deg, var(--light-green) 0%, #f8f9fa 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .main-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }
        
        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(40, 167, 69, 0.1);
            overflow: hidden;
            background: white;
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--green) 0%, var(--dark-green) 100%);
            border-radius: 0;
            padding: 2rem;
            text-align: center;
            position: relative;
        }
        
        .card-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 20"><defs><pattern id="grain" width="100" height="20" patternUnits="userSpaceOnUse"><circle cx="10" cy="10" r="1.5" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100%" height="100%" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }
        
        .card-header h5 {
            position: relative;
            z-index: 1;
            font-size: 1.75rem;
            font-weight: 600;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .card-header .icon {
            font-size: 3rem;
            margin-bottom: 0.5rem;
            opacity: 0.9;
        }
        
        .card-body {
            padding: 3rem;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--dark-green);
            margin-bottom: 0.75rem;
            font-size: 0.95rem;
        }
        
        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 0.875rem 1rem;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background-color: #fafafa;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--green);
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
            background-color: white;
        }
        
        .form-control[readonly] {
            background-color: var(--light-green);
            border-color: var(--accent-green);
            color: var(--dark-green);
            font-weight: 500;
        }
        
        .form-check {
            margin-bottom: 0.75rem;
        }
        
        .form-check-input:checked {
            background-color: var(--green);
            border-color: var(--green);
        }
        
        .form-check-input:focus {
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }
        
        .form-check-label {
            font-weight: 500;
            color: #495057;
            margin-left: 0.5rem;
        }
        
        .btn {
            border-radius: 12px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            border: none;
        }
        
        .btn-success {
            background: linear-gradient(135deg, var(--green) 0%, var(--dark-green) 100%);
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }
        
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
            background: linear-gradient(135deg, var(--dark-green) 0%, var(--green) 100%);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
        }
        
        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(108, 117, 125, 0.4);
            background: linear-gradient(135deg, #495057 0%, #6c757d 100%);
        }
        
        .form-section {
            background: linear-gradient(135deg, #f8f9fa 0%, var(--light-green) 100%);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid var(--accent-green);
        }
        
        .section-title {
            color: var(--dark-green);
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .input-group-text {
            background: var(--accent-green);
            border: 2px solid #e9ecef;
            border-radius: 12px 0 0 12px;
            color: var(--dark-green);
            font-weight: 600;
        }
        
        .action-buttons {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 2rem;
            margin-top: 2rem;
        }
        
        @media (max-width: 768px) {
            .main-container {
                padding: 1rem;
            }
            
            .card-body {
                padding: 2rem;
            }
            
            .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }
        }
        
        .fade-in {
            animation: fadeIn 0.8s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>

<body>
    <div class="main-container fade-in">
        <div class="card shadow-lg">
            <div class="card-header text-white">
                <div class="icon">
                    <i class="bi bi-credit-card-2-front"></i>
                </div>
                <h5 class="mb-0">Form Pembayaran</h5>
                <small class="opacity-75">Sistem Pembayaran Gadai</small>
            </div>
            <div class="card-body">
                <form action="proses_payment.php" method="POST" enctype="multipart/form-data" onsubmit="return confirm('Yakin ingin menyimpan transaksi ini?')">

                    <!-- Section: Informasi Dasar -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="bi bi-info-circle"></i>
                            Informasi Dasar
                        </div>
                        
                        <!-- ID Bayar -->
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-hash me-1"></i>
                                ID Bayar
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-key"></i>
                                </span>
                                <input type="text" class="form-control" name="id_bayar" value="<?= htmlspecialchars($id_bayar) ?>" readonly>
                            </div>
                        </div>

                        <!-- No Gadai -->
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-file-text me-1"></i>
                                No Gadai
                            </label>
                            <select name="no_gadai" id="no_gadai" class="form-select" required>
                                <option value="">-- Pilih No Gadai --</option>
                                <?php while ($g = mysqli_fetch_assoc($gadaiResult)) : ?>
                                    <option value="<?= htmlspecialchars($g['no_gadai']) ?>"><?= htmlspecialchars($g['no_gadai']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <!-- Tanggal Bayar -->
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-calendar-event me-1"></i>
                                Tanggal Bayar
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-calendar3"></i>
                                </span>
                                <input type="date" name="tanggal_bayar" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Keterangan -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="bi bi-chat-square-text"></i>
                            Keterangan Pembayaran
                        </div>
                        
                        <div class="d-flex flex-column gap-3">
                            <!-- Radio pelunasan -->
                            <div class="form-check p-3 border rounded" style="background: rgba(40, 167, 69, 0.05);">
                                <input class="form-check-input" type="radio" name="keterangan" id="radioPelunasan" value="pelunasan" required>
                                <label class="form-check-label" for="keteranganCicilan">
                                    <i class="bi bi-check-circle me-2"></i>
                                    <strong>Pelunasan</strong>
                                    <div class="text-muted small mt-1">Bayar full</div>
                                </label>
                            </div>

                            <!-- Radio perpanjang -->
                            <div class="form-check p-3 border rounded" style="background: rgba(40, 167, 69, 0.05);">
                                <input class="form-check-input" type="radio" name="keterangan" id="keteranganPerpanjang" value="perpanjang" required>
                                <label class="form-check-label" for="keteranganKekurangan">
                                    <i class="bi bi-plus-circle me-2"></i>
                                    <strong>Perpanjangan</strong>
                                    <div class="text-muted small mt-1">Bayar bunga, denda, biaya admin untuk perpanjangan</div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Detail Pembayaran -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="bi bi-cash-coin"></i>
                            Detail Pembayaran
                        </div>
                        
                        <!-- Jumlah Bayar -->
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-currency-dollar me-1"></i>
                                Jumlah Bayar (Tanpa titik / koma)
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" id= "jumlah_bayar" name="jumlah_bayar" class="form-control" placeholder="Masukkan jumlah bayar" required>
                            </div>
                        </div>

                        <!-- Metode Bayar -->
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-wallet2 me-1"></i>
                                Metode Bayar
                            </label>
                            <div class="d-flex gap-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="metode_bayar" value="Cash" id="metodeCash" required>
                                    <label class="form-check-label" for="metodeCash">
                                        <i class="bi bi-cash me-1"></i>
                                        Cash
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="metode_bayar" value="Transfer" id="metodeTransfer" required>
                                    <label class="form-check-label" for="metodeTransfer">
                                        <i class="bi bi-bank me-1"></i>
                                        Transfer
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Bukti Bayar -->
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-image me-1"></i>
                                Bukti Bayar
                            </label>
                            <input type="file" name="bukti_bayar" class="form-control" accept="image/*" required>
                            <div class="form-text text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Upload file gambar (JPG, PNG, etc.)
                            </div>
                        </div>
                    </div>


                    <!-- Action Buttons -->
                    <div class="action-buttons">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <button type="reset" class="btn btn-secondary w-100">
                                    <i class="bi bi-arrow-clockwise me-2"></i>
                                    Reset Form
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bi bi-check-circle me-2"></i>
                                    Simpan Pembayaran
                                </button>
                            </div>
                        </div>
                        
                        <div class="text-center mt-3">
                            <a href="home_payment.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-2"></i>
                                Kembali ke Menu Utama
                            </a>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    const selectNoGadai   = document.getElementById('no_gadai');
    const radioPelunasan  = document.getElementById('radioPelunasan');
    const jumlahBayar     = document.getElementById('jumlah_bayar');

    function isiPelunasan() {
        const noGadai = selectNoGadai.value;

        if (!noGadai) {
            alert('Pilih No Gadai terlebih dahulu');
            radioPelunasan.checked = false;
            return;
        }

        fetch(`api/getSisaTagihan.php?no_gadai=${encodeURIComponent(noGadai)}`)
            .then(res => res.json())
            .then(data => {
                const sisa = parseFloat(data.sisa_tagihan) || 0;
                jumlahBayar.value = Math.round(sisa);
            })
            .catch(err => {
                console.error('Gagal ambil sisa tagihan:', err);
                jumlahBayar.value = 0;
            });
    }

    // klik radio pelunasan
    radioPelunasan.addEventListener('change', function () {
        if (this.checked) {
            isiPelunasan();
        }
    });

    // ganti no gadai → reset jumlah bayar
    selectNoGadai.addEventListener('change', function () {
        jumlahBayar.value = '';
    });
</script>
</body>

</html>