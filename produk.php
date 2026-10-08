<?php
require 'config.php';
wajib_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_csrf();
    if (isset($_POST['hapus']) && admin()) {
        hapus_foto(row('SELECT foto FROM produk WHERE id_produk=?', 'i', (int)$_POST['hapus'])['foto'] ?? null);
        q('DELETE FROM produk WHERE id_produk=?', 'i', (int)$_POST['hapus']);
        header('Location: produk.php?ok=Produk dihapus'); exit;
    }
    $id = (int)($_POST['id'] ?? 0); $nama = trim($_POST['nama']); $kat = trim($_POST['kategori']) ?: 'Lainnya';
    $harga = max(0, (int)$_POST['harga']); $stok = max(0, (int)$_POST['stok']);
    if ($nama === '') { header('Location: produk.php?err=Nama produk wajib diisi'); exit; }
    try {
        $foto = simpan_foto($_FILES['foto'] ?? []);
        if ($id) {
            $lama = row('SELECT foto FROM produk WHERE id_produk=?', 'i', $id)['foto'] ?? null; $baru = $lama;
            if ($foto || isset($_POST['hapus_foto'])) { hapus_foto($lama); $baru = $foto; }
            q('UPDATE produk SET nama_produk=?,kategori=?,harga=?,stok=?,foto=? WHERE id_produk=?', 'ssiisi', $nama, $kat, $harga, $stok, $baru, $id);
        } else q('INSERT INTO produk(nama_produk,kategori,harga,stok,foto) VALUES(?,?,?,?,?)', 'ssiis', $nama, $kat, $harga, $stok, $foto);
    } catch (Exception $x) { header('Location: produk.php?err=' . urlencode($x->getMessage())); exit; }
    header('Location: produk.php?ok=' . ($id ? 'Produk diperbarui' : 'Produk ditambahkan')); exit;
}
head('Produk', 'produk');
$cari = trim($_GET['cari'] ?? ''); $kat = trim($_GET['kategori'] ?? '');
$sql = 'SELECT * FROM produk WHERE (nama_produk LIKE ? OR kategori LIKE ?)'; $t = 'ss'; $p = ["%$cari%", "%$cari%"];
if ($kat !== '') { $sql .= ' AND kategori=?'; $t .= 's'; $p[] = $kat; }
$data = rows($sql . ' ORDER BY nama_produk', $t, ...$p);
$kats = array_column(rows('SELECT DISTINCT kategori FROM produk ORDER BY kategori'), 'kategori');
$ed = isset($_GET['edit']) ? row('SELECT * FROM produk WHERE id_produk=?', 'i', (int)$_GET['edit']) : null;
?>
<h1>Produk</h1><p class="sub">Kelola menu, harga, kategori, dan stok.</p>
<?php if (!empty($_GET['ok'])): ?><div class="alert alert-success py-2"><?= e($_GET['ok']) ?></div><?php endif; ?>
<?php if (!empty($_GET['err'])): ?><div class="alert alert-danger py-2"><?= e($_GET['err']) ?></div><?php endif; ?>
<form method="post" enctype="multipart/form-data" class="box mb-3"><input type="hidden" name="csrf" value="<?= csrf() ?>"><input type="hidden" name="id" value="<?= $ed['id_produk'] ?? 0 ?>">
 <h6 class="fw-bold mb-3"><?= $ed ? 'Edit produk' : 'Tambah produk' ?></h6>
 <div class="d-flex align-items-center gap-3 mb-3"><span id="pv"><?= thumb($ed['foto'] ?? null, 64) ?></span>
  <div><input type="file" name="foto" accept="image/jpeg,image/png,image/webp" class="form-control form-control-sm" onchange="if(this.files[0])document.getElementById('pv').innerHTML='<img src=&quot;'+URL.createObjectURL(this.files[0])+'&quot; style=&quot;width:64px;height:64px;border-radius:12px;object-fit:cover&quot;>'"><small class="text-secondary">Foto produk (opsional): JPG, PNG, atau WebP, maksimal 2 MB.</small>
  <?php if (!empty($ed['foto'])): ?><div class="form-check mt-1"><input class="form-check-input" type="checkbox" name="hapus_foto" id="hf"><label class="form-check-label" for="hf">Hapus foto saat ini</label></div><?php endif; ?></div></div>
 <div class="row g-2">
  <div class="col-md-3"><input class="form-control" name="nama" placeholder="Nama produk" value="<?= e($ed['nama_produk'] ?? '') ?>" required></div>
  <div class="col-md-3"><input class="form-control" name="kategori" list="kl" placeholder="Kategori (pilih / ketik baru)" value="<?= e($ed['kategori'] ?? '') ?>"><datalist id="kl"><?php foreach ($kats as $k): ?><option value="<?= e($k) ?>"><?php endforeach; ?></datalist></div>
  <div class="col-md-2"><input class="form-control" type="number" min="0" name="harga" placeholder="Harga" value="<?= $ed['harga'] ?? '' ?>" required></div>
  <div class="col-md-2"><input class="form-control" type="number" min="0" name="stok" placeholder="Stok" value="<?= $ed['stok'] ?? '' ?>" required></div>
  <div class="col-md-2 d-flex gap-2"><button class="btn btn-leaf flex-fill"><?= $ed ? 'Simpan' : 'Tambah' ?></button><?php if ($ed): ?><a href="produk.php" class="btn btn-outline-secondary">Batal</a><?php endif; ?></div>
 </div></form>
<div class="box">
 <form class="row g-2 mb-3"><div class="col-md-5"><input class="form-control" name="cari" placeholder="Cari nama atau kategori…" value="<?= e($cari) ?>"></div>
  <div class="col-md-3"><select class="form-select" name="kategori"><option value="">Semua kategori</option><?php foreach ($kats as $k): ?><option <?= $k === $kat ? 'selected' : '' ?>><?= e($k) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-4 d-flex gap-2"><button class="btn btn-outline-success">Cari</button><?php if ($cari !== '' || $kat !== ''): ?><a href="produk.php" class="btn btn-outline-secondary">Reset</a><?php endif; ?></div></form>
 <div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th width="70">Foto</th><th>Produk</th><th>Kategori</th><th>Harga</th><th>Stok</th><th></th></tr></thead><tbody>
 <?php foreach ($data as $r): ?><tr><td><?= thumb($r['foto']) ?></td><td class="fw-semibold"><?= e($r['nama_produk']) ?></td><td><span class="badge" style="background:var(--mint);color:var(--pine)"><?= e($r['kategori']) ?></span></td><td><?= rp($r['harga']) ?></td>
  <td><span class="badge <?= $r['stok'] == 0 ? 'bg-danger' : ($r['stok'] <= 5 ? 'bg-warning text-dark' : 'bg-light text-dark border') ?>"><?= $r['stok'] == 0 ? 'Habis' : $r['stok'] ?></span></td>
  <td class="text-end text-nowrap"><a class="btn btn-sm btn-outline-secondary" href="?edit=<?= $r['id_produk'] ?>">Edit</a>
  <?php if (admin()): ?><form method="post" class="d-inline" onsubmit="return confirm('Hapus <?= e(addslashes($r['nama_produk'])) ?>? Riwayat transaksi tetap tersimpan.')"><input type="hidden" name="csrf" value="<?= csrf() ?>"><button name="hapus" value="<?= $r['id_produk'] ?>" class="btn btn-sm btn-outline-danger">Hapus</button></form><?php endif; ?></td></tr>
 <?php endforeach; ?>
 <?php if (!$data): ?><tr><td colspan="6" class="text-center text-secondary py-4"><?= ($cari !== '' || $kat !== '') ? 'Tidak ada produk yang cocok. Coba kata kunci lain.' : 'Belum ada produk. Tambahkan yang pertama di atas.' ?></td></tr><?php endif; ?>
 </tbody></table></div></div>
<?php foot();
