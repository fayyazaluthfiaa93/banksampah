<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include __DIR__ . '/../connect.php';

$no_transaksi = $_GET['no'] ?? '';
if ($no_transaksi === '') {
    header("Location: riwayat.php");
    exit;
}

$script_alert = '';

// Ambil data transaksi utama
$stmt = mysqli_prepare($koneksi, "SELECT * FROM transaksi WHERE no_transaksi = ?");
mysqli_stmt_bind_param($stmt, "s", $no_transaksi);
mysqli_stmt_execute($stmt);
$transaksi = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$transaksi) {
    echo "<script>alert('Data transaksi tidak ditemukan!'); window.location='riwayat.php';</script>";
    exit;
}

// Ambil detail setoran yang sudah ada sebelumnya
$stmt_det = mysqli_prepare($koneksi, "SELECT * FROM detail_setoran WHERE no_transaksi = ?");
mysqli_stmt_bind_param($stmt_det, "s", $no_transaksi);
mysqli_stmt_execute($stmt_det);
$res_det = mysqli_stmt_get_result($stmt_det);
$detail_lama = [];
while ($d = mysqli_fetch_assoc($res_det)) {
    $detail_lama[$d['id_kategori_sampah']] = $d['berat_kg'];
}
mysqli_stmt_close($stmt_det);

// Proses saat form disubmit
if (isset($_POST['update'])) {
    $tanggal_transaksi = $_POST['tanggal_transaksi'] ?? '';
    $petugas_id        = trim($_POST['petugas_id'] ?? '');
    $id_nasabah        = trim($_POST['id_nasabah'] ?? '');
    $berat_input_arr   = $_POST['berat'] ?? [];

    $total_nominal       = 0;
    $ada_transaksi_valid = false;

    mysqli_begin_transaction($koneksi);

    try {
        if ($petugas_id === '' || $id_nasabah === '') {
            throw new Exception("Petugas dan nasabah wajib diisi.");
        }

        // 1. Ambil nominal lama & nasabah lama untuk penyesuaian saldo
        $old_nominal = (int)$transaksi['total_nominal'];
        $old_nasabah = $transaksi['id_nasabah'];

        // 2. Hapus detail setoran lama terlebih dahulu
        $stmt_del = mysqli_prepare($koneksi, "DELETE FROM detail_setoran WHERE no_transaksi = ?");
        mysqli_stmt_bind_param($stmt_del, "s", $no_transaksi);
        mysqli_stmt_execute($stmt_del);
        mysqli_stmt_close($stmt_del);

        // 3. Siapkan statement harga & detail baru
        $stmt_harga  = mysqli_prepare($koneksi, "SELECT harga_beli_per_kg FROM kategori_sampah WHERE id_kategori_sampah = ?");
        $stmt_detail = mysqli_prepare(
            $koneksi,
            "INSERT INTO detail_setoran (no_transaksi, id_kategori_sampah, berat_kg, subtotal_harga) VALUES (?, ?, ?, ?)"
        );

        foreach ($berat_input_arr as $id_sampah => $val_berat) {
            $berat = (float)$val_berat;
            if ($berat <= 0) continue;

            $ada_transaksi_valid = true;

            mysqli_stmt_bind_param($stmt_harga, "s", $id_sampah);
            mysqli_stmt_execute($stmt_harga);
            $res   = mysqli_stmt_get_result($stmt_harga);
            $d     = mysqli_fetch_assoc($res);
            $harga = (float)($d['harga_beli_per_kg'] ?? 0);

            $subtotal      = $berat * $harga;
            $total_nominal += $subtotal;

            mysqli_stmt_bind_param($stmt_detail, "ssdd", $no_transaksi, $id_sampah, $berat, $subtotal);
            mysqli_stmt_execute($stmt_detail);
        }
        mysqli_stmt_close($stmt_harga);
        mysqli_stmt_close($stmt_detail);

        if (!$ada_transaksi_valid) {
            throw new Exception("Harap masukkan berat sampah minimal pada salah satu kategori.");
        }

        $total_int = (int)round($total_nominal);

        // 4. Update data utama transaksi
        $stmt_up = mysqli_prepare($koneksi, "UPDATE transaksi SET tanggal_transaksi = ?, petugas_id = ?, id_nasabah = ?, total_nominal = ? WHERE no_transaksi = ?");
        mysqli_stmt_bind_param($stmt_up, "sssis", $tanggal_transaksi, $petugas_id, $id_nasabah, $total_int, $no_transaksi);
        mysqli_stmt_execute($stmt_up);
        mysqli_stmt_close($stmt_up);

        // 5. Sesuaikan Saldo Nasabah
        if ($old_nasabah === $id_nasabah) {
            $selisih = $total_int - $old_nominal;
            $stmt_bal = mysqli_prepare($koneksi, "UPDATE nasabah SET saldo_rupiah = saldo_rupiah + ? WHERE id_nasabah = ?");
            mysqli_stmt_bind_param($stmt_bal, "is", $selisih, $id_nasabah);
            mysqli_stmt_execute($stmt_bal);
            mysqli_stmt_close($stmt_bal);
        } else {
            $stmt_old_bal = mysqli_prepare($koneksi, "UPDATE nasabah SET saldo_rupiah = saldo_rupiah - ? WHERE id_nasabah = ?");
            mysqli_stmt_bind_param($stmt_old_bal, "is", $old_nominal, $old_nasabah);
            mysqli_stmt_execute($stmt_old_bal);
            mysqli_stmt_close($stmt_old_bal);

            $stmt_new_bal = mysqli_prepare($koneksi, "UPDATE nasabah SET saldo_rupiah = saldo_rupiah + ? WHERE id_nasabah = ?");
            mysqli_stmt_bind_param($stmt_new_bal, "is", $total_int, $id_nasabah);
            mysqli_stmt_execute($stmt_new_bal);
            mysqli_stmt_close($stmt_new_bal);
        }

        mysqli_commit($koneksi);
        header("Location: riwayat.php");
        exit;

    } catch (Throwable $e) {
        mysqli_rollback($koneksi);
        $script_alert = "alert(" . json_encode('Error: ' . $e->getMessage()) . ");";
    }
}

$daftar_petugas = mysqli_query($koneksi, "SELECT * FROM petugas");
$daftar_nasabah = mysqli_query($koneksi, "SELECT * FROM nasabah");
$kategori       = mysqli_query($koneksi, "SELECT * FROM kategori_sampah");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Setor Sampah</title>
    <style>
        body { margin: 0; background: var(--pg-bg, #f4f5f9); color: var(--pg-text, #14161a); font-family: "Inter", sans-serif; }
        .content .card { background: var(--sb-bg, #fff); border-radius: 24px; padding: 24px; }
        .content input, .content select { padding: 8px 10px; border: 1px solid var(--sb-line, #e6e8ee); border-radius: 8px; background: transparent; color: inherit; min-width: 220px; }
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
            <h2>Edit Setor Sampah: <?= htmlspecialchars($no_transaksi) ?></h2>
            <form method="POST" action="">
                <label>Tanggal Transaksi:</label><br>
                <input type="date" name="tanggal_transaksi" required value="<?= htmlspecialchars($transaksi['tanggal_transaksi']) ?>"><br><br>

                <label>Petugas:</label><br>
                <select name="petugas_id" required>
                    <?php while ($p = mysqli_fetch_row($daftar_petugas)) : ?>
                        <option value="<?= htmlspecialchars($p[0]) ?>" <?= (($transaksi['petugas_id'] ?? '') === $p[0]) ? 'selected' : '' ?>><?= htmlspecialchars($p[0] . ' - ' . ($p[1] ?? '')) ?></option>
                    <?php endwhile; ?>
                </select><br><br>

                <label>Nasabah:</label><br>
                <select name="id_nasabah" required>
                    <?php while ($n = mysqli_fetch_row($daftar_nasabah)) : ?>
                        <option value="<?= htmlspecialchars($n[0]) ?>" <?= (($transaksi['id_nasabah'] ?? '') === $n[0]) ? 'selected' : '' ?>><?= htmlspecialchars($n[0] . ' - ' . ($n[1] ?? '')) ?></option>
                    <?php endwhile; ?>
                </select><br><br>

                <h3>Ubah Berat Sampah:</h3>
                <table>
                    <tr>
                        <th>Jenis Sampah</th>
                        <th>Harga per Kg</th>
                        <th>Berat (Kg)</th>
                    </tr>
                    <?php while ($row = mysqli_fetch_assoc($kategori)) : 
                        $id_kat = $row['id_kategori_sampah'];
                        $berat_val = $detail_lama[$id_kat] ?? '';
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($row['nama_sampah']); ?></td>
                        <td>Rp <?= number_format($row['harga_beli_per_kg'], 0, ',', '.'); ?></td>
                        <td>
                            <input type="number" step="0.01" min="0" name="berat[<?= htmlspecialchars($id_kat); ?>]" value="<?= htmlspecialchars($berat_val) ?>" style="width: 100px;">
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </table>
                <br>
                <button type="submit" name="update">Simpan Perubahan</button>
            </form>
        </div>
    </div>
</div>
<?php if ($script_alert): ?><script><?= $script_alert ?></script><?php endif; ?>
</body>
</html>