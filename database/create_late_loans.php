<?php
// File khusus untuk membuat data peminjaman terlambat
$host = 'localhost';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=perpustakaan", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h3>📚 Membuat Data Peminjaman Terlambat</h3>";
    
    // 1. Cek data yang ada
    $anggota_count = $pdo->query("SELECT COUNT(*) FROM anggota")->fetchColumn();
    $buku_count = $pdo->query("SELECT COUNT(*) FROM buku WHERE stok > 0")->fetchColumn();
    $peminjaman_count = $pdo->query("SELECT COUNT(*) FROM peminjaman")->fetchColumn();
    
    echo "Jumlah Anggota: $anggota_count<br>";
    echo "Jumlah Buku tersedia: $buku_count<br>";
    echo "Jumlah Peminjaman: $peminjaman_count<br><br>";
    
    if ($anggota_count == 0 || $buku_count == 0) {
        echo "❌ Tidak ada cukup data anggota atau buku. Silakan tambah data terlebih dahulu.<br>";
        exit();
    }
    
    // 2. Hapus semua peminjaman yang ada (opsional)
    $pdo->exec("DELETE FROM peminjaman");
    echo "🗑️  Data peminjaman lama dihapus<br>";
    
    // Reset stok semua buku ke 5
    $pdo->exec("UPDATE buku SET stok = 5");
    echo "🔄 Stok buku direset ke 5<br>";
    
    // 3. Buat data peminjaman dengan berbagai status
    echo "<h4>🔄 Membuat Data Peminjaman:</h4>";
    
    // Ambil beberapa anggota dan buku
    $anggotas = $pdo->query("SELECT id FROM anggota ORDER BY id LIMIT 3")->fetchAll(PDO::FETCH_COLUMN);
    $bukus = $pdo->query("SELECT id FROM buku ORDER BY id LIMIT 4")->fetchAll(PDO::FETCH_COLUMN);
    
    $sample_loans = [
        // Peminjaman aktif (baru)
        [
            'anggota_id' => $anggotas[0],
            'buku_id' => $bukus[0],
            'tanggal_pinjam' => date('Y-m-d', strtotime('-1 day')), // 1 hari lalu
            'status' => 'dipinjam',
            'keterangan' => 'Aktif (1 hari)'
        ],
        
        // Peminjaman aktif (mendekati deadline)
        [
            'anggota_id' => $anggotas[1],
            'buku_id' => $bukus[1],
            'tanggal_pinjam' => date('Y-m-d', strtotime('-5 days')), // 5 hari lalu
            'status' => 'dipinjam',
            'keterangan' => 'Aktif (5 hari)'
        ],
        
        // Peminjaman terlambat (sedikit)
        [
            'anggota_id' => $anggotas[0],
            'buku_id' => $bukus[2],
            'tanggal_pinjam' => date('Y-m-d', strtotime('-10 days')), // 10 hari lalu
            'status' => 'dipinjam',
            'keterangan' => 'Terlambat (3 hari)'
        ],
        
        // Peminjaman terlambat (parah)
        [
            'anggota_id' => $anggotas[2],
            'buku_id' => $bukus[3],
            'tanggal_pinjam' => date('Y-m-d', strtotime('-20 days')), // 20 hari lalu
            'status' => 'dipinjam',
            'keterangan' => 'Terlambat (13 hari)'
        ],
        
        // Peminjaman yang sudah dikembalikan tepat waktu
        [
            'anggota_id' => $anggotas[1],
            'buku_id' => $bukus[0],
            'tanggal_pinjam' => date('Y-m-d', strtotime('-15 days')),
            'tanggal_kembali' => date('Y-m-d', strtotime('-8 days')),
            'status' => 'dikembalikan',
            'keterangan' => 'Dikembalikan tepat waktu'
        ],
        
        // Peminjaman yang dikembalikan terlambat
        [
            'anggota_id' => $anggotas[2],
            'buku_id' => $bukus[1],
            'tanggal_pinjam' => date('Y-m-d', strtotime('-20 days')),
            'tanggal_kembali' => date('Y-m-d', strtotime('-10 days')),
            'status' => 'terlambat',
            'denda' => 15000,
            'keterangan' => 'Dikembalikan terlambat (denda Rp 15,000)'
        ]
    ];
    
    foreach ($sample_loans as $loan) {
        $kode_peminjaman = 'PJN' . date('YmdHis') . rand(100, 999);
        
        $stmt = $pdo->prepare("
            INSERT INTO peminjaman 
            (kode_peminjaman, anggota_id, buku_id, tanggal_pinjam, tanggal_kembali, status, denda) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $kode_peminjaman,
            $loan['anggota_id'],
            $loan['buku_id'],
            $loan['tanggal_pinjam'],
            $loan['tanggal_kembali'] ?? null,
            $loan['status'],
            $loan['denda'] ?? 0
        ]);
        
        // Kurangi stok buku untuk peminjaman yang masih aktif
        if ($loan['status'] == 'dipinjam') {
            $pdo->exec("UPDATE buku SET stok = stok - 1 WHERE id = " . $loan['buku_id']);
        }
        
        echo "✅ " . $loan['keterangan'] . " - Kode: $kode_peminjaman<br>";
    }
    
    echo "<h4>📊 Status Akhir:</h4>";
    
    // Hitung berbagai status peminjaman
    $stats = $pdo->query("
        SELECT 
            status,
            COUNT(*) as jumlah,
            AVG(DATEDIFF(COALESCE(tanggal_kembali, CURDATE()), tanggal_pinjam)) as rata_rata_hari
        FROM peminjaman 
        GROUP BY status
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($stats as $stat) {
        echo "• {$stat['status']}: {$stat['jumlah']} data";
        if ($stat['rata_rata_hari']) {
            echo " (rata-rata: " . round($stat['rata_rata_hari'], 1) . " hari)";
        }
        echo "<br>";
    }
    
    // Hitung yang seharusnya terlambat
    $should_be_late = $pdo->query("
        SELECT COUNT(*) as should_late 
        FROM peminjaman 
        WHERE status = 'dipinjam' AND DATEDIFF(CURDATE(), tanggal_pinjam) > 7
    ")->fetch(PDO::FETCH_ASSOC);
    
    echo "• Yang seharusnya terlambat: {$should_be_late['should_late']} data<br>";
    
    echo "<h3 style='color: green;'>🎉 Data peminjaman terlambat berhasil dibuat!</h3>";
    echo "<div class='mt-3'>";
    echo "<a href='../peminjaman/index.php' class='btn btn-success me-2'>📚 Lihat Peminjaman</a>";
    echo "<a href='../pengembalian/index.php' class='btn btn-primary'>🔄 Lihat Pengembalian</a>";
    echo "<a href='../dashboard/index.php' class='btn btn-info'>📊 Lihat Dashboard</a>";
    echo "</div>";
    
} catch(PDOException $e) {
    echo "<h3 style='color: red;'>❌ Error: " . $e->getMessage() . "</h3>";
}
?>