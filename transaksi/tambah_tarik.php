<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include __DIR__ . '/../connect.php';
session_start();

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
        header("Location: riwayat.php");
        exit;

    } catch (Throwable $e) {
        mysqli_rollback($koneksi);
        $_SESSION['error_msg'] = 'Error: ' . $e->getMessage();
        header("Location: tarik.php");
        exit;
    }
}