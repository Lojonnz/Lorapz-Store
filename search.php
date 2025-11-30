<?php
if (session_status() == PHP_SESSION_NONE) session_start();
require 'koneksi.php';

// Ambil role user
$user_role = $_SESSION['role'] ?? 'user'; // default 'user'

// Sidebar hanya untuk admin
if ($user_role === 'admin') {
    include 'sidebar.php';
}

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$fallback = 'assets/img/noimage.png';
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
.grid {
    display: grid;
    grid-template-columns: repeat(auto-fill,minmax(220px,1fr));
    gap:1rem;
}
.card-prod {
    border-radius:12px;
    overflow:hidden;
    text-align:center;
    transition:.2s;
    box-shadow:0 4px 16px rgba(0,0,0,0.08);
    background-color:var(--card-bg);
}
.card-prod:hover {
    transform:translateY(-3px);
    box-shadow:0 8px 24px rgba(0,0,0,0.12);
}
.card-prod img {
    width:100%;
    height:180px;
    object-fit:cover;
}
.card-body {
    padding:.5rem .75rem;
}
.price {
    font-weight:bold;
    margin-bottom:.5rem;
}
.theme-body {
    background-color: var(--body-bg);
    color: var(--text-color);
}
.theme-text { color: var(--text-color); }
.theme-btn {
    transition:.2s;
}
.theme-btn:hover {
    filter: brightness(0.9);
}
.theme-input {
    background-color: var(--input-bg);
    color: var(--text-color);
    border:1px solid var(--input-border);
}
.theme-alert {
    background-color: var(--alert-bg);
    color: var(--alert-color);
    border:none;
}
</style>
</head>
<body class="theme-body">

<?php include 'navbar.php'; ?>

<main class="container my-5">

    <div class="col-md-12 mb-3">
      <div class="card p-3 theme-card">
        <h6 class="mb-1 theme-text">Cari Cepat</h6>
        <form action="search.php" method="get">
            <div class="input-group">
                <input name="q" class="form-control theme-input" placeholder="Cari ebook atau layanan..." value="<?= htmlspecialchars($q) ?>">
                <button class="btn btn-outline-primary theme-btn" type="submit">Cari</button>
            </div>
        </form>
      </div>
    </div>

    <h3 class="mb-4 theme-text">Hasil pencarian untuk: <span class="text-primary"><?= htmlspecialchars($q) ?></span></h3>

    <?php if ($q === ''): ?>
        <div class="alert alert-info theme-alert">Masukkan kata kunci untuk mencari produk.</div>
    <?php elseif (empty($products)): ?>
        <div class="alert alert-warning theme-alert">Tidak ada produk ditemukan.</div>
    <?php else: ?>
        <div class="grid">
            <?php foreach ($products as $p): 
                $img = $p['cover_image'] ?: $fallback;
            ?>
                <div class="card-prod theme-card">
                    <a href="product.php?id=<?= $p['id'] ?>&type=<?= $p['type'] ?>">
                        <img src="<?= $img ?>" alt="<?= htmlspecialchars($p['title']) ?>" onerror="this.src='<?= $fallback ?>'">
                    </a>
                    <div class="card-body">
                        <h6 class="mb-0 theme-text"><?= htmlspecialchars($p['title']) ?></h6>
                        <div class="text-muted small"><?= htmlspecialchars($p['category']) ?> • <?= ucfirst($p['type']) ?></div>
                        <div class="price">Rp <?= number_format($p['price'],0,',','.') ?></div>
                        <a class="btn btn-sm btn-outline-primary theme-btn" href="product.php?id=<?= $p['id'] ?>&type=<?= $p['type'] ?>">Detail</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</main>

<?php include 'footer.php'; ?>

<script src="global.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
