<?php
require 'config.php';
wajib_login();
if (($_GET['aksi'] ?? '') === 'simpan') {
    header('Content-Type: application/json');
    $d = json_decode(file_get_contents('php://input'), true);
    try {
        if (!hash_equals(csrf(), $d['csrf'] ?? '')) throw new Exception('Token tidak valid. Muat ulang halaman.');
        $items = $d['items'] ?? []; $bayar = (int)($d['bayar'] ?? 0);
        if (!$items) throw new Exception('Keranjang masih kosong.');
        $conn->begin_transaction(); $total = 0; $det = [];
        foreach ($items as $it) {
            $id = (int)$it['id']; $j = (int)$it['jumlah'];
            if ($j < 1) throw new Exception('Jumlah tidak valid.');
            $p = row('SELECT nama_produk, harga, stok FROM produk WHERE id_produk=? FOR UPDATE', 'i', $id);
            if (!$p) throw new Exception('Produk sudah tidak tersedia.');
            if ($p['stok'] < $j) throw new Exception("Stok {$p['nama_produk']} tinggal {$p['stok']}.");
            $det[] = [$id, $p['nama_produk'], $p['harga'], $j, $p['harga'] * $j]; $total += $p['harga'] * $j;
        }
        if ($bayar < $total) throw new Exception('Uang pembayaran masih kurang.');
        $idt = q('INSERT INTO transaksi(id_user,total,bayar,kembalian) VALUES(?,?,?,?)', 'iiii', $_SESSION['uid'], $total, $bayar, $bayar - $total)->insert_id;
        foreach ($det as $x) {
            q('INSERT INTO detail_transaksi(id_transaksi,id_produk,nama_produk,harga,jumlah,subtotal) VALUES(?,?,?,?,?,?)', 'iisiii', $idt, ...$x);
            q('UPDATE produk SET stok=stok-? WHERE id_produk=?', 'ii', $x[3], $x[0]);
        }
        $conn->commit(); echo json_encode(['ok' => true, 'id' => $idt]);
    } catch (Throwable $ex) { $conn->rollback(); echo json_encode(['ok' => false, 'pesan' => $ex->getMessage()]); }
    exit;
}
head('Kasir', 'kasir');
$produk = rows('SELECT id_produk id, nama_produk nama, kategori, harga, stok, foto FROM produk ORDER BY nama_produk');
$kats = array_values(array_unique(array_column($produk, 'kategori')));
?>
<style>
.pg{display:grid;grid-template-columns:1fr 380px;gap:22px;align-items:start}
.item{background:#fff;border:1px solid var(--line);border-radius:16px;padding:16px;cursor:pointer;text-align:left;width:100%;box-shadow:var(--sh);transition:transform .15s,border-color .15s}
.item:hover:not(:disabled){border-color:var(--leaf);transform:translateY(-2px)}.item:active:not(:disabled){transform:scale(.98)}
.item:disabled{opacity:.45;cursor:not-allowed}.item b{display:block;font-size:15px;margin:8px 0 2px}.item small{color:var(--mut)}
.pic{width:100%;aspect-ratio:4/3;object-fit:cover;border-radius:11px;background:var(--mint);display:grid;place-items:center;color:var(--leaf);font-size:30px}
.item .k{display:inline-block;background:var(--mint);color:var(--pine);border-radius:7px;padding:2px 9px;font-size:12px;font-weight:700}
.item .h{color:var(--pine);font-weight:800;font-size:16px;margin-top:10px}
.chip{border:1px solid var(--line);background:#fff;border-radius:99px;padding:7px 16px;font-weight:600;font-size:13.5px;transition:background .15s}
.chip:hover{background:var(--mint)}.chip.on{background:var(--pine);color:#fff;border-color:var(--pine)}
.cart{position:sticky;top:24px}.cart #total{font-size:24px;letter-spacing:-.02em}
.qty button{width:30px;height:30px;border:1px solid var(--line);background:#fff;border-radius:9px;line-height:1;font-weight:700}.qty button:hover{background:var(--mint)}
#isi{max-height:38vh;overflow:auto}
@media(max-width:1000px){.pg{grid-template-columns:1fr}.cart{position:static}}
</style>
<h1>Kasir</h1><p class="sub">Ketuk produk untuk menambah ke pesanan.</p>
<div class="pg"><section>
 <div class="d-flex gap-2 flex-wrap mb-3"><input id="cari" class="form-control" style="max-width:260px" placeholder="Cari produk…">
  <button class="chip on" data-k="">Semua</button><?php foreach ($kats as $k): ?><button class="chip" data-k="<?= e($k) ?>"><?= e($k) ?></button><?php endforeach; ?></div>
 <div id="grid" class="row g-2"></div></section>
 <aside class="box cart"><h6 class="fw-bold mb-3">Pesanan</h6><div id="isi"></div><hr>
  <div class="d-flex justify-content-between fs-5 fw-bold"><span>Total</span><span id="total">Rp 0</span></div>
  <label class="form-label mt-3 mb-1">Uang diterima</label><input id="bayar" type="number" min="0" class="form-control form-control-lg">
  <div class="d-flex gap-1 flex-wrap my-2" id="cepat"></div>
  <div class="d-flex justify-content-between"><span class="text-secondary">Kembalian</span><b id="kembali">Rp 0</b></div>
  <div id="pesan" class="text-danger small mt-2"></div>
  <button id="simpan" class="btn btn-leaf btn-lg w-100 mt-3" disabled>Bayar</button></aside></div>
<script>
const P = <?= json_encode($produk) ?>, CSRF = <?= json_encode(csrf()) ?>, cart = {};
const rp = n => 'Rp ' + Number(n).toLocaleString('id-ID'); let kat = '';
const $ = id => document.getElementById(id), esc = s => s.replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));
function grid() {
  const k = $('cari').value.toLowerCase();
  $('grid').innerHTML = P.filter(p => (!kat || p.kategori === kat) && p.nama.toLowerCase().includes(k)).map(p =>
    `<div class="col-6 col-md-4 col-xl-3"><button class="item" ${p.stok < 1 ? 'disabled' : ''} onclick="tambah(${p.id})">${p.foto ? `<img class="pic" alt="" src="uploads/${encodeURIComponent(p.foto)}">` : '<div class="pic"><i class="bi bi-cup-hot"></i></div>'}<span class="k mt-2">${esc(p.kategori)}</span><b>${esc(p.nama)}</b><small>${p.stok < 1 ? 'Habis' : 'Stok ' + p.stok}</small><div class="h">${rp(p.harga)}</div></button></div>`).join('')
    || '<p class="text-secondary">Produk tidak ditemukan.</p>';
}
function tambah(id) { const p = P.find(x => x.id == id); if ((cart[id] || 0) < p.stok) cart[id] = (cart[id] || 0) + 1; else $('pesan').textContent = 'Stok ' + p.nama + ' hanya ' + p.stok; draw(); }
function ubah(id, d) { const p = P.find(x => x.id == id); cart[id] += d; if (cart[id] < 1) delete cart[id]; else if (cart[id] > p.stok) cart[id] = p.stok; draw(); }
function total() { return Object.entries(cart).reduce((s, [id, j]) => s + P.find(x => x.id == id).harga * j, 0); }
function draw() {
  const t = total(), b = +$('bayar').value || 0;
  $('isi').innerHTML = Object.entries(cart).map(([id, j]) => { const p = P.find(x => x.id == id);
    return `<div class="d-flex justify-content-between align-items-center mb-2"><div><b>${esc(p.nama)}</b><br><small class="text-secondary">${rp(p.harga)}</small></div><div class="qty d-flex align-items-center gap-2"><button onclick="ubah(${id},-1)">−</button><b>${j}</b><button onclick="ubah(${id},1)">+</button></div></div>`; }).join('')
    || '<p class="text-secondary mb-0">Belum ada pesanan.</p>';
  $('total').textContent = rp(t); $('kembali').textContent = rp(Math.max(0, b - t));
  $('cepat').innerHTML = t ? [t, 20000, 50000, 100000].filter((v, i) => i === 0 || v > t).map(v => `<button class="chip" onclick="$('bayar').value=${v};draw()">${v === t ? 'Uang pas' : rp(v)}</button>`).join('') : '';
  $('simpan').disabled = !t || b < t;
}
$('cari').oninput = grid; $('bayar').oninput = draw;
document.querySelectorAll('.chip[data-k]').forEach(c => c.onclick = () => { kat = c.dataset.k; document.querySelectorAll('.chip[data-k]').forEach(x => x.classList.toggle('on', x === c)); grid(); });
$('simpan').onclick = async () => {
  $('simpan').disabled = true; $('pesan').textContent = '';
  const r = await fetch('kasir.php?aksi=simpan', {method: 'POST', body: JSON.stringify({csrf: CSRF, bayar: +$('bayar').value, items: Object.entries(cart).map(([id, jumlah]) => ({id, jumlah}))})}).then(r => r.json()).catch(() => ({ok: false, pesan: 'Gagal terhubung ke server.'}));
  if (r.ok) location = 'struk.php?baru=1&id=' + r.id; else { $('pesan').textContent = r.pesan; $('simpan').disabled = false; }
};
grid(); draw();
</script>
<?php foot();
