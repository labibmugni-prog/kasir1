<?php
session_start();
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn = new mysqli('localhost', 'root', '', 'kasir1');
$conn->set_charset('utf8mb4');

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function rp($n) { return 'Rp ' . number_format((int)$n, 0, ',', '.'); }
function csrf() { return $_SESSION['csrf'] ??= bin2hex(random_bytes(16)); }
function cek_csrf() { if (!hash_equals(csrf(), $_POST['csrf'] ?? '')) die('Token tidak valid. Muat ulang halaman.'); }
function thumb($f, $px = 46) {
    $st = "width:{$px}px;height:{$px}px;border-radius:12px;background:var(--mint);color:var(--leaf)";
    return $f ? '<img src="uploads/' . rawurlencode($f) . '" alt="" style="' . $st . ';object-fit:cover">'
              : '<span style="' . $st . ';display:inline-grid;place-items:center"><i class="bi bi-cup-hot"></i></span>';
}
function simpan_foto($f) {
    if (empty($f['name']) || $f['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ($f['error'] !== UPLOAD_ERR_OK || $f['size'] > 2 * 1024 * 1024) throw new Exception('Foto gagal diunggah atau lebih dari 2 MB.');
    $info = @getimagesize($f['tmp_name']);
    $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$info['mime'] ?? ''] ?? null;
    if (!$ext) throw new Exception('Format foto harus JPG, PNG, atau WebP.');
    if (!is_dir(__DIR__ . '/uploads')) mkdir(__DIR__ . '/uploads', 0755, true);
    $nama = bin2hex(random_bytes(8)) . '.' . $ext;
    if (!move_uploaded_file($f['tmp_name'], __DIR__ . '/uploads/' . $nama)) throw new Exception('Foto tidak bisa disimpan. Periksa izin folder uploads.');
    return $nama;
}
function hapus_foto($n) { $p = __DIR__ . '/uploads/' . basename((string)$n); if ($n && is_file($p)) unlink($p); }
function wajib_admin() { wajib_login(); if (!admin()) { header('Location: index.php'); exit; } }
function wajib_login() { if (empty($_SESSION['uid'])) { header('Location: login.php'); exit; } }
function admin() { return ($_SESSION['role'] ?? '') === 'admin'; }
function q($sql, $t = '', ...$p) { global $conn; $s = $conn->prepare($sql); if ($t) $s->bind_param($t, ...$p); $s->execute(); return $s; }
function rows($sql, $t = '', ...$p) { return q($sql, $t, ...$p)->get_result()->fetch_all(MYSQLI_ASSOC); }
function row($sql, $t = '', ...$p) { return rows($sql, $t, ...$p)[0] ?? null; }

function head($judul, $aktif) {
    wajib_login();
    $menu = ['index' => [admin() ? 'Dashboard' : 'Beranda', 'bi-grid-1x2'], 'kasir' => ['Kasir', 'bi-receipt']];
    if (admin()) $menu += ['produk' => ['Produk', 'bi-cup-hot'], 'laporan' => ['Laporan', 'bi-bar-chart'], 'user' => ['Pengguna', 'bi-people']];
    ?><!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($judul) ?> | Dulur CAFE</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;700;800&display=swap" rel="stylesheet">
<style>
:root{--pine:#0f3d2e;--leaf:#1f8a5b;--mint:#e6f2ec;--amber:#e3a21a;--bg:#f3f5f2;--ink:#17211c;--mut:#6b7a73;--line:#e3e9e5;--sh:0 1px 2px rgba(15,61,46,.05),0 10px 26px -14px rgba(15,61,46,.22)}
body{font-family:'Plus Jakarta Sans',system-ui,sans-serif;background:var(--bg);color:var(--ink);font-size:14.5px;-webkit-font-smoothing:antialiased}
.side{position:fixed;inset:0 auto 0 0;width:240px;background:linear-gradient(180deg,#0f3d2e,#0a2a20);color:#bcd5c9;padding:24px 14px;display:flex;flex-direction:column}
.brand{display:flex;align-items:center;gap:11px;font-weight:800;font-size:19px;color:#fff;padding:0 8px 28px}
.brand i{width:36px;height:36px;border-radius:11px;background:var(--amber);color:var(--pine);display:grid;place-items:center;font-size:18px}
.side a{position:relative;display:flex;gap:12px;align-items:center;color:#bcd5c9;text-decoration:none;padding:11px 14px;border-radius:11px;font-weight:600;margin-bottom:4px;transition:background .15s}
.side a:hover{background:rgba(255,255,255,.07);color:#fff}
.side a.on{background:rgba(255,255,255,.11);color:#fff}
.side a.on::before{content:"";position:absolute;left:-14px;top:10px;bottom:10px;width:4px;border-radius:0 4px 4px 0;background:var(--amber)}
.side a.on i{color:var(--amber)}
.side .user{margin-top:auto;padding:14px;border-radius:14px;background:rgba(255,255,255,.07);font-size:13px;line-height:1.5}
.side .user b{color:#fff}
main{margin-left:240px;padding:36px 42px;max-width:1500px}
h1{font-size:28px;font-weight:800;letter-spacing:-.02em;margin-bottom:4px}.sub{color:var(--mut);margin-bottom:26px}
h6{font-weight:800}
.box{background:#fff;border:1px solid var(--line);border-radius:18px;padding:22px;box-shadow:var(--sh)}
.hero{background:radial-gradient(circle at 92% -10%,rgba(31,138,91,.55),transparent 50%),#0f3d2e;color:#fff;border:0}
.stat{position:relative;overflow:hidden}
.stat::after{content:"";position:absolute;right:-22px;top:-22px;width:92px;height:92px;border-radius:50%;background:var(--mint)}
.stat small{color:var(--mut);font-weight:600;position:relative;z-index:1}.stat b{position:relative;z-index:1;display:block;font-size:27px;font-weight:800;margin-top:4px;letter-spacing:-.02em}
.btn{border-radius:11px;font-weight:600;padding:.55rem 1rem}
.btn-leaf{background:var(--leaf);color:#fff;border:0;box-shadow:0 8px 16px -8px rgba(31,138,91,.8)}.btn-leaf:hover{background:var(--pine);color:#fff}.btn-leaf:disabled{background:#9db8ab;box-shadow:none;color:#fff}
.btn-outline-success{color:var(--leaf);border-color:#b9d9c9}.btn-outline-success:hover{background:var(--leaf);border-color:var(--leaf)}
.badge{font-weight:600;border-radius:8px;padding:.45em .7em}
.table{--bs-table-bg:transparent}.table>:not(caption)>*>*{padding:.9rem .75rem;border-bottom-color:var(--line)}
.table th{color:var(--mut);font-weight:600;font-size:12.5px;background:#f8faf9}.table tbody tr:hover{background:#f8faf9}
.form-control,.form-select{border-radius:11px;border-color:var(--line);padding:.6rem .9rem}
.form-control:focus,.form-select:focus{border-color:var(--leaf);box-shadow:0 0 0 .22rem rgba(31,138,91,.15)}
.alert{border:0;border-radius:12px}hr{border-color:var(--line);opacity:1}
a:focus-visible,button:focus-visible{outline:2px solid var(--amber);outline-offset:2px}
@media(max-width:860px){.side{position:static;width:auto;flex-direction:row;flex-wrap:wrap;align-items:center;padding:10px 12px;gap:2px}.brand{padding:4px 10px}.side a{padding:8px 12px;margin:0}.side a.on::before{display:none}.side .user{display:none}main{margin:0;padding:20px 14px}}
@media print{.side,.noprint{display:none!important}main{margin:0;padding:0}.box{box-shadow:none}}
</style></head><body>
<nav class="side"><div class="brand"><i class="bi bi-cup-hot-fill"></i> Dulur CAFE</div>
<?php foreach ($menu as $f => [$n, $ic]): ?><a href="<?= $f ?>.php" class="<?= $f === $aktif ? 'on' : '' ?>"><i class="bi <?= $ic ?>"></i><?= $n ?></a><?php endforeach; ?>
<div class="user"><b><?= e($_SESSION['nama']) ?></b><br><?= e($_SESSION['role']) ?> · <a href="logout.php" style="display:inline;padding:0;color:var(--amber)">Keluar</a></div></nav>
<main><?php
}
function foot() { echo '</main></body></html>'; }
