<?php
require 'config.php';
if (!empty($_SESSION['uid'])) { header('Location: index.php'); exit; }
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_csrf();
    $u = row('SELECT * FROM user WHERE username=?', 's', trim($_POST['username'] ?? ''));
    if ($u && password_verify($_POST['password'] ?? '', $u['password'])) {
        session_regenerate_id(true);
        $_SESSION['uid'] = $u['id_user']; $_SESSION['nama'] = $u['nama']; $_SESSION['role'] = $u['role'];
        header('Location: index.php'); exit;
    }
    $err = 'Username atau password salah. Periksa lagi lalu coba masuk.';
}
?><!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Masuk | Dulur CAFE</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;700;800&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box}body{margin:0;font-family:'Plus Jakarta Sans',sans-serif;min-height:100vh;display:grid;grid-template-columns:1.15fr 1fr;background:#f3f5f2;color:#17211c}
.kiri{position:relative;overflow:hidden;color:#fff;padding:56px 64px;display:flex;flex-direction:column;background:radial-gradient(circle at 0 0,rgba(31,138,91,.65),transparent 50%),radial-gradient(circle at 100% 100%,rgba(227,162,26,.22),transparent 45%),#0f3d2e}
.kiri::after{content:"";position:absolute;width:420px;height:420px;border-radius:50%;border:60px solid rgba(255,255,255,.05);right:-140px;top:-120px}
.logo{display:flex;align-items:center;gap:12px;font-weight:800;font-size:22px}.logo i{width:44px;height:44px;border-radius:13px;background:#e3a21a;color:#0f3d2e;display:grid;place-items:center;font-size:22px}
.kiri h1{font-size:50px;font-weight:800;line-height:1.08;letter-spacing:-.025em;margin:auto 0 18px;max-width:460px}.kiri>p{color:#a9c8ba;font-size:16px;max-width:400px}
.fitur{display:grid;gap:12px;margin-top:30px;padding:0;list-style:none}.fitur li{display:flex;gap:12px;align-items:center;color:#d7e8df}.fitur i{color:#e3a21a;font-size:18px}
.kanan{display:flex;align-items:center;justify-content:center;padding:32px}
.kartu{width:100%;max-width:410px;background:#fff;border-radius:24px;padding:40px 36px;box-shadow:0 30px 60px -28px rgba(15,61,46,.4);border:1px solid #e3e9e5}
.kartu h2{font-weight:800;font-size:26px;letter-spacing:-.02em;margin:0 0 4px}.kartu .t{color:#6b7a73;margin-bottom:26px}
label{font-weight:700;font-size:13.5px;margin-bottom:6px}
.in{position:relative}.in>i{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#8a9a92}
.in .form-control{height:50px;padding-left:42px;border-radius:12px;border-color:#dfe6e2}.in .form-control:focus{border-color:#1f8a5b;box-shadow:0 0 0 .22rem rgba(31,138,91,.15)}
.mata{position:absolute;right:6px;top:50%;transform:translateY(-50%);border:0;background:none;width:40px;height:40px;border-radius:10px;color:#6b7a73}.mata:hover{background:#e6f2ec}
.btn-masuk{height:50px;border:0;border-radius:12px;background:#1f8a5b;color:#fff;font-weight:700;width:100%;box-shadow:0 10px 18px -10px rgba(31,138,91,.9)}.btn-masuk:hover{background:#0f3d2e}
.alert{border:0;border-radius:12px;font-size:14px}:focus-visible{outline:2px solid #e3a21a;outline-offset:2px}
@media(max-width:860px){body{grid-template-columns:1fr}.kiri{padding:28px;min-height:0}.kiri h1{font-size:30px;margin:28px 0 8px}.fitur,.kiri>p{display:none}.kanan{margin-top:-24px;padding:0 16px 32px}.kartu{padding:30px 24px}}
</style></head><body>
<section class="kiri"><div class="logo"><i class="bi bi-cup-hot-fill"></i> Dulur CAFE</div>
<h1>Kasir cepat, stok terjaga.</h1><p>Catat pesanan, hitung kembalian, dan pantau omzet harian dari satu tempat.</p>
<ul class="fitur"><li><i class="bi bi-lightning-charge-fill"></i>Transaksi selesai dalam hitungan detik</li><li><i class="bi bi-box-seam-fill"></i>Stok berkurang otomatis setiap penjualan</li><li><i class="bi bi-graph-up-arrow"></i>Laporan harian dan bulanan siap cetak</li></ul></section>
<section class="kanan"><form method="post" class="kartu">
<h2>Masuk ke kasir</h2><p class="t">Gunakan akun dari admin cafe.</p>
<?php if ($err): ?><div class="alert alert-danger"><i class="bi bi-exclamation-circle"></i> <?= e($err) ?></div><?php endif; ?>
<input type="hidden" name="csrf" value="<?= csrf() ?>">
<label for="u">Username</label><div class="in mb-3"><i class="bi bi-person"></i><input id="u" class="form-control" name="username" autocomplete="username" required autofocus></div>
<label for="p">Password</label><div class="in mb-4"><i class="bi bi-lock"></i><input id="p" class="form-control" type="password" name="password" autocomplete="current-password" required style="padding-right:48px"><button type="button" class="mata" aria-label="Tampilkan password" onclick="var p=document.getElementById('p'),s=p.type=='password';p.type=s?'text':'password';this.innerHTML='<i class=&quot;bi bi-eye'+(s?'-slash':'')+'&quot;></i>'"><i class="bi bi-eye"></i></button></div>
<button class="btn-masuk">Masuk</button></form></section></body></html>
