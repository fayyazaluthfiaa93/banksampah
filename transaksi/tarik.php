<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include __DIR__ . '/../connect.php';
session_start();

$script_alert = '';

// Proses saat form disubmit
if (isset($_POST['submit'])) {
    $no_transaksi      = trim($_POST['no_transaksi']);
    $tanggal_transaksi = $_POST['tanggal_transaksi'];
    $petugas_id        = trim($_POST['petugas_id']);
    $id_nasabah        = trim($_POST['id_nasabah']);
    $jumlah_tarik      = (int)$_POST['total_nominal'];
    $jenis_transaksi   = 'Penarikan';

    mysqli_begin_transaction($koneksi);

    try {
        if ($petugas_id === '' || $id_nasabah === '' || $jumlah_tarik <= 0) {
            throw new Exception("Semua field wajib diisi dengan benar.");
        }

        // 1. Cek saldo nasabah saat ini
        $stmt_cek = mysqli_prepare($koneksi, "SELECT saldo_rupiah FROM nasabah WHERE id_nasabah = ?");
        mysqli_stmt_bind_param($stmt_cek, "s", $id_nasabah);
        mysqli_stmt_execute($stmt_cek);
        $nasabah = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_cek));
        mysqli_stmt_close($stmt_cek);

        if (!$nasabah) {
            throw new Exception("Data nasabah tidak ditemukan.");
        }

        $saldo_sekarang = (int)$nasabah['saldo_rupiah'];

        // Validasi: Apakah saldo mencukupi?
        if ($saldo_sekarang < $jumlah_tarik) {
            throw new Exception("Saldo nasabah tidak mencukupi! Saldo saat ini: Rp " . number_format($saldo_sekarang, 0, ',', '.'));
        }

        // 2. Insert ke tabel transaksi
        $stmt_trx = mysqli_prepare(
            $koneksi, 
            "INSERT INTO transaksi (no_transaksi, tanggal_transaksi, jenis_transaksi, petugas_id, id_nasabah, total_nominal) VALUES (?, ?, ?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param($stmt_trx, "sssssi", $no_transaksi, $tanggal_transaksi, $jenis_transaksi, $petugas_id, $id_nasabah, $jumlah_tarik);
        mysqli_stmt_execute($stmt_trx);
        mysqli_stmt_close($stmt_trx);

        // 3. Kurangi saldo nasabah
        $stmt_up_saldo = mysqli_prepare($koneksi, "UPDATE nasabah SET saldo_rupiah = saldo_rupiah - ? WHERE id_nasabah = ?");
        mysqli_stmt_bind_param($stmt_up_saldo, "is", $jumlah_tarik, $id_nasabah);
        mysqli_stmt_execute($stmt_up_saldo);
        mysqli_stmt_close($stmt_up_saldo);

        mysqli_commit($koneksi);
        header("Location: riwayat_tarik.php");
        exit;

    } catch (Throwable $e) {
        mysqli_rollback($koneksi);
        $script_alert = "alert(" . json_encode('Error: ' . $e->getMessage()) . ");";
    }
}

// Data untuk dropdown petugas & nasabah
$daftar_petugas = mysqli_query($koneksi, "SELECT * FROM petugas");
$daftar_nasabah = mysqli_query($koneksi, "SELECT * FROM nasabah");

// Auto-generate no_transaksi untuk penarikan hari ini
$tanggal_hari_ini = date('Ymd');
$query_cek = mysqli_query($koneksi, "SELECT no_transaksi FROM transaksi WHERE no_transaksi LIKE 'TRX-$tanggal_hari_ini-%' ORDER BY no_transaksi DESC LIMIT 1");

if ($data_terakhir = mysqli_fetch_assoc($query_cek)) {
    $no_urut = (int)substr($data_terakhir['no_transaksi'], -3) + 1;
} else {
    $no_urut = 1;
}
$auto_no_transaksi = 'TRX-' . $tanggal_hari_ini . '-' . str_pad($no_urut, 3, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Form Tarik Saldo</title>
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
            <h2>Form Penarikan Saldo</h2>
            <form method="POST" action="">
                <label>No Transaksi:</label><br>
                <input type="text" name="no_transaksi" value="<?= htmlspecialchars($auto_no_transaksi); ?>" readonly style="background-color: #e9ecef; cursor: not-allowed;"><br><br>

                <label>Tanggal Penarikan:</label><br>
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
                        <option value="<?= htmlspecialchars($n[0]) ?>"><?= htmlspecialchars($n[0] . ' - ' . ($n[1] ?? '')) ?> (Saldo: Rp <?= number_format($n[4] ?? 0, 0, ',', '.') ?>)</option>
                    <?php endwhile; ?>
                </select><br><br>

                <label>Nominal Penarikan (Rp):</label><br>
                <input type="number" name="total_nominal" min="1000" step="500" required placeholder="Contoh: 50000"><br><br>

                <button type="submit" name="submit">Simpan Penarikan</button>
            </form>
        </div>
    </div>
</div>

<?php if ($script_alert): ?>
<script><?= $script_alert ?></script>
<?php endif; ?>

</body>
</html>