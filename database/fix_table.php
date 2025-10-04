<?php
// File khusus untuk memperbaiki struktur tabel
$host = 'localhost';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=perpustakaan", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h3>Memperbaiki Struktur Tabel</h3>";
    
    // Perbaiki tabel buku
    echo "<h4>Memperbaiki Tabel Buku:</h4>";
    
    $columns_to_add = [
        'kategori' => "ALTER TABLE buku ADD COLUMN kategori VARCHAR(50) AFTER isbn",
        'jumlah_halaman' => "ALTER TABLE buku ADD COLUMN jumlah_halaman INT AFTER kategori", 
        'stok' => "ALTER TABLE buku ADD COLUMN stok INT DEFAULT 0 AFTER jumlah_halaman"
    ];
    
    foreach ($columns_to_add as $column => $sql) {
        $check = $pdo->query("SHOW COLUMNS FROM buku LIKE '$column'");
        if ($check->rowCount() == 0) {
            $pdo->exec($sql);
            echo "Kolom '$column' berhasil ditambahkan ke tabel buku.<br>";
        } else {
            echo "Kolom '$column' sudah ada.<br>";
        }
    }
    
    // Perbaiki tabel admin
    echo "<h4>Memperbaiki Tabel Admin:</h4>";
    $check_admin_column = $pdo->query("SHOW COLUMNS FROM admin LIKE 'nama_lengkap'");
    if ($check_admin_column->rowCount() == 0) {
        $pdo->exec("ALTER TABLE admin ADD COLUMN nama_lengkap VARCHAR(100) NOT NULL AFTER password");
        echo "Kolom 'nama_lengkap' berhasil ditambahkan ke tabel admin.<br>";
        
        // Update data admin
        $pdo->exec("UPDATE admin SET nama_lengkap = 'Administrator Sistem'");
        echo "Data admin berhasil diperbarui.<br>";
    } else {
        echo "Kolom 'nama_lengkap' sudah ada.<br>";
    }
    
    // Pastikan admin default ada
    $check_admin = $pdo->query("SELECT COUNT(*) FROM admin WHERE username = 'admin'")->fetchColumn();
    if ($check_admin == 0) {
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->exec("INSERT INTO admin (username, password, nama_lengkap) 
                    VALUES ('admin', '$hashedPassword', 'Administrator Sistem')");
        echo "Admin default berhasil dibuat.<br>";
    } else {
        echo "Admin default sudah ada.<br>";
    }
    
    echo "<h3 style='color: green;'>Perbaikan struktur tabel berhasil!</h3>";
    echo "<p><a href='../auth/login.php' class='btn btn-success'>Login Sekarang</a></p>";
    
} catch(PDOException $e) {
    echo "<h3 style='color: red;'>Error: " . $e->getMessage() . "</h3>";
    
    if ($e->getCode() == 1049) {
        echo "<p>Database tidak ditemukan. Silakan jalankan <a href='setup.php'>setup.php</a> terlebih dahulu.</p>";
    }
}
?>