-- ============================================================
-- INIT DATABASE: db_inventaris_ukm
-- Drop semua tabel → Buat ulang → Seed data
-- Jalankan sekali saja di DBeaver
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS pengembalian;
DROP TABLE IF EXISTS detail_peminjaman;
DROP TABLE IF EXISTS peminjaman;
DROP TABLE IF EXISTS barang;
DROP TABLE IF EXISTS anggota;
DROP TABLE IF EXISTS kategori;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- 1. TABEL USERS
-- ============================================================
CREATE TABLE users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'anggota', 'ketua') NOT NULL DEFAULT 'anggota',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- 2. TABEL KATEGORI
-- ============================================================
CREATE TABLE kategori (
    id_kategori INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(100) NOT NULL,
    keterangan TEXT
) ENGINE=InnoDB;

-- ============================================================
-- 3. TABEL BARANG
-- ============================================================
CREATE TABLE barang (
    id_barang INT AUTO_INCREMENT PRIMARY KEY,
    id_kategori INT NOT NULL,
    nama_barang VARCHAR(200) NOT NULL,
    kode_barang VARCHAR(50) NOT NULL UNIQUE,
    stok_total INT NOT NULL DEFAULT 0,
    stok_tersedia INT NOT NULL DEFAULT 0,
    kondisi ENUM('Baik', 'Rusak Ringan', 'Rusak Berat') NOT NULL DEFAULT 'Baik',
    lokasi VARCHAR(200) NOT NULL,
    keterangan TEXT,
    FOREIGN KEY (id_kategori) REFERENCES kategori(id_kategori) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ============================================================
-- 4. TABEL ANGGOTA
-- ============================================================
CREATE TABLE anggota (
    id_anggota INT AUTO_INCREMENT PRIMARY KEY,
    nim VARCHAR(20) NOT NULL UNIQUE,
    nama VARCHAR(150) NOT NULL,
    prodi VARCHAR(100) NOT NULL,
    no_hp VARCHAR(20)
) ENGINE=InnoDB;

-- ============================================================
-- 5. TABEL PEMINJAMAN
-- ============================================================
CREATE TABLE peminjaman (
    id_peminjaman INT AUTO_INCREMENT PRIMARY KEY,
    id_anggota INT NOT NULL,
    tanggal_pinjam DATE NOT NULL,
    tanggal_rencana_kembali DATE NOT NULL,
    status ENUM('menunggu', 'dipinjam', 'selesai', 'ditolak') NOT NULL DEFAULT 'dipinjam',
    catatan TEXT,
    FOREIGN KEY (id_anggota) REFERENCES anggota(id_anggota) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ============================================================
-- 6. TABEL DETAIL PEMINJAMAN
-- ============================================================
CREATE TABLE detail_peminjaman (
    id_detail INT AUTO_INCREMENT PRIMARY KEY,
    id_peminjaman INT NOT NULL,
    id_barang INT NOT NULL,
    jumlah INT NOT NULL DEFAULT 1,
    FOREIGN KEY (id_peminjaman) REFERENCES peminjaman(id_peminjaman) ON DELETE CASCADE,
    FOREIGN KEY (id_barang) REFERENCES barang(id_barang) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ============================================================
-- 7. TABEL PENGEMBALIAN
-- ============================================================
CREATE TABLE pengembalian (
    id_pengembalian INT AUTO_INCREMENT PRIMARY KEY,
    id_peminjaman INT NOT NULL,
    tanggal_kembali DATE NOT NULL,
    kondisi_kembali ENUM('Baik', 'Rusak Ringan', 'Rusak Berat', 'Hilang') NOT NULL,
    denda DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    catatan TEXT,
    FOREIGN KEY (id_peminjaman) REFERENCES peminjaman(id_peminjaman) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ============================================================
-- SEED DATA
-- ============================================================

-- Users (password sementara, akan di-reset via reset_password.php)
INSERT INTO users (nama, username, password, role) VALUES
('Administrator', 'admin', 'temp', 'admin');

-- Kategori
INSERT INTO kategori (id_kategori, nama_kategori, keterangan) VALUES
(1, 'Elektronik', 'Peralatan elektronik dan digital'),
(2, 'Dokumentasi', 'Alat-alat untuk kebutuhan dokumentasi'),
(3, 'Perlengkapan Acara', 'Barang untuk keperluan acara UKM'),
(4, 'Sekretariat', 'Perlengkapan administrasi sekretariat');

-- Barang
INSERT INTO barang (id_barang, id_kategori, nama_barang, kode_barang, stok_total, stok_tersedia, kondisi, lokasi, keterangan) VALUES
(1, 1, 'Kamera DSLR Canon EOS', 'ELK-001', 2, 1, 'Baik', 'Lemari A1', 'Lensa kit 18-55mm'),
(2, 2, 'Tripod Manfrotto', 'DOK-001', 3, 3, 'Baik', 'Rak B2', 'Max height 160cm'),
(3, 3, 'Speaker Portable JBL', 'PAC-001', 2, 0, 'Baik', 'Gudang', 'Sedang dipinjam'),
(4, 1, 'Kabel HDMI 3m', 'ELK-002', 5, 4, 'Baik', 'Laci Kabel', ''),
(5, 1, 'Laptop ASUS VivoBook', 'ELK-003', 1, 1, 'Baik', 'Brankas', 'Untuk presentasi'),
(6, 3, 'Proyektor Epson', 'PAC-002', 1, 1, 'Baik', 'Lemari B1', '3200 lumens'),
(7, 4, 'Jas Almamater', 'SEK-001', 10, 8, 'Baik', 'Lemari Jas', 'Ukuran M dan L'),
(8, 3, 'Banner Roll Up 80x200', 'PAC-003', 3, 3, 'Baik', 'Gudang', ''),
(9, 2, 'Mic Wireless Shure', 'DOK-002', 2, 1, 'Baik', 'Lemari A2', '1 unit sedang dipinjam'),
(10, 1, 'LED Panel 50x50', 'ELK-004', 2, 2, 'Rusak Ringan', 'Gudang', '1 panel ada piksel mati');

-- Anggota
INSERT INTO anggota (id_anggota, nim, nama, prodi, no_hp) VALUES
(1, '2024018029', 'Lucia Tri Wulanningsih', 'Teknik Informatika', '081234567890'),
(2, '2024018055', 'Nurcahyo Syahrul Basuki R.', 'Teknik Informatika', '081234567891'),
(3, '2024018088', 'Hifdzullah Karunia Rizqi', 'Teknik Informatika', '081234567892'),
(4, '2024018094', 'Hifdzullah Anugerah Sejahtera', 'Teknik Informatika', '081234567893'),
(5, '2024018100', 'Rina Ayu Lestari', 'Sistem Informasi', '081234567894'),
(6, '2024018105', 'Budi Santoso', 'Teknik Informatika', '081234567895');

-- Peminjaman
INSERT INTO peminjaman (id_peminjaman, id_anggota, tanggal_pinjam, tanggal_rencana_kembali, status, catatan) VALUES
(1, 1, '2025-06-10', '2025-06-17', 'dipinjam', 'Untuk dokumentasi acara'),
(2, 5, '2025-06-12', '2025-06-15', 'dipinjam', 'Acara seminar'),
(3, 2, '2025-06-01', '2025-06-05', 'selesai', '');

-- Detail Peminjaman
INSERT INTO detail_peminjaman (id_peminjaman, id_barang, jumlah) VALUES
(1, 1, 1),
(1, 9, 1),
(2, 3, 2),
(3, 7, 2);

-- Pengembalian
INSERT INTO pengembalian (id_peminjaman, tanggal_kembali, kondisi_kembali, denda, catatan) VALUES
(3, '2025-06-04', 'Baik', 0.00, 'Dikembalikan tepat waktu');