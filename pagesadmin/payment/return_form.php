<?php
include_once("../../config/koneksi.php");
session_start();
if (!isset($_SESSION['usr']) || !isset($_SESSION['role'])|| $_SESSION['role'] !== 'admin') {
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

$sqlGadai = "
    SELECT g.no_gadai,
           CAST(
             (CAST(IFNULL(g.nilai,0) AS DECIMAL(15,2)) 
              + CAST(IFNULL(g.bunga,0) AS DECIMAL(15,2)) 
              + CAST(IFNULL(g.denda,0) AS DECIMAL(15,2)))
             - IFNULL(b.total_bayar,0)
             AS DECIMAL(15,2)
           ) AS sisa_tagihan
    FROM gadai g
    LEFT JOIN (
        SELECT no_gadai, SUM(jumlah_bayar) AS total_bayar
        FROM bayar
        GROUP BY no_gadai
    ) b ON b.no_gadai = g.no_gadai
    WHERE (g.status = 'dilelang' OR g.status = 'lunas')
      AND CAST(
             (CAST(IFNULL(g.nilai,0) AS DECIMAL(15,2)) 
              + CAST(IFNULL(g.bunga,0) AS DECIMAL(15,2)) 
              + CAST(IFNULL(g.denda,0) AS DECIMAL(15,2)))
             - IFNULL(b.total_bayar,0)
             AS DECIMAL(15,2)
          ) < 0
";

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
    <title>Form Pengembalian</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --light-green: #d4edda;
            --green: #28a745;
            --dark-green: #1e7e34;
            --accent-green: #c3e6cb;
            --return-blue: #17a2b8;
            --return-dark-blue: #117a8b;
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
            box-shadow: 0 15px 35px rgba(23, 162, 184, 0.1);
            overflow: hidden;
            background: white;
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--return-blue) 0%, var(--return-dark-blue) 100%);
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
            border-color: var(--return-blue);
            box-shadow: 0 0 0 0.2rem rgba(23, 162, 184, 0.25);
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
            background-color: var(--return-blue);
            border-color: var(--return-blue);
        }
        
        .form-check-input:focus {
            box-shadow: 0 0 0 0.2rem rgba(23, 162, 184, 0.25);
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
            background: linear-gradient(135deg, var(--return-blue) 0%, var(--return-dark-blue) 100%);
            box-shadow: 0 4px 15px rgba(23, 162, 184, 0.3);
        }
        
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(23, 162, 184, 0.4);
            background: linear-gradient(135deg, var(--return-dark-blue) 0%, var(--return-blue) 100%);
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
        
        .btn-outline-secondary {
            border: 2px solid #6c757d;
            color: #6c757d;
            background: transparent;
        }
        
        .btn-outline-secondary:hover {
            background: #6c757d;
            border-color: #6c757d;
            color: white;
            transform: translateY(-2px);
        }
        
        .form-section {
            background: linear-gradient(135deg, #f8f9fa 0%, rgba(23, 162, 184, 0.05) 100%);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid rgba(23, 162, 184, 0.2);
        }
        
        .section-title {
            color: var(--return-dark-blue);
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .input-group-text {
            background: rgba(23, 162, 184, 0.1);
            border: 2px solid #e9ecef;
            border-radius: 12px 0 0 12px;
            color: var(--return-dark-blue);
            font-weight: 600;
        }
        
        .action-buttons {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 2rem;
            margin-top: 2rem;
        }
        
        .return-highlight {
            background: linear-gradient(135deg, rgba(23, 162, 184, 0.1) 0%, rgba(23, 162, 184, 0.05) 100%);
            border: 2px solid rgba(23, 162, 184, 0.2);
            border-radius: 12px;
            padding: 1rem;
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
                    <i class="bi bi-arrow-return-left"></i>
                </div>
                <h5 class="mb-0">Form Pengembalian</h5>
                <small class="opacity-75">Sistem Pengembalian Gadai</small>
            </div>
            <div class="card-body">
                <form action="proses_return.php" method="POST" enctype="multipart/form-data" onsubmit="return confirm('Yakin ingin menyimpan transaksi ini?')">

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
                            <select name="no_gadai" class="form-select" required>
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

                    <!-- Section: Detail Pengembalian -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="bi bi-cash-coin"></i>
                            Detail Pengembalian
                        </div>
                        
                        <!-- Jumlah Bayar -->
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-currency-dollar me-1"></i>
                                Jumlah Bayar (Tanpa titik / koma)
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="jumlah_bayar" class="form-control" placeholder="Masukkan jumlah bayar" required>
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

                    <!-- Section: Keterangan Pengembalian -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="bi bi-arrow-return-right"></i>
                            Jenis Pengembalian
                        </div>
                        
                        <div class="d-flex flex-column gap-3">
                            <!-- Radio Kelebihan Lelang -->
                            <div class="form-check p-3 border rounded return-highlight">
                                <input class="form-check-input" type="radio" name="keterangan" id="keteranganKlblelang" value="klblelang" required>
                                <label class="form-check-label" for="keteranganKlblelang">
                                    <i class="bi bi-arrow-up-circle me-2 text-info"></i>
                                    <strong>Pengembalian Kelebihan Lelang</strong>
                                    <div class="text-muted small mt-1">Pengembalian kelebihan dari nilai lelang</div>
                                </label>
                            </div>

                            <!-- Radio Kelebihan Bayar -->
                            <div class="form-check p-3 border rounded return-highlight">
                                <input class="form-check-input" type="radio" name="keterangan" id="keteranganKlbbayar" value="klbbayar" required>
                                <label class="form-check-label" for="keteranganKlbbayar">
                                    <i class="bi bi-arrow-return-left me-2 text-info"></i>
                                    <strong>Pengembalian Kelebihan Bayar</strong>
                                    <div class="text-muted small mt-1">Pengembalian kelebihan bayar</div>
                                </label>
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
                                    Simpan Pengembalian
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
</body>

</html>