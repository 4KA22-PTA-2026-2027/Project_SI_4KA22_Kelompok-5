<?php
// Fungsi bersama + kerangka halaman (navbar, gelombang) untuk semua halaman.
if (session_status() === PHP_SESSION_NONE) session_start();

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function rp($n) { return 'Rp ' . number_format((int)$n, 0, ',', '.'); }
function csrf() {
    if (empty($_SESSION['t'])) $_SESSION['t'] = bin2hex(random_bytes(16));
    return $_SESSION['t'];
}
function cek_csrf() {
    if (!hash_equals($_SESSION['t'] ?? '', $_POST['t'] ?? '')) {
        http_response_code(400);
        exit('Sesi tidak valid. Silakan muat ulang halaman.');
    }
}
function jumlah_keranjang() { return array_sum($_SESSION['cart'] ?? []); }
function is_admin() { return !empty($_SESSION['admin']); }
function wajib_admin() { if (!is_admin()) { header('Location: login.php'); exit; } }

function ikon($nama, $kelas = '') {
    $p = [
        'cart'  => '<circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>',
        'topi'  => '<path d="M17 21a1 1 0 0 0 1-1v-5.35c0-.457.316-.844.727-1.041a4 4 0 0 0-2.134-7.589 5 5 0 0 0-9.186 0 4 4 0 0 0-2.134 7.588c.411.198.727.585.727 1.041V20a1 1 0 0 0 1 1Z"/><path d="M6 17h12"/>',
        'garpu' => '<path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"/>',
        'gelas' => '<path d="m6 8 1.75 12.28a2 2 0 0 0 2 1.72h4.54a2 2 0 0 0 2-1.72L18 8"/><path d="M5 8h14"/><path d="M7 15a6.47 6.47 0 0 1 5 0 6.47 6.47 0 0 0 5 0"/><path d="m12 8 1-6h2"/>',
        'wa'    => '<path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/>',
    ];
    return '<svg class="' . $kelas . '" viewBox="0 0 24 24" aria-hidden="true">' . $p[$nama] . '</svg>';
}

function mulai($judul, $aktif, $wave = false) {
    $n = jumlah_keranjang();
    $menu = ['index.php' => 'Beranda', 'menu.php' => 'Menu', 'keranjang.php' => 'Keranjang'];
    ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($judul) ?> - Kedaiku</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Lilita+One&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body class="page-menu">
  <header class="navbar">
    <a href="index.php" class="brand"><img src="images/logo.png" alt="Kedaiku"></a>
    <nav>
      <?php foreach ($menu as $f => $t): ?>
        <a href="<?= $f ?>"<?= $aktif === $f ? ' class="active"' : '' ?>><?= $t ?></a>
      <?php endforeach; ?>
      <?php if (is_admin()): ?>
        <a href="admin.php"<?= $aktif === 'admin.php' ? ' class="active"' : '' ?>>Admin</a>
        <a href="logout.php">Keluar</a>
      <?php endif; ?>
    </nav>
    <a href="keranjang.php" class="cart" aria-label="Keranjang"><?= ikon('cart') ?><span><?= (int)$n ?></span></a>
  </header>
<?php if ($wave): ?>
  <div class="wave wave-top" aria-hidden="true">
    <svg viewBox="0 0 1440 150" preserveAspectRatio="none">
      <path d="M0 0H1440V62C1330 94 1150 120 880 120C560 124 250 86 0 135Z" fill="#ffc82e"/>
      <path d="M0 0H1440V40C1330 70 1150 105 880 108C560 114 250 82 0 125Z" fill="#d4161c"/>
    </svg>
  </div>
<?php endif;
}

function akhir($wave = false) {
    if ($wave): ?>
  <div class="wave wave-bottom" aria-hidden="true">
    <svg viewBox="0 0 1440 170" preserveAspectRatio="none">
      <path d="M0 60C300 20 620 100 920 70C1120 50 1300 25 1440 50V170H0Z" fill="#ffc82e"/>
      <path d="M0 105C300 75 580 125 880 108C1110 94 1300 70 1440 100V170H0Z" fill="#d4161c"/>
      <path d="M0 138C300 118 600 148 900 132C1150 118 1300 126 1440 130V170H0Z" fill="#8f1010"/>
    </svg>
  </div>
<?php endif; ?>
</body>
</html>
<?php
}
