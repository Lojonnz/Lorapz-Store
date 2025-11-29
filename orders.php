<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    die("Anda harus login.");
}

$user_id = $_SESSION['user_id'];

/* ===========================
   QUERY
   =========================== */

// Keranjang
$cart = $db->query("
    SELECT * FROM orders 
    WHERE user_id = $user_id 
    AND order_payment_status = 'pending'
    AND order_approved = 0
    ORDER BY order_created_at DESC
");

// Menunggu approval admin
$waiting = $db->query("
    SELECT * FROM orders 
    WHERE user_id = $user_id 
    AND order_payment_status = 'pending'
    AND order_approved = 1
    ORDER BY order_created_at DESC
");

// Riwayat selesai
$done = $db->query("
    SELECT * FROM orders 
    WHERE user_id = $user_id 
    AND order_payment_status = 'paid'
    ORDER BY order_created_at DESC
");

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Pesanan Saya | Lorapz Store</title>
<link rel="stylesheet" href="global.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container mt-4">

    <h2 class="mb-4">Pesanan Saya</h2>

    <!-- ==========================
         KERANJANG
    =========================== -->
    <h4>Keranjang Saya</h4>
    <div class="card p-3 mb-4">
        <?php if ($cart->num_rows === 0): ?>
            <p class="text-muted">Keranjang kosong.</p>
        <?php else: ?>
            <?php while ($row = $cart->fetch_assoc()): ?>
                <div class="border-bottom py-2 d-flex justify-content-between">
                    <div>
                        <?= ucfirst($row['product_type']) ?> #<?= $row['product_id'] ?>  
                        <br><small class="text-muted">Rp <?= number_format($row['order_total_price'],0,',','.') ?></small>
                    </div>
                    <a href="checkout.php?order=<?= $row['order_id'] ?>" class="btn btn-sm btn-success">Checkout</a>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>

    <!-- ==========================
         MENUNGGU APPROVAL ADMIN
    =========================== -->
    <h4>Menunggu Persetujuan Admin</h4>
    <div class="card p-3 mb-4">
        <?php if ($waiting->num_rows === 0): ?>
            <p class="text-muted">Tidak ada pesanan yang menunggu persetujuan.</p>
        <?php else: ?>
            <?php while ($row = $waiting->fetch_assoc()): ?>
                <div class="border-bottom py-2">
                    <?= ucfirst($row['product_type']) ?> #<?= $row['product_id'] ?>  
                    — <strong>Rp <?= number_format($row['order_total_price'],0,',','.') ?></strong>
                    <br>
                    <span class="badge bg-warning text-dark">Menunggu Admin</span>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>

    <!-- ==========================
         PESANAN SELESAI
    =========================== -->
    <h4>Pesanan Selesai</h4>
    <div class="card p-3 mb-4">
        <?php if ($done->num_rows === 0): ?>
            <p class="text-muted">Belum ada pesanan selesai.</p>
        <?php else: ?>
            <?php while ($row = $done->fetch_assoc()): ?>
                <div class="border-bottom py-2">
                    <?= ucfirst($row['product_type']) ?> #<?= $row['product_id'] ?>
                    — <strong>Rp <?= number_format($row['order_total_price'],0,',','.') ?></strong>
                    <br>
                    <span class="badge bg-success">Selesai</span>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>

</div>

</body>
</html>
