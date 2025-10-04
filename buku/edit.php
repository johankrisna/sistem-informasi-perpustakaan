<?php
include_once '../config/database.php';
include_once '../config/session.php';
redirectIfNotLoggedIn();

$database = new Database();
$db = $database->getConnection();

// Get book data
$id = $_GET['id'];
$query = "SELECT * FROM buku WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$id]);
$book = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$book) {
    header("Location: index.php");
    exit();
}

$message = '';

if ($_POST) {
    $judul = $_POST['judul'];
    $penulis = $_POST['penulis'];
    $penerbit = $_POST['penerbit'];
    $tahun_terbit = $_POST['tahun_terbit'];
    $isbn = $_POST['isbn'];
    $kategori = $_POST['kategori'];
    $jumlah_halaman = $_POST['jumlah_halaman'];
    $stok = $_POST['stok'];

    $query = "UPDATE buku SET judul=?, penulis=?, penerbit=?, tahun_terbit=?, isbn=?, kategori=?, jumlah_halaman=?, stok=? 
              WHERE id=?";
    $stmt = $db->prepare($query);
    if ($stmt->execute([$judul, $penulis, $penerbit, $tahun_terbit, $isbn, $kategori, $jumlah_halaman, $stok, $id])) {
        header("Location: index.php?message=Buku berhasil diupdate");
        exit();
    } else {
        $message = "Gagal mengupdate buku";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Buku - Sistem Perpustakaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            height: 100vh;
            position: fixed;
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
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include '../sidebar.php'; ?>

            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Edit Buku</h2>
                    <div class="text-muted">
                        <i class="fas fa-user"></i> <?php echo $_SESSION['admin_nama']; ?>
                    </div>
                </div>

                <!-- Messages -->
                <?php if ($message): ?>
                    <div class="alert alert-danger"><?php echo $message; ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="judul" class="form-label">Judul Buku <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="judul" name="judul" value="<?php echo htmlspecialchars($book['judul']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="penulis" class="form-label">Penulis <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="penulis" name="penulis" value="<?php echo htmlspecialchars($book['penulis']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="penerbit" class="form-label">Penerbit <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="penerbit" name="penerbit" value="<?php echo htmlspecialchars($book['penerbit']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="tahun_terbit" class="form-label">Tahun Terbit <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="tahun_terbit" name="tahun_terbit" value="<?php echo $book['tahun_terbit']; ?>" min="1900" max="<?php echo date('Y'); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="isbn" class="form-label">ISBN</label>
                                        <input type="text" class="form-control" id="isbn" name="isbn" value="<?php echo htmlspecialchars($book['isbn']); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="kategori" class="form-label">Kategori</label>
                                        <input type="text" class="form-control" id="kategori" name="kategori" value="<?php echo htmlspecialchars($book['kategori']); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="jumlah_halaman" class="form-label">Jumlah Halaman</label>
                                        <input type="number" class="form-control" id="jumlah_halaman" name="jumlah_halaman" value="<?php echo $book['jumlah_halaman']; ?>" min="1">
                                    </div>
                                    <div class="mb-3">
                                        <label for="stok" class="form-label">Stok <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="stok" name="stok" value="<?php echo $book['stok']; ?>" min="0" required>
                                    </div>
                                </div>
                            </div>
                            <div class="text-end">
                                <a href="index.php" class="btn btn-secondary">Kembali</a>
                                <button type="submit" class="btn btn-primary">Update Buku</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>