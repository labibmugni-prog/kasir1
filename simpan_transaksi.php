<?php

session_start();
include 'koneksi.php';

header('Content-Type: application/json');

if (!isset($_SESSION['login'])) {
    echo json_encode([
        "status" => false,
        "pesan" => "Anda belum login."
    ]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || empty($data['keranjang'])) {
    echo json_encode([
        "status" => false,
        "pesan" => "Keranjang masih kosong."
    ]);
    exit;
}

$keranjang = $data['keranjang'];
$bayar = (int) $data['bayar'];

$total = 0;

foreach ($keranjang as $item) {
    $total += (int) $item['subtotal'];
}

if ($bayar < $total) {
    echo json_encode([
        "status" => false,
        "pesan" => "Uang pembayaran masih kurang."
    ]);
    exit;
}

$kembalian = $bayar - $total;

mysqli_begin_transaction($conn);

try {

    // SIMPAN TRANSAKSI

    $query = mysqli_query(
        $conn,
        "INSERT INTO transaksi
        (`Tanggal`, `Total`, `Bayar`, `Kembalian`)
        VALUES
        (NOW(), $total, $bayar, $kembalian)"
    );

    if (!$query) {
        throw new Exception(mysqli_error($conn));
    }

    $id_transaksi = mysqli_insert_id($conn);


    // SIMPAN DETAIL + KURANGI STOK==

    foreach ($keranjang as $item) {

        $id_produk = (int) $item['id'];
        $harga = (int) $item['harga'];
        $jumlah = (int) $item['jumlah'];
        $subtotal = (int) $item['subtotal'];

        // CEK STOK
        $cek_stok = mysqli_query(
            $conn,
            "SELECT `Stok`
             FROM `produk`
             WHERE `Id_produk` = $id_produk
             FOR UPDATE"
        );

        if (!$cek_stok) {
            throw new Exception(mysqli_error($conn));
        }

        $data_stok = mysqli_fetch_assoc($cek_stok);

        if (!$data_stok) {
            throw new Exception(
                "Produk dengan ID $id_produk tidak ditemukan."
            );
        }

        $stok_sekarang = (int) $data_stok['Stok'];


        // CEK STOK CUKUP

        if ($stok_sekarang < $jumlah) {
            throw new Exception(
                "Stok produk ID $id_produk tidak cukup. " .
                "Stok tersedia: $stok_sekarang, " .
                "jumlah dibeli: $jumlah."
            );
        }

        // SIMPAN DETAIL TRANSAKSI

        $query_detail = mysqli_query(
            $conn,
            "INSERT INTO detail_transaksi
            (`Id_transaksi`, `Id_produk`, `Harga`, `Jumlah`, `Subtotal`)
            VALUES
            ($id_transaksi, $id_produk, $harga, $jumlah, $subtotal)"
        );

        if (!$query_detail) {
            throw new Exception(mysqli_error($conn));
        }

        // KURANGI STOK PRODUK

        $query_stok = mysqli_query(
            $conn,
            "UPDATE produk
             SET `Stok` = `Stok` - $jumlah
             WHERE `Id_produk` = $id_produk"
        );

        if (!$query_stok) {
            throw new Exception(mysqli_error($conn));
        }
    }

    // SEMUA BERHASIL

    mysqli_commit($conn);

    echo json_encode([
        "status" => true,
        "pesan" => "Transaksi berhasil disimpan dan stok berhasil dikurangi.",
        "id_transaksi" => $id_transaksi,
        "total" => $total,
        "bayar" => $bayar,
        "kembalian" => $kembalian
    ]);

} catch (Exception $e) {

    mysqli_rollback($conn);

    echo json_encode([
        "status" => false,
        "pesan" => "Gagal menyimpan transaksi: " . $e->getMessage()
    ]);
}

?>