<?php
session_start();

// Redirect jika sudah login
if (isset($_SESSION['admin_id'])) {
    header("Location: ../dashboard/index.php");
    exit();
}

$error = '';

if ($_POST) {
    // Include database
    require_once '../config/database.php';
    
    $database = new Database();
    $db = $database->getConnection();

    $username = $_POST['username'];
    $password = $_POST['password'];
    
    try {
        $query = "SELECT id, username, password, nama_lengkap FROM admin WHERE username = :username";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':username', $username);
        
        if ($stmt->execute()) {
            if ($stmt->rowCount() == 1) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if (password_verify($password, $row['password'])) {
                    $_SESSION['admin_id'] = $row['id'];
                    $_SESSION['admin_username'] = $row['username'];
                    $_SESSION['admin_nama'] = $row['nama_lengkap'];
                    header("Location: ../dashboard/index.php");
                    exit();
                } else {
                    $error = "Password salah!";
                }
            } else {
                $error = "Username tidak ditemukan!";
            }
        } else {
            $error = "Terjadi kesalahan sistem.";
        }
    } catch (PDOException $e) {
        // Jika kolom tidak ditemukan, redirect ke fix
        if ($e->getCode() == '42S22') {
            header("Location: ../database/fix_admin.php");
            exit();
        }
        $error = "Error database: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Perpustakaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 30px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="login-card">
                    <div class="login-header">
                        <h2><i class="fas fa-book"></i> Sistem Perpustakaan</h2>
                        <p class="mb-0">Silakan masuk ke akun Anda</p>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i> 
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" id="username" name="username" value="admin" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" value="admin123" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 py-2">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </button>
                        </form>
                        
                        <div class="text-center mt-3">
                            <small class="text-muted">
                                Default login: <strong>admin / admin123</strong>
                            </small>
                        </div>

                        <div class="text-center mt-3">
                            <a href="../database/setup.php" class="btn btn-outline-primary btn-sm me-2">
                                <i class="fas fa-database"></i> Setup Database
                            </a>
                            <div class="text-center mt-3">
    <div class="btn-group" role="group">
        <a href="../database/fix_peminjaman_only.php" class="btn btn-outline-warning btn-sm">
            <i class="fas fa-wrench"></i> Fix Peminjaman
        </a>
        <a href="../database/complete_reset_v2.php" class="btn btn-outline-danger btn-sm">
            <i class="fas fa-redo"></i> Reset All v2
        </a>
    </div>
</div>
                            <!-- Tambahkan di bagian bawah form login, setelah tombol setup database -->
<div class="text-center mt-3">
    <a href="../database/setup.php" class="btn btn-outline-primary btn-sm me-2">
        <i class="fas fa-database"></i> Setup Database
    </a>
    <a href="../database/fix_tables.php" class="btn btn-outline-warning btn-sm me-2">
        <i class="fas fa-tools"></i> Fix Tables
    </a>
    <a href="../database/setup_simple.php" class="btn btn-outline-danger btn-sm">
        <i class="fas fa-redo"></i> Reset All
    </a>
</div>
                            <a href="../database/fix_admin.php" class="btn btn-outline-warning btn-sm">
                                <i class="fas fa-tools"></i> Fix Admin Table
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>