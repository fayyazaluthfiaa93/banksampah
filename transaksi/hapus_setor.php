<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include __DIR__ . '/../connect.php';

$no_transaksi = $_GET['no'] ?? '';
if ($no_transaksi === '') {
    header("Location: riwayat.php");
    exit;
}

mysqli_begin_transaction($koneksi);

try {
    // 1. Ambil data transaksi untuk tahu nominal dan nasabah
    $stmt = mysqli_prepare($koneksi, "SELECT id_nasabah, total_nominal FROM transaksi WHERE no_transaksi = ?");
    mysqli_stmt_bind_param($stmt, "s", $no_transaksi);
    mysqli_stmt_execute($stmt);
    $data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$data) {
        throw new Exception("Data transaksi tidak ditemukan.");
    }

    $id_nasabah = $data['id_nasabah'];
    $nominal    = (int)$data['total_nominal'];

    // 2. Kurangi kembali saldo nasabah karena transaksinya dibatalkan/dihapus
    $stmt_nasabah = mysqli_prepare($koneksi, "UPDATE nasabah SET saldo_rupiah = saldo_rupiah - ? WHERE id_nasabah = ?");
    mysqli_stmt_bind_param($stmt_nasabah, "is", $nominal, $id_nasabah);
    mysqli_stmt_execute($stmt_nasabah);
    mysqli_stmt_close($stmt_nasabah);

    // 3. Hapus data dari tabel anak (Detail Setoran) terlebih dahulu karena ada Foreign Key
    $stmt_detail = mysqli_prepare($koneksi, "DELETE FROM detail_setoran WHERE no_transaksi = ?");
    mysqli_stmt_bind_param($stmt_detail, "s", $no_transaksi);
    mysqli_stmt_execute($stmt_detail);
    mysqli_stmt_close($stmt_detail);

    // 4. Hapus data dari tabel induk (Transaksi)
    $stmt_trans = mysqli_prepare($koneksi, "DELETE FROM transaksi WHERE no_transaksi = ?");
    mysqli_stmt_bind_param($stmt_trans, "s", $no_transaksi);
    mysqli_stmt_execute($stmt_trans);
    mysqli_stmt_close($stmt_trans);

    mysqli_commit($koneksi);
    header("Location: riwayat.php");
    exit;

} catch (Throwable $e) {
    mysqli_rollback($koneksi);
    echo "<script>alert('Gagal menghapus data: " . addslashes($e->getMessage()) . "'); window.location='riwayat.php';</script>";
}