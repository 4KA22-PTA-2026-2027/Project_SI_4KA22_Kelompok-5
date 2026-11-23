<?php
// Login khusus ADMIN. Pelanggan tidak perlu login. Alamat: /login.php
require 'layout.php';
require 'koneksi.php';
if (is_admin()) { header('Location: admin.php'); exit; }

$galat = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_csrf();
    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';
    $st = $db->prepare('SELECT id, password FROM users WHERE username=?');
    $st->bind_param('s', $u);
    $st->execute();
    $r = $st->get_result()->fetch_assoc();
    if ($r && password_verify($p, $r['password'])) {
        session_regenerate_id(true);
        $_SESSION['admin'] = $r['id'];
        header('Location: admin.php');
        exit;
    }
    sleep(1);
    $galat = 'Username atau password salah.';
}
mulai('Login', 'login.php');
?>
  <main class="sempit">
    <form method="post" class="kartu-login">
      <h1>Login</h1>
      <?php if ($galat): ?><div class="galat"><?= e($galat) ?></div><?php endif; ?>
      <input type="hidden" name="t" value="<?= csrf() ?>">
      <input name="username" placeholder="Username" required autocomplete="username">
      <input name="password" type="password" placeholder="Password" required autocomplete="current-password">
      <button class="btn-gelap">Login</button>
    </form>
  </main>
<?php akhir();
