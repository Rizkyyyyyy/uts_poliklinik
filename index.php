<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include 'koneksi.php';

// Periksa apakah pengguna sudah login
$isLoggedIn = isset($_SESSION['username']);

// Daftar halaman yang diizinkan untuk diakses
$allowedPages = [
    'dokter.php',
    'pasien.php',
    'periksa.php',
    'obat.php',
    'LoginUser.php',
    'RegistrasiUser.php',
    'logout.php'
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Poliklinik</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar {
            background: linear-gradient(to right, #007bff, #0056b3);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
        }
        .navbar-brand i {
            margin-right: 10px;
        }
        .nav-link {
            font-size: 1.1rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }
        .nav-link i {
            margin-right: 8px;
        }
        .nav-link:hover {
            transform: translateY(-2px);
            color: #fff !important;
        }
        .jumbotron {
            background: linear-gradient(to right, #007bff, #0056b3);
            color: white;
            padding: 2rem;
            margin-bottom: 2rem;
            border-radius: 10px;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-hospital"></i> Poliklinik
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if ($isLoggedIn): ?>
                        <!-- Menu untuk pengguna yang sudah login -->
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?page=dokter.php">
                                <i class="fas fa-user-md"></i> Data Dokter
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?page=pasien.php">
                                <i class="fas fa-users"></i> Data Pasien
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?page=periksa.php">
                                <i class="fas fa-stethoscope"></i> Periksa
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?page=obat.php">
                                <i class="fas fa-pills"></i> Data Obat
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-warning fw-bold" href="index.php?page=logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    <?php else: ?>
                        <!-- Menu untuk pengguna yang belum login -->
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?page=LoginUser.php">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?page=RegistrasiUser.php">
                                <i class="fas fa-user-plus"></i> Register
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (!$isLoggedIn): ?>
            <div class="jumbotron text-center">
                <h1 class="display-4">Selamat Datang di Poliklinik</h1>
                <p class="lead">Sistem Informasi Manajemen Pelayanan Kesehatan</p>
                <hr class="my-4">
                <p>Silakan login atau register untuk mengakses fitur.</p>
            </div>
        <?php endif; ?>

        <!-- Konten Halaman -->
        <div class="row">
            <div class="col-12">
                <?php
                // Load halaman berdasarkan parameter 'page' di URL
                if (isset($_GET['page'])) {
                    $page = $_GET['page'];

                    // Validasi halaman yang diizinkan
                    if (in_array($page, $allowedPages)) {
                        include $page;
                    } else {
                        echo "<div class='alert alert-danger'>Halaman tidak ditemukan!</div>";
                    }
                } else {
                    if (!$isLoggedIn) {
                        echo "
                        <div class='row'>
                            <div class='col-md-4'>
                                <div class='card text-center mb-4'>
                                    <div class='card-body'>
                                        <i class='fas fa-sign-in-alt fa-3x text-primary mb-3'></i>
                                        <h5 class='card-title'>Login</h5>
                                        <p class='card-text'>Masuk ke akun Anda</p>
                                        <a href='index.php?page=LoginUser.php' class='btn btn-primary'>Masuk</a>
                                    </div>
                                </div>
                            </div>
                            <div class='col-md-4'>
                                <div class='card text-center mb-4'>
                                    <div class='card-body'>
                                        <i class='fas fa-user-plus fa-3x text-success mb-3'></i>
                                        <h5 class='card-title'>Register</h5>
                                        <p class='card-text'>Buat akun baru</p>
                                        <a href='index.php?page=RegistrasiUser.php' class='btn btn-success'>Daftar</a>
                                    </div>
                                </div>
                            </div>
                            <div class='col-md-4'>
                                <div class='card text-center mb-4'>
                                    <div class='card-body'>
                                        <i class='fas fa-info-circle fa-3x text-info mb-3'></i>
                                        <h5 class='card-title'>Informasi</h5>
                                        <p class ='card-text'>Pelajari lebih lanjut tentang aplikasi ini</p>
                                        <a href='#' class='btn btn-info'>Pelajari Lebih Lanjut</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        ";
                    }
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Tambahkan JS Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>