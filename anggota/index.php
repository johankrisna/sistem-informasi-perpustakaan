<?php
include_once '../config/database.php';
include_once '../config/session.php';
redirectIfNotLoggedIn();

$database = new Database();
$db = $database->getConnection();

// Handle delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $query = "DELETE FROM anggota WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$id]);
    header("Location: index.php?message=Anggota berhasil dihapus");
    exit();
}

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$where = '';
if ($search) {
    $where = " WHERE nama LIKE :search OR kode_anggota LIKE :search OR email LIKE :search";
}

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Get total records
$total_query = "SELECT COUNT(*) FROM anggota" . $where;
$stmt = $db->prepare($total_query);
if ($search) {
    $search_term = "%$search%";
    $stmt->bindParam(':search', $search_term);
}
$stmt->execute();
$total_records = $stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);

// Get members
$query = "SELECT * FROM anggota" . $where . " ORDER BY id DESC LIMIT :limit OFFSET :offset";
$stmt = $db->prepare($query);
if ($search) {
    $stmt->bindParam(':search', $search_term);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$anggota = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Anggota - Sistem Perpustakaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include '../sidebar.php'; ?>

            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Data Anggota</h2>
                    <div class="text-muted">
                        <i class="fas fa-user"></i> <?php echo $_SESSION['admin_nama']; ?>
                    </div>
                </div>

                <!-- Messages -->
                <?php if (isset($_GET['message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $_GET['message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Toolbar -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <form method="GET" class="d-flex">
                                    <input type="text" name="search" class="form-control me-2" placeholder="Cari anggota..." value="<?php echo htmlspecialchars($search); ?>">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </form>
                            </div>
                            <div class="col-md-6 text-end">
                                <a href="tambah.php" class="btn btn-success">
                                    <i class="fas fa-plus"></i> Tambah Anggota
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Members Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Daftar Anggota</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Kode</th>
                                        <th>Nama</th>
                                        <th>Email</th>
                                        <th>Telepon</th>
                                        <th>Alamat</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($anggota) > 0): ?>
                                        <?php $no = $offset + 1; ?>
                                        <?php foreach ($anggota as $member): ?>
                                            <tr>
                                                <td><?php echo $no++; ?></td>
                                                <td><strong><?php echo htmlspecialchars($member['kode_anggota']); ?></strong></td>
                                                <td><?php echo htmlspecialchars($member['nama']); ?></td>
                                                <td><?php echo htmlspecialchars($member['email']); ?></td>
                                                <td><?php echo htmlspecialchars($member['telepon']); ?></td>
                                                <td><?php echo htmlspecialchars($member['alamat']); ?></td>
                                                <td>
                                                    <a href="edit.php?id=<?php echo $member['id']; ?>" class="btn btn-sm btn-warning">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="index.php?delete=<?php echo $member['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center">Tidak ada data anggota</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <nav>
                                <ul class="pagination justify-content-center">
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>