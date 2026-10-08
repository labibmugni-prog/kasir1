<?php
require 'config.php';
wajib_admin();
$ok = $err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_csrf();
    try {
        if (isset($_POST['hapus'])) {
            if ((int)$_POST['hapus'] === (int)$_SESSION['uid']) throw new Exception('Akun yang sedang dipakai tidak bisa dihapus.');
            q('DELETE FROM user WHERE id_user=?', 'i', (int)$_POST['hapus']); $ok = 'Pengguna dihapus.';
        } else {
            $id = (int)$_POST['id']; $un = trim($_POST['username']); $nama = trim($_POST['nama']); $pw = $_POST['password'];
            $role = $_POST['role'] === 'admin' ? 'admin' : 'kasir';
            if ($id === (int)$_SESSION['uid']) $role = 'admin';
            if ($nama === '' || ($id == 0 && $un === '')) throw new Exception('Nama dan username wajib diisi.');
            if (($id == 0 || $pw !== '') && strlen($pw) < 6) throw new Exception('Password minimal 6 karakter.');
            if ($id) {
                q('UPDATE user SET nama=?, role=? WHERE id_user=?', 'ssi', $nama, $role, $id);
                if ($pw !== '') q('UPDATE user SET password=? WHERE id_user=?', 'si', password_hash($pw, PASSWORD_DEFAULT), $id);
                $ok = 'Pengguna diperbarui.';
            } else {
                q('INSERT INTO user(username,password,nama,role) VALUES(?,?,?,?)', 'ssss', $un, password_hash($pw, PASSWORD_DEFAULT), $nama, $role);
                $ok = 'Pengguna ditambahkan.';
            }
        }
    } catch (mysqli_sql_exception $x) { $err = $x->getCode() == 1062 ? 'Username sudah dipakai. Pilih username lain.' : 'Gagal menyimpan data.'; }
    catch (Exception $x) { $err = $x->getMessage(); }
}
head('Pengguna', 'user');
$ed = isset($_GET['edit']) ? row('SELECT * FROM user WHERE id_user=?', 'i', (int)$_GET['edit']) : null;
$data = rows('SELECT u.*, (SELECT COUNT(*) FROM transaksi WHERE id_user=u.id_user) trx FROM user u ORDER BY role, nama');
?>
<h1>Pengguna</h1><p class="sub">Atur akun admin dan kasir yang bisa masuk ke aplikasi.</p>
<?php if ($ok): ?><div class="alert alert-success py-2"><?= e($ok) ?></div><?php endif; ?><?php if ($err): ?><div class="alert alert-danger py-2"><?= e($err) ?></div><?php endif; ?>
<form method="post" class="box mb-3"><input type="hidden" name="csrf" value="<?= csrf() ?>"><input type="hidden" name="id" value="<?= $ed['id_user'] ?? 0 ?>">
 <h6 class="mb-3"><?= $ed ? 'Edit pengguna' : 'Tambah pengguna' ?></h6>
 <div class="row g-2">
  <div class="col-md-3"><input class="form-control" name="nama" placeholder="Nama lengkap" value="<?= e($ed['nama'] ?? '') ?>" required></div>
  <div class="col-md-2"><input class="form-control" name="username" placeholder="Username" value="<?= e($ed['username'] ?? '') ?>" <?= $ed ? 'readonly' : 'required' ?>></div>
  <div class="col-md-2"><select class="form-select" name="role"><option value="kasir" <?= ($ed['role'] ?? '') === 'kasir' ? 'selected' : '' ?>>Kasir</option><option value="admin" <?= ($ed['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option></select></div>
  <div class="col-md-3"><input class="form-control" type="password" name="password" placeholder="<?= $ed ? 'Password baru (kosongkan jika tetap)' : 'Password (min. 6 karakter)' ?>" autocomplete="new-password"></div>
  <div class="col-md-2 d-flex gap-2"><button class="btn btn-leaf flex-fill"><?= $ed ? 'Simpan' : 'Tambah' ?></button><?php if ($ed): ?><a href="user.php" class="btn btn-outline-secondary">Batal</a><?php endif; ?></div>
 </div></form>
<div class="box"><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Nama</th><th>Username</th><th>Peran</th><th>Transaksi</th><th></th></tr></thead><tbody>
<?php foreach ($data as $r): ?><tr><td class="fw-semibold"><?= e($r['nama']) ?><?= $r['id_user'] == $_SESSION['uid'] ? ' <small class="text-secondary">(Anda)</small>' : '' ?></td><td><?= e($r['username']) ?></td>
 <td><span class="badge <?= $r['role'] === 'admin' ? 'bg-dark' : '' ?>" <?= $r['role'] === 'kasir' ? 'style="background:var(--mint);color:var(--pine)"' : '' ?>><?= e(ucfirst($r['role'])) ?></span></td><td><?= $r['trx'] ?></td>
 <td class="text-end text-nowrap"><a class="btn btn-sm btn-outline-secondary" href="?edit=<?= $r['id_user'] ?>">Edit</a>
 <?php if ($r['id_user'] != $_SESSION['uid']): ?><form method="post" class="d-inline" onsubmit="return confirm('Hapus pengguna ini? Riwayat transaksinya tetap tersimpan.')"><input type="hidden" name="csrf" value="<?= csrf() ?>"><button name="hapus" value="<?= $r['id_user'] ?>" class="btn btn-sm btn-outline-danger">Hapus</button></form><?php endif; ?></td></tr>
<?php endforeach; ?></tbody></table></div></div>
<?php foot();
