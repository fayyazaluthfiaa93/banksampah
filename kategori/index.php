<?php
include '../connect.php';

// Ambil data dari tabel kategori_sampah
$sql = "SELECT * FROM kategori_sampah ORDER BY id_kategori_sampah ASC";
$result = mysqli_query($conn ?? $koneksi, $sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Kategori Sampah</title>
    <style>
        body { 
            margin: 0; 
            background-color: var(--pg-bg, #121317); 
            color: var(--pg-text, #f1f2f5); 
            font-family: "Inter", system-ui, -apple-system, sans-serif; 
        }

        .card { 
            background: var(--sb-bg, #1b1d22); 
            padding: 24px; 
            border-radius: 20px; 
            border: 1px solid var(--sb-line, #2d3038); 
        }

        .header-title { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 20px; 
        }

        .header-title h2 { margin: 0; font-size: 20px; font-weight: 600; }

        .btn { 
            padding: 8px 16px; 
            background: var(--sb-active-fg, #4453d6); 
            color: white; 
            text-decoration: none; 
            border-radius: 10px; 
            font-weight: 600; 
            font-size: 13.5px;
            display: inline-block;
        }

        .btn-sm { padding: 6px 12px; font-size: 12px; }
        .btn-edit { background: #f59e0b; color: #000; }
        .btn-hapus { background: #ef4444; color: white; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px 14px; text-align: left; border-bottom: 1px solid var(--sb-line, #2d3038); font-size: 14px; }
        th { color: var(--sb-muted, #7d8391); font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; }
        tr:hover { background-color: var(--sb-hover, #262930); }
    </style>
</head>
<body>

<div class="app">
    <!-- Panggil File Sidebar -->
    <?php include '../includess/sidebar.php'; ?>

    <!-- Konten Utama -->
    <div class="content">
        <div class="card">
            <div class="header-title">
                <h2>Daftar Kategori Sampah</h2>
                <a href="tambah.php" class="btn">+ Tambah Kategori</a>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>ID Kategori</th>
                        <th>Nama Sampah</th>
                        <th>Harga Beli / kg</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    while ($row = mysqli_fetch_assoc($result)) : 
                    ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= htmlspecialchars($row['id_kategori_sampah']); ?></td>
                            <td><?= htmlspecialchars($row['nama_sampah']); ?></td>
                            <td>Rp <?= number_format($row['harga_beli_per_kg'], 0, ',', '.'); ?></td>
                            <td>
                                <a href="edit.php?id=<?= $row['id_kategori_sampah']; ?>" class="btn btn-sm btn-edit">Edit</a>
                                <a href="hapus.php?id=<?= $row['id_kategori_sampah']; ?>" class="btn btn-sm btn-hapus" onclick="return confirm('Yakin hapus data ini?')">Hapus</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>