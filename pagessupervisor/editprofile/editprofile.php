<?php
session_start();
if (!isset($_SESSION['usr']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'supervisor') {
    header('Location: ../../gates/login.php');
    exit;
}
include_once("../../config/koneksi.php");
include_once("datauser.php"); // ambil data user

$user_id = $_SESSION['usr'];
$dataUser = new DataUser($conn, $user_id);
$data = $dataUser->getProfile();
$status = isset($_GET['status']) ? $_GET['status'] : '';

// Mapping agama
$agamaList = [
    '1' => 'Islam',
    '2' => 'Katolik',
    '3' => 'Kristen',
    '4' => 'Hindu',
    '5' => 'Buddha',
    '6' => 'Konghucu'
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data User</title>
    <link rel="stylesheet" href="../../bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.min.css">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="card">
            <div class="card-header bg-success text-white">
                Edit Profil
            </div>
            <div class="card-body">
                <?php if ($data): ?>
                    <form id="formEditProfile" method="post">
                        <input type="hidden" name="user_id" value="<?= htmlspecialchars($data['user_id']) ?>" readonly>

                        <div class="mb-3">
                            <label class="form-label">Nama</label>
                            <input type="text" class="form-control" name="nama" value="<?= htmlspecialchars($data['nama']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">No HP</label>
                            <input type="text" class="form-control" name="nohp" value="<?= htmlspecialchars($data['nohp']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea class="form-control" name="alamat" required><?= htmlspecialchars($data['alamat']) ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Jenis Kelamin</label>
                            <select class="form-select" name="jkel">
                                <option value="Laki-laki" <?= $data['jkel'] === 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
                                <option value="Perempuan" <?= $data['jkel'] === 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Agama</label>
                            <select class="form-select" name="agama" required>
                                <option value="">-- Pilih Agama --</option>
                                <?php foreach ($agamaList as $key => $value): ?>
                                    <option value="<?= $key ?>" <?= $key == $data['agama'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($value) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">RL</label>
                            <input type="text" class="form-control" name="rl" value="<?= htmlspecialchars($data['rl']) ?>" required readonly>
                        </div>

                        <button type="submit" class="btn btn-success" id="btnSimpan">Simpan Perubahan</button>
                        <a href="ubahpassword.php" class="btn btn-warning">Ubah Password</a>
                        <a href="../home.php" class="btn btn-secondary">Kembali</a>
                    </form>
                <?php else: ?>
                    <div class="alert alert-danger">Data user tidak ditemukan.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        document.getElementById("btnSimpan").addEventListener("click", function(event) {
            event.preventDefault(); // cegah submit default

            const yakin = confirm("Apakah kamu yakin ingin menyimpan perubahan?");
            if (yakin) {
                const form = document.getElementById("formEditProfile");
                form.action = "updateprofile.php"; // set action target
                form.submit(); // submit manual
            }
        });

        const status = "<?php echo $status; ?>";

        if (status === "success") {
            alert("✅ Data user diperbarui!");
        } 
        else if (status === "prepare_failed") {
            alert("❌ Gagal memproses data di server.");
        }
    </script>
</body>

</html>