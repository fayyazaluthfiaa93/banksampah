<?php
include '../connect.php';
/** @var mysqli $koneksi */

// Pencarian (opsional)
$cari = trim($_GET['cari'] ?? '');

if ($cari !== '') {
    $like = "%$cari%";
    $stmt = $koneksi->prepare(
        "SELECT * FROM petugas WHERE petugas_id LIKE ? OR nama_petugas LIKE ? ORDER BY petugas_id ASC"
    );
    $stmt->bind_param("ss", $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $koneksi->query("SELECT * FROM petugas ORDER BY petugas_id ASC");
}

// Pesan notifikasi dari tambah/edit/hapus
$pesan = '';
$tipe  = 'sukses';
if (isset($_GET['status'])) {
    switch ($_GET['status']) {
        case 'tambah': $pesan = 'Data petugas berhasil ditambahkan.'; break;
        case 'edit':   $pesan = 'Data petugas berhasil diubah.'; break;
        case 'hapus':  $pesan = 'Data petugas berhasil dihapus.'; break;
        case 'gagal':  $pesan = 'Data gagal dihapus. Petugas mungkin masih dipakai di data lain.'; $tipe = 'gagal'; break;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Petugas</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; margin: 0; padding: 30px; }
        .box { max-width: 800px; margin: auto; background: #fff; padding: 24px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
        h2 { margin-top: 0; }
        .atas { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 16px; }
        .cari { display: flex; gap: 6px; }
        .cari input { padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border-bottom: 1px solid #e0e0e0; text-align: left; font-size: 14px; }
        th { background: #2e7d32; color: #fff; }
        tr:hover td { background: #f1f8f1; }
        .btn { padding: 7px 12px; border: 0; border-radius: 4px; color: #fff; text-decoration: none; font-size: 13px; cursor: pointer; display: inline-block; }
        .btn-tambah { background: #2e7d32; }
        .btn-cari { background: #455a64; }
        .btn-edit { background: #1565c0; }
        .btn-hapus { background: #c62828; }
        .notif { padding: 10px 14px; border-radius: 4px; margin-bottom: 14px; }
        .sukses { background: #e8f5e9; color: #2e7d32; }
        .gagal { background: #ffebee; color: #c62828; }
        .kosong { text-align: center; color: #777; padding: 20px; }
    </style>
</head>
<body>
    <div class="box">
        <h2>Data Petugas</h2>

        <?php if ($pesan): ?>
            <div class="notif <?= $tipe ?>"><?= htmlspecialchars($pesan) ?></div>
        <?php endif; ?>

        <div class="atas">
            <a href="tambah.php" class="btn btn-tambah">+ Tambah Petugas</a>
            <form method="get" class="cari">
                <input type="text" name="cari" placeholder="Cari ID / nama" value="<?= htmlspecialchars($cari) ?>">
                <button type="submit" class="btn btn-cari">Cari</button>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th width="50">No</th>
                    <th width="140">ID Petugas</th>
                    <th>Nama Petugas</th>
                    <th width="150">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php $no = 1; while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['petugas_id']) ?></td>
                            <td><?= htmlspecialchars($row['nama_petugas']) ?></td>
                            <td>
                                <a href="edit.php?id=<?= urlencode($row['petugas_id']) ?>" class="btn btn-edit">Edit</a>
                                <a href="delete.php?id=<?= urlencode($row['petugas_id']) ?>" class="btn btn-hapus"
                                   onclick="return confirm('Yakin ingin menghapus petugas ini?')">Hapus</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="kosong">Belum ada data petugas.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>