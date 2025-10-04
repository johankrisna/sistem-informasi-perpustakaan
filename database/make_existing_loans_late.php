<?php
// File untuk mengubah peminjaman yang sudah ada menjadi terlambat
$host = 'localhost';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=perpustakaan", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h3>⏰ Mengubah Peminjaman Menjadi Terlambat</h3>";
    
    // 1. Cek data peminjaman yang ada
    $loans = $pdo->query("
        SELECT id, kode_peminjaman, tanggal_pinjam, status 
        FROM peminjaman 
        WHERE status = 'dipinjam'
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($loans)) {
        echo "❌ Tidak ada data peminjaman aktif.<br>";
        echo "Silakan buat data peminjaman terlebih dahulu atau jalankan <a href='create_late_loans.php'>create_late_loans.php</a>";
        exit();
    }
    
    echo "Ditemukan " . count($loans) . " peminjaman aktif:<br>";
    
    // 2. Ubah tanggal pinjam untuk membuat terlambat
    $updated_count = 0;
    
    foreach ($loans as $index => $loan) {
        // Buat setiap peminjaman semakin terlambat
        $days_ago = 5 + ($index * 3); // 5, 8, 11, 14 hari yang lalu
        $new_date = date('Y-m-d', strtotime("-$days_ago days"));
        
        $stmt = $pdo->prepare("UPDATE peminjaman SET tanggal_pinjam = ? WHERE id = ?");
        $stmt->execute([$new_date, $loan['id']]);
        
        echo "✅ {$loan['kode_peminjaman']} diubah menjadi $days_ago hari lalu ($new_date)<br>";
        $updated_count++;
    }
    
    // 3. Hitung yang sekarang terlambat
    $late_count = $pdo->query("
        SELECT COUNT(*) as late_count 
        FROM peminjaman 
        WHERE status = 'dipinjam' AND DATEDIFF(CURDATE(), tanggal_pinjam) > 7
    ")->fetch(PDO::FETCH_ASSOC);
    
    echo "<br><h4>📊 Hasil:</h4>";
    echo "• $updated_count data peminjaman diupdate<br>";
    echo "• {$late_count['late_count']} data sekarang statusnya terlambat<br>";
    
    echo "<h3 style='color: green;'>✅ Peminjaman berhasil diubah menjadi terlambat!</h3>";
    echo "<div class='mt-3'>";
    echo "<a href='../peminjaman/index.php' class='btn btn-success me-2'>📚 Lihat Peminjaman</a>";
    echo "<a href='../dashboard/index.php' class='btn btn-info'>📊 Lihat Dashboard</a>";
    echo "</div>";
    
} catch(PDOException $e) {
    echo "<h3 style='color: red;'>❌ Error: " . $e->getMessage() . "</h3>";
}
?>