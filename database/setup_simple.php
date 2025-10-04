<?php
// Setup yang lebih sederhana dan robust
$host = 'localhost';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h3>Simple Database Setup</h3>";
    
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS perpustakaan");
    $pdo->exec("USE perpustakaan");
    echo "Database ready.<br>";
    
    // Drop existing tables and recreate
    $tables = ['peminjaman', 'buku', 'anggota', 'admin'];
    foreach ($tables as $table) {
        $pdo->exec("DROP TABLE IF EXISTS $table");
    }
    echo "Old tables removed.<br>";
    
    // Create tables dengan struktur sederhana
    $pdo->exec("
        CREATE TABLE admin (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            nama_lengkap VARCHAR(100) NOT NULL DEFAULT 'Administrator',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    $pdo->exec("
        CREATE TABLE buku (
            id INT AUTO_INCREMENT PRIMARY KEY,
            judul VARCHAR(255) NOT NULL,
            penulis VARCHAR(100) NOT NULL,
            penerbit VARCHAR(100) NOT NULL,
            tahun_terbit YEAR NOT NULL,
            isbn VARCHAR(20),
            kategori VARCHAR(50) DEFAULT 'Umum',
            jumlah_halaman INT DEFAULT 0,
            stok INT DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    $pdo->exec("
        CREATE TABLE anggota (
            id INT AUTO_INCREMENT PRIMARY KEY,
            kode_anggota VARCHAR(20) UNIQUE NOT NULL,
            nama VARCHAR(100) NOT NULL,
            email VARCHAR(100),
            telepon VARCHAR(15),
            alamat TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    $pdo->exec("
        CREATE TABLE peminjaman (
            id INT AUTO_INCREMENT PRIMARY KEY,
            kode_peminjaman VARCHAR(20) UNIQUE NOT NULL,
            anggota_id INT NOT NULL,
            buku_id INT NOT NULL,
            tanggal_pinjam DATE NOT NULL,
            tanggal_kembali DATE,
            status ENUM('dipinjam', 'dikembalikan', 'terlambat') DEFAULT 'dipinjam',
            denda DECIMAL(10,2) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    echo "All tables created successfully.<br>";
    
    // Insert default admin
    $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->exec("INSERT IGNORE INTO admin (username, password, nama_lengkap) 
                VALUES ('admin', '$hashedPassword', 'Administrator Sistem')");
    echo "Admin created (username: admin, password: admin123).<br>";
    
    // Insert sample books
    $books = [
        ["Pemrograman PHP Dasar", "Budi Santoso", "PT. Tekno Indonesia", 2023, "1111111111", "Teknologi", 300, 5],
        ["Database MySQL Untuk Pemula", "Sari Dewi", "PT. Ilmu Komputer", 2022, "2222222222", "Teknologi", 250, 3],
        ["Sejarah Indonesia Modern", "Prof. Ahmad", "PT. Sejahtera Abadi", 2021, "3333333333", "Sejarah", 400, 2],
        ["Matematika Diskrit", "Dr. Wijaya", "PT. Pendidikan", 2020, "4444444444", "Pendidikan", 350, 4],
        ["Kumpulan Cerpen", "Diana Putri", "PT. Sastra", 2023, "5555555555", "Sastra", 200, 3]
    ];
    
    foreach ($books as $book) {
        $stmt = $pdo->prepare("INSERT INTO buku (judul, penulis, penerbit, tahun_terbit, isbn, kategori, jumlah_halaman, stok) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute($book);
    }
    echo "Sample books added.<br>";
    
    echo "<h3 style='color: green;'>Setup completed successfully!</h3>";
    echo "<p><a href='../auth/login.php' class='btn btn-primary'>Go to Login</a></p>";
    
} catch(PDOException $e) {
    echo "<h3 style='color: red;'>Error: " . $e->getMessage() . "</h3>";
}
?>