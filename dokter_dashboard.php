<?php
include 'koneksi.php';
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'dokter') {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];

$query = "SELECT * FROM dokter WHERE username = '$username'";
$result = mysqli_query($koneksi, $query);
$dokter = mysqli_fetch_assoc($result);

if ($dokter) {
    $nama = $dokter['nama'];
    $spesialis = $dokter['spesialis'];
    $telepon = $dokter['telepon'];
    $kategori_poli = $dokter['kategori_poli'];
} else {
    echo "Data dokter tidak ditemukan.";
    exit;
}

// Query untuk pasien yang belum diperiksa
$query_pasien = "SELECT p.* FROM pasien p 
                 INNER JOIN dokter d ON p.dokter_id = d.id 
                 LEFT JOIN jadwal_periksa j ON p.id = j.id_pasien
                 WHERE d.username = '$username' AND j.id IS NULL";
$result_pasien = mysqli_query($koneksi, $query_pasien);

// Query untuk jadwal periksa (pasien yang sudah diperiksa)
$query_jadwal = "SELECT j.*, p.nama as nama_pasien 
                 FROM jadwal_periksa j 
                 INNER JOIN pasien p ON j.id_pasien = p.id
                 INNER JOIN dokter d ON p.dokter_id = d.id
                 WHERE d.username = '$username'";
$result_jadwal = mysqli_query($koneksi, $query_jadwal);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Dokter</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
        }
        .card {
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            border-radius: 10px;
        }
        .card-header {
            background-color: #007bff;
            color: white;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }
        .navbar {
            background-color: #007bff !important;
        }
        .navbar-brand, .nav-link {
            color: white !important;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(0,123,255,0.1);
        }
        .btn-custom {
            background-color: #007bff;
            color: white;
            transition: all 0.3s ease;
        }
        .btn-custom:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

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
                    <a class="nav-link" href="update_dokter.php">
                        <i class="fas fa-edit"></i> Perbarui Data
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="jadwal_periksa.php">
                        <i class="fas fa-calendar-alt"></i> Jadwal Periksa
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

<div class="container mt-5">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="text-center mb-0">
                        <i class="fas fa-user-md"></i> Selamat datang, Dr. <?= $nama; ?>!
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>Data Dokter</h4>
                            <table class="table table-bordered">
                                <tr>
                                    <th>Nama</th>
                                    <td><?= $nama; ?></td>
                                </tr>
                                <tr>
                                    <th>Spesialis</th>
                                    <td><?= $spesialis; ?></td>
                                </tr>
                                <tr>
                                    <th>Telepon</th>
                                    <td><?= $telepon; ?></td>
                                </tr>
                                <tr>
                                    <th>Kategori Poli</th>
                                    <td><?= $kategori_poli; ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h5>Total Pasien Hari Ini</h5>
                                    <h2><?= mysqli_num_rows($result_pasien) ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-users"></i> Daftar Pasien Menunggu Pemeriksaan
                    </h4>
                </div>
                <div class="card-body">
                    <?php if (mysqli_num_rows($result_pasien) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead class="table-primary">
                                    <tr>
                                        <th>Nama Pasien</th>
                                        <th>No. KTP</th>
                                        <th>Alamat</th>
                                        <th>Poli</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($pasien = mysqli_fetch_assoc($result_pasien)): ?>
                                        <tr>
                                            <td><?= $pasien['nama']; ?></td>
                                            <td><?= $pasien['no_ktp']; ?></td>
                                            <td><?= $pasien['alamat']; ?></td>
                                            <td><?= $pasien['poli']; ?></td>
                                            <td>
                                                <a href="buat_jadwal.php?id_pasien=<?= $pasien['id']; ?>" 
                                                   class="btn btn-sm btn-custom">
                                                    Periksa
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info text-center">
                            Tidak ada pasien yang menunggu pemeriksaan.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-history"></i> Riwayat Pemeriksaan
                    </h4>
                </div>
                <div class="card-body">
                    <?php if (mysqli_num_rows($result_jadwal) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead class="table-primary">
                                    <tr>
                                        <th>Nama Pasien</th>
                                        <th>No RM</th>
                                        <th>Sakit yang Diderita</th>
                                        <th>Catatan Obat</th>
                                        <th>Harga Periksa</th> 
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($jadwal = mysqli_fetch_assoc($result_jadwal)): ?>
                                        <tr>
                                            <td><?= $jadwal['nama_pasien']; ?></td>
                                            <td><?= $jadwal['no_rm']; ?></td>
                                            <td><?= $jadwal['sakit']; ?></td>
                                            <td><?= $jadwal['catatan_obat']; ?></td>
                                            <td><?= "Rp. " . number_format($jadwal['harga_periksa'], 0, ',', '.'); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info text-center">
                            Belum ada riwayat pemeriksaan.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>