<?php
session_start();
require 'koneksi.php';

// Ambil role user
$user_role = $_SESSION['role'] ?? 'user';
if ($user_role === 'admin') include 'sidebar.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];

// Ambil semua order user
$qOrders = $db->prepare("
    SELECT order_id, order_total_price, order_payment_status, order_created_at
    FROM orders
    WHERE user_id = ?
    ORDER BY order_id DESC
");
$qOrders->bind_param("i", $user_id);
$qOrders->execute();
$orders = $qOrders->get_result();

// Ambil semua order_id untuk query items
$orderIds = [];
while ($o = $orders->fetch_assoc()) {
    $orderIds[] = $o['order_id'];
}
$orders->data_seek(0); // reset pointer

// Ambil semua item order sekaligus
$itemsByOrder = [];
if ($orderIds) {
    $in = implode(',', $orderIds);
    $qItems = $db->query("
        SELECT order_id, item_quantity, item_price, ebook_id, service_id
        FROM order_items
        WHERE order_id IN ($in)
    ");
    while ($item = $qItems->fetch_assoc()) {
        $itemsByOrder[$item['order_id']][] = $item;
    }
}

// Ambil semua judul ebook
$ebookTitles = [];
$qEbooks = $db->query("SELECT ebook_id, ebook_title FROM ebooks");
while ($row = $qEbooks->fetch_assoc()) {
    $ebookTitles[$row['ebook_id']] = $row['ebook_title'];
}

// Ambil semua judul service
$serviceTitles = [];
$qServices = $db->query("SELECT service_id, service_name FROM services");
while ($row = $qServices->fetch_assoc()) {
    $serviceTitles[$row['service_id']] = $row['service_name'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Your Orders | Lorapz Store</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="global.css">
<style>
body { font-family: 'Inter', sans-serif; }
.order-card { border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); transition: .2s; }
.order-card:hover { transform: translateY(-3px); box-shadow: 0 6px 24px rgba(0,0,0,0.1); }
.item-box { background: var(--bg-item,#f9fafc); border-radius: 12px; padding: 12px 16px; margin-bottom: 10px; }
</style>
</head>
<body class="page">

<?php include 'navbar.php'; ?>

<div class="container py-4">
    <h2 class="fw-bold mb-4">Your Orders</h2>

    <?php if ($orders->num_rows == 0): ?>
        <div class="alert alert-info p-3">Kamu belum memiliki order.</div>
    <?php else: ?>
        <?php while ($order = $orders->fetch_assoc()): ?>
            <div class="card order-card mb-4 p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-1">Order #<?= $order['order_id']; ?></h5>
                        <p class="text-muted mb-1"><?= date("d M Y - H:i", strtotime($order['order_created_at'])); ?></p>
                    </div>
                    <span class="badge 
                        <?= $order['order_payment_status']=='paid'?'bg-success':($order['order_payment_status']=='failed'?'bg-danger':'bg-warning text-dark'); ?>
                        px-3 py-2">
                        <?= ucfirst($order['order_payment_status']); ?>
                    </span>
                </div>
                <hr>
                <div class="mt-2">
                    <h6 class="fw-bold mb-2">Items</h6>
                    <?php foreach ($itemsByOrder[$order['order_id']] ?? [] as $item): 
                        $title = $item['ebook_id'] 
                            ? $ebookTitles[$item['ebook_id']] ?? 'Unknown Ebook'
                            : $serviceTitles[$item['service_id']] ?? 'Unknown Service';
                    ?>
                        <div class="item-box">
                            <div class="d-flex justify-content-between">
                                <span class="fw-semibold"><?= htmlspecialchars($title) ?></span>
                                <span>Rp <?= number_format($item['item_price'],0,',','.'); ?></span>
                            </div>
                            <small class="text-muted">Qty: <?= $item['item_quantity']; ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                    <h5 class="fw-bold mb-0">Total: Rp <?= number_format($order['order_total_price'],0,',','.'); ?></h5>
                    <?php if ($order['order_payment_status']=="pending"): ?>
                        <a href="payment.php?order_id=<?= $order['order_id']; ?>" class="btn btn-primary">Bayar Sekarang</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
<script src="global.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
