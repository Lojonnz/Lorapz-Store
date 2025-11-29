<?php
// Mulai session hanya jika belum ada
if (session_status() === PHP_SESSION_NONE) session_start();

$isLoggedIn = isset($_SESSION['username']);
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
?>
<nav class="navbar py-3 navbar-expand-lg navbar-light bg-light">
  <div class="container d-flex justify-content-between align-items-center">
    <a href="index.php" class="brand text-decoration-none fw-bold fs-4">Lorapz Store</a>

    <div class="d-flex align-items-center">
      <!-- Theme Toggle -->
      <button id="theme-toggle" class="theme-toggle btn btn-sm btn-outline-secondary me-3">🌓</button>

      <?php if ($isLoggedIn): ?>
        <div class="dropdown me-2">
          <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            Akun (<?= htmlspecialchars($_SESSION['username']) ?>)
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <?php if ($isAdmin): ?>
              <li><a class="dropdown-item" href="dashboard.php">Dashboard Admin</a></li>
              <li><hr class="dropdown-divider"></li>
            <?php endif; ?>
            <li><a class="dropdown-item" href="profile.php">Profil Saya</a></li>
            <li><a class="dropdown-item" href="orders.php">Riwayat Pembelian</a></li>
          </ul>
        </div>
        <a class="btn btn-sm btn-outline-secondary" href="logout.php">Logout</a>
      <?php else: ?>
        <a class="btn btn-sm btn-primary" href="login.php">Login</a>
      <?php endif; ?>
    </div>
  </div>
</nav>
