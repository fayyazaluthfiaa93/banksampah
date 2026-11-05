<?php
include __DIR__ . '/../connect.php';

// Proses Tambah Data
if (isset($_POST['tambah'])) {
    $id_nasabah = mysqli_real_escape_string($koneksi, $_POST['id_nasabah']);
    $nama_nasabah = mysqli_real_escape_string($koneksi, $_POST['nama_nasabah']);
    $nomor_rekening = mysqli_real_escape_string($koneksi, $_POST['nomor_rekening_sampah']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $saldo = mysqli_real_escape_string($koneksi, $_POST['saldo_rupiah']);

    $query_insert = "INSERT INTO nasabah (id_nasabah, nama_nasabah, nomor_rekening_sampah, alamat, saldo_rupiah) 
                     VALUES ('$id_nasabah', '$nama_nasabah', '$nomor_rekening', '$alamat', '$saldo')";
    
    if (mysqli_query($koneksi, $query_insert)) {
        header("Location: index.php");
        exit();
    } else {
        echo "<script>alert('Gagal menambah data!');</script>";
    }
}

$result = mysqli_query($koneksi, "SELECT * FROM nasabah");
$jumlah_data = ($result) ? mysqli_num_rows($result) : 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modul Nasabah - Bank Sampah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f4f7f6; }
        .card { border: none; border-radius: 12px; }
        .table thead th { background-color: #198754; color: white; border: none; padding: 12px; }
        .table tbody td { padding: 12px; vertical-align: middle; }
        .btn-success { background-color: #198754; border: none; border-radius: 8px; padding: 10px 18px; font-weight: 500; }
        .btn-success:hover { background-color: #157347; }
        .badge-id { background-color: #e9ecef; color: #495057; font-weight: 600; padding: 6px 10px; border-radius: 6px; }
    </style>
</head>
<body>
    <div class="app">
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/banksampah/includess/sidebar.php"; ?>

    <div class="container py-5">
        <div class="row mb-4">
            <div class="col-md-12 d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="fw-bold text-dark mb-1">Manajemen Nasabah</h2>
                    <p class="text-muted mb-0">Kelola data nasabah dan saldo simpanan Bank Sampah</p>
                </div>
                <button type="button" class="btn btn-success shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
                    + Tambah Nasabah Baru
                </button>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>ID Nasabah</th>
                                <th>Nama Nasabah</th>
                                <th>No. Rekening Sampah</th>
                                <th>Alamat</th>
                                <th>Saldo (Rp)</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if ($jumlah_data > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $saldo = number_format($row['saldo_rupiah'], 0, ',', '.');
                                    echo "<tr>
                                            <td><span class='badge-id'>{$row['id_nasabah']}</span></td>
                                            <td class='fw-semibold text-dark'>{$row['nama_nasabah']}</td>
                                            <td><code class='text-success'>{$row['nomor_rekening_sampah']}</code></td>
                                            <td class='text-muted'>{$row['alamat']}</td>
                                            <td class='fw-bold text-success'>Rp {$saldo}</td>
                                            <td class='text-center'>
                                                <a href='edit.php?id={$row['id_nasabah']}' class='btn btn-sm btn-outline-success px-3 me-1'>Edit</a>
                                                <a href='hapus.php?id={$row['id_nasabah']}' class='btn btn-sm btn-outline-danger px-3' onclick='return confirm(\"Yakin ingin menghapus data ini?\")'>Hapus</a>
                                            </td>
                                          </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6' class='text-center text-muted py-4'>Belum ada data nasabah di database.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah -->
    <div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
          <form method="POST" action="">
              <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-semibold">Tambah Data Nasabah Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body p-4">
                  <div class="mb-3">
                      <label class="form-label fw-medium">ID Nasabah</label>
                      <input type="text" class="form-control" name="id_nasabah" placeholder="Contoh: NSB03" required>
                  </div>
                  <div class="mb-3">
                      <label class="form-label fw-medium">Nama Nasabah</label>
                      <input type="text" class="form-control" name="nama_nasabah" placeholder="Masukkan nama lengkap" required>
                  </div>
                  <div class="mb-3">
                      <label class="form-label fw-medium">Nomor Rekening Sampah</label>
                      <input type="text" class="form-control" name="nomor_rekening_sampah" placeholder="Contoh: REK-003" required>
                  </div>
                  <div class="mb-3">
                      <label class="form-label fw-medium">Alamat</label>
                      <textarea class="form-control" name="alamat" rows="2" placeholder="Masukkan alamat lengkap" required></textarea>
                  </div>
                  <div class="mb-3">
                      <label class="form-label fw-medium">Saldo Awal (Rp)</label>
                      <input type="number" class="form-control" name="saldo_rupiah" value="0" required>
                  </div>
              </div>
              <div class="modal-footer bg-light px-4 py-3">
                <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">Batal</button>
                <button type="submit" name="tambah" class="btn btn-success px-4">Simpan Data</button>
              </div>
          </form>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>