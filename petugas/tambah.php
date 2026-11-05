<?php
include '../connect.php';
/** @var mysqli $koneksi */

$error = '';

if (isset($_POST['simpan'])) {
    $nama     = trim($_POST['nama_petugas']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $no_hp    = trim($_POST['no_hp']);

    if ($nama === '' || $username === '' || $password === '') {
        $error = 'Nama, username, dan password wajib diisi.';
    } else {
        // Cek username sudah dipakai atau belum
        $cek = $koneksi->prepare("SELECT id_petugas FROM petugas WHERE username = ?");
        $cek->bind_param("s", $username);
        $cek->execute();
        $cek->store_result();

        if ($cek->num_rows > 0) {
            $error = 'Username sudah digunakan.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $koneksi->prepare(
                "INSERT INTO petugas (nama_petugas, username, password, no_hp) VALUES (?, ?, ?, ?)"
            );
            $stmt->bind_param("ssss", $nama, $username, $hash, $no_hp);

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
            <label>Nama Petugas</label>
            <input type="text" name="nama_petugas" value="<?= htmlspecialchars($_POST['nama_petugas'] ?? '') ?>" required>

            <label>Username</label>
            <input type="text" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <label>No. HP</label>
            <input type="text" name="no_hp" value="<?= htmlspecialchars($_POST['no_hp'] ?? '') ?>">

            <button type="submit" name="simpan" class="btn btn-simpan">Simpan</button>
            <a href="index.php" class="btn btn-batal">Batal</a>
        </form>
    </div>
</body>
</html>