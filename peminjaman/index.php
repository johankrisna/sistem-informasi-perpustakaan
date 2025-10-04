<?php
// Aktifkan error reporting
include_once '../config/debug.php';

session_start();

// Redirect jika belum login
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Include database
require_once '../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Test query sederhana untuk cek koneksi
    $test_query = $db->query("SELECT 1");
    if (!$test_query) {
        throw new Exception("Koneksi database gagal");
    }
} catch (Exception $e) {
    die("<div class='alert alert-danger m-3'>❌ Error Database: " . $e->getMessage() . 
        "<br><a href='../database/fix_peminjaman_only.php' class='btn btn-warning btn-sm mt-2'>Perbaiki Database</a></div>");
}

// Redirect jika belum login
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Include database
require_once '../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle new loan
$error = '';
$success = '';

if ($_POST && isset($_POST['anggota_id']) && isset($_POST['buku_id'])) {
    $anggota_id = $_POST['anggota_id'];
    $buku_id = $_POST['buku_id'];
    $tanggal_pinjam = $_POST['tanggal_pinjam'];
    
    try {
        // Generate loan code
        $kode_peminjaman = 'PJN' . date('YmdHis') . rand(100, 999);
        
        // Check book availability
        $check_stock = $db->prepare("SELECT judul, stok FROM buku WHERE id = ?");
        $check_stock->execute([$buku_id]);
        $book = $check_stock->fetch(PDO::FETCH_ASSOC);
        
        if ($book && $book['stok'] > 0) {
            // Insert loan record
            $query = "INSERT INTO peminjaman (kode_peminjaman, anggota_id, buku_id, tanggal_pinjam) 
                      VALUES (?, ?, ?, ?)";
            $stmt = $db->prepare($query);
            
            if ($stmt->execute([$kode_peminjaman, $anggota_id, $buku_id, $tanggal_pinjam])) {
                // Update book stock
                $update_stock = $db->prepare("UPDATE buku SET stok = stok - 1 WHERE id = ?");
                $update_stock->execute([$buku_id]);
                
                $success = "Peminjaman berhasil! Kode: " . $kode_peminjaman;
                
                // Refresh page to show updated data
                header("Location: index.php?success=" . urlencode($success));
                exit();
            } else {
                $error = "Gagal menyimpan data peminjaman";
            }
        } else {
            $error = "Stok buku '" . ($book ? $book['judul'] : '') . "' tidak tersedia";
        }
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Get success message from URL
if (isset($_GET['success'])) {
    $success = $_GET['success'];
}

// Get active loans
try {
    $query = "
        SELECT p.*, a.nama as nama_anggota, a.kode_anggota, b.judul, b.penulis 
        FROM peminjaman p 
        JOIN anggota a ON p.anggota_id = a.id 
        JOIN buku b ON p.buku_id = b.id 
        WHERE p.status = 'dipinjam' 
        ORDER BY p.tanggal_pinjam DESC
    ";
    $loans = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $loans = [];
    $error = "Error mengambil data peminjaman: " . $e->getMessage();
}

// Get members and books for dropdown
try {
    $members = $db->query("SELECT id, kode_anggota, nama FROM anggota ORDER BY nama")->fetchAll(PDO::FETCH_ASSOC);
    $books = $db->query("SELECT id, judul, penulis, stok FROM buku WHERE stok > 0 ORDER BY judul")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $members = [];
    $books = [];
    $error = "Error mengambil data dropdown: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peminjaman - Sistem Perpustakaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            min-height: 100vh;
            position: fixed;
            width: 250px;
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
        .main-content {
            margin-left: 250px;
            padding: 20px;
            width: calc(100% - 250px);
        }
        .debug-info {
            background: #f8f9fa;
            border-left: 4px solid #dc3545;
            padding: 10px;
            margin-bottom: 15px;
            font-family: monospace;
            font-size: 0.9em;
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
                            <a href="../dashboard/index.php" class="nav-link">
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
                            <a href="index.php" class="nav-link active">
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
            <div class="col-md-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Peminjaman Buku</h2>
                    <div class="text-muted">
                        <i class="fas fa-user"></i> <?php echo $_SESSION['admin_nama']; ?>
                    </div>
                </div>

                <!-- Debug Info -->
                <?php if (isset($_GET['debug'])): ?>
                    <div class="debug-info">
                        <strong>Debug Info:</strong><br>
                        Members Count: <?php echo count($members); ?><br>
                        Books Count: <?php echo count($books); ?><br>
                        Loans Count: <?php echo count($loans); ?><br>
                        Error: <?php echo $error ? $error : 'None'; ?>
                    </div>
                <?php endif; ?>

                <!-- Messages -->
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- New Loan Form -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-plus"></i> Peminjaman Baru
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="anggota_id" class="form-label">Anggota <span class="text-danger">*</span></label>
                                        <select class="form-select" id="anggota_id" name="anggota_id" required>
                                            <option value="">Pilih Anggota</option>
                                            <?php foreach ($members as $member): ?>
                                                <option value="<?php echo $member['id']; ?>">
                                                    <?php echo htmlspecialchars($member['kode_anggota'] . ' - ' . $member['nama']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if (empty($members)): ?>
                                            <div class="form-text text-warning">
                                                <i class="fas fa-exclamation-triangle"></i> 
                                                Tidak ada data anggota. 
                                                <a href="../anggota/index.php">Tambah anggota</a> terlebih dahulu.
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="buku_id" class="form-label">Buku <span class="text-danger">*</span></label>
                                        <select class="form-select" id="buku_id" name="buku_id" required>
                                            <option value="">Pilih Buku</option>
                                            <?php foreach ($books as $book): ?>
                                                <option value="<?php echo $book['id']; ?>">
                                                    <?php echo htmlspecialchars($book['judul'] . ' - ' . $book['penulis'] . ' (Stok: ' . $book['stok'] . ')'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if (empty($books)): ?>
                                            <div class="form-text text-warning">
                                                <i class="fas fa-exclamation-triangle"></i> 
                                                Tidak ada buku yang tersedia. 
                                                <a href="../buku/index.php">Tambah buku</a> terlebih dahulu.
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="tanggal_pinjam" class="form-label">Tanggal Pinjam <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="tanggal_pinjam" name="tanggal_pinjam" value="<?php echo date('Y-m-d'); ?>" required>
                                    </div>
                                </div>
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary" <?php echo (empty($members) || empty($books)) ? 'disabled' : ''; ?>>
                                    <i class="fas fa-save"></i> Proses Peminjaman
                                </button>
                                <a href="?debug=1" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-bug"></i> Debug
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Active Loans -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-list"></i> Peminjaman Aktif (<?php echo count($loans); ?>)
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($loans)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Tidak ada peminjaman aktif</p>
                                <a href="?debug=1" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-bug"></i> Debug Data
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Kode</th>
                                            <th>Anggota</th>
                                            <th>Buku</th>
                                            <th>Tanggal Pinjam</th>
                                            <th>Lama Pinjam</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($loans as $loan): ?>
                                            <?php
                                            $days_borrowed = floor((time() - strtotime($loan['tanggal_pinjam'])) / (60 * 60 * 24));
                                            $is_late = $days_borrowed > 7;
                                            ?>
                                            <tr class="<?php echo $is_late ? 'table-warning' : ''; ?>">
                                                <td><strong><?php echo $loan['kode_peminjaman']; ?></strong></td>
                                                <td><?php echo htmlspecialchars($loan['nama_anggota']); ?></td>
                                                <td><?php echo htmlspecialchars($loan['judul']); ?></td>
                                                <td><?php echo $loan['tanggal_pinjam']; ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $is_late ? 'warning' : 'success'; ?>">
                                                        <?php echo $days_borrowed; ?> hari
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo $is_late ? 'danger' : 'warning'; ?>">
                                                        <?php echo $is_late ? 'Terlambat' : $loan['status']; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>