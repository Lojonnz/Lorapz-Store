<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$cart = $_SESSION['cart'] ?? [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Keranjang | Lorapz Store</title>
<link rel="stylesheet" href="global.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container mt-4">
    <h2>Keranjang Belanja</h2>

    <?php if (empty($cart)): ?>
        <div class="alert alert-info mt-3">Keranjang kamu masih kosong.</div>
        <a href="products.php" class="btn btn-primary mt-2">Mulai Belanja</a>
        <?php exit; ?>
    <?php endif; ?>

    <table class="table table-striped mt-3">
        <thead>
            <tr>
                <th>Produk</th>
                <th>Harga</th>
                <th>Qty</th>
                <th>Subtotal</th>
                <th></th>
            </tr>
        </thead>
        <tbody>

        <?php
        $total = 0;
        foreach ($cart as $index => $item):
            $subtotal = $item['price'] * $item['qty'];
            $total += $subtotal;
        ?>
            <tr>
                <td>
                    <img src="<?= $item['cover'] ?>" width="60" class="rounded me-2">
                    <?= htmlspecialchars($item['title']) ?>
                    <br><small class="text-muted">(<?= $item['type'] ?>)</small>
                </td>

                <td>Rp <?= number_format($item['price'], 0, ',', '.') ?></td>

                <td><?= $item['qty'] ?></td>

                <td class="fw-bold text-primary">
                    Rp <?= number_format($subtotal, 0, ',', '.') ?>
                </td>

                <td>
                    <a href="cart_remove.php?index=<?= $index ?>" class="btn btn-danger btn-sm">
                        Hapus
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>

        </tbody>
    </table>

    <div class="text-end">
        <h4>Total: <span class="text-success">Rp <?= number_format($total,0,',','.') ?></span></h4>
        <a href="checkout.php" class="btn btn-success btn-lg mt-3">Lanjut ke Checkout</a>
    </div>
</div>

</body>
</html>
