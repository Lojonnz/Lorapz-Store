<?php
if (session_status() == PHP_SESSION_NONE) session_start();
require 'koneksi.php';

// Ambil role user
$user_role = $_SESSION['role'] ?? 'user';

// Sidebar admin
if ($user_role === 'admin') include 'sidebar.php';

if (!isset($_SESSION['user_id'])) die("Anda harus login untuk mengakses halaman ini.");

$fallback = 'assets/img/noimage.png';

// Ambil parameter
$id = $_GET['id'] ?? null;
$type = $_GET['type'] ?? 'ebook';
if (!$id) die("Produk tidak ditemukan.");

// Default variabel
$title = $author = $category = $price = $cover = $preview = $description = '';

/* ===========================
   AMBIL DATA PRODUK
   =========================== */
if ($type === 'ebook') {
    $stmt = $db->prepare("SELECT * FROM ebooks WHERE ebook_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $ebook = $stmt->get_result()->fetch_assoc();
    if (!$ebook) die("Ebook tidak ditemukan.");

    $title = $ebook['ebook_title'];
    $author = $ebook['ebook_author'];
    $category = $ebook['ebook_category'];
    $price = $ebook['ebook_price'];
    $cover = !empty($ebook['ebook_cover_image']) ? $ebook['ebook_cover_image'] : $fallback;
    $preview = !empty($ebook['ebook_preview_file']) ? $ebook['ebook_preview_file'] : null;
    $description = $ebook['ebook_description'];

} else {
    $stmt = $db->prepare("SELECT * FROM services WHERE service_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $service = $stmt->get_result()->fetch_assoc();
    if (!$service) die("Service tidak ditemukan.");

    $title = $service['service_name'];
    $category = $service['service_category'];
    $price = $service['service_price'];
    $cover = !empty($service['service_thumbnail']) ? $service['service_thumbnail'] : $fallback;
    $description = $service['service_description'];
}

/* ===========================
   TAMBAH KE KERANJANG
   =========================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quantity'])) {
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    $qty = max(1, intval($_POST['quantity']));
    $found = false;

    foreach ($_SESSION['cart'] as &$c) {
        if ($c['id'] == $id && $c['type'] == $type) {
            $c['qty'] += $qty;
            $found = true;
            break;
        }
    }

    if (!$found) {
        $_SESSION['cart'][] = [
            'id' => $id,
            'type' => $type,
            'title' => $title,
            'price' => $price,
            'qty' => $qty,
            'cover' => $cover
        ];
    }

    header("Location: product.php?id=$id&type=$type&added=1");
    exit;
}

/* ===========================
   PREVIEW MEDIA
   =========================== */
function renderPreview($file) {
    if (!$file) return '';
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

    if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
        return "<img src='$file' class='img-fluid rounded mt-3 theme-media'>";
    }
    if ($ext === 'pdf') {
        return "<iframe src='$file' class='w-100 mt-3 theme-media' style='height:500px;border:none;'></iframe>";
    }
    if ($ext === 'txt') {
        $txt = htmlspecialchars(file_get_contents($file));
        return "<pre class='bg-light p-3 rounded mt-3 theme-media' style='max-height:400px; overflow:auto;'>$txt</pre>";
    }
    if (in_array($ext, ['mp4','webm','ogg','mov'])) {
        return "<video controls class='w-100 mt-3 rounded theme-media'><source src='$file'></video>";
    }
    if (in_array($ext, ['mp3','wav','ogg'])) {
        return "<audio controls class='w-100 mt-3 theme-media'><source src='$file'></audio>";
    }
    return "<p class='mt-3 theme-media'>Preview tidak tersedia untuk file ini.</p>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($title) ?> | Lorapz Store</title>
<link rel="stylesheet" href="global.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="theme-body">

<?php include 'navbar.php'; ?>

<div class="container mt-4">

    <?php if(isset($_GET['added'])): ?>
        <div class="alert alert-success theme-alert">Produk berhasil dimasukkan ke keranjang!</div>
    <?php endif; ?>

    <div class="card p-4 mb-4 theme-card">
        <div class="row">
            <div class="col-md-5 text-center">
                <img src="<?= $cover ?>" class="img-fluid rounded theme-media">
            </div>

            <div class="col-md-7">
                <h2 class="theme-text"><?= htmlspecialchars($title) ?></h2>
                <?php if($type == 'ebook'): ?>
                    <p><strong>Author:</strong> <?= htmlspecialchars($author) ?></p>
                <?php endif; ?>
                <p><strong>Kategori:</strong> <?= htmlspecialchars($category) ?></p>
                <p class="fw-bold text-primary fs-4">Rp <?= number_format($price,0,',','.') ?></p>

                <form method="post" class="d-flex gap-2 mt-3">
                    <input type="number" name="quantity" value="1" min="1" class="form-control theme-input" style="width:90px;">
                    <button class="btn btn-primary btn-lg theme-btn">Tambah ke Keranjang</button>
                </form>
            </div>
        </div>

        <?php if($description): ?>
            <hr>
            <h4 class="theme-text">Deskripsi</h4>
            <p class="theme-text"><?= nl2br(htmlspecialchars($description)) ?></p>
        <?php endif; ?>

        <?php if($preview): ?>
            <hr>
            <h4 class="theme-text">Preview</h4>
            <?= renderPreview($preview) ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
<script src="global.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
