<?php
session_start();
if (!isset($_SESSION['usr']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../gates/login.php');
    exit;
}

$user_id = $_SESSION['usr'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Baru</title>
    <link rel="stylesheet" href="../../bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.min.css">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">

                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Ubah Password Baru</h4>
                    </div>
                    <div class="card-body">
                        <form id="formPasswordBaru" method="POST" action="simpanpassword.php">
                            <div class="mb-3">
                                <label for="passwordBaru" class="form-label">Password Baru</label>
                                <input type="password" class="form-control" id="passwordBaru" name="passwordBaru" required>
                            </div>
                            <div class="mb-3">
                                <label for="konfirmasiPassword" class="form-label">Konfirmasi Password</label>
                                <input type="password" class="form-control" id="konfirmasiPassword" name="konfirmasiPassword" required>
                            </div>
                            <div id="errorMsg" class="alert alert-danger d-none"></div>
                            <div class="d-flex justify-content-between mt-3">
                                <button type="submit" class="btn btn-success">Simpan Password</button>
                                <a href="ubahpassword.php" class="btn btn-secondary">Kembali</a>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        document.getElementById("formPasswordBaru").addEventListener("submit", function(e) {
            const pw1 = document.getElementById("passwordBaru").value;
            const pw2 = document.getElementById("konfirmasiPassword").value;
            const errorMsg = document.getElementById("errorMsg");

            if (pw1 !== pw2) {
                e.preventDefault();
                errorMsg.textContent = "Password tidak sama!";
                errorMsg.classList.remove("d-none");
            } else {
                errorMsg.classList.add("d-none");
            }
        });
    </script>


</body>

</html>