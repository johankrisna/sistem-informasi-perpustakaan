<?php
// File untuk memperbaiki semua tabel dan relasi
$host = 'localhost';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=perpustakaan", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h3>Memperbaiki Semua Tabel dan Relasi</h3>";
    
    // 1. Perbaiki tabel admin
    echo "<h4>1. Memperbaiki Tabel Admin:</h4>";
    $check_admin = $pdo->query("SHOW COLUMNS FROM admin LIKE 'nama_lengkap'");
    if ($check_admin->rowCount() == 0) {
        $pdo->exec("ALTER TABLE admin ADD COLUMN nama_lengkap VARCHAR(100) NOT NULL AFTER password");
        $pdo->exec("UPDATE admin SET nama_lengkap = 'Administrator Sistem'");
        echo "✓ Tabel admin diperbaiki<br>";
    } else {
        echo "✓ Tabel admin sudah benar<br>";
    }
    
    // 2. Perbaiki tabel buku
    echo "<h4>2. Memperbaiki Tabel Buku:</h4>";
    $buku_columns = ['kategori', 'jumlah_halaman', 'stok'];
    foreach ($buku_columns as $column) {
        $check = $pdo->query("SHOW COLUMNS FROM buku LIKE '$column'");
        if ($check->rowCount() == 0) {
            if ($column == 'kategori') {
                $pdo->exec("ALTER TABLE buku ADD COLUMN kategori VARCHAR(50) DEFAULT 'Umum' AFTER isbn");
            } elseif ($column == 'jumlah_halaman') {
                $pdo->exec("ALTER TABLE buku ADD COLUMN jumlah_halaman INT DEFAULT 0 AFTER kategori");
            } elseif ($column == 'stok') {
                $pdo->exec("ALTER TABLE buku ADD COLUMN stok INT DEFAULT 1 AFTER jumlah_halaman");
            }
            echo "✓ Kolom '$column' ditambahkan<br>";
        }
    }
    echo "✓ Tabel buku diperbaiki<br>";
    
    // 3. Perbaiki tabel anggota
    echo "<h4>3. Memperbaiki Tabel Anggota:</h4>";
    $anggota_columns = ['email', 'telepon', 'alamat'];
    foreach ($anggota_columns as $column) {
        $check = $pdo->query("SHOW COLUMNS FROM anggota LIKE '$column'");
        if ($check->rowCount() == 0) {
            if ($column == 'email') {
                $pdo->exec("ALTER TABLE anggota ADD COLUMN email VARCHAR(100) AFTER nama");
            } elseif ($column == 'telepon') {
                $pdo->exec("ALTER TABLE anggota ADD COLUMN telepon VARCHAR(15) AFTER email");
            } elseif ($column == 'alamat') {
                $pdo->exec("ALTER TABLE anggota ADD COLUMN alamat TEXT AFTER telepon");
            }
            echo "✓ Kolom '$column' ditambahkan<br>";
        }
    }
    echo "✓ Tabel anggota diperbaiki<br>";
    
    // 4. Perbaiki tabel peminjaman - INI YANG UTAMA
    echo "<h4>4. Memperbaiki Tabel Peminjaman:</h4>";
    
    // Cek apakah tabel peminjaman ada
    $check_table = $pdo->query("SHOW TABLES LIKE 'peminjaman'");
    if ($check_table->rowCount() == 0) {
        // Buat tabel peminjaman dari awal
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
        echo "✓ Tabel peminjaman dibuat ulang<br>";
    } else {
        // Perbaiki kolom yang ada
        $peminjaman_columns = [
            'anggota_id' => "ALTER TABLE peminjaman ADD COLUMN anggota_id INT NOT NULL AFTER kode_peminjaman",
            'buku_id' => "ALTER TABLE peminjaman ADD COLUMN buku_id INT NOT NULL AFTER anggota_id", 
            'tanggal_pinjam' => "ALTER TABLE peminjaman ADD COLUMN tanggal_pinjam DATE NOT NULL AFTER buku_id",
            'tanggal_kembali' => "ALTER TABLE peminjaman ADD COLUMN tanggal_kembali DATE AFTER tanggal_pinjam",
            'status' => "ALTER TABLE peminjaman ADD COLUMN status ENUM('dipinjam', 'dikembalikan', 'terlambat') DEFAULT 'dipinjam' AFTER tanggal_kembali",
            'denda' => "ALTER TABLE peminjaman ADD COLUMN denda DECIMAL(10,2) DEFAULT 0 AFTER status"
        ];
        
        foreach ($peminjaman_columns as $column => $sql) {
            $check = $pdo->query("SHOW COLUMNS FROM peminjaman LIKE '$column'");
            if ($check->rowCount() == 0) {
                $pdo->exec($sql);
                echo "✓ Kolom '$column' ditambahkan<br>";
            }
        }
    }
    echo "✓ Tabel peminjaman diperbaiki<br>";
    
    // 5. Pastikan admin default ada
    echo "<h4>5. Memeriksa Admin Default:</h4>";
    $check_admin = $pdo->query("SELECT COUNT(*) FROM admin WHERE username = 'admin'")->fetchColumn();
    if ($check_admin == 0) {
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->exec("INSERT INTO admin (username, password, nama_lengkap) 
                    VALUES ('admin', '$hashedPassword', 'Administrator Sistem')");
        echo "✓ Admin default dibuat<br>";
    } else {
        echo "✓ Admin default sudah ada<br>";
    }
    
    // 6. Tambahkan sample data jika belum ada
    echo "<h4>6. Memeriksa Data Sample:</h4>";
    $check_books = $pdo->query("SELECT COUNT(*) FROM buku")->fetchColumn();
    if ($check_books == 0) {
        $books = [
            ["Pemrograman PHP Dasar", "Budi Santoso", "PT. Tekno Indonesia", 2023, "1111111111", "Teknologi", 300, 5],
            ["Database MySQL Untuk Pemula", "Sari Dewi", "PT. Ilmu Komputer", 2022, "2222222222", "Teknologi", 250, 3],
            ["Sejarah Indonesia Modern", "Prof. Ahmad", "PT. Sejahtera Abadi", 2021, "3333333333", "Sejarah", 400, 2]
        ];
        
        foreach ($books as $book) {
            $stmt = $pdo->prepare("INSERT INTO buku (judul, penulis, penerbit, tahun_terbit, isbn, kategori, jumlah_halaman, stok) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute($book);
        }
        echo "✓ Data sample buku ditambahkan<br>";
    } else {
        echo "✓ Data buku sudah ada<br>";
    }
    
    $check_anggota = $pdo->query("SELECT COUNT(*) FROM anggota")->fetchColumn();
    if ($check_anggota == 0) {
        $anggotas = [
            ["AGT001", "Ahmad Wijaya", "ahmad@email.com", "08123456789", "Jl. Merdeka No. 123"],
            ["AGT002", "Siti Rahayu", "siti@email.com", "08129876543", "Jl. Sudirman No. 456"],
            ["AGT003", "Budi Pratama", "budi@email.com", "08111222333", "Jl. Gatot Subroto No. 789"]
        ];
        
        foreach ($anggotas as $anggota) {
            $stmt = $pdo->prepare("INSERT INTO anggota (kode_anggota, nama, email, telepon, alamat) 
                                   VALUES (?, ?, ?, ?, ?)");
            $stmt->execute($anggota);
        }
        echo "✓ Data sample anggota ditambahkan<br>";
    } else {
        echo "✓ Data anggota sudah ada<br>";
    }
    
    echo "<h3 style='color: green;'>✅ Semua tabel berhasil diperbaiki!</h3>";
    echo "<div class='mt-3'>";
    echo "<a href='../auth/login.php' class='btn btn-success me-2'>Login Sekarang</a>";
    echo "<a href='../dashboard/index.php' class='btn btn-primary'>Ke Dashboard</a>";
    echo "</div>";
    
} catch(PDOException $e) {
    echo "<h3 style='color: red;'>Error: " . $e->getMessage() . "</h3>";
    
    if ($e->getCode() == 1049) {
        echo "<p>Database tidak ditemukan. Silakan jalankan <a href='setup_simple.php'>setup_simple.php</a> terlebih dahulu.</p>";
    }
}
?>