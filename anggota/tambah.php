<?php
include_once '../config/database.php';
include_once '../config/session.php';
redirectIfNotLoggedIn();

$database = new Database();
$db = $database->getConnection();

$message = '';

if ($_POST) {
    $kode_anggota = $_POST['kode_anggota'];
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $telepon = $_POST['telepon'];
    $alamat = $_POST['alamat'];

    // Check if member code already exists
    $check_query = "SELECT id FROM anggota WHERE kode_anggota = ?";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->execute([$kode_anggota]);
    
    if ($check_stmt->rowCount() > 0) {
        $message = "Kode anggota sudah digunakan";
    } else {
        $query = "INSERT INTO anggota (kode_anggota, nama, email, telepon, alamat) 
                  VALUES (?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        if ($stmt->execute([$kode_anggota, $nama, $email, $telepon, $alamat])) {
            header("Location: index.php?message=Anggota berhasil ditambahkan");
            exit();
        } else {
            $message = "Gagal menambahkan anggota";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Anggota - Sistem Perpustakaan</title>
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
                    <h2>Tambah Anggota</h2>
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
                                        <label for="kode_anggota" class="form-label">Kode Anggota <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="kode_anggota" name="kode_anggota" required>
                                        <div class="form-text">Kode unik untuk identifikasi anggota</div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="nama" name="nama" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="telepon" class="form-label">Telepon</label>
                                        <input type="text" class="form-control" id="telepon" name="telepon">
                                    </div>
                                    <div class="mb-3">
                                        <label for="alamat" class="form-label">Alamat</label>
                                        <textarea class="form-control" id="alamat" name="alamat" rows="4"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="text-end">
                                <a href="index.php" class="btn btn-secondary">Kembali</a>
                                <button type="submit" class="btn btn-primary">Simpan Anggota</button>
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