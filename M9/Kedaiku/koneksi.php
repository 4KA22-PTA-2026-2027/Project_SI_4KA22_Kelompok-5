<?php
// ===== PENGATURAN =====
$db_host = 'localhost';
$db_user = 'root';      // bawaan XAMPP
$db_pass = '';          // bawaan XAMPP: kosong
$db_name = 'kedaiku';

// Nomor WhatsApp kedai untuk menerima pesanan.
// Format internasional TANPA tanda + dan tanpa 0 di depan. Contoh: 0812-3456-7890 -> 6281234567890
$WA_NOMOR = '6281234567890';
// ======================

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $db = new mysqli($db_host, $db_user, $db_pass, $db_name);
    $db->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    exit('Tidak bisa terhubung ke database. Pastikan MySQL sudah menyala dan database "kedaiku" sudah diimport.');
}
