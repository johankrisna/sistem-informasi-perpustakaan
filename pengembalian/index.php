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
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle return
$error = '';
$success = '';

if ($_POST && isset($_POST['peminjaman_id'])) {
    $peminjaman_id = $_POST['peminjaman_id'];
    $tanggal_kembali = $_POST['tanggal_kembali'];
    
    try {
        // Calculate fine (5000 per day late after 7 days)
        $loan_query = "SELECT tanggal_pinjam, buku_id FROM peminjaman WHERE id = ?";
        $loan_stmt = $db->prepare($loan_query);
        $loan_stmt->execute([$peminjaman_id]);
        $loan = $loan_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($loan) {
            $days_late = max(0, (strtotime($tanggal_kembali) - strtotime($loan['tanggal_pinjam'])) / (60 * 60 * 24) - 7);
            $denda = $days_late * 5000;
            
            // Update loan record
            $status = $denda > 0 ? 'terlambat' : 'dikembalikan';
            $update_query = "UPDATE peminjaman SET tanggal_kembali = ?, status = ?, denda = ? WHERE id = ?";
            $update_stmt = $db->prepare($update_query);
            
            if ($update_stmt->execute([$tanggal_kembali, $status, $denda, $peminjaman_id])) {
                // Update book stock
                $stock_query = "UPDATE buku SET stok = stok + 1 WHERE id = ?";
                $stock_stmt = $db->prepare($stock_query);
                $stock_stmt->execute([$loan['buku_id']]);
                
                $success = "Buku berhasil dikembalikan";
                if ($denda > 0) {
                    $success .= " dengan denda Rp " . number_format($denda, 0, ',', '.');
                }
                
                // Refresh page
                header("Location: index.php?success=" . urlencode($success));
                exit();
            } else {
                $error = "Gagal mengupdate data pengembalian";
            }
        } else {
            $error = "Data peminjaman tidak ditemukan";
        }
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Get success message from URL
if (isset($_GET['success'])) {
    $success = $_GET['success'];
}

// Get active loans for return
try {
    $query = "
        SELECT p.*, a.nama as nama_anggota, a.kode_anggota, b.judul, b.penulis 
        FROM peminjaman p 
        JOIN anggota a ON p.anggota_id = a.id 
        JOIN buku b ON p.buku_id = b.id 
        WHERE p.status = 'dipinjam' 
        ORDER BY p.tanggal_pinjam DESC
    ";
    $active_loans = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $active_loans = [];
    $error = "Error mengambil data peminjaman aktif: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengembalian - Sistem Perpustakaan</title>
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
                            <a href="../peminjaman/index.php" class="nav-link">
                                <i class="fas fa-hand-holding"></i> Peminjaman
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="index.php" class="nav-link active">
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
                    <h2>Pengembalian Buku</h2>
                    <div class="text-muted">
                        <i class="fas fa-user"></i> <?php echo $_SESSION['admin_nama']; ?>
                    </div>
                </div>

                <!-- Debug Info -->
                <?php if (isset($_GET['debug'])): ?>
                    <div class="debug-info">
                        <strong>Debug Info:</strong><br>
                        Active Loans Count: <?php echo count($active_loans); ?><br>
                        Error: <?php echo $error ? $error : 'None'; ?><br>
                        Success: <?php echo $success ? $success : 'None'; ?>
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

                <!-- Return Form -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-undo"></i> Proses Pengembalian
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="peminjaman_id" class="form-label">Pilih Peminjaman <span class="text-danger">*</span></label>
                                        <select class="form-select" id="peminjaman_id" name="peminjaman_id" required>
                                            <option value="">Pilih Peminjaman</option>
                                            <?php foreach ($active_loans as $loan): ?>
                                                <option value="<?php echo $loan['id']; ?>">
                                                    <?php echo htmlspecialchars($loan['kode_peminjaman'] . ' - ' . $loan['nama_anggota'] . ' - ' . $loan['judul']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if (empty($active_loans)): ?>
                                            <div class="form-text text-warning">
                                                <i class="fas fa-exclamation-triangle"></i> 
                                                Tidak ada peminjaman aktif.
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="tanggal_kembali" class="form-label">Tanggal Kembali <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="tanggal_kembali" name="tanggal_kembali" value="<?php echo date('Y-m-d'); ?>" required>
                                    </div>
                                </div>
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary" <?php echo empty($active_loans) ? 'disabled' : ''; ?>>
                                    <i class="fas fa-check"></i> Proses Pengembalian
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
                            <i class="fas fa-list"></i> Peminjaman Aktif (<?php echo count($active_loans); ?>)
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($active_loans)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Tidak ada peminjaman aktif</p>
                                <a href="../peminjaman/index.php" class="btn btn-primary me-2">
                                    <i class="fas fa-hand-holding"></i> Buat Peminjaman
                                </a>
                                <a href="?debug=1" class="btn btn-outline-primary">
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
                                        <?php foreach ($active_loans as $loan): ?>
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
                                                    <span class="badge bg-<?php echo $is_late ? 'danger' : 'success'; ?>">
                                                        <?php echo $days_borrowed; ?> hari
                                                        <?php if ($is_late): ?>
                                                            (Terlambat <?php echo $days_borrowed - 7; ?> hari)
                                                        <?php endif; ?>
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