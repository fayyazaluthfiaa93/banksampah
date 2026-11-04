<?php
include '../connect.php';
$db = $conn ?? $koneksi;

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "DELETE FROM kategori_sampah WHERE id_kategori_sampah = '$id'";

    if (mysqli_query($db, $sql)) {
        header("Location: index.php");
        exit();
    } else {
        echo "Gagal menghapus data: " . mysqli_error($db);
    }
} else {
    header("Location: index.php");
    exit();
}
?>