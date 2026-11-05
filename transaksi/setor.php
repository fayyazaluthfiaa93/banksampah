<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include __DIR__ . '/../connect.php';
session_start();

$script_alert = '';
if (isset($_SESSION['error_msg'])) {
    $script_alert = "alert(" . json_encode($_SESSION['error_msg']) . ");";
    unset($_SESSION['error_msg']);
}

// Data untuk dropdown & tabel
$daftar_petugas = mysqli_query($koneksi, "SELECT * FROM petugas");
$daftar_nasabah = mysqli_query($koneksi, "SELECT * FROM nasabah");
$kategori       = mysqli_query($koneksi, "SELECT * FROM kategori_sampah");

$pilih_petugas = $_POST['petugas_id'] ?? '';
$pilih_nasabah = $_POST['id_nasabah'] ?? '';

// Ambil transaksi terakhir hari ini untuk menentukan nomor urut
$tanggal_hari_ini = date('Ymd'); // Contoh: 20261105
$query_cek = mysqli_query($koneksi, "SELECT no_transaksi FROM transaksi WHERE no_transaksi LIKE 'TRX-$tanggal_hari_ini-%' ORDER BY no_transaksi DESC LIMIT 1");

if ($data_terakhir = mysqli_fetch_assoc($query_cek)) {
    // Ambil 3 digit terakhir, lalu increment (+1)
    $no_urut = (int)substr($data_terakhir['no_transaksi'], -3) + 1;
} else {
    $no_urut = 1;
}

// Format jadi TRX-YYYYMMDD-XXX (Contoh: TRX-20261105-001)
$auto_no_transaksi = 'TRX-' . $tanggal_hari_ini . '-' . str_pad($no_urut, 3, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Form Setor Sampah</title>
    <style>
        body { margin: 0; background: var(--pg-bg, #f4f5f9); color: var(--pg-text, #14161a); font-family: "Inter", sans-serif; }
        .content .card { background: var(--sb-bg, #fff); border-radius: 24px; padding: 24px; }
        .content input, .content select { padding: 8px 10px; border: 1px solid var(--sb-line, #e6e8ee); border-radius: 8px; background: transparent; color: inherit; min-width: 220px; }
        .content select option { color: #14161a; }
        .content table { border-collapse: collapse; width: 100%; }
        .content th, .content td { border: 1px solid var(--sb-line, #e6e8ee); padding: 8px 12px; }
        .content button[type="submit"] { padding: 10px 18px; border: 0; border-radius: 10px; background: var(--sb-active-fg, #4453d6); color: #fff; font-weight: 600; cursor: pointer; }
    </style>
</head>
<body>

<div class="app">
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/banksampah/includess/sidebar.php"; ?>

    <div class="content">
        <div class="card">
            <!-- Arahkan action form ke file logika terpisah -->
            <form method="POST" action="tambah_setor.php">
                <label>No Transaksi:</label><br>
                <input type="text" name="no_transaksi" value="<?= htmlspecialchars($auto_no_transaksi); ?>" readonly style="background-color: #e9ecef; cursor: not-allowed;"><br><br>

                <label>Tanggal Transaksi:</label><br>
                <input type="date" name="tanggal_transaksi" required value="<?= date('Y-m-d') ?>"><br><br>

                <label>Petugas:</label><br>
                <select name="petugas_id" required>
                    <option value="">-- Pilih Petugas --</option>
                    <?php while ($p = mysqli_fetch_row($daftar_petugas)) : ?>
                        <option value="<?= htmlspecialchars($p[0]) ?>"><?= htmlspecialchars($p[0] . ' - ' . ($p[1] ?? '')) ?></option>
                    <?php endwhile; ?>
                </select><br><br>

                <label>Nasabah:</label><br>
                <select name="id_nasabah" required>
                    <option value="">-- Pilih Nasabah --</option>
                    <?php while ($n = mysqli_fetch_row($daftar_nasabah)) : ?>
                        <option value="<?= htmlspecialchars($n[0]) ?>"><?= htmlspecialchars($n[0] . ' - ' . ($n[1] ?? '')) ?></option>
                    <?php endwhile; ?>
                </select><br><br>

                <h3>Pilih Jenis Sampah dan Masukkan Berat:</h3>
                <table>
                    <tr>
                        <th>Jenis Sampah</th>
                        <th>Harga per Kg</th>
                        <th>Berat (Kg)</th>
                    </tr>
                    <?php 
                    $jumlah_baris = 0;
                    while ($row = mysqli_fetch_assoc($kategori)) :
                        $jumlah_baris++;
                        $id_kat = $row['id_kategori_sampah'];
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($row['nama_sampah']); ?></td>
                        <td>Rp <?= number_format($row['harga_beli_per_kg'], 0, ',', '.'); ?></td>
                        <td>
                            <input type="number" step="0.01" min="0" name="berat[<?= htmlspecialchars($id_kat); ?>]" style="width: 100px; min-width: 0;" placeholder="0">
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </table>
                <br>
                <button type="submit" name="submit">Simpan Setoran</button>
            </form>
        </div>
    </div>
</div>

<?php if ($script_alert): ?>
<script><?= $script_alert ?></script>
<?php endif; ?>

</body>
</html>