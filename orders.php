<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil semua order sesuai user
$qOrders = $db->prepare("
    SELECT order_id, order_total_price, order_payment_status, order_created_at
    FROM orders
    WHERE user_id = ?
    ORDER BY order_id DESC
");
$qOrders->bind_param("i", $user_id);
$qOrders->execute();
$orders = $qOrders->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Your Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f5f7fa;
            font-family: 'Inter', sans-serif;
        }
        .order-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            transition: .2s;
        }
        .order-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 24px rgba(0,0,0,0.1);
        }
        .item-box {
            background: #f9fafc;
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container py-4">

    <h2 class="fw-bold mb-4">Your Orders</h2>

    <?php if ($orders->num_rows == 0): ?>
        <div class="alert alert-info p-3">
            Kamu belum memiliki order.
        </div>
    <?php else: ?>

        <?php while ($order = $orders->fetch_assoc()): ?>

            <div class="card order-card mb-4 p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-1">Order #<?= $order['order_id']; ?></h5>
                        <p class="text-muted mb-1">
                            <?= date("d M Y - H:i", strtotime($order['order_created_at'])); ?>
                        </p>
                    </div>

                    <!-- Badge Status -->
                    <span class="badge 
                        <?= $order['order_payment_status'] == 'paid' ? 'bg-success' : 
                        ($order['order_payment_status'] == 'failed' ? 'bg-danger' : 'bg-warning text-dark'); ?>
                        px-3 py-2">
                        <?= ucfirst($order['order_payment_status']); ?>
                    </span>
                </div>

                <hr>

                <div class="mt-2">
                    <h6 class="fw-bold mb-2">Items</h6>

                    <?php
                    $qItems = $db->prepare("
                        SELECT item_quantity, item_price, ebook_id, service_id
                        FROM order_items
                        WHERE order_id = ?
                    ");
                    $qItems->bind_param("i", $order['order_id']);
                    $qItems->execute();
                    $items = $qItems->get_result();
                    ?>

                    <?php while ($item = $items->fetch_assoc()): ?>

                        <?php
                        // Ambil nama produk berdasarkan tipe
                        if ($item['ebook_id']) {
                            $qTitle = $db->prepare("SELECT ebook_title AS title FROM ebooks WHERE ebook_id=?");
                            $qTitle->bind_param("i", $item['ebook_id']);
                        } else {
                            $qTitle = $db->prepare("SELECT service_name AS title FROM services WHERE service_id=?");
                            $qTitle->bind_param("i", $item['service_id']);
                        }
                        $qTitle->execute();
                        $title = $qTitle->get_result()->fetch_assoc()['title'];
                        ?>

                        <div class="item-box">
                            <div class="d-flex justify-content-between">
                                <span class="fw-semibold"><?= $title; ?></span>
                                <span>Rp <?= number_format($item['item_price'],0,',','.'); ?></span>
                            </div>
                            <small class="text-muted">Qty: <?= $item['item_quantity']; ?></small>
                        </div>

                    <?php endwhile; ?>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                    <h5 class="fw-bold mb-0">
                        Total: Rp <?= number_format($order['order_total_price'], 0, ',', '.'); ?>
                    </h5>

                    <?php if ($order['order_payment_status'] == "pending"): ?>
                        <a href="payment.php?order_id=<?= $order['order_id']; ?>" class="btn btn-primary">
                            Bayar Sekarang
                        </a>
                    <?php endif; ?>
                </div>
            </div>

        <?php endwhile; ?>

    <?php endif; ?>

</div>

</body>
</html>
