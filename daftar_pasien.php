<?php
include 'koneksi.php';
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: LoginUser.php');
    exit;
}

$nama = $_SESSION['username'];

$query = "SELECT alamat, no_ktp FROM pasien WHERE nama = ?";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "s", $nama);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $alamat, $no_ktp);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

$query_dokter = "SELECT id, nama, hari_praktik, jam_mulai, jam_selesai FROM dokter";
$result_dokter = mysqli_query($koneksi, $query_dokter);

$pesan = "";
$pesan_tipe = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $poli = isset($_POST['poli']) ? htmlspecialchars(trim($_POST['poli'])) : '';
    $dokter_id = isset($_POST['dokter']) ? htmlspecialchars(trim($_POST['dokter'])) : '';

    if (empty($poli) || empty($dokter_id)) {
        $pesan = "Poli dan dokter harus dipilih!";
        $pesan_tipe = "danger";
    } else {
        // Update data pasien
        $query_update = "UPDATE pasien SET poli = ?, dokter_id = ? WHERE nama = ?";
        $stmt = mysqli_prepare($koneksi, $query_update);
        mysqli_stmt_bind_param($stmt, "sis", $poli, $dokter_id, $nama);

        if (mysqli_stmt_execute($stmt)) {
            // Simpan data ke riwayat pendaftaran
            // Ambil nomor antrian
            $query_antrian = "SELECT MAX(nomor_antrian) AS nomor_terakhir FROM riwayat_pendaftaran WHERE poli = ? AND tanggal_pendaftaran = CURDATE()";
            $stmt_antrian = mysqli_prepare($koneksi, $query_antrian);
            mysqli_stmt_bind_param($stmt_antrian, "s", $poli);
            mysqli_stmt_execute($stmt_antrian);
            mysqli_stmt_bind_result($stmt_antrian, $nomor_terakhir);
            mysqli_stmt_fetch($stmt_antrian);
            mysqli_stmt_close($stmt_antrian);

            $nomor_antrian = $nomor_terakhir + 1; // Increment nomor antrian

            // Catatan dan harga obat
            $catatan_dokter = 'Catatan tidak ada'; // Default catatan
            $obat = 'Tidak ada obat'; // Default obat
            $harga_obat = 0; // Default harga obat

            $query_riwayat_insert = "INSERT INTO riwayat_pendaftaran (nama_pasien, poli, dokter_id, nomor_antrian, tanggal_pendaftaran, status_pemeriksaan, catatan_dokter, obat, harga_obat) 
                                     VALUES (?, ?, ?, ?, NOW(), 'Menunggu', ?, ?, ?)";
            $stmt_riwayat = mysqli_prepare($koneksi, $query_riwayat_insert);
            mysqli_stmt_bind_param($stmt_riwayat, "ssisssd", $nama, $poli, $dokter_id, $nomor_antrian, $catatan_dokter, $obat, $harga_obat);
            mysqli_stmt_execute($stmt_riwayat);
            mysqli_stmt_close($stmt_riwayat);

            $pesan = "Data berhasil diperbarui dan riwayat pendaftaran tercatat.";
            $pesan_tipe = "success";
        } else {
            $pesan = "Pembaruan gagal. Silakan coba lagi!";
            $pesan_tipe = "danger";
        }
        mysqli_stmt_close($stmt);
    }
}

// Fetch registration history for the selected patient
$query_history = "SELECT rp.nomor_antrian, d.nama AS dokter, rp.poli, rp.status_pemeriksaan, rp.catatan_dokter, rp.obat, rp.harga_obat
                  FROM riwayat_pendaftaran rp 
                  JOIN dokter d ON rp.dokter_id = d.id 
                  WHERE rp.nama_pasien = ? 
                  ORDER BY rp.tanggal_pendaftaran DESC";


$stmt_history = mysqli_prepare($koneksi, $query_history);
mysqli_stmt_bind_param($stmt_history, "s", $nama);
mysqli_stmt_execute($stmt_history);
$result_history = mysqli_stmt_get_result($stmt_history);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Pasien</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .card-header {
            background: linear-gradient(to right, #007bff, #0056b3);
            color: white;
            text-align: center;
            padding: 1.5rem;
        }
        .form-control {
            border-radius: 10px;
            padding: 0.75rem;
        }
        .btn {
            border-radius: 20px;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background: linear-gradient(to right, #007bff, #0056b3);
            border: none;
        }
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
        }
        .table-hover tbody tr:hover {
            background-color: rgba(0,123,255,0.1);
        }
        .alert {
            border-radius: 10px;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h2 class="mb-0">
                        <i class="fas fa-file-medical"></i> Pendaftaran Pasien
                    </h2>
                </div>
                <div class="card-body">
                    <?php if (!empty($pesan)): ?>
                        <div class="alert alert-<?= $pesan_tipe ?> alert-dismissible fade show" role="alert">
                            <?= $pesan ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="fas fa-user"></i> Nama
                                </label>
                                <input type="text" name="nama" class="form-control" value="<?= $nama; ?>" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="fas fa-id-card"></i> Nomor KTP
                                </label>
                                <input type="text" name="no_ktp" class="form-control" value="<?= $no_ktp; ?>" readonly>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-map-marker-alt"></i> Alamat
                            </label>
                            <textarea name="alamat" class="form-control" readonly><?= $alamat; ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="fas fa-hospital"></i> Poli
                                </label>
                                <select name="poli" class="form-control" required>
                                    <option value="">Pilih Poli</option>
                                    <option value="Poli Umum">Poli Umum</option>
                                    <option value="Poli Gigi">Poli Gigi</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="fas fa-user-md"></i> Dokter
                                </label>
                                <select name="dokter" class="form-control" required>
                                    <option value="">Pilih Dokter</option>
                                    <?php 
                                    // Reset pointer result
                                    mysqli_data_seek($result_dokter, 0);
                                    while($row = mysqli_fetch_assoc($result_dokter)): 
                                    ?>
                                        <option value="<?= $row['id'] ?>">
                                            <?= $row['nama'] ?> - <?= $row['hari_praktik'] ?>, <?= $row['jam_mulai'] ?> - <?= $row['jam_selesai'] ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mt-3">
                            <i class="fas fa-save"></i> Perbarui Data
                        </button>
                    </form>

                    <div class="mt-4">
                        <h3 class="mb-3">
                            <i class="fas fa-history"></i> Riwayat Pendaftaran
                        </h3>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-primary">
                                    <tr>
                                        <th>No</th>
                                        <th>Poli</th>
                                        <th>Dokter</th>
                                        <th>Nomor Antrian</th>
                                        <th>Status</th>
                                        <th>Catatan Obat</th>
                                        <th>Harga Obat</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (mysqli_num_rows($result_history) > 0): ?>
                                        <?php $no = 1; while ($row = mysqli_fetch_assoc($result_history)): ?>
                                            <tr>
                                                <td><?= $no++; ?></td>
                                                <td><?= $row['poli']; ?></td>
                                                <td><?= $row['dokter']; ?></td>
                                                <td><?= $row['nomor_antrian']; ?></td>
                                                <td><?= $row['status_pemeriksaan']; ?></td>
                                                <td><?= $row['catatan_dokter'] ?? 'Tidak ada catatan'; ?></td>
                                                <td>Rp. <?= number_format($row['harga_obat'] ?? 0, 0, ',', '.'); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center">
                                                <i class="fas fa-folder-open"></i> Belum ada riwayat pendaftaran
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="text-center mt-4">
                        <a href="LoginUser.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali ke Halaman Login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
