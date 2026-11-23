
CREATE DATABASE IF NOT EXISTS kedaiku CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE kedaiku;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS detail_pesanan;
DROP TABLE IF EXISTS pesanan;
DROP TABLE IF EXISTS menu;
DROP TABLE IF EXISTS kategori;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. Akun admin 
CREATE TABLE users (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  username   VARCHAR(40)  NOT NULL UNIQUE,
  password   VARCHAR(255) NOT NULL,              -- hash, bukan teks asli
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Kategori menu
CREATE TABLE kategori (
  id     INT AUTO_INCREMENT PRIMARY KEY,
  kode   VARCHAR(30)  NOT NULL UNIQUE,
  nama   VARCHAR(60)  NOT NULL,
  foto   VARCHAR(100) DEFAULT NULL,
  urutan TINYINT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

-- 3. Produk 
CREATE TABLE menu (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  kategori_id INT NOT NULL,
  nama        VARCHAR(100) NOT NULL,
  harga       INT NOT NULL,
  deskripsi   VARCHAR(255) DEFAULT '',
  foto        VARCHAR(100) DEFAULT NULL,
  stok        INT NOT NULL DEFAULT 0,
  FOREIGN KEY (kategori_id) REFERENCES kategori(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 4. Pesanan 
CREATE TABLE pesanan (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  nama_pelanggan VARCHAR(60) NOT NULL,
  kontak         VARCHAR(50) DEFAULT '',
  catatan        VARCHAR(255) DEFAULT '',
  total          INT NOT NULL,
  status         ENUM('Baru','Diproses','Selesai') NOT NULL DEFAULT 'Baru',
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 5. Isi pesanan 
CREATE TABLE detail_pesanan (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  pesanan_id INT NOT NULL,
  menu_id    INT NOT NULL,
  nama       VARCHAR(100) NOT NULL,
  harga      INT NOT NULL,
  jumlah     INT NOT NULL,
  FOREIGN KEY (pesanan_id) REFERENCES pesanan(id) ON DELETE CASCADE
) ENGINE=InnoDB;


-- 
-- username: admin
INSERT INTO users (username, password) VALUES ('admin', '$2b$12$6acrn7GO.twko/au3d2tNe5tn2beqmHXOS.WCADC3JWFLPAdgjUS6');

INSERT INTO kategori (kode, nama, foto, urutan) VALUES
('makanan', 'Makanan', 'kat-utama.jpg',   1),
('minuman', 'Minuman', 'kat-minuman.jpg', 2);

-- Harga & stok 
INSERT INTO menu (kategori_id, nama, harga, stok) VALUES
(1, 'Nasi Putih', 4000, 100),
(1, 'Ayam Goreng', 10000, 30),
(1, 'Ayam Balado', 11000, 30),
(1, 'Ayam Kecap', 11000, 30),
(1, 'Ayam Goreng Kecap', 12000, 30),
(1, 'Telor Balado', 6000, 40),
(1, 'Ati Ampela Balado', 6000, 30),
(1, 'Tahu Goreng', 2000, 50),
(1, 'Tahu Balado', 3000, 40),
(1, 'Tempe Goreng', 2000, 50),
(1, 'Tempe Bacem', 3000, 40),
(1, 'Tempe Orek Pedes', 3000, 40),
(1, 'Tempe Orek Kacang Panjang', 4000, 40),
(1, 'Sayur Sop', 4000, 30),
(1, 'Sayur Lodeh', 4000, 30),
(1, 'Sayur Asem', 4000, 30),
(1, 'Capcay', 5000, 30),
(1, 'Kerupuk', 1000, 100),
(1, 'Sambal Balado', 1000, 100),
(1, 'Sambal Goreng', 3000, 50),
(2, 'Air Teh Manis', 3000, 50),
(2, 'Air Mineral', 3000, 60),
(2, 'Teh Manis', 3000, 50),
(2, 'Teh Tawar', 2000, 50);
