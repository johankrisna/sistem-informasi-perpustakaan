<?php
// File: sidebar.php
?>
<div class="col-md-2 sidebar">
    <div class="d-flex flex-column p-3">
        <h4 class="text-center mb-4">
            <i class="fas fa-book"></i><br>
            Perpustakaan
        </h4>
        
        <ul class="nav nav-pills flex-column">
            <li class="nav-item">
                <a href="../dashboard/index.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' && basename(dirname($_SERVER['PHP_SELF'])) == 'dashboard' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="../buku/index.php" class="nav-link <?php echo basename(dirname($_SERVER['PHP_SELF'])) == 'buku' ? 'active' : ''; ?>">
                    <i class="fas fa-book"></i> Manajemen Buku
                </a>
            </li>
            <li class="nav-item">
                <a href="../anggota/index.php" class="nav-link <?php echo basename(dirname($_SERVER['PHP_SELF'])) == 'anggota' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i> Data Anggota
                </a>
            </li>
            <li class="nav-item">
                <a href="../peminjaman/index.php" class="nav-link <?php echo basename(dirname($_SERVER['PHP_SELF'])) == 'peminjaman' ? 'active' : ''; ?>">
                    <i class="fas fa-hand-holding"></i> Peminjaman
                </a>
            </li>
            <li class="nav-item">
                <a href="../pengembalian/index.php" class="nav-link <?php echo basename(dirname($_SERVER['PHP_SELF'])) == 'pengembalian' ? 'active' : ''; ?>">
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