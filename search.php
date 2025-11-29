<?php
session_start();
require 'koneksi.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$products = [];

if ($q !== '') {
    $stmt = $db->prepare("
        SELECT 
            ebook_id AS id,
            ebook_title AS title,
            ebook_price AS price,
            ebook_cover_image AS cover_image,
            ebook_category AS category,
            'ebook' AS type
        FROM ebooks
        WHERE ebook_title LIKE CONCAT('%', ?, '%')

        UNION ALL

        SELECT
            service_id AS id,
            service_name AS title,
            service_price AS price,
            service_thumbnail AS cover_image,
            service_category AS category,
            'service' AS type
        FROM services
        WHERE service_name LIKE CONCAT('%', ?, '%')
    ");
    $stmt->bind_param("ss", $q, $q);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

$isLoggedIn = isset($_SESSION['username']);
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$fallback = 'assets/img/noimage.png';
// Ambil role user
$user_role = $_SESSION['role'] ?? 'user'; // default 'user'

// Hanya tampilkan sidebar jika admin
if ($user_role === 'admin') {
    include 'sidebar.php';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Pencarian — Lorapz Store</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="global.css">

<style>
.grid { display: grid; grid-template-columns: repeat(auto-fill,minmax(200px,1fr)); gap:1rem; }
.card-prod { border:1px solid #ddd; border-radius:10px; overflow:hidden; text-align:center; transition:.2s; }
.card-prod img { width:100%; height:180px; object-fit:cover; }
.card-body { padding:.5rem .75rem; }
.price { font-weight:bold; margin-bottom:.5rem; }
</style>
</head>
<body>

<?php include 'navbar.php'; ?>

<main class="container my-5">
    <div class="col-md-40">
      <div class="card p-3">
        <h6 class="mb-1">Cari Cepat</h6>
        <form action="search.php" method="get">
            <div class="input-group">
                <input name="q" class="form-control" placeholder="Cari ebook atau layanan...">
                <button class="btn btn-outline-primary" type="submit">Cari</button>
            </div>
        </form>
      </div>
    </div>
    <h3 class="mb-4">Hasil pencarian untuk: <span class="text-primary"><?= htmlspecialchars($q) ?></span></h3>

    <?php if ($q === ''): ?>
        <div class="alert alert-info">Masukkan kata kunci untuk mencari produk.</div>
    <?php elseif (empty($products)): ?>
        <div class="alert alert-warning">Tidak ada produk ditemukan.</div>
    <?php else: ?>
        <div class="grid">
            <?php foreach ($products as $p): ?>
                <?php 
                $img = $p['cover_image'] && file_exists($p['cover_image'])
                       ? $p['cover_image']
                       : $fallback;
                ?>
                <div class="card-prod">
                    <a href="product.php?id=<?= $p['id'] ?>&type=<?= $p['type'] ?>">
                        <img src="<?= $img ?>" alt="<?= htmlspecialchars($p['title']) ?>" onerror="this.src='<?= $fallback ?>'">
                    </a>
                    <div class="card-body">
                        <h6 class="mb-0"><?= htmlspecialchars($p['title']) ?></h6>
                        <div class="text-muted small"><?= htmlspecialchars($p['category']) ?> • <?= ucfirst($p['type']) ?></div>
                        <div class="price">Rp <?= number_format($p['price'],0,',','.') ?></div>
                        <a class="btn btn-sm btn-outline-primary" href="product.php?id=<?= $p['id'] ?>&type=<?= $p['type'] ?>">Detail</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<script src="global.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
