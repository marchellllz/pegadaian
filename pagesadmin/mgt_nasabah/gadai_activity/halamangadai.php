<?php
session_start();
// halaman gadai_activity/halamangadai.php
include "../../../config/koneksi.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $nomor_nasabah = intval($_POST['id']);

    // Ambil nama nasabah
    $qNasabah = $conn->prepare("SELECT nama_nasabah FROM nasabah_gadai WHERE nomor_nasabah = ?");
    $qNasabah->bind_param("i", $nomor_nasabah);
    $qNasabah->execute();
    $resNasabah = $qNasabah->get_result()->fetch_assoc();

    if (!$resNasabah) {
        die("Nasabah tidak ditemukan");
    }

    // Ambil list jenis dari tabel produk
    $produk = [];
    $resProduk = $conn->query("SELECT DISTINCT jenis FROM produk ORDER BY jenis ASC");
    while ($row = $resProduk->fetch_assoc()) {
        $produk[] = $row['jenis'];
    }

    // Ambil list bunga dari tabel dfbunga
    $bungaList = [];
    $resBunga = $conn->query("SELECT tarif FROM dfbunga where bunga='normal' or bunga='bebas'");
    while ($row = $resBunga->fetch_assoc()) {
        $bungaList[] = $row;
    }
} else {
    die("Akses langsung tidak diperbolehkan");
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Gadai</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- DateTime Picker -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/css/tempusdominus-bootstrap-4.min.css" />

    <style>
        :root {
            --primary-green: #28a745;
            --light-green: #d4edda;
            --dark-green: #1e7e34;
            --accent-green: #c3e6cb;
        }

        body {
            background: linear-gradient(135deg, var(--light-green) 0%, #f8f9fa 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        .main-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .form-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(40, 167, 69, 0.1);
            border: 1px solid var(--accent-green);
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary-green), var(--dark-green));
            color: white;
            padding: 2rem;
            text-align: center;
            border: none;
        }

        .card-header h2 {
            margin: 0;
            font-weight: 700;
            font-size: 2rem;
        }

        .card-header .subtitle {
            opacity: 0.9;
            margin-top: 0.5rem;
            font-weight: 300;
        }

        .card-body {
            padding: 2.5rem;
        }

        .form-group {
            margin-bottom: 1.8rem;
        }

        .form-label {
            font-weight: 600;
            color: var(--dark-green);
            margin-bottom: 0.8rem;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-control,
        .form-select {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background-color: #fafafa;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-green);
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
            background-color: white;
        }

        .form-control[readonly] {
            background-color: var(--light-green);
            border-color: var(--accent-green);
            color: var(--dark-green);
            font-weight: 500;
        }

        .btn-success {
            background: linear-gradient(135deg, var(--primary-green), var(--dark-green));
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
            background: linear-gradient(135deg, var(--dark-green), var(--primary-green));
        }

        .btn-secondary {
            background: #6c757d;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: #545b62;
            transform: translateY(-1px);
        }

        .alert-info {
            background-color: var(--light-green);
            border-color: var(--accent-green);
            color: var(--dark-green);
            border-radius: 10px;
            border-left: 4px solid var(--primary-green);
        }

        .input-group {
            border-radius: 10px;
        }

        .calculated-field {
            background: linear-gradient(135deg, var(--light-green), var(--accent-green));
            font-weight: 600;
            color: var(--dark-green);
        }

        .row .col-md-6 {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }

        .form-section {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            border-left: 4px solid var(--primary-green);
        }

        .section-title {
            color: var(--dark-green);
            font-weight: 600;
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }

        @media (max-width: 768px) {
            .main-container {
                margin: 1rem auto;
                padding: 0 0.5rem;
            }

            .card-body {
                padding: 1.5rem;
            }

            .card-header {
                padding: 1.5rem;
            }

            .card-header h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="main-container">
        <div class="form-card">
            <div class="card-header">
                <h2><i class="fas fa-coins me-3"></i>Form Gadai</h2>
                <p class="subtitle mb-0">Silakan lengkapi form di bawah ini untuk transaksi gadai</p>
            </div>

            <div class="card-body">
                <form action="proses_gadai.php" method="POST" onsubmit="return confirm('Yakin ingin menyimpan transaksi ini?')">

                    <!-- Data Nasabah Section -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-user me-2"></i>Informasi Nasabah
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-id-card"></i>Nomor Nasabah
                                    </label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($nomor_nasabah) ?>" readonly>
                                    <input type="hidden" name="nomor_nasabah" value="<?= htmlspecialchars($nomor_nasabah) ?>">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-user-circle"></i>Nama Nasabah
                                    </label>
                                    <input type="text" name="nama_nasabah" class="form-control" value="<?= htmlspecialchars($resNasabah['nama_nasabah']) ?>" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detail Gadai Section -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-gem me-2"></i>Detail Gadai
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-tag"></i>Jenis
                                    </label>
                                    <select name="jenis" class="form-select" required>
                                        <option value="">-- Pilih Jenis --</option>
                                        <?php foreach ($produk as $j): ?>
                                            <option value="<?= htmlspecialchars($j) ?>"><?= htmlspecialchars($j) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-shield-alt"></i>Jaminan
                                    </label>
                                    <input type="text" name="jaminan" class="form-control" placeholder="Masukkan deskripsi jaminan" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-calendar-plus"></i>Tanggal Masuk
                                    </label>
                                    <input type="datetime-local" name="tanggal_masuk" class="form-control" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-calendar-minus"></i>Tanggal Jatuh Tempo
                                    </label>
                                    <input type="datetime-local" name="tanggal_keluar" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Perhitungan Section -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-calculator me-2"></i>Perhitungan Pinjaman
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Perhatian:</strong> Isi nilai pinjaman tanpa menggunakan titik atau koma! Nilai Taksiran Maks 80% dari Nilai Pasar
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-money-bill-wave"></i>Nilai Pasar
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">Rp</span>
                                        <input type="number" name="nilai" id="nilai" class="form-control" placeholder="0" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-money-bill-wave"></i>Nilai Taksiran
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">Rp</span>
                                        <input type="number" name="nilai_taksir" id="nilai_taksir" class="form-control" placeholder="0" required>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-percentage"></i>Bunga
                                    </label>
                                    <select id="bunga" class="form-select" required>
                                        <option value="">-- Pilih Bunga --</option>
                                        <?php foreach ($bungaList as $bunga): ?>
                                            <option value="<?= htmlspecialchars($bunga['tarif']) ?>">
                                                <?= htmlspecialchars($bunga['tarif']) ?>%
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Jumlah Bunga (tampilan saja, tanpa name) -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-chart-line"></i>Jumlah Bunga
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">Rp</span>
                                        <!-- input tampil untuk user (readonly), jangan beri name -->
                                        <input type="text" id="jumlah_bunga_display" class="form-control calculated-field" readonly placeholder="0">
                                        <!-- hidden input untuk dikirim ke server (mentah, tanpa pemisah ribuan) -->
                                        <input type="hidden" id="jumlah_bunga" name="jumlah_bunga" value="0">
                                    </div>
                                </div>
                            </div>
                            <!-- Biaya Admin -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-receipt"></i>Biaya admin
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">Rp</span>
                                        <!-- input tampil untuk user (readonly), jangan beri name -->
                                        <input type="text" id="jumlah_admin_display" class="form-control calculated-field" readonly placeholder="0">
                                        <input type="hidden" id="biaya_adm" name="biaya_adm" value="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hidden default values -->
                    <input type="hidden" name="denda" value="0">
                    <input type="hidden" name="status" value="pending">

                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-success me-3">
                            <i class="fas fa-save me-2"></i>Simpan Transaksi
                        </button>
                        <a href="../datanasabah.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            let tarifAdmin = 0;

            const nilaiInput = document.getElementById('nilai_taksir');
            const bungaSelect = document.getElementById('bunga');
            const jumlahBungaDisplay = document.getElementById('jumlah_bunga_display');
            const jumlahBungaHidden = document.getElementById('jumlah_bunga');
            const biaya_adm = document.getElementById('biaya_adm');
            const jumlahAdminDisplay = document.getElementById('jumlah_admin_display');

            // --- mulai: tambahan untuk aturan nilai_taksir 80% dari nilai pasar ---
            const marketInput = document.getElementById('nilai'); // input nilai pasar
            const taksirInput = nilaiInput; // kamu sudah punya const nilaiInput = document.getElementById('nilai_taksir');
            // pakai dataset untuk track apakah terisi otomatis atau diedit user
            taksirInput.dataset.autofilled = taksirInput.dataset.autofilled || '0';
            taksirInput.dataset.userEdited = taksirInput.dataset.userEdited || '0';

            function hitungBunga() {
                const nilai = parseFloat(nilaiInput.value) || 0;
                const bunga = parseFloat(bungaSelect.value) || 0;
                const totalBunga = (nilai * bunga) / 100;

                // tampilkan yang sudah diformat untuk user
                jumlahBungaDisplay.value = totalBunga.toLocaleString('id-ID', {
                    maximumFractionDigits: 0
                });

                // simpan mentah (tanpa titik/koma) untuk dikirim ke server
                // gunakan Math.round jika kamu ingin integer rupiah
                jumlahBungaHidden.value = Math.round(totalBunga);
            }

            function hitungAdmin() {
                const nilai = parseFloat(nilaiInput.value) || 0;
                //const admin = 2; // 2%
                const totaladmin = (nilai * tarifAdmin) / 100;
                jumlahAdminDisplay.value = totaladmin.toLocaleString('id-ID', {
                    maximumFractionDigits: 0
                });
                biaya_adm.value = Math.round(totaladmin);
            }

            // ambil tarif admin dari database
            fetch('tarifAdmin.php')
                .then(response => response.json())
                .then(data => {
                    tarifAdmin = parseFloat(data.tarif) || 0;
                    hitungAdmin(); // hitung ulang setelah tarif didapat
                })
                .catch(err => {
                    console.error('Gagal ambil tarif admin:', err);
                    tarifAdmin = 0;
                });

            nilaiInput.addEventListener('input', function(e) {
                // biarkan input tipe number tetap numeric; strip non-digit (jika mau)
                let value = e.target.value.replace(/[^\d]/g, '');
                e.target.value = value;
                hitungBunga();
            });

            bungaSelect.addEventListener('change', hitungBunga);

            // initial calc
            hitungBunga();
            hitungAdmin();

            function updateMaxTaksir() {
                const market = parseFloat((marketInput.value || '').toString().replace(/[^\d]/g, '')) || 0;
                const maxTaksir = Math.floor(market * 0.8); // 80%, bulat ke bawah
                taksirInput.max = maxTaksir;

                if (market === 0) {
                    taksirInput.value = '';
                    taksirInput.readOnly = false;
                    taksirInput.dataset.autofilled = '0';
                    taksirInput.dataset.userEdited = '0';
                } else {
                    const curr = parseFloat(taksirInput.value) || 0;
                    // kalau belum pernah diedit user atau nilai saat ini > max atau kosong -> isi auto
                    if (taksirInput.dataset.userEdited !== '1' || curr > maxTaksir || taksirInput.value === '') {
                        taksirInput.value = maxTaksir;
                        taksirInput.readOnly = true; // jadikan readonly saat auto-filled
                        taksirInput.dataset.autofilled = '1';
                        taksirInput.dataset.userEdited = '0';
                    } else {
                        // user sudah mengedit ke nilai lebih kecil -> biarkan editable
                        taksirInput.readOnly = false;
                        taksirInput.dataset.autofilled = '0';
                    }
                }

                // recalc bunga & admin pake nilai taksir saat ini
                hitungBunga();
                hitungAdmin();
            }
            // bikin market input sanitize & update max tiap perubahan
            marketInput.addEventListener('input', function(e) {
                let v = e.target.value.replace(/[^\d]/g, '');
                e.target.value = v;
                updateMaxTaksir();
            });
            // Kalau user fokus ke taksir yang auto-filled -> biarkan dia ubah (remove readonly)
            taksirInput.addEventListener('focus', function() {
                if (taksirInput.dataset.autofilled === '1') {
                    taksirInput.readOnly = false;
                    taksirInput.dataset.userEdited = '1';
                    taksirInput.dataset.autofilled = '0';
                    // optional: select semua angka supaya gampang ketik ulang
                    taksirInput.select();
                }
            });
            // sanitize input taksir, clamp ke max jika perlu, dan hitung ulang
            taksirInput.addEventListener('input', function(e) {
                let v = e.target.value.replace(/[^\d]/g, '');
                e.target.value = v;
                const max = parseFloat(taksirInput.max) || 0;
                let val = parseFloat(v) || 0;
                if (val > max) {
                    e.target.value = max;
                    val = max;
                }
                // set flag userEdited kalau dia memang mengetik
                taksirInput.dataset.userEdited = '1';
                taksirInput.dataset.autofilled = '0';

                hitungBunga();
                hitungAdmin();
            });
            // Setelah blur, kalau nilai == max -> kembalikan jadi readonly (anggap auto)
            taksirInput.addEventListener('blur', function() {
                const max = parseFloat(taksirInput.max) || 0;
                const val = parseFloat(taksirInput.value) || 0;
                if (max > 0 && val === max) {
                    taksirInput.readOnly = true;
                    taksirInput.dataset.autofilled = '1';
                    taksirInput.dataset.userEdited = '0';
                }
            });

            // initial sync (panggil sekali supaya saat load form langsung konsisten)
            updateMaxTaksir();
            // --- akhir tambahan ---
        });
    </script>
</body>

</html>