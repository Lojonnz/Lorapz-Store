<?php
session_start();
require 'koneksi.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

$products = [];

if ($q !== '') {

    $stmt = $db->prepare("
        SELECT ebook_id AS id, title, price, cover_image, category, 'ebook' AS type
        FROM ebooks
        WHERE title LIKE CONCAT('%', ?, '%')

        UNION ALL

        SELECT service_id AS id, name AS title, price, thumbnail AS cover_image, 'Jasa' AS category, 'service' AS type
        FROM services
        WHERE name LIKE CONCAT('%', ?, '%')
    ");

    $stmt->bind_param("ss", $q, $q);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

// Login & role
$isLoggedIn = isset($_SESSION['username']);
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

$fallback = '/mnt/data/67e14b3f-30f4-4645-96f5-d2833048eb72.png';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Pencarian — Lorapz Store</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
:root{--accent:#0d6efd; --bg:#f4f6f9; --card:#fff; --muted:#6b7280; --radius:12px}
body{font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;background:var(--bg);color:#111;margin:0}
.navbar{background:#fff;box-shadow:0 4px 18px rgba(13,110,253,0.08)}
.navbar .brand{font-weight:700;color:var(--accent)}

.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem}
.card-prod{background:var(--card);border-radius:var(--radius);overflow:hidden;box-shadow:0 6px 20px rgba(16,24,40,0.06);transition:.18s}
.card-prod:hover{transform:translateY(-6px)}
.card-prod img{width:100%;height:160px;object-fit:cover}
.price{font-weight:600;color:var(--accent)}

/* Dark mode match homepage */
body.dark{background:#071024;color:#e6eef8}
body.dark .card-prod{background:#0f1724;box-shadow:0 6px 18px rgba(2,6,23,0.6)}
body.dark .navbar{background:#06132a}

.theme-toggle{border:0;background:transparent;font-size:1.05rem}
</style>

</head>
<body>

<!-- NAVBAR IDENTIK -->
<nav class="navbar py-3">
  <div class="container d-flex justify-content-between align-items-center">
    <a href="index.php" class="brand text-decoration-none">Lorapz Store</a>

    <div class="d-flex align-items-center">
        <button id="theme-toggle" class="theme-toggle me-3">🌓</button>

        <?php if ($isLoggedIn): ?>
            <div class="dropdown me-2">
                <button class="btn btn-sm btn-outline-accent dropdown-toggle"
                        type="button"
                        data-bs-toggle="dropdown">
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

<div class="container my-5">

    <h3 class="mb-4">Hasil pencarian untuk:
        <span class="text-primary"><?= htmlspecialchars($q) ?></span>
    </h3>

    <?php if ($q === ''): ?>
        <div class="alert alert-info">Masukkan kata untuk mencari produk.</div>

    <?php elseif (empty($products)): ?>
        <div class="alert alert-warning">Tidak ada produk ditemukan.</div>

    <?php else: ?>

        <div class="grid">
            <?php foreach ($products as $p):
                $img = !empty($p['cover_image'])
                        ? 'uploads/'.htmlspecialchars($p['cover_image'])
                        : $fallback;
            ?>
            <div class="card-prod">
                <img src="<?= $img ?>" onerror="this.src='<?= $fallback ?>'">

                <div class="p-3">
                    <h6 class="mb-0"><?= htmlspecialchars($p['title']) ?></h6>

                    <div class="text-muted small"><?= htmlspecialchars($p['category']) ?></div>

                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <div class="price">Rp <?= number_format($p['price'],0,',','.') ?></div>

                        <div>
                            <a class="btn btn-sm btn-outline-accent"
                               href="product.php?id=<?= $p['id'] ?>&type=<?= $p['type'] ?>">
                                Detail
                            </a>

                            <a class="btn btn-sm btn-primary"
                               href="buy.php?id=<?= $p['id'] ?>&type=<?= $p['type'] ?>">
                                Beli
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>

</div>

<script>
// Dark mode identik homepage
const tbtn = document.getElementById('theme-toggle');
tbtn.addEventListener('click', ()=>{
    document.body.classList.toggle('dark');
    localStorage.setItem('lorapz_theme', document.body.classList.contains('dark') ? 'dark' : 'light');
});
if (localStorage.getItem('lorapz_theme') === 'dark') document.body.classList.add('dark');
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
