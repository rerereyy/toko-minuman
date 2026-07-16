-- Database: toko_minuman_db
CREATE DATABASE IF NOT EXISTS toko_minuman_db;
USE toko_minuman_db;

-- Tabel users (admin/kasir)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'kasir') DEFAULT 'kasir',
    no_hp VARCHAR(15),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabel kategori_produk
CREATE TABLE IF NOT EXISTS kategori_produk (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabel produk
CREATE TABLE IF NOT EXISTS produk (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_produk VARCHAR(20) NOT NULL UNIQUE,
    nama_produk VARCHAR(150) NOT NULL,
    kategori_id INT NOT NULL,
    harga_jual DECIMAL(12,2) NOT NULL,
    stok INT NOT NULL DEFAULT 0,
    deskripsi TEXT,
    gambar VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kategori_id) REFERENCES kategori_produk(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tabel pelanggan
CREATE TABLE IF NOT EXISTS pelanggan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_pelanggan VARCHAR(100) NOT NULL,
    no_hp VARCHAR(15),
    alamat TEXT,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabel transaksi
CREATE TABLE IF NOT EXISTS transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_transaksi VARCHAR(20) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    pelanggan_id INT,
    tanggal_transaksi DATETIME DEFAULT CURRENT_TIMESTAMP,
    subtotal DECIMAL(12,2) NOT NULL,
    diskon DECIMAL(12,2) DEFAULT 0,
    total_bayar DECIMAL(12,2) NOT NULL,
    metode_pembayaran ENUM('cash', 'transfer', 'kartu') DEFAULT 'cash',
    status ENUM('selesai', 'pending', 'batal') DEFAULT 'selesai',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (pelanggan_id) REFERENCES pelanggan(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Tabel detail_transaksi
CREATE TABLE IF NOT EXISTS detail_transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaksi_id INT NOT NULL,
    produk_id INT NOT NULL,
    jumlah INT NOT NULL,
    harga_satuan DECIMAL(12,2) NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (transaksi_id) REFERENCES transaksi(id) ON DELETE CASCADE,
    FOREIGN KEY (produk_id) REFERENCES produk(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Insert kategori default
INSERT INTO kategori_produk (nama_kategori, deskripsi) VALUES
('Beer', 'Minuman beralkohol dari hasil fermentasi barley'),
('Wine', 'Minuman beralkohol dari fermentasi anggur'),
('Spirits', 'Minuman keras hasil penyulingan (whisky, vodka, dll)'),
('Cocktail', 'Campuran minuman beralkohol dan non-alkohol'),
('Liqueur', 'Minuman beralkohol dengan rasa manis dan buah'),
('Non-Alkohol', 'Minuman tanpa alkohol (mocktail, jus, dll)');

-- Insert produk default
INSERT INTO produk (kode_produk, nama_produk, kategori_id, harga_jual, stok, deskripsi) VALUES
('BRW-001', 'Bintang Beer 330ml', 1, 35000, 120, 'Beer lager Indonesia, 330ml kaleng'),
('BRW-002', 'Anker Beer 330ml', 1, 30000, 100, 'Beer lager Indonesia, 330ml kaleng'),
('BRW-003', 'Heineken 330ml', 1, 55000, 80, 'Beer premium Belanda, 330ml botol'),
('BRW-004', 'Bali Hai 330ml', 1, 32000, 90, 'Beer lokal Bali, 330ml kaleng'),
('WNE-001', 'Anggur Merah House 750ml', 2, 120000, 50, 'Red wine impor, 750ml botol'),
('WNE-002', 'Anggur Putih Sauvignon 750ml', 2, 150000, 40, 'White wine Selandia Baru, 750ml'),
('WNE-003', 'Sparkling Wine 750ml', 2, 180000, 30, 'Champagne-style sparkling wine'),
('SPR-001', 'Johnnie Walker Red 700ml', 3, 450000, 25, 'Blended Scotch Whisky'),
('SPR-002', 'Absolut Vodka 700ml', 3, 380000, 30, 'Swedish vodka, 700ml'),
('SPR-003', 'Jack Daniel\'s 700ml', 3, 520000, 20, 'Tennessee Whiskey, 700ml'),
('SPR-004', 'Bacardi Rum 700ml', 3, 350000, 35, 'White rum Caribbean, 700ml'),
('CCK-001', 'Margarita Mix', 4, 85000, 45, 'Tequila, triple sec, lime juice'),
('CCK-002', 'Mojito', 4, 75000, 50, 'White rum, lime, mint, soda'),
('CCK-003', 'Long Island', 4, 95000, 35, 'Campuran 5 jenis alkohol'),
('LQR-001', 'Baileys Irish Cream 700ml', 5, 320000, 25, 'Cream liqueur, 17% ABV'),
('LQR-002', 'Jägermeister 700ml', 5, 280000, 30, 'Herbal liqueur Jerman'),
('NAC-001', 'Fresh Orange Juice', 6, 25000, 60, 'Jus jeruk segar'),
('NAC-002', 'Virgin Mojito', 6, 35000, 50, 'Mocktail tanpa alkohol'),
('NAC-003', 'Lemon Tea', 6, 15000, 80, 'Teh lemon segar');

-- Insert admin default (password: admin123)
INSERT INTO users (nama_lengkap, email, password, role, no_hp) VALUES
('Administrator', 'admin@tokominuman.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '081234567890'),
('Kasir Utama', 'kasir@tokominuman.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'kasir', '081234567891');

-- Insert pelanggan default
INSERT INTO pelanggan (nama_pelanggan, no_hp, alamat, email) VALUES
('Umum', '0000000000', '-', '-'),
('Budi Santoso', '08123456789', 'Jl. Sudirman No. 10, Jakarta', 'budi@email.com'),
('Rina Wati', '08234567890', 'Jl. Thamrin No. 25, Jakarta', 'rina@email.com'),
('Dedi Kurniawan', '08345678901', 'Jl. Gatot Subroto No. 5, Bandung', 'dedi@email.com');
