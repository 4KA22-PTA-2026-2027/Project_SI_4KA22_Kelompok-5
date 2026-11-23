<?php
require 'layout.php';
require 'koneksi.php';
$kategori = $db->query('SELECT * FROM kategori ORDER BY urutan')->fetch_all(MYSQLI_ASSOC);
mulai('Menu', 'menu.php', true);
?>
  <main class="kategori">
    <?= ikon('topi', 'deco d1') ?>
    <?= ikon('garpu', 'deco d2') ?>

    <div class="judul">
      <?= ikon('topi', 'topi') ?>
      <h1><span class="k">KATEGORI</span> <span class="m">MENU</span></h1>
    </div>

    <div class="kat-grid dua">
      <?php foreach ($kategori as $k): ?>
        <a class="kat" href="daftar.php?k=<?= e($k['kode']) ?>">
          <div class="foto"><img src="images/<?= e($k['foto']) ?>" alt="<?= e($k['nama']) ?>"></div>
          <span class="label"><?= e($k['nama']) ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </main>
<?php akhir(true);
