<?php
require 'layout.php';
mulai('Warung Makan Kedaiku', 'index.php');
?>

  <!-- HERO -->
  <section class="hero">
    <div class="hero-text">
      <h2 class="t1">WARUNG MAKAN</h2>
      <h1 class="t2">KEDAIKU</h1>
      <p class="ribbon">Nikmati aneka hidangan lezat<br>dengan harga bersahabat</p>
      <a href="menu.php" class="btn-menu">Lihat Menu &rsaquo;</a>

      <div class="promo">
        <div class="paket"><small>PAKET</small><strong>20.000</strong><em>/ PER PORSI</em></div>
        <div class="free">FREE TEH<br>ES MANIS</div>
      </div>
    </div>
  <img class="hero-photo-m" src="images/hero-photo.jpg" alt="Aneka lauk Kedaiku">
  <div class="hero-img"><img src="images/hero-right.jpg" alt="Maskot dan aneka lauk Kedaiku"></div>
  </section>

  <!-- SELAMAT DATANG -->
  <section class="welcome">
    <img class="plate left" src="images/piring-kiri.jpg" alt="">
    <img class="plate right" src="images/piring-kanan.jpg" alt="">

    <div class="title-line"><span></span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M17 21a1 1 0 0 0 1-1v-5.35c0-.457.316-.844.727-1.041a4 4 0 0 0-2.134-7.589 5 5 0 0 0-9.186 0 4 4 0 0 0-2.134 7.588c.411.198.727.585.727 1.041V20a1 1 0 0 0 1 1Z"/><path d="M6 17h12"/></svg><span></span></div>
    <h2>SELAMAT DATANG DI KEDAIKU</h2>
    <p>Nikmati prasmanan dengan berbagai pilihan menu lezat<br>dan pelayanan yang ramah.</p>

    <div class="features">
      <div><i><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"/></svg></i><b>Menu Lezat</b></div>
      <div><i><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 10v12"/><path d="M15 5.88 14 10h5.83a2 2 0 0 1 1.92 2.56l-2.33 8A2 2 0 0 1 17.5 22H4a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2h2.76a2 2 0 0 0 1.79-1.11L12 2a3.13 3.13 0 0 1 3 3.88Z"/></svg></i><b>Harga Bersahabat</b></div>
      <div><i><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 20a1 1 0 0 1-1-1v-1a1 1 0 0 1 1-1h18a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1Z"/><path d="M20 16a8 8 0 1 0-16 0"/><path d="M12 4v4"/><path d="M10 4h4"/></svg></i><b>Pelayanan Ramah</b></div>
    </div>
  </section>

<?php akhir();
