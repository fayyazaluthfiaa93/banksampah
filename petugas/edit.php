<?php
include '../connect.php';
/** @var mysqli $koneksi */

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = (int) $_GET['id'];
$error = '';

// Ambil data petugas
$stmt = $koneksi->prepare("SELECT * FROM petugas WHERE id_petugas = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    header("Location: index.php");
    exit;
}

if (isset($_POST['update'])) {
    $nama     = trim($_POST['nama_petugas']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $no_hp    = trim($_POST['no_hp']);

    if ($nama === '' || $username === '') {
        $error = 'Nama dan username wajib diisi.';
    } else {
        // Cek username dipakai petugas lain
        $cek = $koneksi->prepare("SELECT id_petugas FROM petugas WHERE username = ? AND id_petugas != ?");
        $cek->bind_param("si", $username, $id);
        $cek->execute();
        $cek->store_result();

        if ($cek->num_rows > 0) {
            $error = 'Username sudah digunakan petugas lain.';
        } else {
            if ($password !== '') {
                // Password diganti
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $up = $koneksi->prepare(
                    "UPDATE petugas SET nama_petugas = ?, username = ?, password = ?, no_hp = ? WHERE id_petugas = ?"
                );
                $up->bind_param("ssssi", $nama, $username, $hash, $no_hp, $id);
            } else {
                // Password tidak diubah
                $up = $koneksi->prepare(
                    "UPDATE petugas SET nama_petugas = ?, username = ?, no_hp = ? WHERE id_petugas = ?"
                );
                $up->bind_param("sssi", $nama, $username, $no_hp, $id);
            }

            if ($up->execute()) {
                header("Location: index.php?status=edit");
                exit;
            } else {
                $error = 'Gagal mengubah data: ' . $up->error;
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
    <title>Edit Petugas</title>
    <style>
        body { 
        font-family: 
        Arial, sans-serif; 
        background: #f4f6f8; 
        margin: 0; 
        padding: 30px; 
        }
        .box { 
            max-width: 420px; 
            margin: auto; 
            background: #fff; 
            padding: 24px; 
            border-radius: 8px; 
            box-shadow: 0 2px 8px rgba(0,0,0,.1); }
        h2 { 
            margin-top: 0; }
        label { 
            display: block; 
            margin-top: 12px; 
            font-weight: bold; }
        input { 
            width: 100%; 
            padding: 8px; 
            margin-top: 4px; 
            box-sizing: border-box; 
            border: 1px solid #ccc; 
            border-radius: 4px; }
        small { 
            color: #777; }
        .btn { 
            margin-top: 18px; 
            padding: 9px 16px; 
            border: 0; 
            border-radius: 4px; 
            cursor: pointer; 
            text-decoration: none; 
            display: inline-block; 
            font-size: 14px; }
        .btn-update { 
            background: #1565c0; 
            color: #fff; }
        .btn-batal { 
            background: #9e9e9e; 
            color: #fff; }
        .error { 
            background: #ffebee; 
            color: #c62828; 
            padding: 10px; 
            border-radius: 4px; 
            margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="box">
        <h2>Edit Petugas</h2>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <label>Nama Petugas</label>
            <input type="text" name="nama_petugas" value="<?= htmlspecialchars($_POST['nama_petugas'] ?? $data['nama_petugas']) ?>" required>

            <label>Username</label>
            <input type="text" name="username" value="<?= htmlspecialchars($_POST['username'] ?? $data['username']) ?>" required>

            <label>Password</label>
            <input type="password" name="password">
            <small>Kosongkan jika tidak ingin mengganti password.</small>

            <label>No. HP</label>
            <input type="text" name="no_hp" value="<?= htmlspecialchars($_POST['no_hp'] ?? $data['no_hp']) ?>">

            <button type="submit" name="update" class="btn btn-update">Update</button>
            <a href="index.php" class="btn btn-batal">Batal</a>
        </form>
    </div>
</body>
</html>