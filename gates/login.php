<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <title>Log In</title>
    <script src="jquery-3.7.1.min.js"></script>
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
            <!-- pakai col yang lebih lebar -->
            <div class="col-11 col-sm-10 col-md-8 col-lg-6 col-xl-5">
                <!-- card lebih lebar otomatis karena kolom, dan rounded besar -->
                <div class="card shadow rounded-4">
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <img src="../assets/logo.jpeg" alt="logo.jpeg" class="img-fluid" style="max-width: 150px;" />
                        </div>
                        <h2 class="text-center mb-4">Form Log In</h2>
                        <form action="proseslogin.php" method="post">
                            <?php if (isset($_GET['error'])): ?>
                                <div class="text-danger text-center mb-3">
                                    <?= htmlspecialchars($_GET['error']); ?>
                                </div>
                            <?php endif; ?>

                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="usr" class="form-control" required />
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="psw" class="form-control" required />
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Login sebagai</label>
                                <select name="role" class="form-select" required>
                                    <option value="admin">Admin</option>
                                    <option value="supervisor">Supervisor</option>
                                </select>
                            </div>
                            <div class="mb-3 text-center">
                                <small class="text-muted">Hubungi supervisor untuk reset password.</small>
                            </div>
                            <div class="d-grid">
                                <input type="submit" value="Kirim" class="btn btn-primary" />
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>