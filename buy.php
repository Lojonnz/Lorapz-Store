<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$fallback = 'assets/img/noimage.png';

// Ambil input
$product_ids = $_POST['product_id'] ?? [];
$product_types = $_POST['product_type'] ?? [];
$quantities = $_POST['quantity'] ?? [];
$payment_method = $_POST['payment_method'] ?? 'Transfer Bank';

if (empty($product_ids)) {
    die("Tidak ada produk yang dipilih.");
}

// Validasi jumlah
$total_products = count($product_ids);
if ($total_products !== count($product_types) || $total_products !== count($quantities)) {
    die("Input produk tidak valid.");
}

// Ambil detail produk dari DB
$cart = [];
$total_price = 0;

for ($i = 0; $i < $total_products; $i++) {
    $id = (int)$product_ids[$i];
    $type = $product_types[$i];
    $qty = max(1, (int)$quantities[$i]);

    if ($type === 'ebook') {
        $stmt = $db->prepare("SELECT * FROM ebooks WHERE ebook_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $product = $res->fetch_assoc();
    } else { // service
        $stmt = $db->prepare("SELECT * FROM services WHERE service_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $product = $res->fetch_assoc();
    }

    if (!$product) continue;

    $price = $type === 'ebook' ? $product['ebook_price'] : $product['service_price'];
    $total_price += $price * $qty;

    $cart[] = [
        'id' => $id,
        'type' => $type,
        'title' => $type === 'ebook' ? $product['ebook_title'] : $product['service_name'],
        'category' => $type === 'ebook' ? $product['ebook_category'] : $product['service_category'],
        'price' => $price,
        'quantity' => $qty,
        'cover' => $type === 'ebook' ? $product['ebook_cover_image'] : $product['service_thumbnail']
    ];
}

// Simpan order ke DB
$order_stmt = $db->prepare("INSERT INTO orders
(user_id, order_total_price, order_payment_status, order_type, order_created_at, product_type, product_id, order_payment_method, order_approved)
VALUES (?, ?, 'pending', ?, NOW(), ?, ?, ?, 0)");

foreach ($cart as $item) {
    $order_type = $item['type'];
    $product_type = $item['type'];
    $product_id = $item['id'];
    $order_stmt->bind_param("idssiss", $user_id, $item['price'] * $item['quantity'], $order_type, $product_type, $product_id, $payment_method);
    $order_stmt->execute();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Konfirmasi Pembelian — Lorapz Store</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="global.css">
<style>
.grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:1rem; }
.card-prod { background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 6px 20px rgba(16,24,40,0.06); transition:.18s; }
.card-prod:hover { transform:translateY(-6px); }
.card-prod img { width:100%; height:160px; object-fit:cover; }
.price { font-weight:600; color:#0d6efd; }
body.dark { background:#071024; color:#e6eef8; }
body.dark .card-prod { background:#0f1724; box-shadow:0 6px 18px rgba(2,6,23,0.6); }
body.dark .navbar { background:#06132a; }
.theme-toggle { border:0; background:transparent; font-size:1.05rem; }
</style>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container my-5">
    <h3 class="mb-4">Terima kasih! Pesananmu telah dicatat.</h3>
    <p class="text-muted">Silakan selesaikan pembayaran melalui <strong><?= htmlspecialchars($payment_method) ?></strong>. Admin akan memverifikasi sebelum pesanan di-approve.</p>

    <div class="grid">
    <?php foreach($cart as $item): ?>
        <?php $img = $item['cover'] && file_exists($item['cover']) ? $item['cover'] : $fallback; ?>
        <div class="card-prod">
            <img src="<?= $img ?>" onerror="this.src='<?= $fallback ?>'">
            <div class="p-3">
                <h6><?= htmlspecialchars($item['title']) ?></h6>
                <div class="text-muted small"><?= htmlspecialchars($item['category']) ?></div>
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <div class="price">Rp <?= number_format($item['price'],0,',','.') ?> x <?= $item['quantity'] ?></div>
                    <div>Total: Rp <?= number_format($item['price']*$item['quantity'],0,',','.') ?></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    </div>

    <div class="mt-4">
        <h5>Total Pembayaran: Rp <?= number_format($total_price,0,',','.') ?></h5>
    </div>
</div>

<script src="global.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const tbtn = document.getElementById('theme-toggle');
tbtn.addEventListener('click', ()=>{
    document.body.classList.toggle('dark');
    localStorage.setItem('lorapz_theme', document.body.classList.contains('dark') ? 'dark' : 'light');
});
if (localStorage.getItem('lorapz_theme') === 'dark') document.body.classList.add('dark');
</script>
</body>
</html>
