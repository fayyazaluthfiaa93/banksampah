<?php
// ============================================================
// BAGIAN 1: LOGIKA (tidak boleh ada output HTML di sini)
// ============================================================
error_reporting(E_ALL);
ini_set('display_errors', 1);

include __DIR__ . '/../connect.php';

$script_alert = ''; // alert error ditampilkan setelah HTML dirender

if (isset($_POST['submit'])) {
    $no_transaksi      = trim($_POST['no_transaksi'] ?? '');
    $tanggal_transaksi = $_POST['tanggal_transaksi'] ?? '';
    $jenis_transaksi   = 'Setor Sampah';
    $petugas_id        = trim($_POST['petugas_id'] ?? '');
    $id_nasabah        = trim($_POST['id_nasabah'] ?? '');

    // Mengambil array berat berdasarkan id kategori sampah
    $berat_input_arr = $_POST['berat'] ?? [];

    $total_nominal       = 0;
    $ada_transaksi_valid = false;

    mysqli_begin_transaction($koneksi);

    try {
        if ($no_transaksi === '' || $petugas_id === '' || $id_nasabah === '') {
            throw new Exception("No transaksi, petugas, dan nasabah wajib diisi.");
        }
        if (mb_strlen($no_transaksi) > 10) {
            throw new Exception("No transaksi maksimal 10 karakter.");
        }

        // 1. Simpan data utama (total_nominal 0 dulu)
        $stmt = mysqli_prepare(
            $koneksi,
            "INSERT INTO transaksi (no_transaksi, tanggal_transaksi, jenis_transaksi, petugas_id, id_nasabah, total_nominal, metode_penarikan)
             VALUES (?, ?, ?, ?, ?, 0, NULL)"
        );
        mysqli_stmt_bind_param($stmt, "sssss", $no_transaksi, $tanggal_transaksi, $jenis_transaksi, $petugas_id, $id_nasabah);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Gagal menyimpan data transaksi utama: " . mysqli_stmt_error($stmt));
        }
        mysqli_stmt_close($stmt);

        // 2. Siapkan statement untuk harga & detail
        $stmt_harga  = mysqli_prepare($koneksi, "SELECT harga_beli_per_kg FROM kategori_sampah WHERE id_kategori_sampah = ?");
        $stmt_detail = mysqli_prepare(
            $koneksi,
            "INSERT INTO detail_setoran (no_transaksi, id_kategori_sampah, berat_kg, subtotal_harga) VALUES (?, ?, ?, ?)"
        );

        // Looping berdasarkan input berat yang dikirimkan menggunakan key ID Kategori
        foreach ($berat_input_arr as $id_sampah => $val_berat) {
            $berat = (float)$val_berat;
            if ($berat <= 0) continue; // Lewati jika tidak diisi atau 0

            $ada_transaksi_valid = true;

            mysqli_stmt_bind_param($stmt_harga, "s", $id_sampah);
            mysqli_stmt_execute($stmt_harga);
            $res   = mysqli_stmt_get_result($stmt_harga);
            $d     = mysqli_fetch_assoc($res);
            $harga = (float)($d['harga_beli_per_kg'] ?? 0);

            $subtotal       = $berat * $harga;
            $total_nominal += $subtotal;

            mysqli_stmt_bind_param($stmt_detail, "ssdd", $no_transaksi, $id_sampah, $berat, $subtotal);
            if (!mysqli_stmt_execute($stmt_detail)) {
                throw new Exception("Gagal menyimpan detail setoran: " . mysqli_stmt_error($stmt_detail));
            }
        }
        mysqli_stmt_close($stmt_harga);
        mysqli_stmt_close($stmt_detail);

        if (!$ada_transaksi_valid) {
            throw new Exception("Harap masukkan berat sampah minimal pada salah satu kategori.");
        }

        // 3. Update total_nominal sebenarnya
        $total_int = (int)round($total_nominal);
        $stmt = mysqli_prepare($koneksi, "UPDATE transaksi SET total_nominal = ? WHERE no_transaksi = ?");
        mysqli_stmt_bind_param($stmt, "is", $total_int, $no_transaksi);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Gagal memperbarui total nominal transaksi.");
        }
        mysqli_stmt_close($stmt);

        // 4. Tambahkan saldo nasabah
        $stmt = mysqli_prepare($koneksi, "UPDATE nasabah SET saldo_rupiah = saldo_rupiah + ? WHERE id_nasabah = ?");
        mysqli_stmt_bind_param($stmt, "is", $total_int, $id_nasabah);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Gagal memperbarui saldo nasabah.");
        }
        mysqli_stmt_close($stmt);

        mysqli_commit($koneksi);

        header("Location: riwayat.php");
        exit;

    } catch (Throwable $e) {
        mysqli_rollback($koneksi);
        $script_alert = "alert(" . json_encode('Error: ' . $e->getMessage()) . ");";
    }
}

// Data untuk dropdown & tabel.
$daftar_petugas = mysqli_query($koneksi, "SELECT * FROM petugas");
$daftar_nasabah = mysqli_query($koneksi, "SELECT * FROM nasabah");
$kategori       = mysqli_query($koneksi, "SELECT * FROM kategori_sampah");

$pilih_petugas = $_POST['petugas_id'] ?? '';
$pilih_nasabah = $_POST['id_nasabah'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Form Setor Sampah</title>
    <style>
        body {
            margin: 0;
            background: var(--pg-bg, #f4f5f9);
            color: var(--pg-text, #14161a);
            font-family: "Inter", system-ui, -apple-system, "Segoe UI", sans-serif;
        }
        .content .card {
            background: var(--sb-bg, #fff);
            border-radius: 24px;
            padding: 24px;
        }
        .content input[type="text"],
        .content input[type="date"],
        .content input[type="number"],
        .content select {
            padding: 8px 10px;
            border: 1px solid var(--sb-line, #e6e8ee);
            border-radius: 8px;
            background: transparent;
            color: inherit;
            font: inherit;
            min-width: 220px;
        }
        .content select option { color: #14161a; }
        .content table { border-collapse: collapse; width: 100%; }
        .content th, .content td { border: 1px solid var(--sb-line, #e6e8ee); padding: 8px 12px; }
        .content button[type="submit"] {
            padding: 10px 18px;
            border: 0;
            border-radius: 10px;
            background: var(--sb-active-fg, #4453d6);
            color: #fff;
            font: inherit;
            font-weight: 600;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="app">

    <?php include $_SERVER['DOCUMENT_ROOT'] . "/banksampah/includess/sidebar.php"; ?>

    <div class="content">
        <div class="card">

            <form method="POST" action="">
                <label>No Transaksi:</label><br>
                <input type="text" name="no_transaksi" maxlength="10" required
                       value="<?= htmlspecialchars($_POST['no_transaksi'] ?? '') ?>"><br><br>

                <label>Tanggal Transaksi:</label><br>
                <input type="date" name="tanggal_transaksi" required
                       value="<?= htmlspecialchars($_POST['tanggal_transaksi'] ?? date('Y-m-d')) ?>"><br><br>

                <label>Petugas:</label><br>
                <select name="petugas_id" required>
                    <option value="">-- Pilih Petugas --</option>
                    <?php while ($p = mysqli_fetch_row($daftar_petugas)) : ?>
                        <option value="<?= htmlspecialchars($p[0]) ?>" <?= $pilih_petugas === (string)$p[0] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p[0] . ' - ' . ($p[1] ?? '')) ?>
                        </option>
                    <?php endwhile; ?>
                </select><br><br>

                <label>Nasabah:</label><br>
                <select name="id_nasabah" required>
                    <option value="">-- Pilih Nasabah --</option>
                    <?php while ($n = mysqli_fetch_row($daftar_nasabah)) : ?>
                        <option value="<?= htmlspecialchars($n[0]) ?>" <?= $pilih_nasabah === (string)$n[0] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($n[0] . ' - ' . ($n[1] ?? '')) ?>
                        </option>
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
                    if ($kategori) {
                        while ($row = mysqli_fetch_assoc($kategori)) {
                            $jumlah_baris++;
                            $id_kat = $row['id_kategori_sampah'];
                            // Mengambil nilai berat sebelumnya jika terjadi error/validasi gagal
                            $berat_lama = $_POST['berat'][$id_kat] ?? '';
                    ?>
                    <tr>
                        <td>
                            <?= htmlspecialchars($row['nama_sampah']); ?>
                        </td>
                        <td>Rp <?= number_format($row['harga_beli_per_kg'], 0, ',', '.'); ?></td>
                        <td>
                            <!-- Menggunakan ID kategori sampah sebagai key index array pada input berat -->
                            <input type="number" step="0.01" min="0" name="berat[<?= htmlspecialchars($id_kat); ?>]"
                                   value="<?= htmlspecialchars($berat_lama) ?>" style="width: 100px; min-width: 0;" placeholder="0">
                        </td>
                    </tr>
                    <?php 
                        }
                    }
                    if ($jumlah_baris === 0) {
                        echo '<tr><td colspan="3" style="text-align: center; color: red;">Data kategori sampah kosong atau tabel belum diisi di database!</td></tr>';
                    }
                    ?>
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