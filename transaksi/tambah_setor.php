<?php
// aksi_setor.php (File khusus menangani proses backend / logic tambah)
error_reporting(E_ALL);
ini_set('display_errors', 1);

include __DIR__ . '/../connect.php';

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
        if (mb_strlen($no_transaksi) > 25) {
            throw new Exception("No transaksi maksimal 25 karakter.");
        }

        // 1. Simpan ke Tabel Induk: Transaksi (total_nominal 0 dulu)
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

        // 2. Siapkan statement untuk ambil harga & simpan ke Tabel Anak: Detail Setoran[cite: 4]
        $stmt_harga  = mysqli_prepare($koneksi, "SELECT harga_beli_per_kg FROM kategori_sampah WHERE id_kategori_sampah = ?");
        $stmt_detail = mysqli_prepare(
            $koneksi,
            "INSERT INTO detail_setoran (no_transaksi, id_kategori_sampah, berat_kg, subtotal_harga) VALUES (?, ?, ?, ?)"
        );

        // Looping berdasarkan input berat dari form
        foreach ($berat_input_arr as $id_sampah => $val_berat) {
            $berat = (float)$val_berat;
            if ($berat <= 0) continue; // Lewati jika tidak diisi / 0

            $ada_transaksi_valid = true;

            // Ambil harga per kg dari tabel Kategori Sampah[cite: 4]
            mysqli_stmt_bind_param($stmt_harga, "s", $id_sampah);
            mysqli_stmt_execute($stmt_harga);
            $res   = mysqli_stmt_get_result($stmt_harga);
            $d     = mysqli_fetch_assoc($res);
            $harga = (float)($d['harga_beli_per_kg'] ?? 0);

            $subtotal      = $berat * $harga;
            $total_nominal += $subtotal;

            // Simpan ke Detail Setoran[cite: 4]
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

        // 3. Update total_nominal sebenarnya ke tabel Transaksi[cite: 4]
        $total_int = (int)round($total_nominal);
        $stmt = mysqli_prepare($koneksi, "UPDATE transaksi SET total_nominal = ? WHERE no_transaksi = ?");
        mysqli_stmt_bind_param($stmt, "is", $total_int, $no_transaksi);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Gagal memperbarui total nominal transaksi.");
        }
        mysqli_stmt_close($stmt);

        // 4. Tambahkan saldo ke tabel Nasabah[cite: 4]
        $stmt = mysqli_prepare($koneksi, "UPDATE nasabah SET saldo_rupiah = saldo_rupiah + ? WHERE id_nasabah = ?");
        mysqli_stmt_bind_param($stmt, "is", $total_int, $id_nasabah);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Gagal memperbarui saldo nasabah.");
        }
        mysqli_stmt_close($stmt);

        mysqli_commit($koneksi);

        // Jika berhasil, lempar ke halaman riwayat
        header("Location: riwayat.php");
        exit;

    } catch (Throwable $e) {
        mysqli_rollback($koneksi);
        // Simpan pesan error untuk ditampilkan di view
        session_start();
        $_SESSION['error_msg'] = $e->getMessage();
        header("Location: setor.php");
        exit;
    }
}