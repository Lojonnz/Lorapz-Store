<?php
session_start();
require 'koneksi.php';

// Ambil semua ebook
$ebooks = $db->query("
    SELECT ebook_id, ebook_title, ebook_author, ebook_price, ebook_cover_image, ebook_category 
    FROM ebooks 
    ORDER BY ebook_created_at DESC
");

$fallback = 'assets/img/noimage.png';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Semua E-Book - Lorapz Store</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="global.css">
<style>
.grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem; }
.card-prod { border: 1px solid #ddd; border-radius: 10px; overflow: hidden; transition: 0.2s; }
.card-prod img { width: 100%; height: 180px; object-fit: cover; }
.card-body { padding: 0.5rem 0.75rem; }
.price { font-weight: bold; }
</style>
</head>
<body>

<?php include 'navbar.php'; ?>

<main class="container my-5">

<section class="mb-5">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Semua E-Book</h4>
    <a href="index.php" class="small text-muted">← Kembali</a>
  </div>

  <div class="grid">
    <?php if ($ebooks->num_rows > 0): ?>
      <?php while ($e = $ebooks->fetch_assoc()):
          $img = $e['ebook_cover_image'] && file_exists($e['ebook_cover_image'])
                 ? $e['ebook_cover_image']
                 : $fallback;
      ?>
      <div class="card-prod text-center">
        <a href="product.php?id=<?= $e['ebook_id'] ?>&type=ebook">
          <img src="<?= $img ?>" alt="Cover Ebook" onerror="this.src='<?= $fallback ?>'">
        </a>
        <div class="card-body">
          <h6 class="mb-1"><?= htmlspecialchars($e['ebook_title']) ?></h6>
          <div class="price mb-2">Rp <?= number_format($e['ebook_price'],0,',','.') ?></div>
          <a class="btn btn-sm btn-outline-primary" href="product.php?id=<?= $e['ebook_id'] ?>&type=ebook">Detail</a>
        </div>
      </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div class="card p-4">Belum ada ebook.</div>
    <?php endif; ?>
  </div>
</section>

</main>

<?php include 'footer.php'; ?>
<script src="global.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
