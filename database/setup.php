<?php
// Koneksi ke MySQL
$host = 'localhost';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database jika belum ada
    $pdo->exec("CREATE DATABASE IF NOT EXISTS perpustakaan DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci");
    echo "Database 'perpustakaan' berhasil dibuat/divalidasi.<br>";
    
    // Gunakan database
    $pdo->exec("USE perpustakaan");
    
    // Create table admin
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admin (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            nama_lengkap VARCHAR(100) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "Tabel 'admin' berhasil dibuat/divalidasi.<br>";
    
    // Create table buku dengan pengecekan kolom
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS buku (
            id INT AUTO_INCREMENT PRIMARY KEY,
            judul VARCHAR(255) NOT NULL,
            penulis VARCHAR(100) NOT NULL,
            penerbit VARCHAR(100) NOT NULL,
            tahun_terbit YEAR NOT NULL,
            isbn VARCHAR(20),
            kategori VARCHAR(50),
            jumlah_halaman INT,
            stok INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "Tabel 'buku' berhasil dibuat/divalidasi.<br>";
    
    // Periksa dan tambahkan kolom yang mungkin hilang
    $columns_to_check = ['kategori', 'jumlah_halaman', 'stok'];
    foreach ($columns_to_check as $column) {
        $check = $pdo->query("SHOW COLUMNS FROM buku LIKE '$column'");
        if ($check->rowCount() == 0) {
            if ($column == 'kategori') {
                $pdo->exec("ALTER TABLE buku ADD COLUMN kategori VARCHAR(50) AFTER isbn");
            } elseif ($column == 'jumlah_halaman') {
                $pdo->exec("ALTER TABLE buku ADD COLUMN jumlah_halaman INT AFTER kategori");
            } elseif ($column == 'stok') {
                $pdo->exec("ALTER TABLE buku ADD COLUMN stok INT DEFAULT 0 AFTER jumlah_halaman");
            }
            echo "Kolom '$column' berhasil ditambahkan ke tabel buku.<br>";
        }
    }
    
    // Create table anggota
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS anggota (
            id INT AUTO_INCREMENT PRIMARY KEY,
            kode_anggota VARCHAR(20) UNIQUE NOT NULL,
            nama VARCHAR(100) NOT NULL,
            email VARCHAR(100),
            telepon VARCHAR(15),
            alamat TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "Tabel 'anggota' berhasil dibuat/divalidasi.<br>";
    
    // Create table peminjaman
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS peminjaman (
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
    echo "Tabel 'peminjaman' berhasil dibuat/divalidasi.<br>";
    
    // Insert default admin
    $check_admin = $pdo->query("SELECT COUNT(*) FROM admin WHERE username = 'admin'")->fetchColumn();
    if ($check_admin == 0) {
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->exec("INSERT INTO admin (username, password, nama_lengkap) 
                    VALUES ('admin', '$hashedPassword', 'Administrator Sistem')");
        echo "Admin default berhasil dibuat.<br>";
    } else {
        echo "Admin default sudah ada.<br>";
    }
    
    // Insert sample data buku jika belum ada
    $check_books = $pdo->query("SELECT COUNT(*) FROM buku")->fetchColumn();
    if ($check_books == 0) {
        // Insert sample books dengan approach yang lebih aman
        $sampleBooks = [
            ["Pemrograman PHP", "John Doe", "PT. Buku Kita", 2023, "1234567890", "Teknologi", 300, 5],
            ["Database MySQL", "Jane Smith", "PT. Ilmu Komputer", 2022, "0987654321", "Teknologi", 250, 3],
            ["Sejarah Indonesia", "Prof. Ahmad", "PT. Sejahtera", 2021, "1122334455", "Sejarah", 400, 2]
        ];
        
        foreach ($sampleBooks as $book) {
            // Gunakan prepared statement dengan named parameters
            $stmt = $pdo->prepare("INSERT INTO buku (judul, penulis, penerbit, tahun_terbit, isbn, kategori, jumlah_halaman, stok) 
                                   VALUES (:judul, :penulis, :penerbit, :tahun_terbit, :isbn, :kategori, :jumlah_halaman, :stok)");
            
            $stmt->execute([
                ':judul' => $book[0],
                ':penulis' => $book[1],
                ':penerbit' => $book[2],
                ':tahun_terbit' => $book[3],
                ':isbn' => $book[4],
                ':kategori' => $book[5],
                ':jumlah_halaman' => $book[6],
                ':stok' => $book[7]
            ]);
        }
        echo "Sample data buku berhasil ditambahkan.<br>";
    } else {
        echo "Data buku sudah ada.<br>";
        
        // Coba update beberapa buku dengan kategori jika belum ada
        try {
            $update_stmt = $pdo->prepare("UPDATE buku SET kategori = :kategori WHERE judul LIKE :judul");
            $update_stmt->execute([':kategori' => 'Teknologi', ':judul' => '%PHP%']);
            $update_stmt->execute([':kategori' => 'Teknologi', ':judul' => '%MySQL%']);
            $update_stmt->execute([':kategori' => 'Sejarah', ':judul' => '%Sejarah%']);
            echo "Kategori buku berhasil diupdate.<br>";
        } catch (Exception $e) {
            echo "Note: Tidak bisa update kategori - " . $e->getMessage() . "<br>";
        }
    }
    
    echo "<h2 style='color: green;'>Setup berhasil! Silakan <a href='../auth/login.php'>login</a></h2>";
    
} catch(PDOException $e) {
    echo "<h2 style='color: red;'>Error: " . $e->getMessage() . "</h2>";
    echo "<p>Detail error: " . $e->getFile() . " on line " . $e->getLine() . "</p>";
    
    // Tampilkan informasi debugging
    if (strpos($e->getMessage(), 'kategori') !== false) {
        echo "<p><strong>Solusi:</strong> Masalah dengan kolom 'kategori'. Silakan jalankan <a href='fix_tables.php'>fix_tables.php</a> untuk memperbaiki.</p>";
    }
}
?>