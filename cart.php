<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require 'koneksi.php';

// Ambil role user
$user_role = $_SESSION['role'] ?? 'user'; // default 'user'

// Hanya tampilkan sidebar jika admin
if ($user_role === 'admin') {
    include 'sidebar.php';
}

// Pastikan login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$cart = $_SESSION['cart'] ?? [];
$fallback = 'assets/img/noimage.png';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Keranjang | Lorapz Store</title>
<link rel="stylesheet" href="global.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
/* Tabel Keranjang */
.table-cart {
    background: var(--card);
    color: inherit;
    border-radius: var(--radius);
    overflow: hidden;
    box-shadow: 0 6px 20px rgba(16,24,40,0.06);
}
.table-cart th, .table-cart td {
    vertical-align: middle;
    color: inherit;
}
.table-cart img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 6px;
}
.btn-cart {
    font-size: 0.85rem;
    padding: 4px 10px;
    border-radius: 6px;
    margin: 2px;
    text-decoration: none;
    display: inline-block;
}
.btn-edit { background: var(--accent); color: #fff; }
.btn-delete { background: #dc3545; color: #fff; }
.btn-delete:hover { opacity: 0.85; }
.empty-cart {
    text-align: center;
    padding: 40px 0;
    background: var(--card);
    border-radius: var(--radius);
    box-shadow: 0 6px 20px rgba(16,24,40,0.06);
}
</style>
</head>
<body>

<?php include 'navbar.php'; ?>

<main class="container my-5">
    <h2>Keranjang Belanja</h2>

    <?php if (empty($cart)): ?>
        <div class="empty-cart mt-3">
            <p class="mb-3">Keranjang kamu masih kosong.</p>
            <a href="products.php" class="btn btn-primary">Mulai Belanja</a>
        </div>
        <?php exit; ?>
    <?php endif; ?>

    <div class="table-responsive mt-3">
        <table class="table table-cart table-striped align-middle">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Harga</th>
                    <th>Qty</th>
                    <th>Subtotal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $total = 0;
            foreach ($cart as $index => $item):
                $subtotal = $item['price'] * $item['qty'];
                $total += $subtotal;
                $cover = $item['cover'] && file_exists($item['cover']) ? $item['cover'] : $fallback;
            ?>
                <tr>
                    <td>
                        <img src="<?= $cover ?>" alt="Cover" class="me-2">
                        <?= htmlspecialchars($item['title']) ?><br>
                        <small class="text-muted">(<?= $item['type'] ?>)</small>
                    </td>
                    <td>Rp <?= number_format($item['price'],0,',','.') ?></td>
                    <td><?= $item['qty'] ?></td>
                    <td class="fw-bold text-primary">Rp <?= number_format($subtotal,0,',','.') ?></td>
                    <td>
                        <a href="cart_remove.php?index=<?= $index ?>" class="btn btn-danger btn-sm">Hapus</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="text-end mt-4">
        <h4>Total: <span class="text-success">Rp <?= number_format($total,0,',','.') ?></span></h4>
        <a href="checkout.php" class="btn btn-success btn-lg mt-2">Lanjut ke Checkout</a>
    </div>
</main>

<?php include 'footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="global.js"></script>
</body>
</html>
