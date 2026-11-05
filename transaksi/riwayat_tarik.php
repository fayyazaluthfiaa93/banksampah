<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include __DIR__ . '/../connect.php';

// Riwayat penarikan saldo beserta relasi ke nasabah dan petugas
$query = "SELECT 
            t.no_transaksi, 
            t.tanggal_transaksi, 
            n.nama_nasabah, 
            p.nama_petugas, 
            t.total_nominal
          FROM transaksi t
          JOIN nasabah n ON t.id_nasabah = n.id_nasabah
          JOIN petugas p ON t.petugas_id = p.petugas_id
          WHERE t.jenis_transaksi = 'Penarikan'
          ORDER BY t.tanggal_transaksi DESC, t.no_transaksi DESC";

$result = mysqli_query($koneksi, $query);

// Total seluruh penarikan (untuk ringkasan di bawah tabel)
$total_semua = 0;
$rows = [];
while ($r = mysqli_fetch_assoc($result)) {
    $rows[] = $r;
    $total_semua += (int)$r['total_nominal'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Riwayat Penarikan Saldo</title>
    <style>
        body { margin: 0; background: var(--pg-bg, #f4f5f9); color: var(--pg-text, #14161a); font-family: "Inter", sans-serif; }
        .content .card { background: var(--sb-bg, #fff); border-radius: 24px; padding: 24px; }
        .content table { border-collapse: collapse; width: 100%; margin-top: 15px; }
        .content th, .content td { border: 1px solid var(--sb-line, #e6e8ee); padding: 10px 12px; text-align: left; }
        .content th { background: #f8f9fa; }
        .content tfoot td { font-weight: 700; background: #f8f9fa; }
        .btn-edit { background: #f0ad4e; color: white; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 13px; }
        .btn-hapus { background: #d9534f; color: white; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 13px; }
        .btn-tambah { background: #4453d6; color: white; padding: 8px 14px; border-radius: 8px; text-decoration: none; display: inline-block; margin-bottom: 15px; margin-right: 8px; font-weight: 600; }
        .btn-alt { background: transparent; color: #4453d6; border: 1px solid #4453d6; padding: 7px 14px; border-radius: 8px; text-decoration: none; display: inline-block; margin-bottom: 15px; font-weight: 600; }
    </style>
</head>
<body>

<div class="app">
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/banksampah/includess/sidebar.php"; ?>

    <div class="content">
        <div class="card">
            <h2>Riwayat Penarikan Saldo</h2>
            <a href="tarik.php" class="btn-tambah">+ Tarik Saldo Baru</a>
            <a href="riwayat.php" class="btn-alt">Lihat Riwayat Setoran</a>

            <table>
                <thead>
                    <tr>
                        <th>No Transaksi</th>
                        <th>Tanggal</th>
                        <th>Nasabah</th>
                        <th>Petugas</th>
                        <th>Nominal Penarikan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $row) : ?>
                    <tr>
                        <td><?= htmlspecialchars($row['no_transaksi']); ?></td>
                        <td><?= htmlspecialchars($row['tanggal_transaksi']); ?></td>
                        <td><?= htmlspecialchars($row['nama_nasabah']); ?></td>
                        <td><?= htmlspecialchars($row['nama_petugas']); ?></td>
                        <td>Rp <?= number_format($row['total_nominal'], 0, ',', '.'); ?></td>
                        <td>
                            <a href="edit_tarik.php?no=<?= urlencode($row['no_transaksi']); ?>" class="btn-edit">Edit</a>
                            <a href="hapus_tarik.php?no=<?= urlencode($row['no_transaksi']); ?>" class="btn-hapus" onclick="return confirm('Yakin ingin menghapus penarikan ini? Saldo nasabah akan dikembalikan.');">Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (count($rows) === 0) : ?>
                    <tr>
                        <td colspan="6" style="text-align: center; color: #777;">Belum ada riwayat penarikan saldo.</td>
                    </tr>
                <?php endif; ?>
                </tbody>

                <?php if (count($rows) > 0) : ?>
                <tfoot>
                    <tr>
                        <td colspan="4" style="text-align: right;">Total Penarikan</td>
                        <td>Rp <?= number_format($total_semua, 0, ',', '.'); ?></td>
                        <td></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

</body>
</html>