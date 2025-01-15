<?php
include 'koneksi.php';

// Pastikan hanya dokter yang dapat mengakses halaman ini
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'dokter') {
    header("Location: login_dokter.php"); // Arahkan ke halaman login jika bukan dokter
    exit;
}

// Ambil username dari sesi
$username = $_SESSION['username'];

// Ambil data dokter dari database berdasarkan username
$query = "SELECT * FROM dokter WHERE username = '$username'";
$result = mysqli_query($koneksi, $query);
$dokter = mysqli_fetch_assoc($result);

// Ambil data kategori poli dari tabel poli
$query_poli = "SELECT * FROM poli";
$result_poli = mysqli_query($koneksi, $query_poli);

// Proses update data dokter jika form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $nama = $_POST['nama'];
    $spesialis = $_POST['spesialis'];
    $telepon = $_POST['telepon'];
    $kategori_poli = $_POST['kategori_poli'];  // Ambil kategori poli dari dropdown

    // Query untuk update data dokter (tanpa password)
    $updateQuery = "UPDATE dokter SET nama='$nama', spesialis='$spesialis', telepon='$telepon', kategori_poli='$kategori_poli' WHERE username='$username'";

    if (mysqli_query($koneksi, $updateQuery)) {
        echo "<script>alert('Data berhasil diperbarui!'); window.location.href='dokter_dashboard.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui data.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Data Dokter</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .card-header {
            background-color: #007bff;
            color: white;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }
        .form-control {
            border-radius: 10px;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
        }
        .navbar {
            background-color: #007bff !important;
        }
        .navbar-brand, .nav-link {
            color: white !important;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container">
        <a class="navbar-brand" href="#">
            <i class="fas fa-user-md"></i> Dokter Dashboard
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="dokter_dashboard.php">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Form Update Data Dokter -->
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header text-center">
                    <h3 class="mb-0">
                        <i class="fas fa-user-edit"></i> Update Data Dokter
                    </h3>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label for="nama" class="form-label">
                                <i class="fas fa-user"></i> Nama
                            </label>
                            <input type="text" class="form-control" id="nama" name="nama" 
                                   value="<?= htmlspecialchars($dokter['nama']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="spesialis" class="form-label">
                                <i class="fas fa-stethoscope"></i> Spesialis
                            </label>
                            <input type="text" class="form-control" id="spesialis" name="spesialis" 
                                   value="<?= htmlspecialchars($dokter['spesialis']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="telepon" class="form-label">
                                <i class="fas fa-phone"></i> Nomor Telepon
                            </label>
                            <input type="text" class="form-control" id="telepon" name="telepon" 
                                   value="<?= htmlspecialchars($dokter['telepon']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="kategori_poli" class="form-label">
                                <i class="fas fa-hospital"></i> Kategori Poli
                            </label>
                            <input type="text" class="form-control" id="kategori_poli" name="kategori_poli" 
                                   value="Poli Umum" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="password_lama" class="form-label">
                                <i class="fas fa-lock"></i> Password Lama
                            </label>
                            <input type="password" class="form-control" id="password_lama" 
                                   name="password_lama" placeholder="Masukkan password lama">
                        </div>

                        <div class="mb-3">
                            <label for="password_baru" class="form-label">
                                <i class="fas fa-unlock"></i> Password Baru
                            </label>
                            <input type="password" class="form-control" id="password_baru" 
                                   name="password_baru" placeholder="Masukkan password baru">
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save"></i> Perbarui Data
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"></script>
</body>
</html>