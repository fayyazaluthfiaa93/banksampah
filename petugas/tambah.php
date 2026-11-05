<?php
include '../connect.php';
/** @var mysqli $koneksi */

$error = '';

if (isset($_POST['simpan'])) {
    $id   = trim($_POST['petugas_id']);
    $nama = trim($_POST['nama_petugas']);

    if ($id === '' || $nama === '') {
        $error = 'ID petugas dan nama petugas wajib diisi.';
    } elseif (strlen($id) > 10) {
        $error = 'ID petugas maksimal 10 karakter.';
    } else {
        // Cek ID sudah dipakai atau belum
        $cek = $koneksi->prepare("SELECT petugas_id FROM petugas WHERE petugas_id = ?");
        $cek->bind_param("s", $id);
        $cek->execute();
        $cek->store_result();

        if ($cek->num_rows > 0) {
            $error = 'ID petugas sudah digunakan.';
        } else {
            $stmt = $koneksi->prepare("INSERT INTO petugas (petugas_id, nama_petugas) VALUES (?, ?)");
            $stmt->bind_param("ss", $id, $nama);

            if ($stmt->execute()) {
                header("Location: index.php?status=tambah");
                exit;
            } else {
                $error = 'Gagal menyimpan data: ' . $stmt->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Petugas</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; margin: 0; padding: 30px; }
        .box { max-width: 420px; margin: auto; background: #fff; padding: 24px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
        h2 { margin-top: 0; }
        label { display: block; margin-top: 12px; font-weight: bold; }
        input { width: 100%; padding: 8px; margin-top: 4px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        .btn { margin-top: 18px; padding: 9px 16px; border: 0; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; font-size: 14px; }
        .btn-simpan { background: #2e7d32; color: #fff; }
        .btn-batal { background: #9e9e9e; color: #fff; }
        .error { background: #ffebee; color: #c62828; padding: 10px; border-radius: 4px; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="box">
        <h2>Tambah Petugas</h2>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <label>ID Petugas</label>
            <input type="text" name="petugas_id" maxlength="10" value="<?= htmlspecialchars($_POST['petugas_id'] ?? '') ?>" required>

            <label>Nama Petugas</label>
            <input type="text" name="nama_petugas" maxlength="100" value="<?= htmlspecialchars($_POST['nama_petugas'] ?? '') ?>" required>

            <button type="submit" name="simpan" class="btn btn-simpan">Simpan</button>
            <a href="index.php" class="btn btn-batal">Batal</a>
        </form>
    </div>
</body>
</html>