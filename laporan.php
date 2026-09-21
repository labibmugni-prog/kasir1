<?php

session_start();

include 'koneksi.php';

// =============================
// CEK LOGIN
// =============================

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}


// =============================
// DATA RINGKASAN
// =============================

// Total transaksi
$query_jumlah_transaksi = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS jumlah FROM transaksi"
);

$data_jumlah_transaksi = mysqli_fetch_assoc(
    $query_jumlah_transaksi
);

$total_transaksi = $data_jumlah_transaksi['jumlah'];


// Total pendapatan
$query_pendapatan = mysqli_query(
    $conn,
    "SELECT COALESCE(SUM(Total), 0) AS pendapatan
     FROM transaksi"
);

$data_pendapatan = mysqli_fetch_assoc(
    $query_pendapatan
);

$total_pendapatan = $data_pendapatan['pendapatan'];


// Total barang terjual
$query_barang = mysqli_query(
    $conn,
    "SELECT COALESCE(SUM(Jumlah), 0) AS jumlah_barang
     FROM detail_transaksi"
);

$data_barang = mysqli_fetch_assoc(
    $query_barang
);

$total_barang = $data_barang['jumlah_barang'];


// =============================
// DATA LAPORAN TRANSAKSI
// =============================

$query_laporan = mysqli_query(
    $conn,
    "SELECT

        t.`Id_transaksi` AS id_transaksi,
        t.`Tanggal`,
        t.`Total`,
        t.`Bayar`,
        t.`Kembalian`,

        COALESCE(
            SUM(d.`Jumlah`), 0
        ) AS jumlah_barang

     FROM `transaksi` AS t

     LEFT JOIN `detail_transaksi` AS d
        ON t.`Id_transaksi` = d.`Id_transaksi`

     GROUP BY
        t.`Id_transaksi`,
        t.`Tanggal`,
        t.`Total`,
        t.`Bayar`,
        t.`Kembalian`

     ORDER BY
        t.`Tanggal` DESC"
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

    <title>Laporan - Dulur Cafe</title>


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

        /* =====================================================
           BODY
        ===================================================== */

        body {
            background-color: #f5f5f5;
        }


        /* =====================================================
           NOTIFIKASI MODERN
        ===================================================== */

        #notifOverlay,
        #confirmOverlay {

            display: none;

            position: fixed;

            inset: 0;

            width: 100%;
            height: 100%;

            background: rgba(0, 0, 0, 0.55);

            backdrop-filter: blur(3px);

            z-index: 99999;

            justify-content: center;

            align-items: center;

            padding: 20px;
        }


        /* Kotak notifikasi */

        #notifBox,
        #confirmBox {

            background: #ffffff;

            width: 400px;

            max-width: 100%;

            padding: 32px 30px;

            border-radius: 18px;

            text-align: center;

            box-shadow:
                0 20px 60px rgba(0, 0, 0, 0.25);

            animation: munculNotif .3s ease;

            position: relative;
        }


        /* Tombol X */

        .notifClose {

            position: absolute;

            top: 12px;

            right: 15px;

            border: none;

            background: transparent;

            color: #6c757d;

            font-size: 22px;

            width: 35px;

            height: 35px;

            border-radius: 50%;

            transition: .2s;
        }


        .notifClose:hover {

            background: #f1f3f5;

            color: #212529;
        }


        /* Icon sukses */

        .notifIcon {

            width: 70px;

            height: 70px;

            margin: 0 auto 18px;

            border-radius: 50%;

            background: #d1f7e5;

            color: #198754;

            font-size: 40px;

            font-weight: bold;

            display: flex;

            align-items: center;

            justify-content: center;

            animation: iconMasuk .35s ease;
        }


        /* Icon konfirmasi */

        .confirmIcon {

            width: 70px;

            height: 70px;

            margin: 0 auto 18px;

            border-radius: 50%;

            background: #fff3cd;

            color: #dc3545;

            font-size: 34px;

            display: flex;

            align-items: center;

            justify-content: center;

        }


        /* Judul sukses */

        #notifBox h3 {

            color: #198754;

            font-weight: 700;

            margin-bottom: 10px;
        }


        /* Judul konfirmasi */

        #confirmBox h3 {

            color: #212529;

            font-weight: 700;

            margin-bottom: 10px;
        }


        /* Isi */

        #isiNotif,
        #isiConfirm {

            color: #6c757d;

            font-size: 15px;

            line-height: 1.6;
        }


        /* Tombol */

        .notifButton {

            min-width: 100px;

            border-radius: 10px;

            padding: 9px 20px;

            font-weight: 600;
        }


        /* Animasi */

        @keyframes munculNotif {

            from {

                opacity: 0;

                transform: scale(.75) translateY(20px);
            }

            to {

                opacity: 1;

                transform: scale(1) translateY(0);
            }
        }


        @keyframes iconMasuk {

            from {

                transform: scale(.5);

                opacity: 0;
            }

            to {

                transform: scale(1);

                opacity: 1;
            }
        }


        /* =====================================================
           NAVBAR
        ===================================================== */

        .modern-navbar {

            background: linear-gradient(
                135deg,
                #087f5b,
                #0ca678
            ) !important;

            min-height: 68px;
        }


        /* Logo */

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


        /* =====================================================
           USER
        ===================================================== */

        .user-info {

            background: rgba(
                255,
                255,
                255,
                .12
            );

            border: 1px solid rgba(
                255,
                255,
                255,
                .15
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


        /* =====================================================
           SIDEBAR
        ===================================================== */

        .sidebar {

            min-height: calc(
                100vh - 68px
            );

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
                0 5px 15px
                rgba(
                    12,
                    166,
                    120,
                    .20
                );
        }


        /* =====================================================
           LOGOUT
        ===================================================== */

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


        /* =====================================================
           CARD
        ===================================================== */

        .card {

            border: none;

            border-radius: 12px;
        }


        .stat-card {

            transition: .2s;
        }


        .stat-card:hover {

            transform: translateY(-5px);
        }


        .stat-icon {

            font-size: 32px;
        }


        /* =====================================================
           TABLE
        ===================================================== */

        .table th {

            white-space: nowrap;
        }


        /* =====================================================
           MOBILE
        ===================================================== */

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


            #notifBox,
            #confirmBox {

                padding: 28px 20px;
            }

        }


        /* =====================================================
           PRINT
        ===================================================== */

        @media print {

            .sidebar,
            .navbar,
            .btn-print {

                display: none !important;
            }


            .col-md-9,
            .col-lg-10 {

                width: 100% !important;
            }


            body {

                background: white;
            }

        }

    </style>

</head>


<body>


<!-- =====================================================
     NOTIFIKASI BERHASIL
===================================================== -->

<div id="notifOverlay">

    <div id="notifBox">

        <button
            type="button"
            class="notifClose"
            onclick="tutupNotif()"
        >
            <i class="bi bi-x"></i>
        </button>


        <div class="notifIcon">

            <i class="bi bi-check-lg"></i>

        </div>


        <h3 id="judulNotif">
            Berhasil!
        </h3>


        <div id="isiNotif">
            Data berhasil diproses.
        </div>


        <button
            type="button"
            class="btn btn-success notifButton mt-4"
            onclick="tutupNotif()"
        >
            OK
        </button>

    </div>

</div>



<!-- =====================================================
     KONFIRMASI HAPUS
===================================================== -->

<div id="confirmOverlay">

    <div id="confirmBox">

        <button
            type="button"
            class="notifClose"
            onclick="tutupConfirm()"
        >
            <i class="bi bi-x"></i>
        </button>


        <div class="confirmIcon">

            <i class="bi bi-trash3"></i>

        </div>


        <h3>
            Hapus Transaksi?
        </h3>


        <div id="isiConfirm">

            Apakah kamu yakin ingin menghapus
            transaksi ini?

        </div>


        <div class="d-flex justify-content-center gap-2 mt-4">

            <button
                type="button"
                class="btn btn-light border notifButton"
                onclick="tutupConfirm()"
            >
                Batal
            </button>


            <button
                type="button"
                class="btn btn-danger notifButton"
                onclick="lanjutHapus()"
            >
                <i class="bi bi-trash3 me-1"></i>
                Hapus
            </button>

        </div>

    </div>

</div>



<!-- =====================================================
     NAVBAR
===================================================== -->

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

                    <div
                        style="
                            font-size: 10px;
                            opacity: .7;
                        "
                    >

                        Selamat datang

                    </div>


                    <div class="fw-semibold">

                        <?= htmlspecialchars(
                            $_SESSION['nama']
                        ); ?>

                    </div>

                </div>

            </div>

        </div>

    </div>

</nav>



<!-- =====================================================
     CONTAINER
===================================================== -->

<div class="container-fluid">

    <div class="row">


        <!-- =================================================
             SIDEBAR
        ================================================= -->

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
                    onclick="
                        return confirm(
                            'Yakin ingin keluar dari sistem kasir?'
                        );
                    "
                >

                    <i class="bi bi-box-arrow-left"></i>

                    <span>
                        Logout
                    </span>

                </a>

            </div>

        </div>



        <!-- =================================================
             CONTENT
        ================================================= -->

        <div class="col-md-9 col-lg-10 p-4">


            <!-- JUDUL -->

            <div
                class="
                    d-flex
                    justify-content-between
                    align-items-center
                    mb-4
                "
            >

                <div>

                    <h2 class="fw-bold mb-1">

                        <i class="bi bi-bar-chart-fill"></i>

                        Laporan Penjualan

                    </h2>


                    <p class="text-muted mb-0">

                        Riwayat transaksi Dulur Cafe

                    </p>

                </div>


                <!-- CETAK -->

                <button
                    type="button"
                    class="btn btn-dark btn-print"
                    onclick="window.print()"
                >

                    <i
                        class="
                            bi
                            bi-printer-fill
                            me-1
                        "
                    ></i>

                    Cetak Laporan

                </button>

            </div>



            <!-- =================================================
                 STATISTIK
            ================================================= -->

            <div class="row mb-4">


                <!-- TOTAL TRANSAKSI -->

                <div class="col-md-4 mb-3">

                    <div class="card shadow stat-card p-4">

                        <div
                            class="
                                d-flex
                                justify-content-between
                            "
                        >

                            <div>

                                <p class="text-muted mb-1">

                                    Total Transaksi

                                </p>


                                <h2 class="mb-0">

                                    <?= $total_transaksi; ?>

                                </h2>

                            </div>


                            <div class="stat-icon">

                                <i class="bi bi-receipt"></i>

                            </div>

                        </div>

                    </div>

                </div>



                <!-- BARANG TERJUAL -->

                <div class="col-md-4 mb-3">

                    <div class="card shadow stat-card p-4">

                        <div
                            class="
                                d-flex
                                justify-content-between
                            "
                        >

                            <div>

                                <p class="text-muted mb-1">

                                    Barang Terjual

                                </p>


                                <h2 class="mb-0">

                                    <?= $total_barang; ?>

                                </h2>

                            </div>


                            <div class="stat-icon">

                                <i class="bi bi-box-seam"></i>

                            </div>

                        </div>

                    </div>

                </div>



                <!-- PENDAPATAN -->

                <div class="col-md-4 mb-3">

                    <div class="card shadow stat-card p-4">

                        <div
                            class="
                                d-flex
                                justify-content-between
                            "
                        >

                            <div>

                                <p class="text-muted mb-1">

                                    Total Pendapatan

                                </p>


                                <h4 class="text-success mb-0">

                                    Rp

                                    <?= number_format(
                                        $total_pendapatan,
                                        0,
                                        ',',
                                        '.'
                                    ); ?>

                                </h4>

                            </div>


                            <div class="stat-icon">

                                <i class="bi bi-cash-coin"></i>

                            </div>

                        </div>

                    </div>

                </div>

            </div>



            <!-- =================================================
                 RIWAYAT TRANSAKSI
            ================================================= -->

            <div class="card shadow">

                <div class="card-header bg-dark text-white fw-bold">

                    <i
                        class="
                            bi
                            bi-clipboard-data
                            me-1
                        "
                    ></i>

                    Riwayat Transaksi

                </div>


                <div class="card-body">

                    <div class="table-responsive">

                        <table
                            class="
                                table
                                table-bordered
                                table-hover
                                align-middle
                            "
                        >

                            <thead>

                                <tr>

                                    <th width="60">
                                        No
                                    </th>

                                    <th>
                                        ID Transaksi
                                    </th>

                                    <th>
                                        Tanggal
                                    </th>

                                    <th>
                                        Jumlah Barang
                                    </th>

                                    <th>
                                        Total
                                    </th>

                                    <th>
                                        Bayar
                                    </th>

                                    <th>
                                        Kembalian
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                            <?php

                            $no = 1;

                            if (
                                mysqli_num_rows(
                                    $query_laporan
                                ) > 0
                            ) {

                                while (
                                    $data =
                                    mysqli_fetch_assoc(
                                        $query_laporan
                                    )
                                ) {

                            ?>

                                <tr>


                                    <!-- NO -->

                                    <td>

                                        <?= $no++; ?>

                                    </td>



                                    <!-- ID -->

                                    <td>

                                        <span
                                            class="
                                                badge
                                                text-bg-primary
                                            "
                                        >

                                            #<?= $data[
                                                'id_transaksi'
                                            ]; ?>

                                        </span>


                                        <!-- DETAIL -->

                                        <a
                                            href="
                                                detail_laporan.php?id=
                                                <?= $data[
                                                    'id_transaksi'
                                                ]; ?>
                                            "
                                            class="
                                                btn
                                                btn-sm
                                                btn-primary
                                                ms-2
                                            "
                                        >

                                            <i
                                                class="
                                                    bi
                                                    bi-eye-fill
                                                "
                                            ></i>

                                            Detail

                                        </a>


                                        <!-- HAPUS -->

                                        <button
                                            type="button"
                                            class="
                                                btn
                                                btn-sm
                                                btn-danger
                                                ms-1
                                            "
                                            onclick="
                                                bukaConfirm(
                                                    <?= $data[
                                                        'id_transaksi'
                                                    ]; ?>
                                                )
                                            "
                                        >

                                            <i
                                                class="
                                                    bi
                                                    bi-trash-fill
                                                "
                                            ></i>

                                            Hapus

                                        </button>

                                    </td>



                                    <!-- TANGGAL -->

                                    <td>

                                        <?= date(
                                            'd-m-Y H:i',
                                            strtotime(
                                                $data['Tanggal']
                                            )
                                        ); ?>

                                    </td>



                                    <!-- JUMLAH -->

                                    <td>

                                        <?= $data[
                                            'jumlah_barang'
                                        ]; ?>

                                        barang

                                    </td>



                                    <!-- TOTAL -->

                                    <td>

                                        <b class="text-success">

                                            Rp

                                            <?= number_format(
                                                $data['Total'],
                                                0,
                                                ',',
                                                '.'
                                            ); ?>

                                        </b>

                                    </td>



                                    <!-- BAYAR -->

                                    <td>

                                        Rp

                                        <?= number_format(
                                            $data['Bayar'],
                                            0,
                                            ',',
                                            '.'
                                        ); ?>

                                    </td>



                                    <!-- KEMBALIAN -->

                                    <td>

                                        Rp

                                        <?= number_format(
                                            $data['Kembalian'],
                                            0,
                                            ',',
                                            '.'
                                        ); ?>

                                    </td>


                                </tr>


                            <?php

                                }

                            } else {

                            ?>

                                <tr>

                                    <td
                                        colspan="7"
                                        class="
                                            text-center
                                            py-4
                                        "
                                    >

                                        <i
                                            class="
                                                bi
                                                bi-inbox
                                                fs-3
                                                d-block
                                                mb-2
                                            "
                                        ></i>

                                        Belum ada transaksi

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

        </div>

    </div>

</div>



<!-- =====================================================
     BOOTSTRAP JS
===================================================== -->

<script
    src="
        https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js
    "
></script>



<script>

/* =====================================================
   VARIABEL ID TRANSAKSI
===================================================== */

let idTransaksiHapus = null;


/* =====================================================
   NOTIFIKASI BERHASIL
===================================================== */

function tampilkanNotif(
    pesan,
    judul = "Berhasil!"
) {

    const overlay =
        document.getElementById(
            "notifOverlay"
        );

    const isi =
        document.getElementById(
            "isiNotif"
        );

    const judulElement =
        document.getElementById(
            "judulNotif"
        );


    if (
        !overlay ||
        !isi ||
        !judulElement
    ) {

        return;
    }


    judulElement.innerText =
        judul;


    isi.innerHTML =
        pesan;


    overlay.style.display =
        "flex";
}


/* =====================================================
   TUTUP NOTIFIKASI
===================================================== */

function tutupNotif() {

    const overlay =
        document.getElementById(
            "notifOverlay"
        );


    if (overlay) {

        overlay.style.display =
            "none";
    }
}


/* =====================================================
   BUKA KONFIRMASI HAPUS
===================================================== */

function bukaConfirm(id) {

    idTransaksiHapus = id;


    const overlay =
        document.getElementById(
            "confirmOverlay"
        );

    const isi =
        document.getElementById(
            "isiConfirm"
        );


    isi.innerHTML =

        "Yakin ingin menghapus transaksi " +
        "<strong>#" +
        id +
        "</strong>?" +
        "<br>" +
        "<small>" +
        "Data transaksi dan detail barang " +
        "akan ikut dihapus." +
        "</small>";


    overlay.style.display =
        "flex";
}


/* =====================================================
   TUTUP KONFIRMASI
===================================================== */

function tutupConfirm() {

    const overlay =
        document.getElementById(
            "confirmOverlay"
        );


    if (overlay) {

        overlay.style.display =
            "none";
    }


    idTransaksiHapus =
        null;
}


/* =====================================================
   LANJUT HAPUS TRANSAKSI
===================================================== */

function lanjutHapus() {

    if (
        idTransaksiHapus === null
    ) {

        return;
    }


    window.location.href =
        "hapus_transaksi.php?id=" +
        idTransaksiHapus;
}


/* =====================================================
   KLIK DI LUAR MODAL
===================================================== */

document.addEventListener(
    "click",
    function(event) {

        const notifOverlay =
            document.getElementById(
                "notifOverlay"
            );

        const confirmOverlay =
            document.getElementById(
                "confirmOverlay"
            );


        if (
            event.target ===
            notifOverlay
        ) {

            tutupNotif();
        }


        if (
            event.target ===
            confirmOverlay
        ) {

            tutupConfirm();
        }

    }
);


/* =====================================================
   TOMBOL ESCAPE
===================================================== */

document.addEventListener(
    "keydown",
    function(event) {

        if (
            event.key === "Escape"
        ) {

            tutupNotif();

            tutupConfirm();
        }

    }
);

</script>



<?php

/* =====================================================
   PESAN DARI HAPUS_TRANSAKSI.PHP
===================================================== */

$pesan =
    isset($_GET['pesan'])
        ? $_GET['pesan']
        : '';

?>


<?php if ($pesan === 'hapus'): ?>

<script>

    tampilkanNotif(

        "Data transaksi dan detail barang berhasil dihapus.",

        "Transaksi Dihapus!"

    );

</script>


<?php elseif ($pesan === 'gagal'): ?>

<script>

    tampilkanNotif(

        "Transaksi gagal dihapus. Silakan coba lagi.",

        "Gagal!"

    );

</script>

<?php endif; ?>



</body>

</html>