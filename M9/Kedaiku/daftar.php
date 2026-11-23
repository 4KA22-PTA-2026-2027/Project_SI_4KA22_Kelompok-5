<?php
require 'layout.php';
require 'koneksi.php';

$kode = $_GET['k'] ?? '';
$st = $db->prepare('SELECT * FROM kategori WHERE kode=?');
$st->bind_param('s', $kode);
$st->execute();
$kat = $st->get_result()->fetch_assoc();
if (!$kat) { header('Location: menu.php'); exit; }

$st = $db->prepare('SELECT * FROM menu WHERE kategori_id=? ORDER BY id');
$st->bind_param('i', $kat['id']);
$st->execute();
$items = $st->get_result()->fetch_all(MYSQLI_ASSOC);

$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);
$ikonKat = $kat['kode'] === 'minuman' ? 'gelas' : 'garpu';
mulai($kat['nama'], 'menu.php', true);
?>
  <main class="kategori">
    <a class="kembali" href="menu.php">&lsaquo; Kembali ke kategori</a>

    <div class="judul">
      <?= ikon('topi', 'topi') ?>
      <h1><span class="k">MENU</span> <span class="m"><?= e(mb_strtoupper($kat['nama'])) ?></span></h1>
    </div>

    <?php if ($items): ?>
    <div class="produk-grid">
      <?php foreach ($items as $m): $habis = $m['stok'] <= 0; ?>
        <article class="produk<?= $habis ? ' habis' : '' ?>">
          <div class="pic">
            <?php if ($m['foto']): ?>
              <img src="uploads/<?= e($m['foto']) ?>" alt="<?= e($m['nama']) ?>" loading="lazy">
            <?php else: echo ikon($ikonKat); endif; ?>
            <?php if ($habis): ?><span class="lencana">Habis</span>
            <?php elseif ($m['stok'] <= 5): ?><span class="lencana sisa">Sisa <?= (int)$m['stok'] ?></span><?php endif; ?>
          </div>
          <h3><?= e($m['nama']) ?></h3>
          <?php if ($m['deskripsi']): ?><p class="desk"><?= e($m['deskripsi']) ?></p><?php endif; ?>
          <div class="baris">
            <span class="harga"><?= rp($m['harga']) ?></span>
            <?php if ($habis): ?>
              <button class="btn-add" disabled>Habis</button>
            <?php else: ?>
            <form method="post" action="keranjang.php">
              <input type="hidden" name="t" value="<?= csrf() ?>">
              <input type="hidden" name="a" value="add">
              <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
              <input type="hidden" name="back" value="daftar.php?k=<?= e($kat['kode']) ?>">
              <button class="btn-add">+ Keranjang</button>
            </form>
            <?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
      <p class="kosong">Belum ada menu di kategori ini.</p>
    <?php endif; ?>
  </main>

  <?php if ($flash): ?>
    <div class="toast"><?= e($flash) ?> <a href="keranjang.php">Lihat keranjang</a></div>
  <?php endif; ?>
<?php akhir(true);
