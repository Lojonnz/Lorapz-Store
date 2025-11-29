<?php
session_start();
require 'koneksi.php';

// Ambil 4 ebook terbaru
$ebooks = $db->query("
    SELECT ebook_id, ebook_title, ebook_price, ebook_cover_image, ebook_category 
    FROM ebooks 
    ORDER BY ebook_created_at DESC 
    LIMIT 4
");

// Ambil 4 service terbaru
$services = $db->query("
    SELECT service_id, service_name, service_price, service_thumbnail, service_category 
    FROM services 
    ORDER BY service_created_at DESC 
    LIMIT 4
");

// fallback image
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
<title>Lorapz Store — Marketplace</title>
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

<section class="hero py-5 bg-light text-center">
  <div class="container">
    <h1 style="font-size:2.2rem;margin-bottom:.4rem">Temukan E-Book & Jasa Digital Berkualitas</h1>
    <p style="opacity:.95;max-width:760px;margin:auto">Kurikulum, template, dan layanan profesional yang langsung bisa dibeli dan diunduh.</p>
  </div>
</section>

<main class="container my-5">

  <!-- Highlight + search -->
  <div class="row mb-4">
    <div class="col-md-8">
      <div class="card p-3">
        <h5 class="mb-0">Highlight</h5>
        <p class="text-muted small mb-0">Produk unggulan & penawaran khusus.</p>
      </div>
    </div>
    <div class="col-md-4">
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
  </div>

  <!-- EBOOKS -->
<section class="mb-5">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4>E-Book Terbaru</h4>
    <a href="ebooks.php" class="small text-muted">Lihat Semua →</a>
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

<!-- SERVICES -->
<section class="mb-5">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Jasa & Layanan</h4>
    <a href="services.php" class="small text-muted">Lihat Semua →</a>
  </div>

  <div class="grid">
    <?php if ($services->num_rows > 0): ?>
      <?php while ($s = $services->fetch_assoc()):
          $img = $s['service_thumbnail'] && file_exists($s['service_thumbnail'])
                 ? $s['service_thumbnail']
                 : $fallback;
      ?>
      <div class="card-prod text-center">
        <a href="product.php?id=<?= $s['service_id'] ?>&type=service">
          <img src="<?= $img ?>" alt="Thumbnail Service" onerror="this.src='<?= $fallback ?>'">
        </a>
        <div class="card-body">
          <h6 class="mb-1"><?= htmlspecialchars($s['service_name']) ?></h6>
          <div class="price mb-2">Rp <?= number_format($s['service_price'],0,',','.') ?></div>
          <a class="btn btn-sm btn-outline-primary" href="product.php?id=<?= $s['service_id'] ?>&type=service">Detail</a>
        </div>
      </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div class="card p-4">Belum ada layanan.</div>
    <?php endif; ?>
  </div>
</section>


</main>

<footer class="footer py-3 bg-light">
  <div class="container">
    <div class="row">
      <div class="col-md-6 text-start">
        <strong>Lorapz Store</strong><br>Marketplace E-Book & Jasa Digital
      </div>
      <div class="col-md-6 text-end text-muted">
        &copy; <?= date('Y') ?> Lorapz Store
      </div>
    </div>
  </div>
</footer>

<script src="global.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
