<?php
require 'layout.php';
require 'koneksi.php';

$_SESSION['cart'] = $_SESSION['cart'] ?? [];
$aksi = $_POST['a'] ?? '';


function tujuan($b) {
    return preg_match('/^daftar\.php\?k=[a-z0-9_-]+$/', $b) ? $b : 'keranjang.php';
}

// --- Ubah isi keranjang ---
if (in_array($aksi, ['add', 'inc', 'dec', 'hapus'], true)) {
    cek_csrf();
    $id = (int)($_POST['id'] ?? 0);
    if ($aksi === 'add' || $aksi === 'inc') {
        $st = $db->prepare('SELECT nama, stok FROM menu WHERE id=?');
        $st->bind_param('i', $id);
        $st->execute();
        if ($r = $st->get_result()->fetch_assoc()) {
            $sekarang = $_SESSION['cart'][$id] ?? 0;
            if ($r['stok'] <= 0) {
                $_SESSION['flash'] = 'Maaf, ' . $r['nama'] . ' sudah habis.';
            } elseif ($sekarang + 1 > $r['stok']) {
                $_SESSION['flash'] = 'Stok ' . $r['nama'] . ' tinggal ' . $r['stok'] . '.';
            } else {
                $_SESSION['cart'][$id] = min(20, $sekarang + 1);
                if ($aksi === 'add') $_SESSION['flash'] = $r['nama'] . ' ditambahkan ke keranjang.';
            }
        }
    } elseif ($aksi === 'dec') {
        if (isset($_SESSION['cart'][$id]) && --$_SESSION['cart'][$id] <= 0) unset($_SESSION['cart'][$id]);
    } else {
        unset($_SESSION['cart'][$id]);
    }
    header('Location: ' . tujuan($_POST['back'] ?? ''));
    exit;
}

// --- Ambil isi keranjang dari database 
$baris = [];
$total = 0;
$info  = '';
if ($_SESSION['cart']) {
    $ids = implode(',', array_map('intval', array_keys($_SESSION['cart'])));
    $r = $db->query("SELECT id, nama, harga, stok FROM menu WHERE id IN ($ids) ORDER BY FIELD(id, $ids)");
    $ada = [];
    while ($m = $r->fetch_assoc()) {
        $ada[] = (int)$m['id'];
        $jml = $_SESSION['cart'][$m['id']];
        if ($m['stok'] <= 0) {
            unset($_SESSION['cart'][$m['id']]);
            $info .= $m['nama'] . ' sudah habis dan dikeluarkan dari keranjang. ';
            continue;
        }
        if ($jml > $m['stok']) {
            $jml = (int)$m['stok'];
            $_SESSION['cart'][$m['id']] = $jml;
            $info .= 'Jumlah ' . $m['nama'] . ' disesuaikan dengan stok. ';
        }
        $m['jml'] = $jml;
        $m['sub'] = $m['harga'] * $jml;
        $total += $m['sub'];
        $baris[] = $m;
    }
    foreach (array_keys($_SESSION['cart']) as $k) {   // menu yang sudah dihapus admin
        if (!in_array((int)$k, $ada, true)) unset($_SESSION['cart'][$k]);
    }
}

// --- Kirim pesanan
$galat = '';
$nama = $kontak = $catatan = '';
if ($aksi === 'pesan') {
    cek_csrf();
    $nama    = mb_substr(trim($_POST['nama'] ?? ''), 0, 60);
    $kontak  = mb_substr(trim($_POST['kontak'] ?? ''), 0, 50);
    $catatan = mb_substr(trim($_POST['catatan'] ?? ''), 0, 255);

    if (!$baris) {
        $galat = 'Keranjang masih kosong.';
    } elseif ($nama === '') {
        $galat = 'Isi nama pemesan dulu ya.';
    } elseif (time() - ($_SESSION['last_order'] ?? 0) < 20) {
        $galat = 'Tunggu sebentar sebelum memesan lagi.';
    } else {
        $db->begin_transaction();
        $ok = true;
        $kurang = '';
        $u = $db->prepare('UPDATE menu SET stok = stok - ? WHERE id=? AND stok >= ?');
        foreach ($baris as $m) {
            $u->bind_param('iii', $m['jml'], $m['id'], $m['jml']);
            $u->execute();
            if ($u->affected_rows < 1) { $ok = false; $kurang = $m['nama']; break; }
        }
        if (!$ok) {
            $db->rollback();
            $galat = 'Maaf, stok ' . $kurang . ' baru saja habis atau tidak cukup. Periksa keranjangmu.';
        } else {
            $st = $db->prepare('INSERT INTO pesanan (nama_pelanggan, kontak, catatan, total) VALUES (?,?,?,?)');
            $st->bind_param('sssi', $nama, $kontak, $catatan, $total);
            $st->execute();
            $pid = $db->insert_id;
            $d = $db->prepare('INSERT INTO detail_pesanan (pesanan_id, menu_id, nama, harga, jumlah) VALUES (?,?,?,?,?)');
            foreach ($baris as $m) {
                $d->bind_param('iisii', $pid, $m['id'], $m['nama'], $m['harga'], $m['jml']);
                $d->execute();
            }
            $db->commit();

            $teks = "Halo Kedaiku, saya mau pesan (Pesanan #$pid):\n\n";
            $no = 1;
            foreach ($baris as $m) {
                $teks .= $no++ . '. ' . $m['nama'] . ' x' . $m['jml'] . ' = ' . rp($m['sub']) . "\n";
            }
            $teks .= "\nTotal: " . rp($total) . "\nNama: $nama";
            if ($kontak !== '')  $teks .= "\nMeja/Kontak: $kontak";
            if ($catatan !== '') $teks .= "\nCatatan: $catatan";
            $teks .= "\n\nMohon info pembayarannya ya. Terima kasih!";

            $_SESSION['cart'] = [];
            $_SESSION['last_order'] = time();
            header('Location: https://wa.me/' . $WA_NOMOR . '?text=' . rawurlencode($teks));
            exit;
        }
    }
}

mulai('Keranjang', 'keranjang.php', true);
?>
  <main class="kategori">
    <div class="judul">
      <?= ikon('topi', 'topi') ?>
      <h1><span class="k">KERANJANG</span></h1>
    </div>

    <?php if ($info): ?><div class="galat info k-lebar"><?= e(trim($info)) ?></div><?php endif; ?>

    <?php if ($baris): ?>
      <div class="k-list">
        <?php foreach ($baris as $m): ?>
          <div class="k-item">
            <div class="k-nama"><b><?= e($m['nama']) ?></b><small><?= rp($m['harga']) ?><?= $m['stok'] <= 5 ? ' &middot; sisa ' . (int)$m['stok'] : '' ?></small></div>
            <div class="k-qty">
              <form method="post"><input type="hidden" name="t" value="<?= csrf() ?>"><input type="hidden" name="a" value="dec"><input type="hidden" name="id" value="<?= (int)$m['id'] ?>"><button class="bulat" aria-label="Kurangi">&minus;</button></form>
              <span><?= (int)$m['jml'] ?></span>
              <form method="post"><input type="hidden" name="t" value="<?= csrf() ?>"><input type="hidden" name="a" value="inc"><input type="hidden" name="id" value="<?= (int)$m['id'] ?>"><button class="bulat" aria-label="Tambah">+</button></form>
            </div>
            <div class="k-sub"><?= rp($m['sub']) ?></div>
            <form method="post"><input type="hidden" name="t" value="<?= csrf() ?>"><input type="hidden" name="a" value="hapus"><input type="hidden" name="id" value="<?= (int)$m['id'] ?>"><button class="hapus">Hapus</button></form>
          </div>
        <?php endforeach; ?>
      </div>

      <p class="k-total">Total <strong><?= rp($total) ?></strong></p>

      <form method="post" class="form-pesan">
        <h2>Data Pemesan</h2>
        <?php if ($galat): ?><div class="galat"><?= e($galat) ?></div><?php endif; ?>
        <input type="hidden" name="t" value="<?= csrf() ?>">
        <input type="hidden" name="a" value="pesan">
        <input name="nama" placeholder="Nama pemesan *" maxlength="60" required value="<?= e($nama) ?>">
        <input name="kontak" placeholder="Nomor meja / nomor WhatsApp (opsional)" maxlength="50" value="<?= e($kontak) ?>">
        <input name="catatan" placeholder="Catatan, mis. sambal dipisah (opsional)" maxlength="255" value="<?= e($catatan) ?>">
        <button class="btn-wa"><?= ikon('wa') ?> Pesan lewat WhatsApp</button>
        <p class="catatan-kecil">Pesananmu akan dikirim ke WhatsApp kedai untuk konfirmasi dan pembayaran.</p>
      </form>
    <?php else: ?>
      <p class="kosong">Keranjangmu masih kosong.</p>
      <p class="tengah"><a class="btn-add besar" href="menu.php">Lihat Menu</a></p>
    <?php endif; ?>
  </main>
<?php akhir(true);
