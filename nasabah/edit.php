<?php
include '../connect.php';

if (isset($_GET['id'])) {
    $id_nasabah = $_GET['id'];
    $result = mysqli_query($koneksi, "SELECT * FROM nasabah WHERE id_nasabah = '$id_nasabah'");
    $row = mysqli_fetch_assoc($result);
    
    if (!$row) {
        header("Location: index.php");
        exit();
    }
}

if (isset($_POST['update'])) {
    $id_nasabah = $_POST['id_nasabah'];
    $nama_nasabah = mysqli_real_escape_string($koneksi, $_POST['nama_nasabah']);
    $nomor_rekening = mysqli_real_escape_string($koneksi, $_POST['nomor_rekening_sampah']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $saldo = mysqli_real_escape_string($koneksi, $_POST['saldo_rupiah']);

    $query_update = "UPDATE nasabah SET 
                     nama_nasabah = '$nama_nasabah', 
                     nomor_rekening_sampah = '$nomor_rekening', 
                     alamat = '$alamat', 
                     saldo_rupiah = '$saldo' 
                     WHERE id_nasabah = '$id_nasabah'";

    if (mysqli_query($koneksi, $query_update)) {
        header("Location: index.php");
        exit();
    } else {
        echo "<script>alert('Gagal memperbarui data!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Nasabah - Bank Sampah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f4f7f6; }
        .card { border: none; border-radius: 12px; }
        .btn-success { background-color: #198754; border: none; border-radius: 8px; padding: 10px 18px; }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm p-4">
                    <h3 class="fw-bold mb-4 text-success">Edit Data Nasabah</h3>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label fw-medium">ID Nasabah (Tidak dapat diubah)</label>
                            <input type="text" class="form-control bg-light" name="id_nasabah" value="<?php echo $row['id_nasabah']; ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium">Nama Nasabah</label>
                            <input type="text" class="form-control" name="nama_nasabah" value="<?php echo $row['nama_nasabah']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium">Nomor Rekening Sampah</label>
                            <input type="text" class="form-control" name="nomor_rekening_sampah" value="<?php echo $row['nomor_rekening_sampah']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium">Alamat</label>
                            <textarea class="form-control" name="alamat" rows="2" required><?php echo $row['alamat']; ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium">Saldo (Rp)</label>
                            <input type="number" class="form-control" name="saldo_rupiah" value="<?php echo $row['saldo_rupiah']; ?>" required>
                        </div>
                        <div class="d-flex justify-content-between mt-4">
                            <a href="index.php" class="btn btn-secondary px-4">Kembali</a>
                            <button type="submit" name="update" class="btn btn-success px-4">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>