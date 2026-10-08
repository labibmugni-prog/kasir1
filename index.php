<?php
require 'config.php';
head('Beranda', 'index');
if (!admin()) {
    $uid = (int)$_SESSION['uid'];
    $s = row("SELECT COALESCE(SUM(total),0) omzet, COUNT(*) trx FROM transaksi WHERE id_user=? AND DATE(tanggal)=CURDATE()", 'i', $uid);
    $it = row("SELECT COALESCE(SUM(d.jumlah),0) n FROM detail_transaksi d JOIN transaksi t USING(id_transaksi) WHERE t.id_user=? AND DATE(t.tanggal)=CURDATE()", 'i', $uid)['n'];
    $mine = rows("SELECT * FROM transaksi WHERE id_user=? AND DATE(tanggal)=CURDATE() ORDER BY id_transaksi DESC LIMIT 15", 'i', $uid);
    ?>
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3"><div><h1>Halo, <?= e(explode(' ', $_SESSION['nama'])[0]) ?></h1><p class="sub mb-0"><?= date('d M Y') ?> · penjualan Anda hari ini</p></div>
 <a href="kasir.php" class="btn btn-leaf btn-lg px-4"><i class="bi bi-receipt"></i> Mulai transaksi</a></div>
<div class="row g-3 mb-3"><div class="col-md-6"><div class="box hero h-100"><small style="color:#a9c8ba">Penjualan Anda hari ini</small><div style="font-size:40px;font-weight:800"><?= rp($s['omzet']) ?></div></div></div>
 <div class="col-6 col-md-3"><div class="box stat h-100"><small>Transaksi</small><b><?= $s['trx'] ?></b></div></div><div class="col-6 col-md-3"><div class="box stat h-100"><small>Item terjual</small><b><?= $it ?></b></div></div></div>
<div class="box"><h6 class="mb-3">Transaksi Anda hari ini</h6><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>ID</th><th>Jam</th><th>Total</th><th>Kembalian</th><th></th></tr></thead><tbody>
<?php foreach ($mine as $r): ?><tr><td>#<?= $r['id_transaksi'] ?></td><td><?= date('H:i', strtotime($r['tanggal'])) ?></td><td class="fw-semibold"><?= rp($r['total']) ?></td><td><?= rp($r['kembalian']) ?></td><td class="text-end"><a class="btn btn-sm btn-outline-secondary" href="struk.php?id=<?= $r['id_transaksi'] ?>">Struk</a></td></tr><?php endforeach; ?>
<?php if (!$mine): ?><tr><td colspan="5" class="text-center text-secondary py-4">Belum ada transaksi hari ini. Tekan "Mulai transaksi" untuk menerima pesanan.</td></tr><?php endif; ?></tbody></table></div></div>
<?php foot(); exit; }
$bulan = row("SELECT COALESCE(SUM(total),0) omzet, COUNT(*) trx FROM transaksi WHERE DATE_FORMAT(tanggal,'%Y-%m')=DATE_FORMAT(CURDATE(),'%Y-%m')");
$perkasir = rows("SELECT u.nama, COUNT(*) trx, SUM(t.total) omzet FROM transaksi t JOIN user u USING(id_user) WHERE DATE(t.tanggal)=CURDATE() GROUP BY u.id_user ORDER BY omzet DESC");
$h = row("SELECT COALESCE(SUM(total),0) omzet, COUNT(*) trx FROM transaksi WHERE DATE(tanggal)=CURDATE()");
$brg = row("SELECT COALESCE(SUM(d.jumlah),0) n FROM detail_transaksi d JOIN transaksi t USING(id_transaksi) WHERE DATE(t.tanggal)=CURDATE()")['n'];
$hari = rows("SELECT DATE(tanggal) d, SUM(total) s FROM transaksi WHERE tanggal>=CURDATE()-INTERVAL 6 DAY GROUP BY d");
$map = array_column($hari, 's', 'd'); $seri = []; $maks = 1;
for ($i = 6; $i >= 0; $i--) { $d = date('Y-m-d', strtotime("-$i day")); $seri[$d] = (int)($map[$d] ?? 0); $maks = max($maks, $seri[$d]); }
$laris = rows("SELECT nama_produk, SUM(jumlah) n FROM detail_transaksi d JOIN transaksi t USING(id_transaksi) WHERE t.tanggal>=CURDATE()-INTERVAL 30 DAY GROUP BY nama_produk ORDER BY n DESC LIMIT 5");
$tipis = rows("SELECT nama_produk, stok FROM produk WHERE stok<=5 ORDER BY stok LIMIT 6");
$baru = rows("SELECT t.*, u.nama FROM transaksi t LEFT JOIN user u USING(id_user) ORDER BY id_transaksi DESC LIMIT 6");
?>
<h1>Halo, <?= e(explode(' ', $_SESSION['nama'])[0]) ?></h1><p class="sub"><?= date('d M Y') ?> · ringkasan hari ini</p>
<div class="row g-3 mb-3">
 <div class="col-lg-6"><div class="box h-100 hero">
  <small style="color:#a9c8ba">Omzet hari ini</small><div style="font-size:40px;font-weight:800"><?= rp($h['omzet']) ?></div>
  <div class="d-flex align-items-end gap-2 mt-3" style="height:90px">
  <?php foreach ($seri as $d => $v): ?><div class="flex-fill text-center" title="<?= date('d M', strtotime($d)) ?>: <?= rp($v) ?>"><div style="height:<?= max(4, round($v / $maks * 70)) ?>px;background:<?= $d === date('Y-m-d') ? 'var(--amber)' : 'rgba(255,255,255,.28)' ?>;border-radius:7px 7px 0 0"></div><small style="color:#a9c8ba;font-size:11px"><?= date('D', strtotime($d)) ?></small></div><?php endforeach; ?>
  </div></div></div>
 <div class="col-6 col-lg-3"><div class="box stat h-100"><small>Transaksi</small><b><?= $h['trx'] ?></b></div></div>
 <div class="col-6 col-lg-3"><div class="box stat h-100"><small>Item terjual</small><b><?= $brg ?></b></div></div>
</div>
<div class="row g-3 mb-3">
 <div class="col-md-4"><div class="box stat h-100"><small>Omzet bulan ini</small><b><?= rp($bulan['omzet']) ?></b><small><?= $bulan['trx'] ?> transaksi</small></div></div>
 <div class="col-md-8"><div class="box h-100"><h6 class="mb-3">Penjualan per kasir hari ini</h6>
  <?php foreach ($perkasir as $r): ?><div class="d-flex justify-content-between py-1"><span><?= e($r['nama']) ?> <small class="text-secondary"><?= $r['trx'] ?> transaksi</small></span><b><?= rp($r['omzet']) ?></b></div><?php endforeach; ?>
  <?php if (!$perkasir): ?><p class="text-secondary mb-0">Belum ada penjualan hari ini.</p><?php endif; ?></div></div>
</div>
<div class="row g-3">
 <div class="col-lg-4"><div class="box h-100"><h6 class="fw-bold mb-3">Terlaris 30 hari</h6>
  <?php foreach ($laris as $r): ?><div class="d-flex justify-content-between py-1"><span><?= e($r['nama_produk']) ?></span><b><?= $r['n'] ?>×</b></div><?php endforeach; ?>
  <?php if (!$laris): ?><p class="text-secondary mb-0">Belum ada penjualan. Mulai dari menu Kasir.</p><?php endif; ?></div></div>
 <div class="col-lg-4"><div class="box h-100"><h6 class="fw-bold mb-3">Stok menipis</h6>
  <?php foreach ($tipis as $r): ?><div class="d-flex justify-content-between py-1"><span><?= e($r['nama_produk']) ?></span><span class="badge <?= $r['stok'] == 0 ? 'bg-danger' : 'bg-warning text-dark' ?>"><?= $r['stok'] == 0 ? 'Habis' : $r['stok'] . ' sisa' ?></span></div><?php endforeach; ?>
  <?php if (!$tipis): ?><p class="text-secondary mb-0">Semua stok aman.</p><?php endif; ?></div></div>
 <div class="col-lg-4"><div class="box h-100"><h6 class="fw-bold mb-3">Transaksi terbaru</h6>
  <?php foreach ($baru as $r): ?><a href="struk.php?id=<?= $r['id_transaksi'] ?>" class="d-flex justify-content-between py-1 text-decoration-none text-reset"><span>#<?= $r['id_transaksi'] ?> <small class="text-secondary"><?= date('H:i', strtotime($r['tanggal'])) ?></small></span><b><?= rp($r['total']) ?></b></a><?php endforeach; ?>
  <?php if (!$baru): ?><p class="text-secondary mb-0">Belum ada transaksi.</p><?php endif; ?></div></div>
</div>
<?php foot();
