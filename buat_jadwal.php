<?php
include 'koneksi.php';
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'dokter') {
    header("Location: login.php");
    exit;
}

// Cek apakah ID pasien ada dalam URL (opsi pertama: dari daftar pasien)
if (isset($_GET['id_pasien'])) {
    $id_pasien = $_GET['id_pasien'];
    $query = "SELECT * FROM pasien WHERE id = ?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "i", $id_pasien);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($pasien = mysqli_fetch_assoc($result)) {
        $nama_pasien = $pasien['nama'];
        $no_ktp = $pasien['no_ktp'];
        $alamat = $pasien['alamat'];
        $no_rm = $pasien['no_rm'];
    } else {
        echo "Data pasien tidak ditemukan.";
        exit;
    }
} else {
    // Jika tidak ada ID pasien, tampilkan form kosong untuk opsi manual (opsi kedua)
    $id_pasien = null;
    $nama_pasien = "";
    $no_ktp = "";
    $alamat = "";
    $no_rm = "";
}

// Jika nomor RM kosong, generate RM baru
if (empty($no_rm)) {
    $query_rm = "SELECT no_rm FROM pasien ORDER BY no_rm DESC LIMIT 1";
    $result_rm = mysqli_query($koneksi, $query_rm);
    $last_rm = mysqli_fetch_assoc($result_rm);

    if ($last_rm) {
        $last_number = (int) substr($last_rm['no_rm'], 3);
        $new_number = $last_number + 1;
        $no_rm = 'RM-' . str_pad($new_number, 6, '0', STR_PAD_LEFT);
    } else {
        $no_rm = 'RM-000001';
    }
}

// Update catatan dan harga obat pada riwayat pendaftaran
$query_update_riwayat = "UPDATE riwayat_pendaftaran 
                         SET catatan_dokter = ?, obat = ?, harga_obat = ?
                         WHERE nama_pasien = ? AND poli = ? AND dokter_id = ?";
$stmt_update = mysqli_prepare($koneksi, $query_update_riwayat);
mysqli_stmt_bind_param($stmt_update, "ssdsis", $catatan_dokter, $obat, $harga_obat, $nama_pasien, $poli, $dokter_id);
mysqli_stmt_execute($stmt_update);
mysqli_stmt_close($stmt_update);


// Query daftar obat
$query_obat = "SELECT * FROM obat";
$result_obat = mysqli_query($koneksi, $query_obat);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_pasien = $_POST['nama_pasien'];
    $no_ktp = $_POST['no_ktp'];
    $alamat = $_POST['alamat'];
    $sakit = $_POST['sakit'];
    $obat_array = isset($_POST['catatan_obat']) ? $_POST['catatan_obat'] : [];
    $catatan_obat = implode(', ', $obat_array);
    $harga_obat = $_POST['harga_obat'];
    $harga_periksa = $harga_obat + 50000;

    // Insert data pemeriksaan
    $query_insert = "INSERT INTO jadwal_periksa (no_rm, catatan_obat, harga_periksa, id_pasien, sakit) 
                     VALUES (?, ?, ?, ?, ?)";
    $stmt_insert = mysqli_prepare($koneksi, $query_insert);
    mysqli_stmt_bind_param($stmt_insert, "ssdis", $no_rm, $catatan_obat, $harga_periksa, $id_pasien, $sakit);

    if (mysqli_stmt_execute($stmt_insert)) {
        header("Location: dokter_dashboard.php");
        exit;
    } else {
        echo "Gagal membuat jadwal periksa.";
    }
    mysqli_stmt_close($stmt_insert);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Jadwal Periksa</title>
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
        .btn-primary {
            background: linear-gradient(to right, #007bff, #0056b3);
            border: none;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
        }
        .select2-container {
            width: 100% !important;
        }
        .form-label {
            font-weight: 600;
        }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h2 class="mb-0">
                        <i class="fas fa-file-medical"></i> Buat Jadwal Periksa
                    </h2>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="fas fa-user"></i> Nama Pasien
                                </label>
                                <input type="text" name="nama_pasien" class="form-control" 
                                       value="<?= $nama_pasien; ?>" 
                                       <?= $id_pasien ? 'readonly' : ''; ?> required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="fas fa-id-card"></i> No. KTP
                                </label>
                                <input type="text" name="no_ktp" class="form-control" 
                                       value="<?= $no_ktp; ?>" 
                                       <?= $id_pasien ? 'readonly' : ''; ?> required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="fas fa-map-marker-alt"></i> Alamat
                                </label>
                                <textarea name="alamat" class="form-control" 
                                          <?= $id_pasien ? 'readonly' : ''; ?> required><?= $alamat; ?></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="fas fa-notes-medical"></i> No RM
                                </label>
                                <input type="text" name="no_rm" class="form-control" 
                                       value="<?= $no_rm; ?>" readonly>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-diagnoses"></i> Sakit yang Diderita
                            </label>
                            <textarea name="sakit" class="form-control" required 
                                      placeholder="Deskripsikan gejala atau penyakit"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-pills"></i> Obat
                            </label>
                            <select name="catatan_obat[]" class="form-control select2" required multiple onchange="hitungHarga()">
                                <?php 
                                mysqli_data_seek($result_obat, 0);
                                while($obat = mysqli_fetch_assoc($result_obat)): 
                                ?>
                                    <option value="<?= $obat['nama_obat']; ?>" 
                                            data-harga="<?= $obat['harga']; ?>">
                                        <?= $obat['nama_obat']; ?> - Rp. <?= number_format($obat['harga'], 0, ',', '.'); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="fas fa-money-bill-wave"></i> Harga Obat
                                </label>
                                <input type="text" id="harga_obat_display" class="form-control" readonly>
                                <input type="hidden" name="harga_obat" id="harga_obat">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="fas fa-receipt"></i> Total Harga Periksa
                                </label>
                                <input type="text" name="harga_periksa" id="harga_periksa" 
                                       class="form-control" readonly>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mt-3">
                            <i class="fas fa-save"></i> Buat Jadwal
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    $('.select2').select2({
        placeholder: "Pilih obat",
        allowClear: true
    });
});

function hitungHarga() {
    const select = document.querySelector('select[name="catatan_obat[]"]');
    const selectedOptions = Array.from(select.selectedOptions);
    let totalHargaObat = 0;
     selectedOptions.forEach(option => {
        totalHargaObat += parseInt(option.dataset.harga);
    });
    
    const hargaPeriksa = totalHargaObat + 150000;
    
    document.getElementById('harga_obat').value = totalHargaObat;
    document.getElementById('harga_obat_display').value = 'Rp. ' + totalHargaObat.toLocaleString('id-ID');
    document.getElementById('harga_periksa').value = 'Rp. ' + hargaPeriksa.toLocaleString('id-ID');
}
</script>
</body>
</html>