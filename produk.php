<?php

session_start();

include 'koneksi.php';

// Cek login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}


// =========================
// TAMBAH PRODUK
// =========================

if (isset($_POST['tambah'])) {

    $nama_produk = trim($_POST['nama_produk']);
    $harga       = (int) $_POST['harga'];
    $stok        = (int) $_POST['stok'];


    if (
        $nama_produk != '' &&
        $harga >= 0 &&
        $stok >= 0
    ) {

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO produk
             (nama_produk, harga, stok)
             VALUES (?, ?, ?)"
        );


        mysqli_stmt_bind_param(
            $stmt,
            "sii",
            $nama_produk,
            $harga,
            $stok
        );


        mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);


        header(
            "Location: produk.php?pesan=tambah"
        );

        exit;
    }
}


// =========================
// HAPUS PRODUK
// =========================

if (isset($_GET['hapus'])) {

    $id_produk =
        (int) $_GET['hapus'];


    if ($id_produk > 0) {

        $stmt = mysqli_prepare(
            $conn,
            "DELETE FROM produk
             WHERE id_produk = ?"
        );


        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $id_produk
        );


        mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);
    }


    header(
        "Location: produk.php?pesan=hapus"
    );

    exit;
}


// =========================
// AMBIL DATA EDIT
// =========================

$edit_produk = null;


if (isset($_GET['edit'])) {

    $id_produk =
        (int) $_GET['edit'];


    $stmt = mysqli_prepare(
        $conn,
        "SELECT *
         FROM produk
         WHERE id_produk = ?"
    );


    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $id_produk
    );


    mysqli_stmt_execute($stmt);


    $hasil_edit =
        mysqli_stmt_get_result($stmt);


    $edit_produk =
        mysqli_fetch_assoc(
            $hasil_edit
        );


    mysqli_stmt_close($stmt);
}


// =========================
// UPDATE PRODUK
// =========================

if (isset($_POST['update'])) {

    $id_produk =
        (int) $_POST['id_produk'];

    $nama_produk =
        trim($_POST['nama_produk']);

    $harga =
        (int) $_POST['harga'];

    $stok =
        (int) $_POST['stok'];


    if (
        $id_produk > 0 &&
        $nama_produk != '' &&
        $harga >= 0 &&
        $stok >= 0
    ) {

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE produk
             SET nama_produk = ?,
                 harga = ?,
                 stok = ?
             WHERE id_produk = ?"
        );


        mysqli_stmt_bind_param(
            $stmt,
            "siii",
            $nama_produk,
            $harga,
            $stok,
            $id_produk
        );


        mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);


        header(
            "Location: produk.php?pesan=update"
        );

        exit;
    }
}


// =========================
// PENCARIAN
// =========================

$cari =
    isset($_GET['cari'])
        ? trim($_GET['cari'])
        : '';


if ($cari != '') {

    $stmt = mysqli_prepare(
        $conn,
        "SELECT *
         FROM produk
         WHERE nama_produk LIKE ?
         ORDER BY id_produk DESC"
    );


    $keyword =
        "%" . $cari . "%";


    mysqli_stmt_bind_param(
        $stmt,
        "s",
        $keyword
    );


    mysqli_stmt_execute($stmt);


    $query_produk =
        mysqli_stmt_get_result($stmt);

} else {

    $query_produk =
        mysqli_query(
            $conn,
            "SELECT *
             FROM produk
             ORDER BY id_produk DESC"
        );
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Data Produk - KASIR LABIB</title>


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >


    <style>

        body {
            background: #f5f5f5;
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

            background: rgba(255, 255, 255, 0.18);

            border: 1px solid rgba(255, 255, 255, 0.25);

            backdrop-filter: blur(8px);

            display: flex;

            align-items: center;

            justify-content: center;

            color: white;

            box-shadow:
                0 5px 15px rgba(0, 0, 0, 0.12);
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


        .user-info {

            background: rgba(255, 255, 255, 0.12);

            border: 1px solid rgba(255, 255, 255, 0.15);

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
                0 5px 15px rgba(12, 166, 120, .20);
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
                0 5px 12px rgba(0, 0, 0, .25);
        }


        /* =========================
           CARD
        ========================= */

        .card {

            border: none;

            border-radius: 12px;

            overflow: hidden;
        }


        .card-header-hijau {

            background: linear-gradient(
                135deg,
                #087f5b,
                #0ca678
            );

            color: white;

            font-weight: 600;
        }


        .judul {

            font-weight: 700;

            color: #212529;
        }


        .table th {

            background-color: #e9ecef;
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
   NOTIFIKASI TENGAH LAYAR
========================= */

#notifOverlay {
    display: none;

    position: fixed;

    top: 0;
    left: 0;

    width: 100%;
    height: 100%;

    background: rgba(0, 0, 0, 0.55);

    z-index: 99999;

    justify-content: center;
    align-items: center;
}

#notifBox {
    background: white;

    width: 400px;
    max-width: 90%;

    padding: 30px;

    border-radius: 15px;

    text-align: center;

    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);

    animation: munculNotif 0.3s ease;
}

.notifIcon {
    width: 70px;
    height: 70px;

    margin: 0 auto 15px;

    border-radius: 50%;

    background: #198754;
    color: white;

    font-size: 45px;
    font-weight: bold;

    display: flex;
    align-items: center;
    justify-content: center;
}

#notifBox h3 {
    color: #198754;

    font-weight: bold;

    margin-bottom: 15px;
}

@keyframes munculNotif {

    from {
        opacity: 0;
        transform: scale(0.7);
    }

    to {
        opacity: 1;
        transform: scale(1);
    }

}

/* ============================= */
/* MODAL HAPUS PRODUK */
/* ============================= */

.modal-hapus {
    display: none;
    position: fixed;
    z-index: 9999;
    inset: 0;
    background: rgba(15, 23, 42, 0.65);
    justify-content: center;
    align-items: center;
}

.modal-hapus.show {
    display: flex;
}

.modal-hapus-box {
    width: 345px;
    background: #fff;
    border-radius: 15px;
    text-align: center;
    overflow: hidden;
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.25);
    animation: popupHapus .25s ease;
}

.modal-hapus-content {
    padding: 25px 25px 24px;
}

/* ICON X */
.icon-hapus {
    width: 58px;
    height: 58px;
    background: #dc3545;
    color: white;
    border-radius: 50%;
    margin: 0 auto 16px;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 32px;
    font-weight: bold;

    box-shadow: 0 5px 15px rgba(220, 53, 69, 0.25);
}

.modal-hapus-content h4 {
    color: #dc3545;
    font-size: 22px;
    font-weight: 700;
    margin-bottom: 10px;
}

.modal-hapus-content p {
    color: #444;
    font-size: 14px;
    line-height: 1.6;
    margin-bottom: 20px;
}

/* TOMBOL */
.btn-batal-hapus {
    border: none;
    background: #6c757d;
    color: white;
    padding: 9px 24px;
    border-radius: 6px;
    font-size: 14px;
    margin-right: 5px;
    cursor: pointer;
}

.btn-konfirmasi-hapus {
    border: none;
    background: #dc3545;
    color: white;
    padding: 9px 24px;
    border-radius: 6px;
    font-size: 14px;
    text-decoration: none;
    display: inline-block;
    cursor: pointer;
}

.btn-batal-hapus:hover {
    background: #5c636a;
    color: white;
}

.btn-konfirmasi-hapus:hover {
    background: #bb2d3b;
    color: white;
}

@keyframes popupHapus {
    from {
        opacity: 0;
        transform: scale(0.9);
    }

    to {
        opacity: 1;
        transform: scale(1);
    }
}

    </style>

</head>


<body>

<!-- NOTIFIKASI -->

<div id="notifOverlay">

    <div id="notifBox">

        <div class="notifIcon">
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
            OK
        </button>

    </div>

</div>

<!-- NAVBAR -->

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

                    <div style="font-size: 10px; opacity: .7;">
                        Selamat datang
                    </div>


                    <div class="fw-semibold">

                        <?= htmlspecialchars($_SESSION['nama']); ?>

                    </div>

                </div>

            </div>

        </div>

    </div>

</nav>



<div class="container-fluid">

    <div class="row">


        <!-- SIDEBAR -->

        <div class="col-md-3 col-lg-2 sidebar p-3">


            <div>

                <hr class="text-secondary">


                <a
                    href="index.php"
                    class="sidebar-menu"
                >

                    <i class="bi bi-cart3"></i>

                    <span>
                        Transaksi
                    </span>

                </a>


                <a
                    href="produk.php"
                    class="sidebar-menu active"
                >

                    <i class="bi bi-box-seam"></i>

                    <span>
                        Data Produk
                    </span>

                </a>


                <a
                    href="laporan.php"
                    class="sidebar-menu"
                >

                    <i class="bi bi-bar-chart-line"></i>

                    <span>
                        Laporan
                    </span>

                </a>

            </div>


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



        <!-- CONTENT -->

        <div class="col-md-9 col-lg-10 p-4">


            <h2 class="judul mb-4">
                Data Produk
            </h2>


            <!-- PESAN -->

         



            <!-- FORM -->

            <div class="card shadow-sm mb-4">

                <div class="card-header card-header-hijau">

                    <?php if ($edit_produk): ?>

                        Edit Produk

                    <?php else: ?>

                        Tambah Produk

                    <?php endif; ?>

                </div>


                <div class="card-body">


                    <?php if ($edit_produk): ?>

                        <form method="POST">

                            <input
                                type="hidden"
                                name="id_produk"
                                value="<?= $edit_produk['Id_produk'] ?>"
                            >


                            <div class="row">

                                <div class="col-md-5 mb-3">

                                    <label class="form-label">
                                        Nama Produk
                                    </label>

                                    <input
                                        type="text"
                                        name="nama_produk"
                                        class="form-control"
                                        value="<?= htmlspecialchars($edit_produk['Nama_produk']) ?>"
                                        required
                                    >

                                </div>


                                <div class="col-md-3 mb-3">

                                    <label class="form-label">
                                        Harga
                                    </label>

                                    <input
                                        type="number"
                                        name="harga"
                                        class="form-control"
                                        value="<?= $edit_produk['Harga'] ?>"
                                        min="0"
                                        required
                                    >

                                </div>


                                <div class="col-md-2 mb-3">

                                    <label class="form-label">
                                        Stok
                                    </label>

                                    <input
                                        type="number"
                                        name="stok"
                                        class="form-control"
                                        value="<?= $edit_produk['Stok'] ?>"
                                        min="0"
                                        required
                                    >

                                </div>


                                <div class="col-md-2 mb-3 d-flex align-items-end">

                                    <button
                                        type="submit"
                                        name="update"
                                        class="btn btn-success w-100"
                                    >
                                        Simpan
                                    </button>

                                </div>

                            </div>


                            <a
                                href="produk.php"
                                class="btn btn-secondary"
                            >
                                Batal
                            </a>

                        </form>


                    <?php else: ?>


                        <form method="POST">

                            <div class="row">

                                <div class="col-md-5 mb-3">

                                    <label class="form-label">
                                        Nama Produk
                                    </label>

                                    <input
                                        type="text"
                                        name="nama_produk"
                                        class="form-control"
                                        required
                                    >

                                </div>


                                <div class="col-md-3 mb-3">

                                    <label class="form-label">
                                        Harga
                                    </label>

                                    <input
                                        type="number"
                                        name="harga"
                                        class="form-control"
                                        min="0"
                                        required
                                    >

                                </div>


                                <div class="col-md-2 mb-3">

                                    <label class="form-label">
                                        Stok
                                    </label>

                                    <input
                                        type="number"
                                        name="stok"
                                        class="form-control"
                                        min="0"
                                        required
                                    >

                                </div>


                                <div class="col-md-2 mb-3 d-flex align-items-end">

                                    <button
                                        type="submit"
                                        name="tambah"
                                        class="btn btn-primary w-100"
                                    >
                                        + Tambah
                                    </button>

                                </div>

                            </div>

                        </form>

                    <?php endif; ?>

                </div>

            </div>


            <!-- DAFTAR PRODUK -->

            <div class="card shadow-sm">

                <div class="card-header bg-dark text-white fw-bold">

                    Daftar Produk

                </div>


                <div class="card-body">


                    <!-- SEARCH -->

                    <form
                        method="GET"
                        class="mb-3"
                    >

                        <div class="input-group">

                            <input
                                type="text"
                                name="cari"
                                class="form-control"
                                placeholder="🔍 Cari nama produk..."
                                value="<?= htmlspecialchars($cari) ?>"
                            >


                            <button
                                type="submit"
                                class="btn btn-primary"
                            >
                                Cari
                            </button>


                            <?php if ($cari != ''): ?>

                                <a
                                    href="produk.php"
                                    class="btn btn-secondary"
                                >
                                    Reset
                                </a>

                            <?php endif; ?>

                        </div>

                    </form>


                    <?php if ($cari != ''): ?>

                        <div class="alert alert-info">

                            Menampilkan hasil pencarian untuk:

                            <strong>
                                <?= htmlspecialchars($cari) ?>
                            </strong>

                        </div>

                    <?php endif; ?>


                    <div class="table-responsive">

                        <table class="table table-bordered table-hover">

                            <thead>

                                <tr>

                                    <th width="60">
                                        No
                                    </th>

                                    <th>
                                        Nama Produk
                                    </th>

                                    <th>
                                        Harga
                                    </th>

                                    <th width="120">
                                        Stok
                                    </th>

                                    <th width="180">
                                        Aksi
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                                <?php

                                $no = 1;

                                if (
                                    mysqli_num_rows(
                                        $query_produk
                                    ) > 0
                                ):

                                    while (
                                        $produk =
                                        mysqli_fetch_assoc(
                                            $query_produk
                                        )
                                    ):

                                ?>

                                    <tr>

                                        <td>
                                            <?= $no++ ?>
                                        </td>


                                        <td>
                                            <?= htmlspecialchars(
                                                $produk['Nama_produk']
                                            ) ?>
                                        </td>


                                        <td>

                                            Rp
                                            <?= number_format(
                                                $produk['Harga'],
                                                0,
                                                ',',
                                                '.'
                                            ) ?>

                                        </td>


                                        <td>
                                            <?= $produk['Stok'] ?>
                                        </td>


                                        <td>

                                            <a
                                                href="produk.php?edit=<?= $produk['Id_produk'] ?>"
                                                class="btn btn-warning btn-sm"
                                            >
                                                Edit
                                            </a>


                                            <a 
                                               href="produk.php?hapus=<?= $produk['Id_produk'] ?>" 
                                               class="btn btn-danger btn-sm btn-hapus"
                                              >
                                                    Hapus
                                            </a>

                                        </td>

                                    </tr>


                                <?php

                                    endwhile;

                                else:

                                ?>

                                    <tr>

                                        <td
                                            colspan="5"
                                            class="text-center py-4"
                                        >

                                            <?php if ($cari != ''): ?>

                                                Produk

                                                <strong>
                                                    "<?= htmlspecialchars($cari) ?>"
                                                </strong>

                                                tidak ditemukan.

                                            <?php else: ?>

                                                Belum ada produk.

                                            <?php endif; ?>

                                        </td>

                                    </tr>

                                <?php endif; ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

      <script>

function tampilkanNotif(pesan, judul = "Berhasil!") {

    document.getElementById(
        "judulNotif"
    ).innerText = judul;

    document.getElementById(
        "isiNotif"
    ).innerHTML = pesan;

    document.getElementById(
        "notifOverlay"
    ).style.display = "flex";
}


function tutupNotif() {

    document.getElementById(
        "notifOverlay"
    ).style.display = "none";
}

</script>


<?php if (isset($_GET['pesan'])): ?>

<script>

<?php if ($_GET['pesan'] == 'tambah'): ?>

    tampilkanNotif(
        "Produk berhasil ditambahkan.",
        "Produk Berhasil!"
    );

<?php elseif ($_GET['pesan'] == 'update'): ?>

    tampilkanNotif(
        "Data produk berhasil diperbarui.",
        "Produk Berhasil!"
    );

<?php elseif ($_GET['pesan'] == 'hapus'): ?>

    tampilkanNotif(
        "Produk berhasil dihapus.",
        "Produk Dihapus!"
    );

<?php endif; ?>

</script>

<?php endif; ?>


<!-- MODAL KONFIRMASI HAPUS -->
<div class="modal-hapus" id="modalHapus">

    <div class="modal-hapus-box">

        <div class="modal-hapus-content">

            <div class="icon-hapus">
                ×
            </div>

            <h4>Hapus Produk?</h4>

            <p>
                Yakin ingin menghapus produk ini?
            </p>

            <button 
                type="button" 
                class="btn-batal-hapus" 
                id="btnBatalHapus"
            >
                Batal
            </button>

            <a 
                href="#" 
                class="btn-konfirmasi-hapus" 
                id="btnKonfirmasiHapus"
            >
                Hapus
            </a>

        </div>

    </div>

</div>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const modalHapus = document.getElementById("modalHapus");
    const btnBatalHapus = document.getElementById("btnBatalHapus");
    const btnKonfirmasiHapus = document.getElementById("btnKonfirmasiHapus");

    document.querySelectorAll(".btn-hapus").forEach(function (button) {

        button.addEventListener("click", function (e) {

            e.preventDefault();

            // Ambil link hapus
            const linkHapus = this.getAttribute("href");

            // Masukkan link ke tombol konfirmasi
            btnKonfirmasiHapus.setAttribute("href", linkHapus);

            // Tampilkan popup
            modalHapus.classList.add("show");

        });

    });

    // Tombol batal
    btnBatalHapus.addEventListener("click", function () {

        modalHapus.classList.remove("show");

    });

    // Klik area luar popup
    modalHapus.addEventListener("click", function (e) {

        if (e.target === modalHapus) {
            modalHapus.classList.remove("show");
        }

    });

});
</script>


</body>

</html>
