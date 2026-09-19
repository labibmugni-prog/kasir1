<?php

session_start();
include 'koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: laporan.php");
    exit;
}

$id_transaksi = (int) $_GET['id'];

mysqli_begin_transaction($conn);

try {

    // AMBIL DETAIL TRANSAKSI

    $query_detail = mysqli_query(
        $conn,
        "SELECT `Id_produk`, `Jumlah`
         FROM `detail_transaksi`
         WHERE `Id_transaksi` = $id_transaksi
         FOR UPDATE"
    );

    if (!$query_detail) {
        throw new Exception(mysqli_error($conn));
    }


    // KEMBALIKAN STOK PRODUK

    while ($detail = mysqli_fetch_assoc($query_detail)) {

        $id_produk = (int) $detail['Id_produk'];
        $jumlah = (int) $detail['Jumlah'];

        $update_stok = mysqli_query(
            $conn,
            "UPDATE `produk`
             SET `Stok` = `Stok` + $jumlah
             WHERE `Id_produk` = $id_produk"
        );

        if (!$update_stok) {
            throw new Exception(mysqli_error($conn));
        }
    }


    // HAPUS DETAIL TRANSAKSI

    $hapus_detail = mysqli_query(
        $conn,
        "DELETE FROM `detail_transaksi`
         WHERE `Id_transaksi` = $id_transaksi"
    );

    if (!$hapus_detail) {
        throw new Exception(mysqli_error($conn));
    }


    // HAPUS TRANSAKSI UTAMA

    $hapus_transaksi = mysqli_query(
        $conn,
        "DELETE FROM `transaksi`
         WHERE `Id_transaksi` = $id_transaksi"
    );

    if (!$hapus_transaksi) {
        throw new Exception(mysqli_error($conn));
    }

    // SEMUA BERHASIL

    mysqli_commit($conn);

    header("Location: laporan.php?pesan=hapus_berhasil");
    exit;


} catch (Exception $e) {

    mysqli_rollback($conn);

    die(
        "Gagal menghapus transaksi: " .
        htmlspecialchars($e->getMessage())
    );
}

?>