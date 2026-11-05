<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include __DIR__ . '/../connect.php';

$no_transaksi = $_GET['no'] ?? '';
if ($no_transaksi === '') {
    header("Location: riwayat_tarik.php");
    exit;
}

$script_alert = '';

// Ambil data transaksi penarikan
$stmt = mysqli_prepare($koneksi, "SELECT * FROM transaksi WHERE no_transaksi = ?");
mysqli_stmt_bind_param($stmt, "s", $no_transaksi);
mysqli_stmt_execute($stmt);
$transaksi = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$transaksi || $transaksi['jenis_transaksi'] !== 'Penarikan') {
    echo "<script>alert('Data penarikan tidak ditemukan!'); window.location='riwayat_tarik.php';</script>";
    exit;
}

if (isset($_POST['update'])) {
    $tanggal_transaksi = $_POST['tanggal_transaksi'];
    $petugas_id        = trim($_POST['petugas_id']);
    $id_nasabah        = trim($_POST['id_nasabah']);
    $nominal_baru      = (int)$_POST['total_nominal'];

    mysqli_begin_transaction($koneksi);

    try {
        $old_nominal = (int)$transaksi['total_nominal'];
        $old_nasabah = $transaksi['id_nasabah'];

        // Jika nasabah sama, hitung selisihnya
        if ($old_nasabah === $id_nasabah) {
            $selisih = $nominal_baru - $old_nominal; // Jika nominal naik, saldo harus dikurangi lagi.

            // Cek apakah saldo mencukupi jika penarikan ditambah
            if ($selisih > 0) {
                $stmt_cek = mysqli_prepare($koneksi, "SELECT saldo_rupiah FROM nasabah WHERE id_nasabah = ?");
                mysqli_stmt_bind_param($stmt_cek, "s", $id_nasabah);
                mysqli_stmt_execute($stmt_cek);
                $nasabah_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_cek));
                mysqli_stmt_close($stmt_cek);

                if ($nasabah_data['saldo_rupiah'] < $selisih) {
                    throw new Exception("Penambahan nominal melebihi sisa saldo nasabah!");
                }
            }

            // Update saldo dengan selisih (karena penarikan sifatnya mengurangi saldo, kenaikan penarikan berarti saldo dikurangi lagi)
            $stmt_bal = mysqli_prepare($koneksi, "UPDATE nasabah SET saldo_rupiah = saldo_rupiah - ? WHERE id_nasabah = ?");
            mysqli_stmt_bind_param($stmt_bal, "is", $selisih, $id_nasabah);
            mysqli_stmt_execute($stmt_bal);
            mysqli_stmt_close($stmt_bal);

        } else {
            // Jika nasabah diubah: kembalikan saldo nasabah lama, lalu kurangi saldo nasabah baru
            $stmt_old = mysqli_prepare($koneksi, "UPDATE nasabah SET saldo_rupiah = saldo_rupiah + ? WHERE id_nasabah = ?");
            mysqli_stmt_bind_param($stmt_old, "is", $old_nominal, $old_nasabah);
            mysqli_stmt_execute($stmt_old);
            mysqli_stmt_close($stmt_old);

            // Cek saldo nasabah baru
            $stmt_cek_baru = mysqli_prepare($koneksi, "SELECT saldo_rupiah FROM nasabah WHERE id_nasabah = ?");
            mysqli_stmt_bind_param($stmt_cek_baru, "s", $id_nasabah);
            mysqli_stmt_execute($stmt_cek_baru);
            $nasabah_baru = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_cek_baru));
            mysqli_stmt_close($stmt_cek_baru);

            if ($nasabah_baru['saldo_rupiah'] < $nominal_baru) {
                throw new Exception("Saldo nasabah baru tidak mencukupi untuk penarikan ini!");
            }

            $stmt_new = mysqli_prepare($koneksi, "UPDATE nasabah SET saldo_rupiah = saldo_rupiah - ? WHERE id_nasabah = ?");
            mysqli_stmt_bind_param($stmt_new, "is", $nominal_baru, $id_nasabah);
            mysqli_stmt_execute($stmt_new);
            mysqli_stmt_close($stmt_new);
        }

        // Update data transaksi utama
        $stmt_up = mysqli_prepare($koneksi, "UPDATE transaksi SET tanggal_transaksi = ?, petugas_id = ?, id_nasabah = ?, total_nominal = ? WHERE no_transaksi = ?");
        mysqli_stmt_bind_param($stmt_up, "sssis", $tanggal_transaksi, $petugas_id, $id_nasabah, $nominal_baru, $no_transaksi);
        mysqli_stmt_execute($stmt_up);
        mysqli_stmt_close($stmt_up);

        mysqli_commit($koneksi);
        header("Location: riwayat_tarik.php");
        exit;

    } catch (Throwable $e) {
        mysqli_rollback($koneksi);
        $script_alert = "alert(" . json_encode('Error: ' . $e->getMessage()) . ");";
    }
}

$daftar_petugas = mysqli_query($koneksi, "SELECT * FROM petugas");
$daftar_nasabah = mysqli_query($koneksi, "SELECT * FROM nasabah");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Penarikan Saldo</title>
    <style>
        body { margin: 0; background: var(--pg-bg, #f4f5f9); color: var(--pg-text, #14161a); font-family: "Inter", sans-serif; }
        .content .card { background: var(--sb-bg, #fff); border-radius: 24px; padding: 24px; max-width: 600px; margin: 20px auto; }
        .content input, .content select { padding: 8px 10px; border: 1px solid var(--sb-line, #e6e8ee); border-radius: 8px; background: transparent; color: inherit; width: 100%; box-sizing: border-box; }
        .content button[type="submit"] { padding: 10px 18px; border: 0; border-radius: 10px; background: var(--sb-active-fg, #4453d6); color: #fff; font-weight: 600; cursor: pointer; }
    </style>
</head>
<body>
<div class="app">
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/banksampah/includess/sidebar.php"; ?>
    <div class="content">
        <div class="card">
            <h2>Edit Penarikan: <?= htmlspecialchars($no_transaksi) ?></h2>
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

                <label>Nominal Penarikan (Rp):</label><br>
                <input type="number" name="total_nominal" min="1000" step="500" required value="<?= htmlspecialchars($transaksi['total_nominal']) ?>"><br><br>

                <button type="submit" name="update">Simpan Perubahan</button>
            </form>
        </div>
    </div>
</div>
<?php if ($script_alert): ?><script><?= $script_alert ?></script><?php endif; ?>
</body>
</html>