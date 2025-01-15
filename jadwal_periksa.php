<?php
include 'koneksi.php';
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'dokter') {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];

// Ambil ID dokter
$query_dokter = "SELECT id FROM dokter WHERE username = '$username'";
$result_dokter = mysqli_query($koneksi, $query_dokter);
$dokter = mysqli_fetch_assoc($result_dokter);
$dokter_id = $dokter['id'];

$pesan = "";

// Proses form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Tambah/Edit Jadwal
    if (isset($_POST['aksi']) && $_POST['aksi'] == 'tambah_jadwal') {
        $hari = $_POST['hari'];
        $jam_mulai = $_POST['jam_mulai'];
        $jam_selesai = $_POST['jam_selesai'];

        // Cek apakah sudah ada jadwal aktif untuk hari yang sama
        $query_cek = "SELECT * FROM jadwal_dokter WHERE dokter_id = '$dokter_id' AND hari = '$hari' AND status = 1";
        $result_cek = mysqli_query($koneksi, $query_cek);

        if (mysqli_num_rows($result_cek) > 0) {
            // Jika jadwal untuk hari tersebut sudah ada aktif
            $pesan = "Jadwal sudah ada untuk hari $hari. Silakan tambahkan jadwal dihari lain.";
        } else {
            // Nonaktifkan semua jadwal untuk hari yang sama
            $query_nonaktifkan = "UPDATE jadwal_dokter 
                                   SET status = 0 
                                   WHERE dokter_id = '$dokter_id' AND hari = '$hari'";
            mysqli_query($koneksi, $query_nonaktifkan);

            // Tambah jadwal baru (status aktif)
            $query_insert = "INSERT INTO jadwal_dokter 
                             (dokter_id, hari, jam_mulai, jam_selesai, status) 
                             VALUES 
                             ('$dokter_id', '$hari', '$jam_mulai', '$jam_selesai', 1)";
            mysqli_query($koneksi, $query_insert);
            $pesan = "Jadwal berhasil ditambahkan";
        }
    }

    // Ubah Status Jadwal
    if (isset($_POST['aksi']) && $_POST['aksi'] == 'ubah_status') {
        $jadwal_id = $_POST['jadwal_id'];
        
        // Ambil status saat ini
        $query_status_sekarang = "SELECT status, hari FROM jadwal_dokter WHERE id = '$jadwal_id'";
        $result_status = mysqli_query($koneksi, $query_status_sekarang);
        $data_status = mysqli_fetch_assoc($result_status);
        
        // Jika status saat ini aktif, maka nonaktifkan
        if ($data_status['status'] == 1) {
            $query_update_status = "UPDATE jadwal_dokter 
                                    SET status = 0 
                                    WHERE id = '$jadwal_id'";
            mysqli_query($koneksi, $query_update_status);
            $pesan = "Jadwal berhasil dinonaktifkan";
        } else {
            // Nonaktifkan semua jadwal untuk hari yang sama
            $query_nonaktifkan = "UPDATE jadwal_dokter 
                                   SET status = 0 
                                   WHERE dokter_id = '$dokter_id' AND hari = '".$data_status['hari']."'";

            mysqli_query($koneksi, $query_nonaktifkan);
            
            // Aktifkan jadwal yang dipilih
            $query_update_status = "UPDATE jadwal_dokter 
                                    SET status = 1 
                                    WHERE id = '$jadwal_id'";
            mysqli_query($koneksi, $query_update_status);
            $pesan = "Jadwal berhasil diaktifkan";
        }
    }

    // Hapus Jadwal
    if (isset($_POST['aksi']) && $_POST['aksi'] == 'hapus_jadwal') {
        $jadwal_id = $_POST['jadwal_id'];

        // Hapus jadwal
        $query_hapus = "DELETE FROM jadwal_dokter WHERE id = '$jadwal_id' AND dokter_id = '$dokter_id'";
        mysqli_query($koneksi, $query_hapus);
        $pesan = "Jadwal berhasil dihapus";
    }
}

// Ambil jadwal dokter
$query_jadwal = "SELECT * FROM jadwal_dokter WHERE dokter_id = '$dokter_id'";
$result_jadwal = mysqli_query($koneksi, $query_jadwal);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Periksa Dokter</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
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
        .btn-primary, .btn-success, .btn-danger {
            transition: all 0.3s ease;
        }
        .btn-primary:hover, .btn-success:hover, .btn-danger:hover {
            transform: translateY(-2px);
        }
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0,123,255,0.05);
        }
        .table-hover tbody tr:hover {
            background-color: rgba(0,123,255,0.1);
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <?php if(!empty($pesan)): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <?= $pesan ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row mb-3">
        <div class="col-12">
            <a href="dokter_dashboard.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-5">
            <div class="card">
                <div class="card-header text-center">
                    <h4 class="mb-0">
                        <i class="fas fa-calendar-plus"></i> Tambah Jadwal Periksa
                    </h4>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="aksi" value="tambah_jadwal">
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-calendar"></i> Hari
                            </label>
                            <select name="hari" class="form-control" required>
                                <option value="">Pilih Hari</option>
                                <option value="Senin">Senin</option>
                                <option value="Selasa">Selasa</option>
                                <option value="Rabu">Rabu</option>
                                <option value="Kamis">Kamis</option>
                                <option value="Jumat">Jumat</option>
                                <option value="Sabtu">Sabtu</option>
                                <option value="Minggu">Minggu</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-clock"></i> Jam Mulai
                            </label>
                            <input type="time" name="jam_mulai" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-clock"></i> Jam Selesai
                            </label>
                            <input type="time" name="jam_selesai" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save"></i> Simpan Jadwal
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card">
                <div class="card-header text-center">
                    <h4 class="mb-0">
                        <i class="fas fa-calendar-alt"></i> Daftar Jadwal Periksa
                    </h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead class="table-primary">
                                <tr>
                                    <th>No</th>
                                    <th>Hari</th>
                                    <th>Jam Mulai</th>
                                    <th>Jam Selesai</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1; 
                                // Reset pointer result
                                mysqli_data_seek($result_jadwal, 0);
                                while ($jadwal = mysqli_fetch_assoc($result_jadwal)): 
                                ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= $jadwal['hari'] ?></td>
                                    <td><?= $jadwal['jam_mulai'] ?></td>
                                    <td><?= $jadwal['jam_selesai'] ?></td>
                                    <td>
                                        <span class="badge <?= $jadwal['status'] == 1 ? 'bg-success' : 'bg-danger' ?>">
                                            <?= $jadwal['status'] == 1 ? 'Aktif' : 'Tidak Aktif' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="aksi" value="ubah_status">
                                            <input type="hidden" name="jadwal_id" value="<?= $jadwal['id'] ?>">
                                            <button type="submit" class="btn btn-sm <?= $jadwal['status'] == 1 ? 'btn-outline-danger' : 'btn-outline-success' ?>">
                                                <i class="fas <?= $jadwal['status'] == 1 ? 'fa-times' : 'fa-check' ?>"></i>
                                                <?= $jadwal['status'] == 1 ? 'Nonaktifkan' : 'Aktifkan' ?>
                                            </button>
                                        </form>
                                        <!-- <form method="POST" style="display:inline;">
                                            <input type="hidden" name="aksi" value="hapus_jadwal">
                                            <input type="hidden" name="jadwal_id" value="<?= $jadwal['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                        </form> -->
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"></script>
</body>
</html>