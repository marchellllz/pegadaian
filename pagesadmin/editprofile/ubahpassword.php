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
    <title>Ubah Password</title>
    <link rel="stylesheet" href="../../bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.min.css">
</head>

<body>
    <div class="container mt-5">
        <h3 class="mb-4">Verifikasi Password Lama</h3>

        <form id="formCheckPassword">
            <div class="mb-3">
                <label for="passwordLama" class="form-label">Masukkan Password Lama</label>
                <input type="password" class="form-control" id="passwordLama" name="passwordLama" required>
            </div>
            <button type="submit" class="btn btn-primary">Cek Password</button>
            <div id="hasil" class="mt-3"></div>
            <a href="editprofile.php" class="btn btn-secondary">Kembali</a>
        </form>
    </div>

    <script>
        document.getElementById("formCheckPassword").addEventListener("submit", function(e) {
            e.preventDefault();

            const passwordLama = document.getElementById("passwordLama").value;
            
            if (passwordLama.trim() === "") {
                hasil.innerHTML = `<div class="alert alert-warning">Password tidak boleh kosong.</div>`;
                return;
            }

            fetch("cek_password.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: "passwordLama=" + encodeURIComponent(passwordLama)
                })
                .then(response => response.json())
                .then(data => {
                    const hasil = document.getElementById("hasil");
                    if (data.status === "valid") {
                        hasil.innerHTML = `<div class="alert alert-success">Password benar. Mengalihkan...</div>`;
                        setTimeout(() => {
                            window.location.href = "passwordbaru.php";
                        }, 1000);
                    } else if (data.status === "invalid") {
                        hasil.innerHTML = `<div class="alert alert-danger">Password salah. Coba lagi.</div>`;
                    } else if (data.status === "unauthorized") {
                        hasil.innerHTML = `<div class="alert alert-danger">Kamu belum login.</div>`;
                    } else {
                        hasil.innerHTML = `<div class="alert alert-danger">Terjadi kesalahan.</div>`;
                    }
                })
                .catch(err => {
                    hasil.innerHTML = `<div class="alert alert-danger">Gagal terhubung ke server.</div>`;
                    console.error(err);
                });
        });
    </script>
</body>

</html>