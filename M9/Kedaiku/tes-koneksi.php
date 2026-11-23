<?php
require 'koneksi.php';
$tabel = ['users', 'kategori', 'menu', 'pesanan', 'detail_pesanan'];
?>
<!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8"><title>Tes Koneksi</title>
<style>body{font-family:Arial,sans-serif;max-width:480px;margin:40px auto}td,th{padding:6px 14px;border-bottom:1px solid #ddd;text-align:left}.ok{color:#0a7a2f}</style></head>
<body>
  <h2 class="ok">Koneksi database berhasil ✔</h2>
  <table><tr><th>Tabel</th><th>Jumlah data</th></tr>
  <?php foreach ($tabel as $t):
      $n = $db->query("SELECT COUNT(*) c FROM `$t`")->fetch_assoc()['c']; ?>
    <tr><td><?= $t ?></td><td><?= $n ?></td></tr>
  <?php endforeach; ?>
  </table>
  <p>Kalau sudah muncul 4 kategori dan 12 menu, database siap dipakai. Hapus file ini setelah selesai mengetes.</p>
</body></html>
