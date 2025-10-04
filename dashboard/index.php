<?php
session_start();

// Redirect jika belum login
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Include database
require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Get statistics
$stats = [
    'buku_count' => 0,
    'anggota_count' => 0,
    'peminjaman_aktif' => 0,
    'peminjaman_terlambat' => 0
];

try {
    $stats['buku_count'] = $db->query("SELECT COUNT(*) FROM buku")->fetchColumn();
    $stats['anggota_count'] = $db->query("SELECT COUNT(*) FROM anggota")->fetchColumn();
    $stats['peminjaman_aktif'] = $db->query("SELECT COUNT(*) FROM peminjaman WHERE status = 'dipinjam'")->fetchColumn();
    $stats['peminjaman_terlambat'] = $db->query("SELECT COUNT(*) FROM peminjaman WHERE status = 'terlambat'")->fetchColumn();
} catch (Exception $e) {
    // Handle error - mungkin tabel belum ada
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Perpustakaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            min-height: 100vh;
        }
        .sidebar .nav-link {
            color: white;
            padding: 12px 20px;
            margin: 5px 0;
            border-radius: 8px;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: rgba(255,255,255,0.2);
        }
        .stat-card {
            border-radius: 10px;
            border: none;
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar p-0">
                <div class="d-flex flex-column p-3">
                    <h4 class="text-center mb-4">
                        <i class="fas fa-book"></i><br>
                        Perpustakaan
                    </h4>
                    
                    <ul class="nav nav-pills flex-column">
                        <li class="nav-item">
                            <a href="index.php" class="nav-link active">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="../buku/index.php" class="nav-link">
                                <i class="fas fa-book"></i> Manajemen Buku
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="../anggota/index.php" class="nav-link">
                                <i class="fas fa-users"></i> Data Anggota
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="../peminjaman/index.php" class="nav-link">
                                <i class="fas fa-hand-holding"></i> Peminjaman
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="../pengembalian/index.php" class="nav-link">
                                <i class="fas fa-undo"></i> Pengembalian
                            </a>
                        </li>
                        <li class="nav-item mt-4">
                            <a href="../auth/logout.php" class="nav-link text-danger">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Dashboard</h2>
                    <div class="text-muted">
                        <i class="fas fa-user"></i> <?php echo $_SESSION['admin_nama']; ?>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card stat-card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo $stats['buku_count']; ?></h4>
                                        <p>Total Buku</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-book fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo $stats['anggota_count']; ?></h4>
                                        <p>Anggota</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-users fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-warning text-dark">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo $stats['peminjaman_aktif']; ?></h4>
                                        <p>Dipinjam</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-hand-holding fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-danger text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo $stats['peminjaman_terlambat']; ?></h4>
                                        <p>Terlambat</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bolt"></i> Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <a href="../database/create_late_loans.php" class="btn btn-warning w-100 mb-2">
                            <i class="fas fa-clock"></i> Buat Data Terlambat
                        </a>
                        <small class="text-muted">Buat sample data peminjaman terlambat untuk testing</small>
                    </div>
                    <div class="col-md-4">
                        <a href="../peminjaman/index.php" class="btn btn-primary w-100 mb-2">
                            <i class="fas fa-hand-holding"></i> Buat Peminjaman
                        </a>
                        <small class="text-muted">Peminjaman buku baru</small>
                    </div>
                    <div class="col-md-4">
                        <a href="../pengembalian/index.php" class="btn btn-success w-100 mb-2">
                            <i class="fas fa-undo"></i> Proses Pengembalian
                        </a>
                        <small class="text-muted">Pengembalian buku dengan denda</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body text-center">
                                <h4>Selamat Datang di Sistem Perpustakaan</h4>
                                <p class="text-muted">Gunakan menu di sidebar untuk mengelola sistem</p>
                                
                                <?php if ($stats['buku_count'] == 0): ?>
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        Database masih kosong. 
                                        <a href="../database/setup.php" class="alert-link">Klik di sini untuk setup database</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>