<?php
require 'config.php';
head('Struk', 'laporan');
$t = row('SELECT t.*, u.nama FROM transaksi t LEFT JOIN user u USING(id_user) WHERE id_transaksi=?', 'i', (int)($_GET['id'] ?? 0));
if ($t && !admin() && $t['id_user'] != $_SESSION['uid']) $t = null;
if (!$t) { echo '<div class="alert alert-warning">Transaksi tidak ditemukan.</div>'; foot(); exit; }
$d = rows('SELECT * FROM detail_transaksi WHERE id_transaksi=? ORDER BY id_detail', 'i', $t['id_transaksi']);
?>
<div class="noprint mb-3 d-flex gap-2"><button onclick="print()" class="btn btn-leaf"><i class="bi bi-printer"></i> Cetak struk</button><a href="kasir.php" class="btn btn-outline-success">Transaksi baru</a><a href="<?= admin() ? 'laporan.php' : 'index.php' ?>" class="btn btn-outline-secondary"><?= admin() ? 'Ke laporan' : 'Beranda' ?></a></div>
<?php if (isset($_GET['baru'])): ?><div class="alert alert-success noprint">Transaksi tersimpan. Kembalian: <b><?= rp($t['kembalian']) ?></b></div><?php endif; ?>
<div class="box mx-auto" style="max-width:380px;font-family:ui-monospace,Consolas,monospace;font-size:14px">
 <div class="text-center mb-2"><b style="font-size:18px">DULUR CAFE</b><br><small>#<?= $t['id_transaksi'] ?> · <?= date('d/m/Y H:i', strtotime($t['tanggal'])) ?><br>Kasir: <?= e($t['nama'] ?? '-') ?></small></div><hr>
 <?php foreach ($d as $r): ?><div><?= e($r['nama_produk']) ?></div><div class="d-flex justify-content-between"><span><?= $r['jumlah'] ?> × <?= number_format($r['harga'], 0, ',', '.') ?></span><span><?= number_format($r['subtotal'], 0, ',', '.') ?></span></div><?php endforeach; ?><hr>
 <div class="d-flex justify-content-between fw-bold"><span>TOTAL</span><span><?= rp($t['total']) ?></span></div>
 <div class="d-flex justify-content-between"><span>Bayar</span><span><?= rp($t['bayar']) ?></span></div>
 <div class="d-flex justify-content-between"><span>Kembali</span><span><?= rp($t['kembalian']) ?></span></div><hr>
 <div class="text-center">Hatur nuhun, mangga kadieu deui!</div></div>
<?php foot();
