<?php
// File khusus untuk memperbaiki tabel peminjaman saja
$host = 'localhost';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=perpustakaan", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h3>🛠️ Memperbaiki Tabel Peminjaman Secara Khusus</h3>";
    
    // 1. Cek apakah tabel peminjaman ada
    $check_table = $pdo->query("SHOW TABLES LIKE 'peminjaman'");
    if ($check_table->rowCount() == 0) {
        echo "❌ Tabel peminjaman tidak ditemukan. Membuat baru...<br>";
        
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
        echo "✅ Tabel peminjaman berhasil dibuat<br>";
    } else {
        echo "✅ Tabel peminjaman sudah ada<br>";
    }
    
    // 2. Daftar kolom yang harus ada di tabel peminjaman
    $required_columns = [
        'anggota_id' => "INT NOT NULL AFTER kode_peminjaman",
        'buku_id' => "INT NOT NULL AFTER anggota_id", 
        'tanggal_pinjam' => "DATE NOT NULL AFTER buku_id",
        'tanggal_kembali' => "DATE AFTER tanggal_pinjam",
        'status' => "ENUM('dipinjam', 'dikembalikan', 'terlambat') DEFAULT 'dipinjam' AFTER tanggal_kembali",
        'denda' => "DECIMAL(10,2) DEFAULT 0 AFTER status"
    ];
    
    // 3. Periksa dan tambahkan kolom yang hilang
    foreach ($required_columns as $column => $definition) {
        $check = $pdo->query("SHOW COLUMNS FROM peminjaman LIKE '$column'");
        if ($check->rowCount() == 0) {
            echo "❌ Kolom '$column' tidak ditemukan. Menambahkan...<br>";
            $pdo->exec("ALTER TABLE peminjaman ADD COLUMN $column $definition");
            echo "✅ Kolom '$column' berhasil ditambahkan<br>";
        } else {
            echo "✅ Kolom '$column' sudah ada<br>";
        }
    }
    
    // 4. Cek foreign key constraints (jika perlu)
    echo "<h4>🔍 Memeriksa Foreign Keys:</h4>";
    
    // Cek apakah ada data di tabel peminjaman yang merujuk ke anggota/buku yang tidak ada
    $check_orphaned = $pdo->query("
        SELECT COUNT(*) as orphaned 
        FROM peminjaman p 
        LEFT JOIN anggota a ON p.anggota_id = a.id 
        LEFT JOIN buku b ON p.buku_id = b.id 
        WHERE a.id IS NULL OR b.id IS NULL
    ")->fetch(PDO::FETCH_ASSOC);
    
    if ($check_orphaned['orphaned'] > 0) {
        echo "⚠️  Ditemukan $check_orphaned[orphaned] data peminjaman yang merujuk ke data yang tidak ada<br>";
        echo "🗑️  Membersihkan data orphaned...<br>";
        
        // Hapus data peminjaman yang merujuk ke data yang tidak ada
        $pdo->exec("
            DELETE p FROM peminjaman p 
            LEFT JOIN anggota a ON p.anggota_id = a.id 
            LEFT JOIN buku b ON p.buku_id = b.id 
            WHERE a.id IS NULL OR b.id IS NULL
        ");
        echo "✅ Data orphaned berhasil dibersihkan<br>";
    } else {
        echo "✅ Tidak ada data orphaned<br>";
    }
    
    // 5. Buat sample data peminjaman untuk testing
    echo "<h4>🧪 Membuat Data Sample:</h4>";
    
    // Cek apakah ada anggota dan buku
    $anggota_count = $pdo->query("SELECT COUNT(*) FROM anggota")->fetchColumn();
    $buku_count = $pdo->query("SELECT COUNT(*) FROM buku WHERE stok > 0")->fetchColumn();
    $peminjaman_count = $pdo->query("SELECT COUNT(*) FROM peminjaman")->fetchColumn();
    
    echo "Jumlah Anggota: $anggota_count<br>";
    echo "Jumlah Buku tersedia: $buku_count<br>";
    echo "Jumlah Peminjaman: $peminjaman_count<br>";
    
    if ($peminjaman_count == 0 && $anggota_count > 0 && $buku_count > 0) {
        echo "📝 Membuat sample peminjaman...<br>";
        
        // Ambil anggota pertama
        $anggota = $pdo->query("SELECT id FROM anggota ORDER BY id LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        // Ambil buku pertama yang tersedia
        $buku = $pdo->query("SELECT id FROM buku WHERE stok > 0 ORDER BY id LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        
        if ($anggota && $buku) {
            $kode_peminjaman = 'PJN' . date('YmdHis') . rand(100, 999);
            $tanggal_pinjam = date('Y-m-d', strtotime('-3 days')); // 3 hari yang lalu
            
            $stmt = $pdo->prepare("
                INSERT INTO peminjaman (kode_peminjaman, anggota_id, buku_id, tanggal_pinjam, status) 
                VALUES (?, ?, ?, ?, 'dipinjam')
            ");
            $stmt->execute([$kode_peminjaman, $anggota['id'], $buku['id'], $tanggal_pinjam]);
            
            // Update stok buku
            $pdo->exec("UPDATE buku SET stok = stok - 1 WHERE id = " . $buku['id']);
            
            echo "✅ Sample peminjaman berhasil dibuat: $kode_peminjaman<br>";
        }
    }
    
    echo "<h3 style='color: green;'>🎉 Perbaikan tabel peminjaman selesai!</h3>";
    echo "<div class='mt-3'>";
    echo "<a href='../peminjaman/index.php' class='btn btn-success me-2'>➡️ Ke Halaman Peminjaman</a>";
    echo "<a href='../pengembalian/index.php' class='btn btn-primary'>➡️ Ke Halaman Pengembalian</a>";
    echo "</div>";
    
} catch(PDOException $e) {
    echo "<h3 style='color: red;'>❌ Error: " . $e->getMessage() . "</h3>";
    
    if ($e->getCode() == 1049) {
        echo "<p>Database tidak ditemukan. Silakan jalankan <a href='complete_reset.php'>complete_reset.php</a> terlebih dahulu.</p>";
    }
}
?>