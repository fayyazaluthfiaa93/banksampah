<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include __DIR__ . '/../connect.php';

$no_transaksi = $_GET['no'] ?? '';
if ($no_transaksi === '') {
    header("Location: riwayat_tarik.php");
    exit;
}

mysqli_begin_transaction($koneksi);

try {
    // 1. Ambil data transaksi sebelum dihapus
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM transaksi WHERE no_transaksi = ?");
    mysqli_stmt_bind_param($stmt, "s", $no_transaksi);
    mysqli_stmt_execute($stmt);
    $transaksi = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$transaksi || $transaksi['jenis_transaksi'] !== 'Penarikan') {
        throw new Exception("Data penarikan tidak ditemukan.");
    }

    $nominal = (int)$transaksi['total_nominal'];
    $id_nasabah = $transaksi['id_nasabah'];

    // 2. Kembalikan saldo nasabah karena penarikan dibatalkan/dihapus
    $stmt_bal = mysqli_prepare($koneksi, "UPDATE nasabah SET saldo_rupiah = saldo_rupiah + ? WHERE id_nasabah = ?");
    mysqli_stmt_bind_param($stmt_bal, "is", $nominal, $id_nasabah);
    mysqli_stmt_execute($stmt_bal);
    mysqli_stmt_close($stmt_bal);

    // 3. Hapus data transaksi utama
    $stmt_del = mysqli_prepare($koneksi, "DELETE FROM transaksi WHERE no_transaksi = ?");
    mysqli_stmt_bind_param($stmt_del, "s", $no_transaksi);
    mysqli_stmt_execute($stmt_del);
    mysqli_stmt_close($stmt_del);

    mysqli_commit($koneksi);
    header("Location: riwayat_tarik.php");
    exit;

} catch (Throwable $e) {
    mysqli_rollback($koneksi);
    echo "<script>alert('Gagal menghapus: " . addslashes($e->getMessage()) . "'); window.location='riwayat_tarik.php';</script>";
    exit;
}