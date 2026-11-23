<?php
require 'layout.php';
require 'koneksi.php';
wajib_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_csrf();
    $a = $_POST['a'] ?? '';
    $pesan = '';

    if ($a === 'status' && in_array($_POST['status'] ?? '', ['Baru', 'Diproses', 'Selesai'], true)) {
        $id = (int)$_POST['id'];
        $st = $db->prepare('UPDATE pesanan SET status=? WHERE id=?');
        $st->bind_param('si', $_POST['status'], $id);
        $st->execute();

    } elseif ($a === 'simpan') {                       // ubah harga & stok
        $id    = (int)$_POST['id'];
        $harga = max(1, (int)($_POST['harga'] ?? 0));
        $stok  = max(0, (int)($_POST['stok'] ?? 0));
        $st = $db->prepare('UPDATE menu SET harga=?, stok=? WHERE id=?');
        $st->bind_param('iii', $harga, $stok, $id);
        $st->execute();
        $pesan = 'Harga dan stok disimpan.';

    } elseif ($a === 'foto') {                         // pasang / ganti foto produk yang sudah ada
        $id = (int)$_POST['id'];
        $st = $db->prepare('SELECT foto FROM menu WHERE id=?');
        $st->bind_param('i', $id);
        $st->execute();
        $r = $st->get_result()->fetch_assoc();
        if (!$r) {
            $pesan = 'Produk tidak ditemukan.';
        } elseif (empty($_FILES['foto']['tmp_name']) || !is_uploaded_file($_FILES['foto']['tmp_name'])) {
            $pesan = 'Pilih file foto dulu sebelum menekan Unggah.';
        } else {
            $info = @getimagesize($_FILES['foto']['tmp_name']);
            $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$info['mime'] ?? ''] ?? null;
            if (!$ext || $_FILES['foto']['size'] >= 5000000) {
                $pesan = 'Foto harus JPG, PNG, atau WEBP dengan ukuran maksimal 5 MB.';
            } else {
                $baru = bin2hex(random_bytes(8)) . '.' . $ext;
                if (move_uploaded_file($_FILES['foto']['tmp_name'], __DIR__ . '/uploads/' . $baru)) {
                    $st = $db->prepare('UPDATE menu SET foto=? WHERE id=?');
                    $st->bind_param('si', $baru, $id);
                    $st->execute();
                    if ($r['foto'] && is_file(__DIR__ . '/uploads/' . basename($r['foto']))) {
                        unlink(__DIR__ . '/uploads/' . basename($r['foto']));
                    }
                    $pesan = 'Foto berhasil dipasang.';
                } else {
                    $pesan = 'Foto gagal disimpan. Pastikan folder uploads bisa ditulis.';
                }
            }
        }

    } elseif ($a === 'hapus') {
        $id = (int)$_POST['id'];
        $st = $db->prepare('SELECT foto FROM menu WHERE id=?');
        $st->bind_param('i', $id);
        $st->execute();
        $r = $st->get_result()->fetch_assoc();
        if ($r && $r['foto'] && is_file(__DIR__ . '/uploads/' . basename($r['foto']))) {
            unlink(__DIR__ . '/uploads/' . basename($r['foto']));
        }
        $st = $db->prepare('DELETE FROM menu WHERE id=?');
        $st->bind_param('i', $id);
        $st->execute();
        $pesan = 'Produk dihapus.';

    } elseif ($a === 'tambah') {
        $nama  = mb_substr(trim($_POST['nama'] ?? ''), 0, 100);
        $harga = (int)($_POST['harga'] ?? 0);
        $stok  = max(0, (int)($_POST['stok'] ?? 0));
        $kid   = (int)($_POST['kategori_id'] ?? 0);
        $desk  = mb_substr(trim($_POST['deskripsi'] ?? ''), 0, 255);
        $foto  = null;

        if (!empty($_FILES['foto']['tmp_name']) && is_uploaded_file($_FILES['foto']['tmp_name'])) {
            $info = @getimagesize($_FILES['foto']['tmp_name']);
            $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$info['mime'] ?? ''] ?? null;
            if ($ext && $_FILES['foto']['size'] < 5000000) {
                $foto = bin2hex(random_bytes(8)) . '.' . $ext;
                move_uploaded_file($_FILES['foto']['tmp_name'], __DIR__ . '/uploads/' . $foto);
            } else {
                $pesan = 'Foto harus JPG, PNG, atau WEBP maksimal 5 MB, jadi produk disimpan tanpa foto. ';
            }
        }
        if ($nama !== '' && $harga > 0 && $kid > 0) {
            $st = $db->prepare('INSERT INTO menu (kategori_id, nama, harga, deskripsi, foto, stok) VALUES (?,?,?,?,?,?)');
            $st->bind_param('isissi', $kid, $nama, $harga, $desk, $foto, $stok);
            $st->execute();
            $pesan .= 'Produk "' . $nama . '" ditambahkan.';
        } else {
            $pesan = 'Nama, harga, dan kategori wajib diisi.';
        }

    } elseif ($a === 'ganti_pass') {
        $lama = $_POST['lama'] ?? '';
        $baru = $_POST['baru'] ?? '';
        $uid  = (int)$_SESSION['admin'];
        $st = $db->prepare('SELECT password FROM users WHERE id=?');
        $st->bind_param('i', $uid);
        $st->execute();
        $r = $st->get_result()->fetch_assoc();
        if (!$r || !password_verify($lama, $r['password'])) {
            $pesan = 'Password lama salah.';
        } elseif (strlen($baru) < 8) {
            $pesan = 'Password baru minimal 8 karakter.';
        } else {
            $h = password_hash($baru, PASSWORD_DEFAULT);
            $st = $db->prepare('UPDATE users SET password=? WHERE id=?');
            $st->bind_param('si', $h, $uid);
            $st->execute();
            $pesan = 'Password berhasil diganti.';
        }
    }
    if ($pesan !== '') $_SESSION['flash'] = $pesan;
    $balik = in_array($a, ['simpan', 'foto', 'hapus', 'tambah'], true) ? 'admin.php#produk' : 'admin.php';
    header('Location: ' . $balik);
    exit;
}

$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);
$kategori = $db->query('SELECT id, nama FROM kategori ORDER BY urutan')->fetch_all(MYSQLI_ASSOC);
$pesanan  = $db->query('SELECT * FROM pesanan ORDER BY id DESC LIMIT 50')->fetch_all(MYSQLI_ASSOC);
$produk   = $db->query('SELECT m.*, k.nama AS kat FROM menu m JOIN kategori k ON k.id=m.kategori_id ORDER BY k.urutan, m.id')->fetch_all(MYSQLI_ASSOC);
mulai('Admin', 'admin.php');
?>
  <main class="admin">
    <h1 class="judul-admin">Panel Admin</h1>
    <?php if ($flash): ?><div class="galat info"><?= e(trim($flash)) ?></div><?php endif; ?>

    <h2>Pesanan Masuk</h2>
    <div class="scroll">
    <table class="tabel">
      <tr><th>No</th><th>Pemesan</th><th>Pesanan</th><th>Total</th><th>Status</th></tr>
      <?php foreach ($pesanan as $o):
          $li = $db->query('SELECT nama, jumlah FROM detail_pesanan WHERE pesanan_id=' . (int)$o['id'])->fetch_all(MYSQLI_ASSOC); ?>
        <tr>
          <td>#<?= (int)$o['id'] ?><br><small><?= e($o['created_at']) ?></small></td>
          <td><?= e($o['nama_pelanggan']) ?><br><small><?= e($o['kontak']) ?></small><?php if ($o['catatan']): ?><br><small>Catatan: <?= e($o['catatan']) ?></small><?php endif; ?></td>
          <td><?php foreach ($li as $i) echo e($i['nama']) . ' &times;' . (int)$i['jumlah'] . '<br>'; ?></td>
          <td><?= rp($o['total']) ?></td>
          <td>
            <form method="post">
              <input type="hidden" name="t" value="<?= csrf() ?>"><input type="hidden" name="a" value="status"><input type="hidden" name="id" value="<?= (int)$o['id'] ?>">
              <select name="status" onchange="this.form.submit()">
                <?php foreach (['Baru', 'Diproses', 'Selesai'] as $s): ?><option<?= $s === $o['status'] ? ' selected' : '' ?>><?= $s ?></option><?php endforeach; ?>
              </select>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$pesanan): ?><tr><td colspan="5" class="kosong-sel">Belum ada pesanan.</td></tr><?php endif; ?>
    </table>
    </div>

    <h2 id="produk">Stok &amp; Harga Produk</h2>
    <div class="scroll">
    <table class="tabel">
      <tr><th>Produk</th><th>Foto</th><th>Harga (Rp)</th><th>Stok</th><th></th><th></th></tr>
      <?php foreach ($produk as $m): ?>
        <tr>
          <td><?= e($m['nama']) ?><br><small><?= e($m['kat']) ?></small><?php if ($m['stok'] <= 0): ?> <span class="tag-habis">Habis</span><?php endif; ?>
            <form method="post" id="f<?= (int)$m['id'] ?>"><input type="hidden" name="t" value="<?= csrf() ?>"><input type="hidden" name="a" value="simpan"><input type="hidden" name="id" value="<?= (int)$m['id'] ?>"></form>
          </td>
          <td class="berkas">
            <?php if ($m['foto']): ?><img class="thumb" src="uploads/<?= e($m['foto']) ?>" alt=""><?php else: ?><span class="tanpa">Belum ada</span><?php endif; ?>
            <form method="post" enctype="multipart/form-data" id="g<?= (int)$m['id'] ?>"><input type="hidden" name="t" value="<?= csrf() ?>"><input type="hidden" name="a" value="foto"><input type="hidden" name="id" value="<?= (int)$m['id'] ?>"></form>
            <input form="g<?= (int)$m['id'] ?>" type="file" name="foto" accept="image/jpeg,image/png,image/webp">
            <button form="g<?= (int)$m['id'] ?>" class="pil">Unggah</button>
          </td>
          <td><input form="f<?= (int)$m['id'] ?>" class="angka" type="number" name="harga" min="1" value="<?= (int)$m['harga'] ?>"></td>
          <td><input form="f<?= (int)$m['id'] ?>" class="angka kecil" type="number" name="stok" min="0" value="<?= (int)$m['stok'] ?>"></td>
          <td><button form="f<?= (int)$m['id'] ?>" class="pil on">Simpan</button></td>
          <td>
            <form method="post" onsubmit="return confirm('Hapus produk ini?')"><input type="hidden" name="t" value="<?= csrf() ?>"><input type="hidden" name="a" value="hapus"><input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
              <button class="hapus">Hapus</button></form>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
    </div>

    <h2>Tambah Produk</h2>
    <form method="post" enctype="multipart/form-data" class="form-admin">
      <input type="hidden" name="t" value="<?= csrf() ?>"><input type="hidden" name="a" value="tambah">
      <input name="nama" placeholder="Nama produk *" maxlength="100" required>
      <input name="harga" type="number" min="1" placeholder="Harga (Rp) *" required>
      <input name="stok" type="number" min="0" placeholder="Stok awal (porsi) *" required>
      <select name="kategori_id" required>
        <?php foreach ($kategori as $k): ?><option value="<?= (int)$k['id'] ?>"><?= e($k['nama']) ?></option><?php endforeach; ?>
      </select>
      <input name="deskripsi" placeholder="Deskripsi singkat (opsional)" maxlength="255">
      <label class="file">Foto produk (opsional, JPG/PNG/WEBP, maks 5 MB)<input type="file" name="foto" accept="image/jpeg,image/png,image/webp"></label>
      <button class="btn-gelap">Simpan Produk</button>
    </form>

    <h2>Ganti Password Admin</h2>
    <form method="post" class="form-admin">
      <input type="hidden" name="t" value="<?= csrf() ?>"><input type="hidden" name="a" value="ganti_pass">
      <input name="lama" type="password" placeholder="Password lama" required autocomplete="current-password">
      <input name="baru" type="password" placeholder="Password baru (min. 8 karakter)" required autocomplete="new-password">
      <button class="btn-gelap">Ganti Password</button>
    </form>
  </main>
<?php akhir();
