<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$query_produk = mysqli_query(
    $conn,
    "SELECT 
        Id_produk,
        Nama_produk AS nama_produk,
        Harga AS harga,
        Stok AS stok
     FROM produk
     ORDER BY Nama_produk ASC"
);

if (!$query_produk) {
    die("Query produk gagal: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dulur Cafe - Transaksi</title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Bootstrap Icons -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f5f5f5;
            font-family: Arial, sans-serif;
        }

        /* =========================================
           NAVBAR - SAMA SEPERTI PRODUK
        ========================================= */

        .modern-navbar {
            background: linear-gradient(
                135deg,
                #087f5b,
                #0ca678
            ) !important;

            min-height: 68px;
        }

        .brand-logo {
            width: 42px;
            height: 42px;
            border-radius: 12px;

            background: rgba(255,255,255,.18);
            border: 1px solid rgba(255,255,255,.25);

            backdrop-filter: blur(8px);

            display: flex;
            align-items: center;
            justify-content: center;

            color: white;

            box-shadow:
                0 5px 15px rgba(0,0,0,.12);
        }

        .brand-logo i {
            font-size: 21px;
        }

        .brand-name {
            font-size: 18px;
            font-weight: 700;
            letter-spacing: .2px;
            line-height: 1;
        }

        .brand-subtitle {
            font-size: 10px;
            opacity: .75;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            margin-top: 4px;
        }

        .user-info {
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.15);

            padding: 7px 12px;
            border-radius: 12px;

            backdrop-filter: blur(8px);
        }

        .user-icon {
            width: 30px;
            height: 30px;

            border-radius: 50%;

            background: white;
            color: #087f5b;

            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* =========================================
           SIDEBAR - SAMA SEPERTI PRODUK
        ========================================= */

        .sidebar {
            min-height: calc(100vh - 68px);

            background: #171a1f !important;

            display: flex;
            flex-direction: column;
        }

        .sidebar-title {
            color: #ffffff;

            font-size: 14px;
            font-weight: 600;

            display: flex;
            align-items: center;

            gap: 10px;

            padding: 10px 12px;
        }

        .sidebar-title i {
            color: #20c997;
        }

        .sidebar-menu {
            display: flex;
            align-items: center;

            gap: 12px;

            width: 100%;

            padding: 11px 13px;
            margin-bottom: 6px;

            color: #adb5bd;

            text-decoration: none;

            border-radius: 10px;

            font-size: 14px;
            font-weight: 500;

            transition: all .2s ease;
        }

        .sidebar-menu i {
            font-size: 17px;
            width: 20px;
            text-align: center;
        }

        .sidebar-menu:hover {
            background: #242930;
            color: #ffffff;

            transform: translateX(2px);
        }

        .sidebar-menu.active {
            background: linear-gradient(
                135deg,
                #087f5b,
                #0ca678
            );

            color: #ffffff;

            box-shadow:
                0 5px 15px rgba(12,166,120,.20);
        }

        .sidebar-logout {
            display: flex;
            align-items: center;

            gap: 12px;

            width: 100%;

            padding: 11px 13px;

            color: #ced4da;

            background: #343a40;

            border: 1px solid #495057;

            border-radius: 10px;

            text-decoration: none;

            font-size: 14px;
            font-weight: 500;

            transition: all .2s ease;
        }

        .sidebar-logout i {
            font-size: 17px;
            width: 20px;
            text-align: center;
        }

        .sidebar-logout:hover {
            background: #495057;
            color: #ffffff;

            border-color: #6c757d;

            transform: translateY(-1px);

            box-shadow:
                0 5px 12px rgba(0,0,0,.25);
        }

        /* =========================================
           CONTENT
        ========================================= */

        .main-content {
            padding: 24px;
        }

        .judul {
            font-weight: 700;
            color: #212529;
        }

        .page-title {
            font-weight: 700;
            color: #212529;
        }

        /* =========================================
           CARD
        ========================================= */

        .card {
            border: none;

            border-radius: 12px;

            overflow: hidden;
        }

        .card-custom {
            border: none;

            border-radius: 12px;

            box-shadow:
                0 4px 15px rgba(0,0,0,.06);
        }

        .card-header-hijau,
        .card-header-custom {
            background: linear-gradient(
                135deg,
                #087f5b,
                #0ca678
            );

            color: white;

            font-weight: 600;

            padding: 14px 18px;

            border: none;
        }

        /* =========================================
           FORM
        ========================================= */

        .form-control,
        .form-select {
            border-radius: 9px;

            padding: 10px 12px;

            border: 1px solid #dee2e6;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #087f5b;

            box-shadow:
                0 0 0 .2rem rgba(8,127,91,.15);
        }

        .btn {
            border-radius: 9px;
        }

        .btn-success {
            background: #087f5b;
            border-color: #087f5b;
        }

        .btn-success:hover {
            background: #066b4d;
            border-color: #066b4d;
        }

        /* =========================================
           TABLE
        ========================================= */

        .table {
            vertical-align: middle;
        }

        .table th {
            background-color: #e9ecef;
        }

        .table thead th {
            white-space: nowrap;
        }

        .empty-cart {
            text-align: center;

            color: #6c757d;

            padding: 35px 10px;
        }

        .empty-cart i {
            font-size: 40px;

            display: block;

            margin-bottom: 10px;
        }

        /* =========================================
           TOTAL
        ========================================= */

        .total-box {
            background: #f8f9fa;

            border-radius: 12px;

            padding: 18px;

            margin-top: 15px;

            border: 1px solid #e9ecef;
        }

        .total-label {
            color: #6c757d;

            font-size: 14px;
        }

        .total-value {
            color: #087f5b;

            font-size: 28px;

            font-weight: 700;
        }

        /* =========================================
           NOTIFIKASI MODERN
        ========================================= */

        #notifOverlay,
        #confirmOverlay,
        #logoutOverlay {

            display: none;

            position: fixed;

            inset: 0;

            width: 100%;
            height: 100%;

            background: rgba(0,0,0,.55);

            backdrop-filter: blur(4px);

            z-index: 100000;

            justify-content: center;
            align-items: center;

            padding: 20px;
        }

        #notifBox,
        #confirmBox,
        #logoutBox {

            width: 430px;

            max-width: 95%;

            background: white;

            padding: 30px;

            border-radius: 20px;

            text-align: center;

            box-shadow:
                0 20px 60px rgba(0,0,0,.30);

            animation: munculModal .25s ease;
        }

        .notifIcon,
        .confirmIcon,
        .logoutIcon {

            width: 75px;
            height: 75px;

            margin: 0 auto 17px;

            border-radius: 50%;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 38px;

            font-weight: bold;
        }

        .notifIcon {
            background: #d1e7dd;
            color: #198754;
        }

        .confirmIcon {
            background: #fff3cd;
            color: #856404;
        }

        .logoutIcon {
            background: #f8d7da;
            color: #842029;
        }

        #notifBox h3,
        #confirmBox h3,
        #logoutBox h3 {

            color: #212529;

            font-weight: 700;

            margin-bottom: 12px;
        }

        #isiNotif,
        #isiConfirm,
        #isiLogout {

            color: #6c757d;

            line-height: 1.6;

            font-size: 15px;
        }

        @keyframes munculModal {

            from {
                opacity: 0;

                transform:
                    scale(.8)
                    translateY(15px);
            }

            to {
                opacity: 1;

                transform:
                    scale(1)
                    translateY(0);
            }
        }

        /* =========================================
           MOBILE
        ========================================= */

        @media (max-width: 768px) {

            .sidebar {
                min-height: auto;
            }

            .main-content {
                padding: 15px;
            }

            .total-value {
                font-size: 23px;
            }

            #notifBox,
            #confirmBox,
            #logoutBox {
                padding: 25px 20px;
            }
        }

        @media (max-width: 576px) {

            .welcome-text {
                display: none;
            }

            .brand-subtitle {
                display: none;
            }

            .brand-name {
                font-size: 16px;
            }
        }

    </style>
</head>

<body>

<!-- =====================================================
     NAVBAR
===================================================== -->

<nav class="navbar navbar-dark modern-navbar shadow-sm">

    <div class="container-fluid px-3 px-md-4">

        <a
            href="index.php"
            class="navbar-brand d-flex align-items-center gap-2"
        >

            <div class="brand-logo">
                <i class="bi bi-cup-hot-fill"></i>
            </div>

            <div>

                <div class="brand-name">
                    Dulur Cafe
                </div>

                <div class="brand-subtitle">
                    Point of Sale
                </div>

            </div>

        </a>

        <div class="d-flex align-items-center">

            <div class="user-info d-flex align-items-center gap-2">

                <div class="user-icon">
                    <i class="bi bi-person-fill"></i>
                </div>

                <div class="welcome-text text-white small">

                    <div style="font-size:10px;opacity:.7;">
                        Selamat datang
                    </div>

                    <div class="fw-semibold">
                        <?= htmlspecialchars($_SESSION['nama'] ?? 'User'); ?>
                    </div>

                </div>

            </div>

        </div>

    </div>

</nav>


<!-- =====================================================
     LAYOUT
===================================================== -->

<div class="container-fluid">

    <div class="row">

        <!-- =================================================
             SIDEBAR
        ================================================== -->

        <div class="col-md-3 col-lg-2 sidebar p-3">

            <div>

                <div class="sidebar-title">
                    <i class="bi bi-grid-fill"></i>
                    Menu Utama
                </div>

                <a
                    href="index.php"
                    class="sidebar-menu active"
                >
                    <i class="bi bi-cart3"></i>
                    <span>Transaksi</span>
                </a>

                <a
                    href="produk.php"
                    class="sidebar-menu"
                >
                    <i class="bi bi-box-seam"></i>
                    <span>Data Produk</span>
                </a>

                <a
                    href="laporan.php"
                    class="sidebar-menu"
                >
                    <i class="bi bi-bar-chart-line"></i>
                    <span>Laporan</span>
                </a>

            </div>


            <div class="mt-auto pt-3">

                <a
                    href="#"
                    class="sidebar-logout"
                    onclick="bukaConfirmLogout(); return false;"
                >

                    <i class="bi bi-box-arrow-left"></i>

                    <span>
                        Logout
                    </span>

                </a>

            </div>

        </div>


        <!-- MAIN CONTENT -->

        <div class="col-md-9 col-lg-10 main-content">

            <div class="mb-4">

                <h2 class="judul mb-1">
                    <i class="bi bi-cart-check text-success"></i>
                    Transaksi
                </h2>

            </div>


            <!-- =================================================
                 TAMBAH BARANG
            ================================================== -->

            <div class="card shadow-sm mb-4">

                <div class="card-header card-header-hijau">

                    <i class="bi bi-plus-circle"></i>

                    Tambah Barang

                </div>

                <div class="card-body">

                    <div class="row g-3">

                        <!-- PRODUK -->

                        <div class="col-md-6">

                            <label
                                for="produk"
                                class="form-label fw-semibold"
                            >
                                Nama Produk
                            </label>

                            <select
                                id="produk"
                                class="form-select"
                                onchange="hitungHarga()"
                            >

                                <option value="">
                                    -- Pilih Produk --
                                </option>

                                <?php while ($produk = mysqli_fetch_assoc($query_produk)): ?>

                                   <option
                                      value="<?= $produk['Id_produk']; ?>"
                                      data-harga="<?= $produk['harga']; ?>"
                                      data-stok="<?= $produk['stok']; ?>"
>
                                      <?= htmlspecialchars($produk['nama_produk']); ?>
    
                                 
                                   </option>

                                <?php endwhile; ?>

                            </select>

                        </div>


                        <!-- HARGA -->

                        <div class="col-md-3">

                            <label class="form-label fw-semibold">
                                Harga
                            </label>

                            <input
                                type="text"
                                id="harga"
                                class="form-control"
                                value="Rp 0"
                                readonly
                            >

                        </div>


                        <!-- JUMLAH -->

                        <div class="col-md-3">

                            <label
                                for="jumlah"
                                class="form-label fw-semibold"
                            >
                                Jumlah
                            </label>

                            <input
                                type="number"
                                id="jumlah"
                                class="form-control"
                                min="1"
                                value="1"
                            >

                        </div>

                    </div>


                    <div class="mt-3">

                        <button
                            type="button"
                            class="btn btn-success px-4"
                            onclick="tambahBarang()"
                        >

                            <i class="bi bi-plus-lg"></i>

                            Tambah ke Keranjang

                        </button>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 KERANJANG
            ================================================== -->

            <div class="card shadow-sm mb-4">

                <div class="card-header card-header-hijau">

                    <i class="bi bi-basket"></i>

                    Keranjang Belanja

                </div>

                <div class="card-body">

                    <div class="table-responsive">

                        <table class="table table-hover">

                            <thead>

                                <tr>

                                    <th>No</th>

                                    <th>Produk</th>

                                    <th>Harga</th>

                                    <th>Jumlah</th>

                                    <th>Subtotal</th>

                                    <th>Aksi</th>

                                </tr>

                            </thead>

                            <tbody id="tabelKeranjang">

                                <tr>

                                    <td
                                        colspan="6"
                                        class="empty-cart"
                                    >

                                        <i class="bi bi-cart-x"></i>

                                        Keranjang masih kosong.

                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>


                    <!-- TOTAL -->

                    <div class="total-box">

                        <div class="d-flex justify-content-between align-items-center">

                            <div>

                                <div class="total-label">
                                    Total Pembayaran
                                </div>

                                <div class="total-value">

                                    Rp
                                    <span id="total">
                                        0
                                    </span>

                                </div>

                            </div>

                            <div>

                                <i
                                    class="bi bi-receipt-cutoff text-success"
                                    style="font-size:45px;"
                                ></i>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 PEMBAYARAN
            ================================================== -->

            <div class="card shadow-sm">

                <div class="card-header card-header-hijau">

                    <i class="bi bi-cash-coin"></i>

                    Pembayaran

                </div>

                <div class="card-body">

                    <div class="row g-3">

                        <!-- BAYAR -->

                        <div class="col-md-6">

                            <label
                                for="bayar"
                                class="form-label fw-semibold"
                            >
                                Uang Pembayaran
                            </label>

                            <input
                                type="number"
                                class="form-control"
                                id="bayar"
                                placeholder="Masukkan uang"
                                oninput="hitungKembalian()"
                            >

                        </div>


                        <!-- KEMBALIAN -->

                        <div class="col-md-6">

                            <label
                                for="kembalian"
                                class="form-label fw-semibold"
                            >
                                Kembalian
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                id="kembalian"
                                value="Rp 0"
                                readonly
                            >

                        </div>

                    </div>


                    <div class="d-flex gap-2 mt-4">

                        <button
                            type="button"
                            class="btn btn-success px-4"
                            onclick="simpanTransaksi()"
                        >

                            <i class="bi bi-check-circle"></i>

                            Bayar

                        </button>


                        <button
                            type="button"
                            class="btn btn-danger px-4"
                            onclick="batalTransaksi()"
                        >

                            <i class="bi bi-x-circle"></i>

                            Batal

                        </button>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


<!-- =====================================================
     NOTIFIKASI
===================================================== -->

<div id="notifOverlay">

    <div id="notifBox">

        <div
            class="notifIcon"
            id="notifIcon"
        >
            ✓
        </div>

        <h3 id="judulNotif">
            Berhasil!
        </h3>

        <div id="isiNotif"></div>

        <button
            type="button"
            class="btn btn-success mt-3 px-4"
            onclick="tutupNotif()"
        >

            <i class="bi bi-check-lg"></i>

            OK

        </button>

    </div>

</div>


<!-- =====================================================
     KONFIRMASI BATAL
===================================================== -->

<div id="confirmOverlay">

    <div id="confirmBox">

        <div class="confirmIcon">

            <i class="bi bi-exclamation-triangle"></i>

        </div>

        <h3>
            Batalkan Transaksi?
        </h3>

        <div id="isiConfirm">

            Apakah kamu yakin ingin
            membatalkan transaksi ini?

            <br>

            <small>
                Semua barang di keranjang akan dihapus.
            </small>

        </div>

        <div class="d-flex justify-content-center gap-2 mt-4">

            <button
                type="button"
                class="btn btn-light border px-4"
                onclick="tutupConfirm()"
            >

                <i class="bi bi-x-lg"></i>

                Tidak

            </button>

            <button
                type="button"
                class="btn btn-danger px-4"
                onclick="lanjutBatalTransaksi()"
            >

                <i class="bi bi-trash3"></i>

                Ya, Batalkan

            </button>

        </div>

    </div>

</div>


<!-- =====================================================
     KONFIRMASI LOGOUT
===================================================== -->

<div id="logoutOverlay">

    <div id="logoutBox">

        <div class="logoutIcon">

            <i class="bi bi-box-arrow-right"></i>

        </div>

        <h3>
            Keluar dari Sistem?
        </h3>

        <div id="isiLogout">

            Apakah kamu yakin ingin keluar
            dari sistem kasir?

        </div>

        <div class="d-flex justify-content-center gap-2 mt-4">

            <button
                type="button"
                class="btn btn-light border px-4"
                onclick="tutupConfirmLogout()"
            >

                <i class="bi bi-x-lg"></i>

                Batal

            </button>

            <button
                type="button"
                class="btn btn-danger px-4"
                onclick="lanjutLogout()"
            >

                <i class="bi bi-box-arrow-right"></i>

                Ya, Keluar

            </button>

        </div>

    </div>

</div>


<script>

/* =====================================================
   DATA KERANJANG
===================================================== */

let keranjang = [];


/* =====================================================
   NOTIFIKASI
===================================================== */

function tampilkanNotif(
    pesan,
    judul = "Berhasil!",
    tipe = "success"
) {

    const overlay =
        document.getElementById("notifOverlay");

    const isi =
        document.getElementById("isiNotif");

    const judulElement =
        document.getElementById("judulNotif");

    const icon =
        document.getElementById("notifIcon");


    judulElement.innerText = judul;

    isi.innerHTML = pesan;


    if (tipe === "danger") {

        icon.innerHTML =
            '<i class="bi bi-x-lg"></i>';

        icon.style.background = "#f8d7da";
        icon.style.color = "#842029";

    }

    else if (tipe === "warning") {

        icon.innerHTML =
            '<i class="bi bi-exclamation-triangle"></i>';

        icon.style.background = "#fff3cd";
        icon.style.color = "#856404";

    }

    else {

        icon.innerHTML =
            '<i class="bi bi-check-lg"></i>';

        icon.style.background = "#d1e7dd";
        icon.style.color = "#198754";

    }


    overlay.style.display = "flex";
}


function tutupNotif() {

    document.getElementById(
        "notifOverlay"
    ).style.display = "none";
}


/* =====================================================
   HITUNG HARGA
===================================================== */

function hitungHarga() {

    const produk =
        document.getElementById("produk");

    const option =
        produk.options[produk.selectedIndex];


    if (!produk.value) {

        document.getElementById(
            "harga"
        ).value = "Rp 0";

        return;
    }


    const harga =
        parseInt(option.dataset.harga) || 0;


    document.getElementById(
        "harga"
    ).value =
        "Rp " + formatRupiah(harga);
}


/* =====================================================
   HITUNG TOTAL
===================================================== */

function hitungTotal() {

    let total = 0;


    keranjang.forEach(function(item) {

        total += item.subtotal;

    });


    document.getElementById(
        "total"
    ).innerText =
        formatRupiah(total);


    return total;
}


/* =====================================================
   TAMBAH BARANG
===================================================== */

function tambahBarang() {

    const produk =
        document.getElementById("produk");

    const jumlahInput =
        document.getElementById("jumlah");


    if (produk.value === "") {

        tampilkanNotif(
            "Silakan pilih barang terlebih dahulu.",
            "Pilih Barang",
            "warning"
        );

        return;
    }


    let jumlah =
        parseInt(jumlahInput.value) || 0;


    if (jumlah < 1) {

        tampilkanNotif(
            "Jumlah barang minimal 1.",
            "Jumlah Tidak Valid",
            "warning"
        );

        return;
    }


    const option =
        produk.options[produk.selectedIndex];


    const id =
        produk.value;

    const nama =
        option.text.split(" - Rp")[0];

    const harga =
        parseInt(option.dataset.harga) || 0;

    const stok =
        parseInt(option.dataset.stok) || 0;


    if (jumlah > stok) {

        tampilkanNotif(
            `
                Stok produk tidak mencukupi.
                <br><br>
                <strong>Stok tersedia:</strong>
                ${stok}
            `,
            "Stok Tidak Cukup",
            "warning"
        );

        return;
    }


    const index =
        keranjang.findIndex(
            item => item.id == id
        );


    if (index !== -1) {

        const jumlahBaru =
            keranjang[index].jumlah + jumlah;


        if (jumlahBaru > stok) {

            tampilkanNotif(
                `
                    Jumlah barang melebihi stok.
                    <br><br>
                    <strong>Stok tersedia:</strong>
                    ${stok}
                `,
                "Stok Tidak Cukup",
                "warning"
            );

            return;
        }


        keranjang[index].jumlah =
            jumlahBaru;

        keranjang[index].subtotal =
            jumlahBaru * harga;

    }

    else {

        keranjang.push({

            id: id,

            nama: nama,

            harga: harga,

            jumlah: jumlah,

            subtotal: jumlah * harga

        });

    }


    tampilkanKeranjang();

    hitungKembalian();


    produk.value = "";

    document.getElementById(
        "harga"
    ).value = "Rp 0";

    jumlahInput.value = 1;
}


/* =====================================================
   TAMPILKAN KERANJANG
===================================================== */

function tampilkanKeranjang() {

    const tbody =
        document.getElementById(
            "tabelKeranjang"
        );


    tbody.innerHTML = "";


    if (keranjang.length === 0) {

        tbody.innerHTML = `

            <tr>

                <td
                    colspan="6"
                    class="empty-cart"
                >

                    <i class="bi bi-cart-x"></i>

                    Keranjang masih kosong.

                </td>

            </tr>

        `;


        hitungTotal();

        return;
    }


    keranjang.forEach(
        function(item, index) {

            tbody.innerHTML += `

                <tr>

                    <td>
                        ${index + 1}
                    </td>

                    <td>
                        ${item.nama}
                    </td>

                    <td>
                        Rp ${formatRupiah(item.harga)}
                    </td>

                    <td>
                        ${item.jumlah}
                    </td>

                    <td class="fw-semibold">
                        Rp ${formatRupiah(item.subtotal)}
                    </td>

                    <td>

                        <button
                            type="button"
                            class="btn btn-sm btn-danger"
                            onclick="hapusBarang(${index})"
                        >

                            <i class="bi bi-trash"></i>

                        </button>

                    </td>

                </tr>

            `;

        }
    );


    hitungTotal();
}


/* =====================================================
   HAPUS BARANG
===================================================== */

function hapusBarang(index) {

    if (
        index < 0 ||
        index >= keranjang.length
    ) {
        return;
    }


    const nama =
        keranjang[index].nama;


    keranjang.splice(index, 1);


    tampilkanKeranjang();

    hitungKembalian();


    tampilkanNotif(
        `
            Produk
            <strong>${nama}</strong>
            berhasil dihapus dari keranjang.
        `,
        "Barang Dihapus!",
        "success"
    );
}


/* =====================================================
   FORMAT RUPIAH
===================================================== */

function formatRupiah(angka) {

    return Number(angka).toLocaleString(
        "id-ID"
    );
}


/* =====================================================
   HITUNG KEMBALIAN
===================================================== */

function hitungKembalian() {

    const total =
        hitungTotal();


    const bayar =
        parseInt(
            document.getElementById(
                "bayar"
            ).value
        ) || 0;


    const kembalian =
        bayar - total;


    if (bayar === 0) {

        document.getElementById(
            "kembalian"
        ).value = "Rp 0";

        return;
    }


    if (kembalian < 0) {

        document.getElementById(
            "kembalian"
        ).value =
            "Uang kurang Rp " +
            formatRupiah(
                Math.abs(kembalian)
            );

    }

    else {

        document.getElementById(
            "kembalian"
        ).value =
            "Rp " +
            formatRupiah(
                kembalian
            );

    }
}


/* =====================================================
   SIMPAN TRANSAKSI
===================================================== */

async function simpanTransaksi() {

    if (keranjang.length === 0) {

        tampilkanNotif(
            "Belum ada barang yang dibeli.",
            "Transaksi Kosong",
            "warning"
        );

        return;
    }


    const total =
        hitungTotal();


    const bayar =
        parseInt(
            document.getElementById(
                "bayar"
            ).value
        ) || 0;


    /* PEMBAYARAN KURANG */

    if (bayar < total) {

        const kurang =
            total - bayar;


        tampilkanNotif(
            `
                <div class="text-center">

                    <div class="mb-2">
                        Uang pembayaran masih kurang.
                    </div>

                    <div>

                        <strong>
                            Kekurangan:
                        </strong>

                        <br>

                        <span
                            class="text-danger fs-5"
                        >
                            Rp ${formatRupiah(kurang)}
                        </span>

                    </div>

                </div>
            `,
            "Pembayaran Kurang!",
            "warning"
        );

        return;
    }


    try {

        const response =
            await fetch(
                "simpan_transaksi.php",
                {
                    method: "POST",

                    headers: {
                        "Content-Type":
                            "application/json"
                    },

                    body: JSON.stringify({

                        keranjang:
                            keranjang,

                        bayar:
                            bayar

                    })

                }
            );


        const hasil =
            await response.json();


        /* TRANSAKSI BERHASIL */

        if (hasil.status) {

            tampilkanNotif(
                `
                    <div class="text-start">

                        <div class="mb-2">

                            <strong>
                                ID Transaksi:
                            </strong>

                            #${hasil.id_transaksi}

                        </div>

                        <div class="mb-2">

                            <strong>
                                Total:
                            </strong>

                            Rp ${formatRupiah(
                                hasil.total
                            )}

                        </div>

                        <div class="mb-2">

                            <strong>
                                Bayar:
                            </strong>

                            Rp ${formatRupiah(
                                hasil.bayar
                            )}

                        </div>

                        <div>

                            <strong>
                                Kembalian:
                            </strong>

                            Rp ${formatRupiah(
                                hasil.kembalian
                            )}

                        </div>

                    </div>
                `,
                "Transaksi Berhasil!",
                "success"
            );


            keranjang = [];


            document.getElementById(
                "bayar"
            ).value = "";


            document.getElementById(
                "kembalian"
            ).value = "Rp 0";


            tampilkanKeranjang();

        }

        /* TRANSAKSI GAGAL */

        else {

            tampilkanNotif(
                hasil.pesan ||
                "Transaksi gagal disimpan.",
                "Transaksi Gagal!",
                "danger"
            );

        }

    }

    catch (error) {

        console.error(error);


        tampilkanNotif(
            `
                Terjadi kesalahan saat
                menyimpan transaksi.
                <br>
                Silakan coba lagi.
            `,
            "Terjadi Kesalahan!",
            "danger"
        );

    }
}


/* =====================================================
   BATAL TRANSAKSI
===================================================== */

function batalTransaksi() {

    if (keranjang.length === 0) {

        document.getElementById(
            "bayar"
        ).value = "";

        document.getElementById(
            "kembalian"
        ).value = "Rp 0";

        return;
    }


    bukaConfirmBatal();
}


/* =====================================================
   KONFIRMASI BATAL
===================================================== */

function bukaConfirmBatal() {

    const overlay =
        document.getElementById(
            "confirmOverlay"
        );


    overlay.style.display = "flex";
}


function tutupConfirm() {

    document.getElementById(
        "confirmOverlay"
    ).style.display = "none";
}


function lanjutBatalTransaksi() {

    keranjang = [];


    document.getElementById(
        "bayar"
    ).value = "";


    document.getElementById(
        "kembalian"
    ).value = "Rp 0";


    tampilkanKeranjang();


    tutupConfirm();


    tampilkanNotif(
        "Transaksi berhasil dibatalkan.",
        "Transaksi Dibatalkan!",
        "success"
    );
}


/* =====================================================
   KONFIRMASI LOGOUT
===================================================== */

function bukaConfirmLogout() {

    document.getElementById(
        "logoutOverlay"
    ).style.display = "flex";
}


function tutupConfirmLogout() {

    document.getElementById(
        "logoutOverlay"
    ).style.display = "none";
}


function lanjutLogout() {

    window.location.href =
        "logout.php";
}


/* =====================================================
   KLIK LUAR MODAL
===================================================== */

document.addEventListener(
    "click",
    function(event) {

        const notif =
            document.getElementById(
                "notifOverlay"
            );

        const confirm =
            document.getElementById(
                "confirmOverlay"
            );

        const logout =
            document.getElementById(
                "logoutOverlay"
            );


        if (event.target === notif) {

            tutupNotif();

        }


        if (event.target === confirm) {

            tutupConfirm();

        }


        if (event.target === logout) {

            tutupConfirmLogout();

        }

    }
);


/* =====================================================
   TOMBOL ESC
===================================================== */

document.addEventListener(
    "keydown",
    function(event) {

        if (event.key !== "Escape") {
            return;
        }


        const confirm =
            document.getElementById(
                "confirmOverlay"
            );

        const logout =
            document.getElementById(
                "logoutOverlay"
            );

        const notif =
            document.getElementById(
                "notifOverlay"
            );


        if (confirm.style.display === "flex") {

            tutupConfirm();

        }

        else if (logout.style.display === "flex") {

            tutupConfirmLogout();

        }

        else if (notif.style.display === "flex") {

            tutupNotif();

        }

    }
);


/* =====================================================
   TAMPILKAN KERANJANG SAAT HALAMAN DIBUKA
===================================================== */

tampilkanKeranjang();

</script>

</body>
</html>