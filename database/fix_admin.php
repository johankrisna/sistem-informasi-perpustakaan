<?php
// File untuk memperbaiki tabel admin yang bermasalah
$host = 'localhost';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=perpustakaan", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h3>Memperbaiki Tabel Admin</h3>";
    
    // Cek struktur tabel admin
    $stmt = $pdo->query("DESCRIBE admin");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Jika kolom nama_lengkap tidak ada, tambahkan
    if (!in_array('nama_lengkap', $columns)) {
        $pdo->exec("ALTER TABLE admin ADD COLUMN nama_lengkap VARCHAR(100) NOT NULL AFTER password");
        echo "Kolom 'nama_lengkap' berhasil ditambahkan.<br>";
        
        // Update data admin yang sudah ada
        $pdo->exec("UPDATE admin SET nama_lengkap = 'Administrator Sistem' WHERE username = 'admin'");
        echo "Data admin berhasil diperbarui.<br>";
    } else {
        echo "Kolom 'nama_lengkap' sudah ada.<br>";
    }
    
    // Cek apakah admin sudah ada
    $check_admin = $pdo->query("SELECT COUNT(*) FROM admin WHERE username = 'admin'")->fetchColumn();
    if ($check_admin == 0) {
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->exec("INSERT INTO admin (username, password, nama_lengkap) 
                    VALUES ('admin', '$hashedPassword', 'Administrator Sistem')");
        echo "Admin default berhasil dibuat.<br>";
    } else {
        echo "Admin default sudah ada.<br>";
    }
    
    echo "<h3 style='color: green;'>Perbaikan berhasil! Silakan <a href='../auth/login.php'>login</a></h3>";
    
} catch(PDOException $e) {
    echo "<h3 style='color: red;'>Error: " . $e->getMessage() . "</h3>";
    
    // Jika database tidak ada, buat dari awal
    if ($e->getCode() == 1049) {
        echo "<p>Database tidak ditemukan. Silakan jalankan <a href='setup.php'>setup.php</a> terlebih dahulu.</p>";
    }
}
?>