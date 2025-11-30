<?php
session_start();
require 'koneksi.php';

// cek admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Ambil semua ebook
$ebooks = $db->query("
    SELECT ebook_id, ebook_title, ebook_price, ebook_cover_image, ebook_category 
    FROM ebooks 
    ORDER BY ebook_created_at DESC
");

// Ambil semua service
$services = $db->query("
    SELECT service_id, service_name, service_price, service_thumbnail, service_category 
    FROM services 
    ORDER BY service_created_at DESC
");

$fallback = 'assets/img/noimage.png';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin – Kelola Produk</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="global.css">
<style>
.grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1rem; }
.card-prod { border: 1px solid #ddd; border-radius: 10px; overflow: hidden; transition: 0.2s; }
.card-prod img { width: 100%; height: 180px; object-fit: cover; }
.card-body { padding: 0.5rem 0.75rem; }
.price { font-weight: bold; }
.action-btn { font-size: 13px; padding: 5px 10px; border-radius: 6px; margin: 2px; text-decoration: none; }
.edit { background: #0d6efd; color: white; }
.delete { background: #dc3545; color: white; }
.delete:hover { opacity: 0.85; }
</style>
</head>
<body>

<?php include 'navbar.php'; ?>

<main class="container my-5">

<h3 class="mb-4">Kelola Produk</h3>

<!-- EBOOKS -->
<section class="mb-5">
  <h4>E-Book</h4>
  <div class="grid">
    <?php if ($ebooks->num_rows > 0): ?>
      <?php while ($e = $ebooks->fetch_assoc()):
          $img = $e['ebook_cover_image'] && file_exists($e['ebook_cover_image'])
                 ? $e['ebook_cover_image']
                 : $fallback;
      ?>
      <div class="card-prod text-center">
        <img src="<?= $img ?>" alt="Cover Ebook" onerror="this.src='<?= $fallback ?>'">
        <div class="card-body">
          <h6 class="mb-1"><?= htmlspecialchars($e['ebook_title']) ?></h6>
          <div class="price mb-2">Rp <?= number_format($e['ebook_price'],0,',','.') ?></div>
          <a class="action-btn edit" href="admin_edit_product.php?type=ebook&id=<?= $e['ebook_id'] ?>">Edit</a>
          <a class="action-btn delete" href="admin_products.php?delete=ebook&id=<?= $e['ebook_id'] ?>" onclick="return confirm('Hapus ebook ini?')">Hapus</a>
        </div>
      </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div class="card p-4">Belum ada ebook.</div>
    <?php endif; ?>
  </div>
</section>

<!-- SERVICES -->
<section class="mb-5">
  <h4>Service</h4>
  <div class="grid">
    <?php if ($services->num_rows > 0): ?>
      <?php while ($s = $services->fetch_assoc()):
          $img = $s['service_thumbnail'] && file_exists($s['service_thumbnail'])
                 ? $s['service_thumbnail']
                 : $fallback;
      ?>
      <div class="card-prod text-center">
        <img src="<?= $img ?>" alt="Thumbnail Service" onerror="this.src='<?= $fallback ?>'">
        <div class="card-body">
          <h6 class="mb-1"><?= htmlspecialchars($s['service_name']) ?></h6>
          <div class="price mb-2">Rp <?= number_format($s['service_price'],0,',','.') ?></div>
          <a class="action-btn edit" href="admin_edit_product.php?type=service&id=<?= $s['service_id'] ?>">Edit</a>
          <a class="action-btn delete" href="admin_products.php?delete=service&id=<?= $s['service_id'] ?>" onclick="return confirm('Hapus service ini?')">Hapus</a>
        </div>
      </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div class="card p-4">Belum ada layanan.</div>
    <?php endif; ?>
  </div>
</section>

</main>

<?php include 'footer.php'; ?>

<?php
// ===== HAPUS PRODUK =====
if (isset($_GET['delete'], $_GET['id'])) {
    $id = intval($_GET['id']);
    $type = $_GET['delete'];

    if ($type === 'ebook') {
        $stmt = $db->prepare("DELETE FROM ebooks WHERE ebook_id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    } elseif ($type === 'service') {
        $stmt = $db->prepare("DELETE FROM services WHERE service_id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }

    header("Location: admin_products.php");
    exit;
}
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
