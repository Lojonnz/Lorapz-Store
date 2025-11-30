<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    die("Anda harus login untuk mengakses halaman ini.");
}

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

    // Tidak pakai file_exists — langsung cek value
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
   TOMBOL TAMBAH KERANJANG
   =========================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quantity'])) {

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $qty = max(1, intval($_POST['quantity']));

    // Cek apakah item sudah ada di keranjang
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
            'id'    => $id,
            'type'  => $type,
            'title' => $title,
            'price' => $price,
            'qty'   => $qty,
            'cover' => $cover
        ];
    }

    header("Location: product.php?id=$id&type=$type&added=1");
    exit;
}


/* ===========================
   PREVIEW FILE
   =========================== */
function renderPreview($file) {
    if (!$file) return '';

    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

    if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
        return "<img src='$file' class='img-fluid rounded mt-3'>";
    }

    if ($ext === 'pdf') {
        return "<iframe src='$file' style='width:100%; height:500px;' class='mt-3'></iframe>";
    }

    if ($ext === 'txt') {
        $txt = htmlspecialchars(file_get_contents($file));
        return "<pre class='bg-light p-3 rounded mt-3' style='max-height:400px; overflow:auto;'>$txt</pre>";
    }

    if (in_array($ext, ['mp4','webm','ogg','mov'])) {
        return "<video controls class='w-100 mt-3 rounded'><source src='$file'></video>";
    }

    if (in_array($ext, ['mp3','wav','ogg'])) {
        return "<audio controls class='w-100 mt-3'><source src='$file'></audio>";
    }

    return "<p class='mt-3'>Preview tidak tersedia untuk file ini.</p>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($title) ?> | Lorapz Store</title>
<link rel="stylesheet" href="global.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container mt-4">

    <?php if(isset($_GET['added'])): ?>
        <div class="alert alert-success">Produk berhasil dimasukkan ke keranjang!</div>
    <?php endif; ?>

    <div class="card p-4 mb-4">
        <div class="row">
            <div class="col-md-5 text-center">
                <img src="<?= $cover ?>" class="img-fluid rounded">
            </div>

            <div class="col-md-7">
                <h2><?= htmlspecialchars($title) ?></h2>

                <?php if($type == 'ebook'): ?>
                    <p><strong>Author:</strong> <?= htmlspecialchars($author) ?></p>
                <?php endif; ?>

                <p><strong>Kategori:</strong> <?= htmlspecialchars($category) ?></p>
                <p class="fw-bold text-primary fs-4">Rp <?= number_format($price,0,',','.') ?></p>

                <form method="post" class="d-flex gap-2 mt-3">
                    <input type="number" name="quantity" value="1" min="1" class="form-control" style="width:90px;">
                    <button class="btn btn-primary btn-lg">Tambah ke Keranjang</button>
                </form>
            </div>
        </div>

        <?php if($description): ?>
            <hr>
            <h4>Deskripsi</h4>
            <p><?= nl2br(htmlspecialchars($description)) ?></p>
        <?php endif; ?>

        <?php if($preview): ?>
            <hr>
            <h4>Preview</h4>
            <?= renderPreview($preview) ?>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
