<?php
require 'config.php';
wajib_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && admin()) {
    cek_csrf(); $id = (int)$_POST['hapus'];
    $conn->begin_transaction();
    foreach (rows('SELECT id_produk, jumlah FROM detail_transaksi WHERE id_transaksi=? AND id_produk IS NOT NULL', 'i', $id) as $r)
        q('UPDATE produk SET stok=stok+? WHERE id_produk=?', 'ii', $r['jumlah'], $r['id_produk']);
    q('DELETE FROM transaksi WHERE id_transaksi=?', 'i', $id);
    $conn->commit();
    header('Location: laporan.php?ok=Transaksi dihapus dan stok dikembalikan'); exit;
}
$dari = $_GET['dari'] ?? date('Y-m-01'); $sampai = $_GET['sampai'] ?? date('Y-m-d');
$data = rows("SELECT t.*, u.nama, (SELECT COALESCE(SUM(jumlah),0) FROM detail_transaksi WHERE id_transaksi=t.id_transaksi) item
  FROM transaksi t LEFT JOIN user u USING(id_user) WHERE DATE(t.tanggal) BETWEEN ? AND ? ORDER BY t.tanggal DESC", 'ss', $dari, $sampai);
if (isset($_GET['csv'])) {
    header('Content-Type: text/csv; charset=utf-8'); header('Content-Disposition: attachment; filename=laporan_' . $dari . '_' . $sampai . '.csv');
    $o = fopen('php://output', 'w'); fputcsv($o, ['ID', 'Tanggal', 'Kasir', 'Item', 'Total', 'Bayar', 'Kembalian']);
    foreach ($data as $r) fputcsv($o, [$r['id_transaksi'], $r['tanggal'], $r['nama'], $r['item'], $r['total'], $r['bayar'], $r['kembalian']]);
    exit;
}
head('Laporan', 'laporan');
$omzet = array_sum(array_column($data, 'total')); $item = array_sum(array_column($data, 'item'));
?>
<h1>Laporan penjualan</h1><p class="sub">Pilih rentang tanggal untuk melihat omzet dan riwayat transaksi.</p>
<?php if (!empty($_GET['ok'])): ?><div class="alert alert-success py-2"><?= e($_GET['ok']) ?></div><?php endif; ?>
<form class="box mb-3 row g-2 align-items-end noprint"><div class="col-md-3"><label class="form-label">Dari</label><input type="date" class="form-control" name="dari" value="<?= e($dari) ?>"></div>
 <div class="col-md-3"><label class="form-label">Sampai</label><input type="date" class="form-control" name="sampai" value="<?= e($sampai) ?>"></div>
 <div class="col-md-6 d-flex gap-2"><button class="btn btn-leaf">Tampilkan</button><a class="btn btn-outline-success" href="?dari=<?= e($dari) ?>&sampai=<?= e($sampai) ?>&csv=1"><i class="bi bi-download"></i> Unduh CSV</a><button type="button" onclick="print()" class="btn btn-outline-secondary"><i class="bi bi-printer"></i> Cetak</button></div></form>
<div class="row g-3 mb-3"><div class="col-md-4"><div class="box stat"><small>Omzet</small><b><?= rp($omzet) ?></b></div></div><div class="col-md-4"><div class="box stat"><small>Transaksi</small><b><?= count($data) ?></b></div></div><div class="col-md-4"><div class="box stat"><small>Item terjual</small><b><?= $item ?></b></div></div></div>
<div class="box"><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>ID</th><th>Waktu</th><th>Kasir</th><th>Item</th><th>Total</th><th>Bayar</th><th class="noprint"></th></tr></thead><tbody>
<?php foreach ($data as $r): ?><tr><td>#<?= $r['id_transaksi'] ?></td><td><?= date('d/m/Y H:i', strtotime($r['tanggal'])) ?></td><td><?= e($r['nama'] ?? '-') ?></td><td><?= $r['item'] ?></td><td class="fw-semibold"><?= rp($r['total']) ?></td><td><?= rp($r['bayar']) ?></td>
 <td class="text-end text-nowrap noprint"><a class="btn btn-sm btn-outline-secondary" href="struk.php?id=<?= $r['id_transaksi'] ?>">Lihat</a>
 <?php if (admin()): ?><form method="post" class="d-inline" onsubmit="return confirm('Hapus transaksi #<?= $r['id_transaksi'] ?>? Stok produk akan dikembalikan.')"><input type="hidden" name="csrf" value="<?= csrf() ?>"><button name="hapus" value="<?= $r['id_transaksi'] ?>" class="btn btn-sm btn-outline-danger">Hapus</button></form><?php endif; ?></td></tr>
<?php endforeach; ?>
<?php if (!$data): ?><tr><td colspan="7" class="text-center text-secondary py-4">Tidak ada transaksi pada rentang ini. Ubah tanggal di atas.</td></tr><?php endif; ?>
</tbody></table></div></div>
<?php foot();
