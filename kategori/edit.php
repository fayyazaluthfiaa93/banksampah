<?php
include '../connect.php';
$db = $conn ?? $koneksi;

$id = $_GET['id'] ?? '';
$query = "SELECT * FROM kategori_sampah WHERE id_kategori_sampah = '$id'";
$result = mysqli_query($db, $query);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    header("Location: index.php");
    exit();
}

if (isset($_POST['update'])) {
    $nama_sampah       = mysqli_real_escape_string($db, $_POST['nama_sampah']);
    $harga_beli_per_kg = (int) $_POST['harga_beli_per_kg'];

    $sql = "UPDATE kategori_sampah 
            SET nama_sampah = '$nama_sampah', harga_beli_per_kg = $harga_beli_per_kg 
            WHERE id_kategori_sampah = '$id'";

    if (mysqli_query($db, $sql)) {
        header("Location: index.php");
        exit();
    } else {
        $error = "Gagal mengupdate data: " . mysqli_error($db);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Kategori Sampah</title>
    <style>
        body { margin: 0; background-color: var(--pg-bg, #121317); color: var(--pg-text, #f1f2f5); font-family: "Inter", system-ui, sans-serif; }
        .card { background: var(--sb-bg, #1b1d22); padding: 24px; border-radius: 20px; border: 1px solid var(--sb-line, #2d3038); max-width: 500px; }
        .card h2 { margin-top: 0; margin-bottom: 20px; font-size: 20px; font-weight: 600; }
        .form-group { margin-bottom: 16px; }
        label { display: block; margin-bottom: 6px; font-size: 13.5px; color: var(--sb-muted, #7d8391); }
        input[type="text"], input[type="number"] { width: 100%; padding: 10px 14px; border-radius: 10px; border: 1px solid var(--sb-line, #2d3038); background: var(--pg-bg, #121317); color: var(--pg-text, #f1f2f5); box-sizing: border-box; font-size: 14px; outline: none; }
        input:disabled { opacity: 0.6; cursor: not-allowed; }
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
            <h2>Edit Kategori Sampah</h2>
            <?php if (isset($error)) echo "<p style='color:#ef4444;'>$error</p>"; ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label>ID Kategori</label>
                    <input type="text" value="<?= $data['id_kategori_sampah']; ?>" disabled>
                </div>
                <div class="form-group">
                    <label>Nama Sampah</label>
                    <input type="text" name="nama_sampah" value="<?= $data['nama_sampah']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Harga Beli per kg (Rp)</label>
                    <input type="number" name="harga_beli_per_kg" value="<?= $data['harga_beli_per_kg']; ?>" required>
                </div>
                <div class="btn-group">
                    <button type="submit" name="update" class="btn">Update Data</button>
                    <a href="index.php" class="btn btn-batal">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>