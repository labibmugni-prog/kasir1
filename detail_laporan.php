<?php

session_start();

include 'koneksi.php';

// Cek login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}


// =============================
// CEK ID TRANSAKSI
// =============================

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: laporan.php");
    exit;
}

$id_transaksi = (int) $_GET['id'];


// =============================
// AMBIL DATA TRANSAKSI
// =============================

$query_transaksi = mysqli_query(
    $conn,
    "SELECT
        Id_transaksi,
        Tanggal,
        Total,
        Bayar,
        Kembalian
     FROM transaksi
     WHERE Id_transaksi = $id_transaksi"
);

$data_transaksi = mysqli_fetch_assoc($query_transaksi);

if (!$data_transaksi) {
    echo "Transaksi tidak ditemukan.";
    exit;
}


// =============================
// AMBIL DETAIL BARANG
// =============================

$query_detail = mysqli_query(
    $conn,
    "SELECT
        d.Id_produk,
        d.Harga,
        d.Jumlah,
        d.Subtotal,
        p.Nama_produk
     FROM detail_transaksi AS d
     LEFT JOIN produk AS p
        ON d.Id_produk = p.Id_produk
     WHERE d.Id_transaksi = $id_transaksi
     ORDER BY d.Id_detail ASC"
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

    <title>Detail Laporan - Dulur Cafe</title>


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

        body {
            background-color: #f5f5f5;
        }


        /* =========================
           NAVBAR
        ========================= */

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

            background: rgba(
                255,
                255,
                255,
                0.18
            );

            border: 1px solid rgba(
                255,
                255,
                255,
                0.25
            );

            backdrop-filter: blur(8px);

            display: flex;

            align-items: center;

            justify-content: center;

            color: white;

            box-shadow:
                0 5px 15px rgba(
                    0,
                    0,
                    0,
                    0.12
                );
        }


        .brand-logo i {
            font-size: 21px;
        }


        .brand-name {

            font-size: 18px;

            font-weight: 700;

            letter-spacing: 0.2px;

            line-height: 1;
        }


        .brand-subtitle {

            font-size: 10px;

            opacity: 0.75;

            letter-spacing: 1.2px;

            text-transform: uppercase;

            margin-top: 4px;
        }


        /* =========================
           USER
        ========================= */

        .user-info {

            background: rgba(
                255,
                255,
                255,
                0.12
            );

            border: 1px solid rgba(
                255,
                255,
                255,
                0.15
            );

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


        /* =========================
           SIDEBAR
        ========================= */

        .sidebar {

            min-height: calc(
                100vh - 68px
            );

            background: #171a1f !important;

            display: flex;

            flex-direction: column;
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
                0 5px 15px
                rgba(
                    12,
                    166,
                    120,
                    .20
                );
        }


        /* =========================
           LOGOUT
        ========================= */

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
                0 5px 12px
                rgba(
                    0,
                    0,
                    0,
                    .25
                );
        }


        /* =========================
           CARD
        ========================= */

        .card {

            border: none;

            border-radius: 12px;
        }


        .info-box {

            background: #f8f9fa;

            border-radius: 10px;

            padding: 15px;

            border: 1px solid #e9ecef;
        }


        /* =========================
           TABLE
        ========================= */

        .table th {

            white-space: nowrap;
        }


        /* =========================
           MOBILE
        ========================= */

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


        /* =========================
           PRINT
        ========================= */

        @media print {

            .sidebar,
            .navbar,
            .btn-print,
            .btn-back {

                display: none !important;
            }


            .col-md-9,
            .col-lg-10 {

                width: 100% !important;
            }


            body {

                background: white;
            }


            .card {

                box-shadow: none !important;

                border: 1px solid #ddd;
            }

        }

    </style>

</head>


<body>


<!-- =========================
     NAVBAR
========================= -->

<nav class="navbar navbar-dark modern-navbar shadow-sm">

    <div class="container-fluid px-3 px-md-4">


        <!-- BRAND -->

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


        <!-- USER -->

        <div class="d-flex align-items-center">

            <div class="user-info d-flex align-items-center gap-2">

                <div class="user-icon">

                    <i class="bi bi-person-fill"></i>

                </div>


                <div class="welcome-text text-white small">

                    <div style="font-size: 10px; opacity: .7;">

                        Selamat datang

                    </div>


                    <div class="fw-semibold">

                        <?= htmlspecialchars(
                            $_SESSION['nama'] ?? 'Admin'
                        ); ?>

                    </div>

                </div>

            </div>

        </div>

    </div>

</nav>



<!-- =========================
     CONTAINER
========================= -->

<div class="container-fluid">

    <div class="row">


        <!-- =========================
             SIDEBAR
        ========================= -->

        <div class="col-md-3 col-lg-2 sidebar p-3">


            <div>

                <hr class="text-secondary">


                <!-- TRANSAKSI -->

                <a
                    href="index.php"
                    class="sidebar-menu"
                >

                    <i class="bi bi-cart3"></i>

                    <span>
                        Transaksi
                    </span>

                </a>


                <!-- DATA PRODUK -->

                <a
                    href="produk.php"
                    class="sidebar-menu"
                >

                    <i class="bi bi-box-seam"></i>

                    <span>
                        Data Produk
                    </span>

                </a>


                <!-- LAPORAN -->

                <a
                    href="laporan.php"
                    class="sidebar-menu active"
                >

                    <i class="bi bi-bar-chart-line"></i>

                    <span>
                        Laporan
                    </span>

                </a>

            </div>


            <!-- LOGOUT -->

            <div class="mt-auto pt-3">

                <a
                    href="logout.php"
                    class="sidebar-logout"
                    onclick="return confirm('Yakin ingin keluar dari sistem kasir?');"
                >

                    <i class="bi bi-box-arrow-left"></i>

                    <span>
                        Logout
                    </span>

                </a>

            </div>

        </div>



        <!-- =========================
             CONTENT
        ========================= -->

        <div class="col-md-9 col-lg-10 p-4">


            <!-- =========================
                 HEADER
            ========================= -->

            <div class="d-flex justify-content-between align-items-center mb-4">

                <div>

                    <h2 class="fw-bold mb-1">

                        <i class="bi bi-receipt-cutoff"></i>

                        Detail Transaksi

                    </h2>


                    <p class="text-muted mb-0">

                        Informasi lengkap transaksi Dulur Cafe

                    </p>

                </div>


                <div>

                    <a
                        href="laporan.php"
                        class="btn btn-secondary btn-back me-1"
                    >

                        <i class="bi bi-arrow-left me-1"></i>

                        Kembali

                    </a>


                    <button
                        type="button"
                        class="btn btn-dark btn-print"
                        onclick="window.print()"
                    >

                        <i class="bi bi-printer-fill me-1"></i>

                        Cetak

                    </button>

                </div>

            </div>



            <!-- =========================
                 INFORMASI TRANSAKSI
            ========================= -->

            <div class="card shadow mb-4">

                <div class="card-header bg-success text-white fw-bold">

                    <i class="bi bi-info-circle-fill me-1"></i>

                    Informasi Transaksi

                </div>


                <div class="card-body">

                    <div class="row">


                        <!-- ID TRANSAKSI -->

                        <div class="col-md-4 mb-3">

                            <div class="info-box">

                                <small class="text-muted">

                                    ID Transaksi

                                </small>


                                <h5 class="mb-0 mt-1">

                                    <span class="badge text-bg-primary">

                                        #<?= $data_transaksi['Id_transaksi']; ?>

                                    </span>

                                </h5>

                            </div>

                        </div>



                        <!-- TANGGAL -->

                        <div class="col-md-4 mb-3">

                            <div class="info-box">

                                <small class="text-muted">

                                    Tanggal

                                </small>


                                <h5 class="mb-0 mt-1">

                                    <i class="bi bi-calendar3 me-1"></i>

                                    <?= date(
                                        'd-m-Y H:i',
                                        strtotime(
                                            $data_transaksi['Tanggal']
                                        )
                                    ); ?>

                                </h5>

                            </div>

                        </div>



                        <!-- TOTAL -->

                        <div class="col-md-4 mb-3">

                            <div class="info-box">

                                <small class="text-muted">

                                    Total

                                </small>


                                <h5 class="text-success mb-0 mt-1">

                                    Rp
                                    <?= number_format(
                                        $data_transaksi['Total'],
                                        0,
                                        ',',
                                        '.'
                                    ); ?>

                                </h5>

                            </div>

                        </div>

                    </div>

                </div>

            </div>



            <!-- =========================
                 DETAIL BARANG
            ========================= -->

            <div class="card shadow mb-4">

                <div class="card-header bg-dark text-white fw-bold">

                    <i class="bi bi-bag-check-fill me-1"></i>

                    Barang yang Dibeli

                </div>


                <div class="card-body">

                    <div class="table-responsive">

                        <table
                            class="table table-bordered table-hover align-middle"
                        >

                            <thead class="table-dark">

                                <tr>

                                    <th width="60">
                                        No
                                    </th>

                                    <th>
                                        Nama Barang
                                    </th>

                                    <th>
                                        Harga
                                    </th>

                                    <th>
                                        Jumlah
                                    </th>

                                    <th>
                                        Subtotal
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                            <?php

                            $no = 1;

                            if (
                                mysqli_num_rows(
                                    $query_detail
                                ) > 0
                            ) {

                                while (
                                    $detail =
                                    mysqli_fetch_assoc(
                                        $query_detail
                                    )
                                ) {

                            ?>

                                <tr>

                                    <td>

                                        <?= $no++; ?>

                                    </td>


                                    <td>

                                        <strong>

                                            <?= htmlspecialchars(
                                                $detail['Nama_produk'] ?? 'Produk tidak ditemukan'
                                            ); ?>

                                        </strong>

                                    </td>


                                    <td>

                                        Rp
                                        <?= number_format(
                                            $detail['Harga'],
                                            0,
                                            ',',
                                            '.'
                                        ); ?>

                                    </td>


                                    <td>

                                        <?= $detail['Jumlah']; ?>

                                        barang

                                    </td>


                                    <td>

                                        <b class="text-success">

                                            Rp
                                            <?= number_format(
                                                $detail['Subtotal'],
                                                0,
                                                ',',
                                                '.'
                                            ); ?>

                                        </b>

                                    </td>

                                </tr>


                            <?php

                                }

                            } else {

                            ?>

                                <tr>

                                    <td
                                        colspan="5"
                                        class="text-center py-4"
                                    >

                                        <i
                                            class="bi bi-inbox fs-3 d-block mb-2"
                                        ></i>

                                        Belum ada detail barang

                                    </td>

                                </tr>

                            <?php

                            }

                            ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>



            <!-- =========================
                 PEMBAYARAN
            ========================= -->

            <div class="card shadow">

                <div class="card-header bg-success text-white fw-bold">

                    <i class="bi bi-cash-coin me-1"></i>

                    Pembayaran

                </div>


                <div class="card-body">

                    <div class="row">


                        <!-- TOTAL -->

                        <div class="col-md-4 mb-3">

                            <div class="info-box">

                                <p class="text-muted mb-1">

                                    Total

                                </p>


                                <h4 class="text-success mb-0">

                                    Rp
                                    <?= number_format(
                                        $data_transaksi['Total'],
                                        0,
                                        ',',
                                        '.'
                                    ); ?>

                                </h4>

                            </div>

                        </div>



                        <!-- BAYAR -->

                        <div class="col-md-4 mb-3">

                            <div class="info-box">

                                <p class="text-muted mb-1">

                                    Uang Bayar

                                </p>


                                <h4 class="mb-0">

                                    Rp
                                    <?= number_format(
                                        $data_transaksi['Bayar'],
                                        0,
                                        ',',
                                        '.'
                                    ); ?>

                                </h4>

                            </div>

                        </div>



                        <!-- KEMBALIAN -->

                        <div class="col-md-4 mb-3">

                            <div class="info-box">

                                <p class="text-muted mb-1">

                                    Kembalian

                                </p>


                                <h4 class="mb-0">

                                    Rp
                                    <?= number_format(
                                        $data_transaksi['Kembalian'],
                                        0,
                                        ',',
                                        '.'
                                    ); ?>

                                </h4>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


        </div>

    </div>

</div>


<!-- =========================
     BOOTSTRAP JS
========================= -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>
