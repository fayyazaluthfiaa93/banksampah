<?php
include '../connect.php';
/** @var mysqli $koneksi */

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int) $_GET['id'];

    $stmt = $koneksi->prepare("DELETE FROM petugas WHERE id_petugas = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: index.php?status=hapus");
    } else {
        // Biasanya gagal karena masih dipakai sebagai foreign key di tabel lain
        header("Location: index.php?status=gagal");
    }
    exit;
}

header("Location: index.php");
exit;