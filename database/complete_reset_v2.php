<?php
// Reset database lengkap - versi 2
$host = 'localhost';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h3>🔄 Complete Database Reset v2</h3>";
    
    // Drop database jika ada
    $pdo->exec("DROP DATABASE IF EXISTS perpustakaan");
    echo "✅ Database lama dihapus<br>";
    
    // Create database baru
    $pdo->exec("CREATE DATABASE perpustakaan DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci");
    $pdo->exec("USE perpustakaan");
    echo "✅ Database baru dibuat<br>";
    
    // Buat semua tabel dengan struktur yang benar
    $tables_sql = [
        'admin' => "
            CREATE TABLE admin (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                nama_lengkap VARCHAR(100) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ",
        
        'buku' => "
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
        ",
        
        'anggota' => "
            CREATE TABLE anggota (
                id INT AUTO_INCREMENT PRIMARY KEY,
                kode_anggota VARCHAR(20) UNIQUE NOT NULL,
                nama VARCHAR(100) NOT NULL,
                email VARCHAR(100),
                telepon VARCHAR(15),
                alamat TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ",
        
        'peminjaman' => "
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
        "
    ];
    
    foreach ($tables_sql as $table_name => $sql) {
        $pdo->exec($sql);
        echo "✅ Tabel $table_name dibuat<br>";
    }
    
    // Insert default admin
    $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->exec("INSERT INTO admin (username, password, nama_lengkap) 
                VALUES ('admin', '$hashedPassword', 'Administrator Sistem')");
    echo "✅ Admin default dibuat (admin/admin123)<br>";
    
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
    echo "✅ Sample books ditambahkan<br>";
    
    // Insert sample members
    $anggotas = [
        ["AGT001", "Ahmad Wijaya", "ahmad@email.com", "08123456789", "Jl. Merdeka No. 123"],
        ["AGT002", "Siti Rahayu", "siti@email.com", "08129876543", "Jl. Sudirman No. 456"],
        ["AGT003", "Budi Pratama", "budi@email.com", "08111222333", "Jl. Gatot Subroto No. 789"],
        ["AGT004", "Maya Sari", "maya@email.com", "08133445566", "Jl. Thamrin No. 321"]
    ];
    
    foreach ($anggotas as $anggota) {
        $stmt = $pdo->prepare("INSERT INTO anggota (kode_anggota, nama, email, telepon, alamat) 
                               VALUES (?, ?, ?, ?, ?)");
        $stmt->execute($anggota);
    }
    echo "✅ Sample members ditambahkan<br>";
    
    // Insert sample peminjaman
    $peminjamans = [
        ['PJN' . date('Ymd') . '001', 1, 1, date('Y-m-d', strtotime('-5 days')), 'dipinjam'],
        ['PJN' . date('Ymd') . '002', 2, 3, date('Y-m-d', strtotime('-2 days')), 'dipinjam']
    ];
    
    foreach ($peminjamans as $peminjaman) {
        $stmt = $pdo->prepare("INSERT INTO peminjaman (kode_peminjaman, anggota_id, buku_id, tanggal_pinjam, status) 
                               VALUES (?, ?, ?, ?, ?)");
        $stmt->execute($peminjaman);
        
        // Update stok buku
        $pdo->exec("UPDATE buku SET stok = stok - 1 WHERE id = " . $peminjaman[2]);
    }
    echo "✅ Sample peminjaman ditambahkan<br>";
    
    echo "<h3 style='color: green;'>🎉 Reset database berhasil!</h3>";
    echo "<div class='mt-3'>";
    echo "<a href='../auth/login.php' class='btn btn-success me-2'>🔐 Login Sekarang</a>";
    echo "<a href='../peminjaman/index.php' class='btn btn-primary'>📚 Ke Peminjaman</a>";
    echo "</div>";
    
} catch(PDOException $e) {
    echo "<h3 style='color: red;'>❌ Error: " . $e->getMessage() . "</h3>";
    echo "<p>Pastikan MySQL server berjalan.</p>";
}
?>