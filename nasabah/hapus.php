<?php
include __DIR__ . '/../connect.php';

if (isset($_GET['id'])) {
    $id_nasabah = $_GET['id'];

    $query = "DELETE FROM nasabah WHERE id_nasabah = '$id_nasabah'";
    
    if (mysqli_query($koneksi, $query)) {
        header("Location: index.php");
        exit();
    } else {
        echo "<script>alert('Gagal menghapus data!'); window.location='index.php';</script>";
    }
}
?>