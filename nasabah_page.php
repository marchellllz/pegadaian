<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Nasabah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-green: #8FD14E;
            --light-green: #E8F5D8;
            --medium-green: #7BC143;
            --dark-green: #6BA037;
            --bg-green: #F4FCF0;
        }

        body {
            background: linear-gradient(135deg, var(--bg-green) 0%, var(--light-green) 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .header-section {
            background: white;
            color: var(--dark-green);
            padding: 1.5rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 25px 25px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border-bottom: 3px solid var(--primary-green);
        }

        .header-text h1 {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--dark-green);
        }

        .header-text p {
            font-size: 0.95rem;
            font-weight: 400;
            color: var(--medium-green);
        }

        .main-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(143, 209, 78, 0.2);
            margin-bottom: 2rem;
        }

        .form-control {
            border: 2px solid rgba(143, 209, 78, 0.3);
            border-radius: 12px;
            padding: 12px 16px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-green);
            box-shadow: 0 0 0 0.2rem rgba(143, 209, 78, 0.25);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--medium-green) 100%);
            border: none;
            border-radius: 12px;
            padding: 12px 30px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--medium-green) 0%, var(--dark-green) 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(143, 209, 78, 0.4);
        }

        .input-group-text {
            background: var(--light-green);
            border: 2px solid rgba(143, 209, 78, 0.3);
            border-right: none;
            color: var(--dark-green);
        }

        .result-section {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            border-left: 5px solid var(--primary-green);
        }

        .section-title {
            color: var(--dark-green);
            font-weight: 700;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .floating-elements {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }

        .floating-circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(143, 209, 78, 0.1);
            animation: float 6s ease-in-out infinite;
        }

        .floating-circle:nth-child(1) {
            width: 60px;
            height: 60px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .floating-circle:nth-child(2) {
            width: 40px;
            height: 40px;
            top: 60%;
            right: 15%;
            animation-delay: 2s;
        }

        .floating-circle:nth-child(3) {
            width: 80px;
            height: 80px;
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px) rotate(0deg);
            }

            50% {
                transform: translateY(-20px) rotate(180deg);
            }
        }

        .form-label {
            color: var(--dark-green);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .placeholder-glow {
            animation: placeholder-glow 2s ease-in-out infinite alternate;
        }

        @keyframes placeholder-glow {
            to {
                opacity: 0.5;
            }
        }
    </style>
</head>

<body>
    <!-- Floating Background Elements -->
    <div class="floating-elements">
        <div class="floating-circle"></div>
        <div class="floating-circle"></div>
        <div class="floating-circle"></div>
    </div>

    <!-- Header Section -->
    <div class="header-section">
        <div class="container">
            <div class="logo-container">
                <img src="assets/logo.jpeg" alt="Logo" class="logo" width="90">
                <div class="header-text">
                    <h1><i class="fas fa-user-circle me-2"></i>Portal Nasabah</h1>
                    <p>Portal Nasabah Sobat Gadai</p>
                </div>
            </div>
        </div>
    </div>
    <!-- Captcha Section -->
    <div class="container my-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-header text-center bg-success text-white">
                        <i class="fas fa-robot me-2"></i> Verifikasi Captcha
                    </div>
                    <div class="card-body">

                        <!-- Captcha Section -->
                        <div class="col-md-12 mb-3">
                            <label for="captcha" class="form-label text-center w-100">
                                <i class="fas fa-robot me-1"></i>Captcha
                            </label>

                            <div class="d-flex justify-content-center mb-3">
                                <img src="pagenasabah/captcha.php" id="captchaImg" alt="captcha"
                                    style="width:220px; height:70px; border-radius: 8px; border:1px solid #ccc; background:#fff; padding:4px;">
                                <button type="button" class="btn btn-outline-secondary ms-3" onclick="refreshCaptcha()">
                                    <i class="fas fa-sync-alt me-1"></i> Refresh
                                </button>
                            </div>

                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-key"></i>
                                </span>
                                <input type="text" name="captcha" id="captcha" class="form-control text-center fw-bold" placeholder="Masukkan Captcha" required>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Form Section -->
        <div class="main-card">
            <h3 class="section-title">
                <i class="fas fa-search"></i>
                Cek Data Gadai
            </h3>

            <form id="cekForm">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="no_gadai" class="form-label">
                            <i class="fas fa-hashtag me-1"></i>Nomor Gadai
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-ticket-alt"></i>
                            </span>
                            <input type="text" name="no_gadai" id="no_gadai" class="form-control" placeholder="Masukkan Nomor Gadai" required>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="nohp" class="form-label">
                            <i class="fas fa-phone me-1"></i>Nomor HP Terdaftar
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-mobile-alt"></i>
                            </span>
                            <input type="text" name="nohp" id="nohp" class="form-control" placeholder="Masukkan Nomor HP" required>
                        </div>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-search me-2"></i>Cek Data Gadai
                    </button>
                </div>
            </form>
        </div>

        <!-- Output Section -->
        <div id="output"></div>

        <!-- Riwayat Bayar Section -->
        <div id="riwayatBayar"></div>

        <!-- Detail Bayar Section -->
        <div id="detailBayar"></div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById("cekForm").addEventListener("submit", function(e) {
            e.preventDefault();

            // Show loading state
            const submitBtn = e.target.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';
            submitBtn.disabled = true;

            const no_gadai = document.getElementById("no_gadai").value;
            const nohp = document.getElementById("nohp").value;
            const captcha = document.getElementById("captcha").value;
            fetch("pagenasabah/nasabah_cek.php?no_gadai=" + encodeURIComponent(no_gadai) + "&nohp=" + encodeURIComponent(nohp) + "&captcha=" + encodeURIComponent(captcha))
                .then(res => res.text())
                .then(html => {
                    // Wrap result in styled container
                    document.getElementById("output").innerHTML = `
                        <div class="result-section">
                            <h4 class="section-title">
                                <i class="fas fa-info-circle"></i>
                                Hasil Pencarian
                            </h4>
                            ${html}
                        </div>
                    `;

                    // Restore button state
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById("output").innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Terjadi kesalahan saat memproses data. Silakan coba lagi.
                        </div>
                    `;

                    // Restore button state
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                });
        });

        // Enhanced loader functions dengan styling
        function loadBayar(no_gadai) {
            document.getElementById("riwayatBayar").innerHTML = `
                <div class="result-section">
                    <div class="d-flex justify-content-center">
                        <div class="spinner-border text-success" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            `;

            fetch("pagenasabah/nasabah_bayar.php?no_gadai=" + encodeURIComponent(no_gadai))
                .then(res => res.text())
                .then(html => {
                    document.getElementById("riwayatBayar").innerHTML = `
                        <div class="result-section">
                            <h4 class="section-title">
                                <i class="fas fa-history"></i>
                                Riwayat Pembayaran
                            </h4>
                            ${html}
                        </div>
                    `;
                });
        }

        function loadDetail(id_bayar) {
            document.getElementById("detailBayar").innerHTML = `
                <div class="result-section">
                    <div class="d-flex justify-content-center">
                        <div class="spinner-border text-success" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            `;

            fetch("pagenasabah/detail_bayar.php?id_bayar=" + encodeURIComponent(id_bayar))
                .then(res => res.text())
                .then(html => {
                    document.getElementById("detailBayar").innerHTML = `
                        <div class="result-section">
                            <h4 class="section-title">
                                <i class="fas fa-file-invoice-dollar"></i>
                                Detail Pembayaran
                            </h4>
                            ${html}
                        </div>
                    `;
                });
        }

        function refreshCaptcha() {
            document.getElementById('captchaImg').src = 'pagenasabah/captcha.php?' + Date.now();
        }
    </script>
</body>

</html>