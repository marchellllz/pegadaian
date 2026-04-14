<?php
session_start();
if (!isset($_SESSION['usr']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'supervisor' || !isset($_SESSION['cabang'])) {
    header('Location: login.php');
    exit;
}
$cabang = $_SESSION['cabang'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <title>Form Daftar</title>
    <style>
        body {
            background: url('../assets/backgroundform.jpg') center/cover no-repeat fixed;
            /* kalau pengin gelapin biar card lebih kontras: */
            background-blend-mode: darken;
            background-color: rgba(0, 0, 0, 0.2);
        }
    </style>
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <!-- buat kolom lebih lebar supaya card jadi lebih lebar -->
            <div class="col-11 col-sm-10 col-md-8 col-lg-6 col-xl-5">
                <div class="card shadow rounded-4">
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <img src="../assets/logo.jpeg" alt="logo.jpeg" class="img-fluid" style="max-width: 150px;">
                        </div>
                        <h2 class="text-center mb-4">Form Daftar Karyawan</h2>
                        <?php if (isset($_GET['pesan'])): ?>
                            <div class="alert alert-danger text-center p-2">
                                <?= htmlspecialchars($_GET['pesan']) //pesan gagal 
                                ?>
                            </div>
                        <?php elseif (isset($_GET['sukses'])): ?>
                            <div class="alert alert-success text-center p-2">
                                <?= htmlspecialchars($_GET['sukses']) // pesansukses 
                                ?>
                            </div>
                        <?php endif; ?>
                        <form action="prosessignin.php" method="post">
                            <div class="mb-3">
                                <label class="form-label">User ID</label>
                                <input type="text" name="usr" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nama Karyawan</label>
                                <input type="text" name="nama" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nomor Ponsel</label>
                                <input type="text" name="nohp" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Alamat</label>
                                <input type="text" name="alamat" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Jenis Kelamin</label>
                                <select name="jkel" class="form-select" required>
                                    <option value="laki-laki">Laki-Laki</option>
                                    <option value="perempuan">Perempuan</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Agama</label>
                                <select name="agama" class="form-select" required>
                                    <option value="1">Islam</option>
                                    <option value="2">Katolik</option>
                                    <option value="3">Kristen</option>
                                    <option value="4">Hindu</option>
                                    <option value="5">Budha</option>
                                    <option value="6">Kong Hu Chu</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Role sebagai</label>
                                <select name="role" class="form-select" required>
                                    <option value="admin">Admin</option>
                                    <option value="supervisor">Supervisor</option>
                                    <option value="accounting">Accounting</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Cabang</label>
                                <select name="cabang" class="form-select" required>
                                    <?php if ($cabang === 'mataram'): ?>
                                        <option value="mataram">Mataram</option>
                                        <option value="majapahit">Majapahit</option>
                                        <option value="ambarawa">Ambarawa</option>
                                    <?php else: ?>
                                        <option value="<?= htmlspecialchars($cabang) ?>" selected><?= ucfirst($cabang) ?></option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="psw" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Konfirmasi Password</label>
                                <input type="password" name="psw_cfm" class="form-control" required>
                            </div>

                            <div class="d-grid">
                                <input type="submit" value="Kirim" class="btn btn-primary">
                            </div>
                            <br>
                            <div class="mb-3 text-center">
                                <a href="../../pagessupervisor/datakaryawan/datakaryawan.php" class="text-decoration-none">
                                    <label class="form-label mb-0">Kembali</label>
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>