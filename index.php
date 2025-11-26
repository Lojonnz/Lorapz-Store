<?php
// homepage.php — Modular layout (Option C)
session_start();
require 'koneksi.php';

// Ambil produk: 4 ebook terbaru + 4 service terbaru
$ebooks = $db->query("SELECT ebook_id, title, price, cover_image, category FROM ebooks ORDER BY created_at DESC LIMIT 4");
$services = $db->query("SELECT service_id, name AS title, price, thumbnail AS cover_image FROM services ORDER BY created_at DESC LIMIT 4");

// Deteksi login
$isLoggedIn = isset($_SESSION['username']);
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

// Fallback image dari upload history
$fallback = '/mnt/data/67e14b3f-30f4-4645-96f5-d2833048eb72.png';

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Lorapz Store — Marketplace</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
:root{--accent:#0d6efd; --bg:#f4f6f9; --card:#fff; --muted:#6b7280; --radius:12px}
body{font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;background:var(--bg);color:#111;margin:0}
.navbar{background:#fff;box-shadow:0 4px 18px rgba(13,110,253,0.08)}
.navbar .brand{font-weight:700;color:var(--accent)}
.hero{background:linear-gradient(90deg, rgba(13,110,253,0.95), rgba(6,95,70,0.9));color:#fff;padding:60px 0;text-align:center;border-bottom-left-radius:24px;border-bottom-right-radius:24px}
.section-title{display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem}
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem}
.card-prod{background:var(--card);border-radius:var(--radius);overflow:hidden;box-shadow:0 6px 20px rgba(16,24,40,0.06);transition:transform .18s}
.card-prod:hover{transform:translateY(-6px)}
.card-prod img{width:100%;height:160px;object-fit:cover}
.card-body{padding:12px}
.price{font-weight:600;color:var(--accent)}
.footer{padding:24px;text-align:center;color:var(--muted)}
.btn-outline-accent{border-color:var(--accent);color:var(--accent)}

/* dark mode */
body.dark{background:#071024;color:#e6eef8}
body.dark .card-prod{background:#0f1724;box-shadow:0 6px 18px rgba(2,6,23,0.6)}
body.dark .navbar{background:#06132a}

.theme-toggle{border:0;background:transparent;font-size:1.05rem}
</style>
</head>
<body>

<nav class="navbar py-3">
  <div class="container d-flex justify-content-between align-items-center">
    <div class="brand">Lorapz Store</div>

    <div class="d-flex align-items-center">
        <button id="theme-toggle" class="theme-toggle me-3">🌓</button>

        <?php if ($isLoggedIn): ?>
            <!-- Dropdown Akun -->
            <div class="dropdown me-2">
                <button class="btn btn-sm btn-outline-accent dropdown-toggle" 
                        type="button" 
                        data-bs-toggle="dropdown" 
                        aria-expanded="false">
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

            <!-- Logout -->
            <a class="btn btn-sm btn-outline-secondary" href="logout.php">Logout</a>

        <?php else: ?>
            <!-- Belum Login -->
            <a class="btn btn-sm btn-primary" href="login.php">Login</a>
        <?php endif; ?>
    </div>

  </div>
</nav>


<section class="hero">
  <div class="container">
    <h1 style="font-size:2.2rem;margin-bottom:.4rem">Temukan E-Book & Jasa Digital Berkualitas</h1>
    <p style="opacity:.95;max-width:760px;margin:auto">Kurikulum, template, dan layanan profesional — langsung bisa dibeli dan diunduh. Pilih kategori di bawah untuk mulai menjelajah.</p>
  </div>
</section>

<main class="container my-5">

  <!-- Featured & Quick Links -->
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
                <button class="btn btn-outline-accent" type="submit">Cari</button>
            </div>
        </form>
      </div>
    </div>
  </div>

  <!-- EBOOKS -->
  <section class="mb-5">
    <div class="section-title">
      <h4>E-Book Terbaru</h4>
      <a href="ebooks.php" class="small text-muted">Lihat Semua →</a>
    </div>

    <div class="grid">
      <?php if ($ebooks && $ebooks->num_rows > 0): ?>
        <?php while ($e = $ebooks->fetch_assoc()):
            $img = !empty($e['cover_image']) ? 'uploads/'.htmlspecialchars($e['cover_image']) : $fallback;
            $title = htmlspecialchars($e['title']);
        ?>
        <div class="card-prod">
          <img src="<?= $img ?>" alt="<?= $title ?>" onerror="this.src='<?= $fallback ?>'">
          <div class="card-body">
            <h6 style="margin:0;"><?= $title ?></h6>
            <div class="text-muted small"><?= htmlspecialchars($e['category'] ?? '') ?></div>
            <div class="d-flex justify-content-between align-items-center mt-2">
              <div class="price">Rp <?= number_format($e['price'],0,',','.') ?></div>
              <div>
                <a class="btn btn-sm btn-outline-accent" href="product.php?id=<?= $e['ebook_id'] ?>&type=ebook">Detail</a>
                <a class="btn btn-sm btn-primary" href="buy.php?id=<?= $e['ebook_id'] ?>&type=ebook">Beli</a>
              </div>
            </div>
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
    <div class="section-title">
      <h4>Jasa & Layanan</h4>
      <a href="services.php" class="small text-muted">Lihat Semua →</a>
    </div>

    <div class="grid">
      <?php if ($services && $services->num_rows > 0): ?>
        <?php while ($s = $services->fetch_assoc()):
            $img = !empty($s['cover_image']) ? 'uploads/'.htmlspecialchars($s['cover_image']) : $fallback;
            $title = htmlspecialchars($s['title']);
        ?>
        <div class="card-prod">
          <img src="<?= $img ?>" alt="<?= $title ?>" onerror="this.src='<?= $fallback ?>'">
          <div class="card-body">
            <h6 style="margin:0;"><?= $title ?></h6>
            <div class="text-muted small">Jasa Profesional</div>
            <div class="d-flex justify-content-between align-items-center mt-2">
              <div class="price">Rp <?= number_format($s['price'],0,',','.') ?></div>
              <div>
                <a class="btn btn-sm btn-outline-accent" href="product.php?id=<?= $s['service_id'] ?>&type=service">Detail</a>
                <a class="btn btn-sm btn-primary" href="buy.php?id=<?= $s['service_id'] ?>&type=service">Beli</a>
              </div>
            </div>
          </div>
        </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="card p-4">Belum ada layanan.</div>
      <?php endif; ?>
    </div>
  </section>

</main>

<footer class="footer">
  <div class="container footer">
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

<script>
// Theme toggle
const tbtn = document.getElementById('theme-toggle');
tbtn.addEventListener('click', ()=>{
    document.body.classList.toggle('dark');
    localStorage.setItem('lorapz_theme', document.body.classList.contains('dark') ? 'dark' : 'light');
});
if (localStorage.getItem('lorapz_theme') === 'dark') document.body.classList.add('dark');
</script>

</body>
</html>