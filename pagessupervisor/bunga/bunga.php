<?php
session_start();
if (!isset($_SESSION['usr']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'supervisor' || !isset($_SESSION['cabang'])) {
    header('Location: ../../gates/login.php');
    exit;
}
include_once("../../config/koneksi.php");
include_once("ambilbunga.php"); // ambil data bunga
$username = $_SESSION['usr'];
$nama = $_SESSION['nama'];
$role = $_SESSION['role'];
$cabang = $_SESSION['cabang'];
$status = isset($_GET['status']) ? $_GET['status'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bunga</title>
    <link rel="stylesheet" href="../../bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #ccffd5; /* hijau muda */
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .card {
            width: 100%;
            max-width: 500px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .form-label {
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="card p-4 bg-white">
    <h5 class="mb-4 text-center">Kebijakan Bunga</h5>
    <form action="proses_bunga.php" method="POST">
        <div class="mb-3">
            <label for="bunga_reguler" class="form-label">Bunga Reguler (%)</label>
            <input type="number" step="0.01" class="form-control" id="bunga_reguler" name="bunga_reguler"
                    value="<?= htmlspecialchars($bunga_reguler) ?>" placeholder="Misal: 2.5">
        </div>
        <div class="mb-4">
            <label for="bunga_denda" class="form-label">Bunga Denda (%) Per Hari</label>
            <input type="number" step="0.01" class="form-control" id="bunga_denda" name="bunga_denda" value="<?= htmlspecialchars($bunga_denda) ?>" placeholder="Misal: 1.75">
        </div>
        <div class="mb-4">
            <label for="bunga_denda" class="form-label">Biaya Admin (%)</label>
            <input type="number" step="0.01" class="form-control" id="bunga_admin" name="bunga_admin" value="<?= htmlspecialchars($bunga_admin) ?>" placeholder="Misal: 1.75">
        </div>
        <div class="text-center">
            <button type="submit" class="btn btn-success px-4">Simpan</button>
        </div>
        <div class="mt-3 text-center">
            <a href="../home.php" class="btn btn-secondary">Kembali</a>
        </div>
    </form>
</div>

<script>
    const status = "<?php echo $status; ?>";

    if (status === "success") {
        alert("✅ Tarif bunga berhasil diperbarui!");
    } else if (status === "invalid") {
        alert("⚠️ Input tidak valid. Masukkan angka yang benar.");
    } else if (status === "prepare_failed") {
        alert("❌ Gagal memproses data di server.");
    } else if (status === "unauthorized") {
        alert("🚫 Anda tidak memiliki akses.");
    }
</script>
</body>
</html>