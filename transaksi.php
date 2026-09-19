<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}


//PROSES SIMPAN TRANSAKSI

if (
    isset($_POST['aksi']) &&
    $_POST['aksi'] === 'simpan_transaksi'
) {

    header('Content-Type: application/json');

    $data = json_decode(
        $_POST['keranjang'] ?? '[]',
        true
    );

    $bayar = (int) ($_POST['bayar'] ?? 0);

    if (!is_array($data) || count($data) === 0) {

        echo json_encode([
            'success' => false,
            'message' => 'Belum ada barang yang dibeli.'
        ]);

        exit;
    }

    if ($bayar < 0) {

        echo json_encode([
            'success' => false,
            'message' => 'Jumlah pembayaran tidak valid.'
        ]);

        exit;
    }

    mysqli_begin_transaction($conn);

    try {

        $total_transaksi = 0;


        //CEK SEMUA PRODUK DAN HITUNG TOTAL

        $produk_transaksi = [];

        foreach ($data as $item) {

            $id_produk = (int) ($item['id_produk'] ?? 0);
            $jumlah = (int) ($item['jumlah'] ?? 0);

            if ($id_produk <= 0 || $jumlah <= 0) {

                throw new Exception(
                    'Data produk tidak valid.'
                );
            }

            //AMBIL PRODUK TERBARU

            $stmt = mysqli_prepare(
                $conn,
                "SELECT Id_produk, Nama_produk, Harga, Stok
                 FROM produk
                 WHERE Id_produk = ?
                 FOR UPDATE"
            );

            if (!$stmt) {
                throw new Exception(
                    'Gagal menyiapkan data produk.'
                );
            }

            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $id_produk
            );

            mysqli_stmt_execute($stmt);

            $hasil = mysqli_stmt_get_result($stmt);

            $produk_db = mysqli_fetch_assoc($hasil);

            mysqli_stmt_close($stmt);

            //CEK PRODUK


            if (!$produk_db) {

                throw new Exception(
                    'Produk tidak ditemukan.'
                );
            }


            //CEK STOK

            if ((int) $produk_db['Stok'] < $jumlah) {

                throw new Exception(
                    'Stok ' .
                    $produk_db['Nama_produk'] .
                    ' tidak mencukupi. ' .
                    'Stok tersedia: ' .
                    $produk_db['Stok']
                );
            }


            //HARGA PRODUK

            $harga = (int) $produk_db['Harga'];

            $subtotal = $harga * $jumlah;

            $total_transaksi += $subtotal;


            //SIMPAN SEMENTARA

            $produk_transaksi[] = [
                'id_produk' => $id_produk,
                'jumlah' => $jumlah,
                'harga' => $harga,
                'subtotal' => $subtotal
            ];
        }


        //CEK PEMBAYARAN

        if ($bayar < $total_transaksi) {

            throw new Exception(
                'Uang pembayaran kurang. ' .
                'Total transaksi: Rp ' .
                number_format(
                    $total_transaksi,
                    0,
                    ',',
                    '.'
                )
            );
        }


        //HITUNG KEMBALIAN

        $kembalian =
            $bayar - $total_transaksi;

        //SIMPAN TRANSAKSI UTAMA

        $stmt_transaksi = mysqli_prepare(
            $conn,
            "INSERT INTO transaksi
             (total, bayar, kembalian)
             VALUES (?, ?, ?)"
        );

        if (!$stmt_transaksi) {

            throw new Exception(
                'Gagal menyiapkan transaksi.'
            );
        }

        mysqli_stmt_bind_param(
            $stmt_transaksi,
            "iii",
            $total_transaksi,
            $bayar,
            $kembalian
        );

        if (!mysqli_stmt_execute($stmt_transaksi)) {

            mysqli_stmt_close($stmt_transaksi);

            throw new Exception(
                'Gagal menyimpan transaksi.'
            );
        }

        $id_transaksi =
            mysqli_insert_id($conn);

        mysqli_stmt_close($stmt_transaksi);


        //SIMPAN DETAIL TRANSAKSI

        foreach ($produk_transaksi as $item) {

            $stmt_detail = mysqli_prepare(
                $conn,
                "INSERT INTO detail_transaksi
                 (
                    id_transaksi,
                    id_produk,
                    jumlah,
                    harga,
                    subtotal
                 )
                 VALUES (?, ?, ?, ?, ?)"
            );

            if (!$stmt_detail) {

                throw new Exception(
                    'Gagal menyiapkan detail transaksi.'
                );
            }

            mysqli_stmt_bind_param(
                $stmt_detail,
                "iiiii",
                $id_transaksi,
                $item['id_produk'],
                $item['jumlah'],
                $item['harga'],
                $item['subtotal']
            );

            if (!mysqli_stmt_execute($stmt_detail)) {

                mysqli_stmt_close($stmt_detail);

                throw new Exception(
                    'Gagal menyimpan detail transaksi.'
                );
            }

            mysqli_stmt_close($stmt_detail);
        }



        //KURANGI STOK


        foreach ($produk_transaksi as $item) {

            $stmt_stok = mysqli_prepare(
                $conn,
                "UPDATE produk
                 SET Stok = Stok - ?
                 WHERE Id_produk = ?"
            );

            if (!$stmt_stok) {

                throw new Exception(
                    'Gagal menyiapkan update stok.'
                );
            }

            mysqli_stmt_bind_param(
                $stmt_stok,
                "ii",
                $item['jumlah'],
                $item['id_produk']
            );

            if (!mysqli_stmt_execute($stmt_stok)) {

                mysqli_stmt_close($stmt_stok);

                throw new Exception(
                    'Gagal mengurangi stok.'
                );
            }

            mysqli_stmt_close($stmt_stok);
        }


        //SEMUA BERHASIL

        mysqli_commit($conn);

        echo json_encode([
            'success' => true,
            'message' => 'Transaksi berhasil disimpan.',
            'id_transaksi' => $id_transaksi,
            'total' => $total_transaksi,
            'bayar' => $bayar,
            'kembalian' => $kembalian
        ]);

    } catch (Exception $e) {

        //JIKA GAGAL

        mysqli_rollback($conn);

        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }

    exit;
}

//AMBIL DATA PRODUK

$query_produk = mysqli_query(
    $conn,
    "SELECT *
     FROM produk
     WHERE Stok > 0
     ORDER BY Nama_produk ASC"
);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Transaksi - Dulur Cafe</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>

        body {
            background-color: #f5f5f5;
        }

        .navbar {
            padding: 15px;
        }

        .sidebar {
            min-height: calc(100vh - 66px);
        }

        .sidebar a {
            display: block;
            margin-bottom: 8px;
        }

        .card {
            border: none;
            border-radius: 10px;
        }

        .card-header {
            border-radius: 10px 10px 0 0 !important;
        }

        .judul {
            font-weight: bold;
        }

        .total-box {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
        }

        .total-harga {
            font-size: 28px;
            font-weight: bold;
            color: #198754;
        }

        .table th {
            white-space: nowrap;
        }

    </style>

</head>

<body>


<!-- NAVBAR -->

<nav class="navbar navbar-dark bg-success">

    <div class="container-fluid">

        <span class="navbar-brand fs-5 fw-bold">
            🛒 Dulur Cafe
        </span>

        <div class="text-white d-flex align-items-center gap-2">

            <span>

                Selamat datang,

                <b>
                    <?= htmlspecialchars(
                        $_SESSION['nama'] ?? 'Admin'
                    ) ?>
                </b>

            </span>

            <a
                href="logout.php"
                class="btn btn-danger btn-sm"
                onclick="return confirm('Yakin ingin keluar dari sistem kasir?');"
            >
                Logout
            </a>

        </div>

    </div>

</nav>


<div class="container-fluid">

    <div class="row">


        <!-- SIDEBAR -->

        <div class="col-md-3 col-lg-2 bg-dark sidebar p-3">

            <h5 class="text-white text-center mt-2">
                Menu
            </h5>

            <hr class="text-white">


            <a
                href="index.php"
                class="btn btn-success w-100"
            >
                🛒 Transaksi
            </a>


            <a
                href="produk.php"
                class="btn btn-light w-100"
            >
                📦 Data Produk
            </a>


            <a
                href="laporan.php"
                class="btn btn-light w-100"
            >
                📊 Laporan
            </a>

        </div>


        <!-- CONTENT -->

        <div class="col-md-9 col-lg-10 p-4">

            <h2 class="judul mb-4">
                Transaksi Penjualan
            </h2>


            <!-- INPUT BARANG -->

            <div class="card shadow-sm mb-4">

                <div class="card-header bg-success text-white">

                    <b>🛒 Input Barang</b>

                </div>


                <div class="card-body">

                    <div class="row">


                        <!-- PRODUK -->

                        <div class="col-md-4 mb-3">

                            <label class="form-label fw-bold">
                                Nama Barang
                            </label>

                            <select
                                class="form-select"
                                id="produk"
                                onchange="hitungHarga()"
                            >

                                <option value="">
                                    -- Pilih Barang --
                                </option>

                                <?php
                                while (
                                    $data =
                                    mysqli_fetch_assoc(
                                        $query_produk
                                    )
                                ):
                                ?>

                                    <option
                                        value="<?= $data['Id_produk']; ?>"
                                        data-nama="<?= htmlspecialchars($data['Nama_produk']); ?>"
                                        data-harga="<?= $data['Harga']; ?>"
                                        data-stok="<?= $data['Stok']; ?>"
                                    >

                                        <?= htmlspecialchars(
                                            $data['Nama_produk']
                                        ); ?>

                                        -
                                        Stok:
                                        <?= $data['Stok']; ?>

                                    </option>

                                <?php endwhile; ?>

                            </select>

                        </div>


                        <!-- HARGA -->

                        <div class="col-md-2 mb-3">

                            <label class="form-label fw-bold">
                                Harga
                            </label>

                            <input
                                type="number"
                                class="form-control"
                                id="harga"
                                value="0"
                                readonly
                            >

                        </div>


                        <!-- JUMLAH -->

                        <div class="col-md-2 mb-3">

                            <label class="form-label fw-bold">
                                Jumlah
                            </label>

                            <input
                                type="number"
                                class="form-control"
                                id="jumlah"
                                value="1"
                                min="1"
                                oninput="hitungTotal()"
                            >

                        </div>


                        <!-- SUBTOTAL -->

                        <div class="col-md-2 mb-3">

                            <label class="form-label fw-bold">
                                Subtotal
                            </label>

                            <input
                                type="number"
                                class="form-control"
                                id="total"
                                value="0"
                                readonly
                            >

                        </div>


                        <!-- TAMBAH -->

                        <div class="col-md-2 mb-3 d-flex align-items-end">

                            <button
                                type="button"
                                class="btn btn-primary w-100"
                                onclick="tambahBarang()"
                            >
                                + Tambah
                            </button>

                        </div>

                    </div>

                </div>

            </div>


            <!-- DAFTAR BELANJA -->

            <div class="card shadow-sm mb-4">

                <div class="card-header bg-dark text-white">

                    <b>🛍️ Daftar Belanja</b>

                </div>


                <div class="card-body">

                    <div class="table-responsive">

                        <table
                            class="table table-bordered table-hover align-middle"
                        >

                            <thead class="table-secondary">

                                <tr>

                                    <th width="60">
                                        No
                                    </th>

                                    <th>
                                        Barang
                                    </th>

                                    <th>
                                        Harga
                                    </th>

                                    <th width="100">
                                        Jumlah
                                    </th>

                                    <th>
                                        Subtotal
                                    </th>

                                    <th width="100">
                                        Aksi
                                    </th>

                                </tr>

                            </thead>


                            <tbody id="daftarBelanja">

                                <tr>

                                    <td
                                        colspan="6"
                                        class="text-center text-muted py-4"
                                    >
                                        Belum ada transaksi
                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>


<!-- PEMBAYARAN -->

<div class="card shadow-sm">

    <div class="card-body">

        <div class="row">

            <div class="col-md-4 mb-3">

                <div class="total-box">

                    <div class="text-muted">
                        Total Bayar
                    </div>

                    <div class="total-harga">

                        Rp
                        <span id="totalBayar">
                            0
                        </span>

                    </div>

                </div>

            </div>


            <div class="col-md-4 mb-3">

                <label class="form-label fw-bold">
                    Bayar
                </label>

                <input
                    type="number"
                    class="form-control form-control-lg"
                    id="bayar"
                    placeholder="Masukkan uang"
                    min="0"
                    oninput="hitungKembalian()"
                >

            </div>


            <div class="col-md-4 mb-3">

                <label class="form-label fw-bold">
                    Kembalian
                </label>

                <input
                    type="text"
                    class="form-control form-control-lg"
                    id="kembalian"
                    value="Rp 0"
                    readonly
                >

            </div>

        </div>


        <hr>


        <div class="d-flex gap-2">

            <!-- TOMBOL BAYAR -->

            <button
                type="button"
                class="btn btn-success px-4"
                onclick="simpanTransaksi()"
            >
                Simpan Transaksi
            </button>


            <!-- TOMBOL BATAL -->

            <button
                type="button"
                class="btn btn-danger px-4"
                onclick="batalTransaksi()"
            >
                ✕ Batal
            </button>

        </div>

    </div>

</div>



        </div>

    </div>

</div>


<!-- JAVASCRIPT -->

<script>

let keranjang = [];


// HITUNG HARGA

function hitungHarga() {

    const produk =
        document.getElementById("produk");

    const pilihan =
        produk.options[produk.selectedIndex];

    const harga =
        parseInt(
            pilihan.getAttribute("data-harga")
        ) || 0;

    document.getElementById("harga").value =
        harga;

    hitungTotal();
}

// HITUNG SUBTOTAL

function hitungTotal() {

    const harga =
        parseInt(
            document.getElementById("harga").value
        ) || 0;

    const jumlah =
        parseInt(
            document.getElementById("jumlah").value
        ) || 0;

    document.getElementById("total").value =
        harga * jumlah;
}


// TAMBAH BARANG

function tambahBarang() {

    const produk =
        document.getElementById("produk");

    const pilihan =
        produk.options[produk.selectedIndex];


    if (produk.value === "") {

        alert(
            "Silakan pilih barang terlebih dahulu!"
        );

        return;
    }


    const id_produk =
        produk.value;

    const nama =
        pilihan.getAttribute("data-nama");

    const harga =
        parseInt(
            pilihan.getAttribute("data-harga")
        ) || 0;

    const stok =
        parseInt(
            pilihan.getAttribute("data-stok")
        ) || 0;

    const jumlah =
        parseInt(
            document.getElementById("jumlah").value
        ) || 0;


    if (jumlah < 1) {

        alert(
            "Jumlah barang minimal 1!"
        );

        return;
    }


    const index =
        keranjang.findIndex(
            item =>
                item.id_produk === id_produk
        );


    let jumlahSekarang = jumlah;


    if (index !== -1) {

        jumlahSekarang =
            keranjang[index].jumlah +
            jumlah;

    }


    if (jumlahSekarang > stok) {

        alert(
            "Stok " +
            nama +
            " hanya tersedia " +
            stok +
            " barang."
        );

        return;
    }


    if (index !== -1) {

        keranjang[index].jumlah =
            jumlahSekarang;

        keranjang[index].subtotal =
            keranjang[index].harga *
            keranjang[index].jumlah;

    } else {

        keranjang.push({

            id_produk: id_produk,

            nama: nama,

            harga: harga,

            jumlah: jumlah,

            subtotal: harga * jumlah

        });

    }


    tampilkanKeranjang();


    produk.value = "";

    document.getElementById("harga").value =
        0;

    document.getElementById("jumlah").value =
        1;

    document.getElementById("total").value =
        0;
}


// TAMPILKAN KERANJANG

function tampilkanKeranjang() {

    const tbody =
        document.getElementById(
            "daftarBelanja"
        );

    tbody.innerHTML = "";


    if (keranjang.length === 0) {

        tbody.innerHTML = `

            <tr>

                <td
                    colspan="6"
                    class="text-center text-muted py-4"
                >
                    Belum ada transaksi
                </td>

            </tr>

        `;

        document.getElementById(
            "totalBayar"
        ).innerText = "0";

        hitungKembalian();

        return;
    }


    let totalSemua = 0;


    keranjang.forEach(
        (item, index) => {

            totalSemua +=
                item.subtotal;


            tbody.innerHTML += `

                <tr>

                    <td>
                        ${index + 1}
                    </td>

                    <td>
                        <strong>
                            ${item.nama}
                        </strong>
                    </td>

                    <td>
                        Rp
                        ${formatRupiah(item.harga)}
                    </td>

                    <td>
                        ${item.jumlah}
                    </td>

                    <td>
                        <strong>
                            Rp
                            ${formatRupiah(item.subtotal)}
                        </strong>
                    </td>

                    <td>

                        <button
                            type="button"
                            class="btn btn-danger btn-sm"
                            onclick="hapusBarang(${index})"
                        >
                            Hapus
                        </button>

                    </td>

                </tr>

            `;

        }
    );


    document.getElementById(
        "totalBayar"
    ).innerText =
        formatRupiah(totalSemua);


    hitungKembalian();
}


// HAPUS BARANG

function hapusBarang(index) {

    keranjang.splice(
        index,
        1
    );

    tampilkanKeranjang();
}


// FORMAT RUPIAH


function formatRupiah(angka) {

    return Number(angka)
        .toLocaleString("id-ID");
}


// HITUNG KEMBALIAN

function hitungKembalian() {

    let total = 0;


    keranjang.forEach(
        item => {

            total +=
                item.subtotal;

        }
    );


    const bayar =
        parseInt(
            document.getElementById(
                "bayar"
            ).value
        ) || 0;


    const kembali =
        bayar - total;


    if (bayar === 0) {

        document.getElementById(
            "kembalian"
        ).value =
            "Rp 0";

        return;
    }


    if (kembali < 0) {

        document.getElementById(
            "kembalian"
        ).value =
            "Uang kurang Rp " +
            formatRupiah(
                Math.abs(kembali)
            );

    } else {

        document.getElementById(
            "kembalian"
        ).value =
            "Rp " +
            formatRupiah(kembali);
    }
}


// BATAL TRANSAKSI

function batalTransaksi() {

    if (keranjang.length === 0) {

        document.getElementById(
            "bayar"
        ).value = "";

        document.getElementById(
            "kembalian"
        ).value =
            "Rp 0";

        return;
    }


    if (
        !confirm(
            "Yakin ingin membatalkan transaksi?"
        )
    ) {
        return;
    }


    keranjang = [];


    document.getElementById(
        "bayar"
    ).value = "";


    document.getElementById(
        "kembalian"
    ).value =
        "Rp 0";


    tampilkanKeranjang();
}


// SIMPAN TRANSAKSI

function simpanTransaksi() {

    if (keranjang.length === 0) {

        alert(
            "Belum ada barang yang dibeli!"
        );

        return;
    }


    let total = 0;


    keranjang.forEach(
        item => {

            total +=
                item.subtotal;

        }
    );


    const bayar =
        parseInt(
            document.getElementById(
                "bayar"
            ).value
        ) || 0;


    if (bayar < total) {

        alert(
            "Uang pembayaran masih kurang!"
        );

        return;
    }


    if (
        !confirm(
            "Simpan transaksi ini?"
        )
    ) {
        return;
    }


    const formData =
        new FormData();


    formData.append(
        "aksi",
        "simpan_transaksi"
    );


    formData.append(
        "keranjang",
        JSON.stringify(
            keranjang
        )
    );


    formData.append(
        "bayar",
        bayar
    );



    //KIRIM DATA KE PHP

    fetch(
        "transaksi.php",
        {
            method: "POST",
            body: formData
        }
    )

    .then(
        response => {

            return response.json();

        }
    )

    .then(
        data => {

            if (data.success) {

                const kembali =
                    data.kembalian;


                alert(
                    "Transaksi berhasil!\n\n" +

                    "No. Transaksi : " +
                    data.id_transaksi +

                    "\nTotal : Rp " +
                    formatRupiah(
                        data.total
                    ) +

                    "\nBayar : Rp " +
                    formatRupiah(
                        data.bayar
                    ) +

                    "\nKembalian : Rp " +
                    formatRupiah(
                        kembali
                    )
                );


                keranjang = [];


                document.getElementById(
                    "bayar"
                ).value = "";


                document.getElementById(
                    "kembalian"
                ).value =
                    "Rp 0";


                tampilkanKeranjang();


                //REFRESH PRODUK AGAR STOK TERBARU MUNCUL


                setTimeout(
                    function () {

                        location.reload();

                    },
                    500
                );


            } else {

                alert(
                    "Transaksi gagal!\n\n" +
                    data.message
                );

            }

        }
    )

    .catch(
        error => {

            console.error(
                error
            );

            alert(
                "Terjadi kesalahan saat menghubungi server."
            );

        }
    );
}

</script>

</body>

</html>