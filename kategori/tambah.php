<?php
include '../connect.php';

if (isset($_POST['simpan'])) {
    $id_kategori       = mysqli_real_escape_string($conn ?? $koneksi, $_POST['id_kategori']);
    $nama_sampah       = mysqli_real_escape_string($conn ?? $koneksi, $_POST['nama_sampah']);
    $harga_beli_per_kg = (int) $_POST['harga_beli_per_kg'];

    $sql = "INSERT INTO kategori_sampah (id_kategori_sampah, nama_sampah, harga_beli_per_kg) 
            VALUES ('$id_kategori', '$nama_sampah', $harga_beli_per_kg)";

    if (mysqli_query($conn ?? $koneksi, $sql)) {
        header("Location: index.php");
        exit();
    } else {
        $error = "Gagal menyimpan data: " . mysqli_error($conn ?? $koneksi);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Kategori Sampah</title>
    <style>
        body { margin: 0; background-color: var(--pg-bg, #121317); color: var(--pg-text, #f1f2f5); font-family: "Inter", system-ui, sans-serif; }
        .card { background: var(--sb-bg, #1b1d22); padding: 24px; border-radius: 20px; border: 1px solid var(--sb-line, #2d3038); max-width: 500px; }
        .card h2 { margin-top: 0; margin-bottom: 20px; font-size: 20px; font-weight: 600; }
        .form-group { margin-bottom: 16px; }
        label { display: block; margin-bottom: 6px; font-size: 13.5px; color: var(--sb-muted, #7d8391); }
        input[type="text"], input[type="number"] { width: 100%; padding: 10px 14px; border-radius: 10px; border: 1px solid var(--sb-line, #2d3038); background: var(--pg-bg, #121317); color: var(--pg-text, #f1f2f5); box-sizing: border-box; font-size: 14px; outline: none; }
        input:focus { border-color: var(--sb-active-fg, #4453d6); }
        .btn-group { display: flex; gap: 10px; margin-top: 20px; }
        .btn { padding: 10px 18px; background: var(--sb-active-fg, #4453d6); color: white; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; text-decoration: none; font-size: 14px; }
        .btn-batal { background: transparent; color: var(--sb-muted, #7d8391); border: 1px solid var(--sb-line, #2d3038); }
    </style>
</head>
<body>

<div class="app">
    <?php include '../includess/sidebar.php'; ?>

    <div class="content">
        <div class="card">
            <h2>Tambah Kategori Sampah</h2>
            <?php if (isset($error)) echo "<p style='color:#ef4444;'>$error</p>"; ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label>ID Kategori</label>
                    <input type="text" name="id_kategori" placeholder="Contoh: KAT010" required>
                </div>
                <div class="form-group">
                    <label>Nama Sampah</label>
                    <input type="text" name="nama_sampah" placeholder="Contoh: Botol Kaca Bening" required>
                </div>
                <div class="form-group">
                    <label>Harga Beli per kg (Rp)</label>
                    <input type="number" name="harga_beli_per_kg" placeholder="Contoh: 500" required>
                </div>
                <div class="btn-group">
                    <button type="submit" name="simpan" class="btn">Simpan</button>
                    <a href="index.php" class="btn btn-batal">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>