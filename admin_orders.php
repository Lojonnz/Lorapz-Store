<?php
session_start();
require 'admin_check.php';
require 'koneksi.php';

// Pastikan hanya admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$user_role = $_SESSION['role'] ?? 'user';

// Tampilkan sidebar hanya jika admin
if ($user_role === 'admin') {
    include 'sidebar.php';
}

// === Approve / Reject ===
if (isset($_GET['action']) && isset($_GET['order_id'])) {
    $order_id = intval($_GET['order_id']);

    if ($_GET['action'] === "approve") {
        $upd = $db->prepare("UPDATE orders SET order_payment_status='paid', order_approved=1 WHERE order_id=?");
        $upd->bind_param("i", $order_id);
        $upd->execute();
    }

    if ($_GET['action'] === "reject") {
        $upd = $db->prepare("UPDATE orders SET order_payment_status='failed', order_approved=0 WHERE order_id=?");
        $upd->bind_param("i", $order_id);
        $upd->execute();
    }

    header("Location: admin_orders.php?updated=1");
    exit;
}

// Ambil semua order
$orders = $db->query("
    SELECT o.order_id, o.user_id, o.order_total_price, o.order_payment_status,
           o.order_created_at, o.order_payment_method, o.order_payment_proof, o.order_approved,
           u.user_name
    FROM orders o
    JOIN users u ON u.user_id = o.user_id
    ORDER BY o.order_created_at DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin – Kelola Order</title>
<link rel="stylesheet" href="global.css">
<link rel="stylesheet" href="admin.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.admin-content { margin-left: 220px; padding: 30px; }
.table .status.pending { color: #ffc107; font-weight: 600; }
.table .status.paid { color: #198754; font-weight: 600; }
.table .status.failed { color: #dc3545; font-weight: 600; }
.proof-img { width: 60px; height: 60px; object-fit: cover; cursor: pointer; border-radius: 8px; }
.btn-edit, .btn-delete { margin-right: 4px; text-decoration: none; padding: 4px 8px; border-radius: 6px; font-size: .85rem; }
.btn-edit { background-color: #0d6efd; color: #fff; }
.btn-delete { background-color: #dc3545; color: #fff; }
</style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="admin-content">

<h3 class="section-title">Kelola Order</h3>

<?php if ($orders->num_rows == 0): ?>
    <div class="card p-4">Belum ada order.</div>
<?php else: ?>
<div class="table-responsive">
<table class="table table-striped align-middle">
    <thead>
        <tr>
            <th>ID</th>
            <th>Pembeli</th>
            <th>Total</th>
            <th>Status</th>
            <th>Metode</th>
            <th>Bukti</th>
            <th>Tanggal</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($o = $orders->fetch_assoc()): ?>
        <tr>
            <td>#<?= $o['order_id'] ?></td>
            <td><?= htmlspecialchars($o['user_name']) ?></td>
            <td>Rp <?= number_format($o['order_total_price'],0,',','.') ?></td>
            <td><span class="status <?= $o['order_payment_status'] ?>"><?= ucfirst($o['order_payment_status']) ?></span></td>
            <td><?= $o['order_payment_method'] ?: '-' ?></td>
            <td>
                <?php if ($o['order_payment_proof']): ?>
                    <img src="uploads/payment/<?= $o['order_payment_proof'] ?>"
                         class="proof-img"
                         alt="Bukti Pembayaran">
                <?php else: ?>
                    -
                <?php endif; ?>
            </td>
            <td><?= $o['order_created_at'] ?></td>
            <td>
                <?php if ($o['order_payment_status'] === "pending"): ?>
                    <a class="btn-edit" href="admin_orders.php?action=approve&order_id=<?= $o['order_id'] ?>">Approve</a>
                    <a class="btn-delete" href="admin_orders.php?action=reject&order_id=<?= $o['order_id'] ?>">Reject</a>
                <?php else: ?>
                    <span style="font-weight: 600; color: #666;">—</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
</div>
<?php endif; ?>

</div>

<?php include 'footer.php'; ?>

<!-- Lightbox Modal -->
<div id="imgModal" class="modal" style="display:none; position:fixed; z-index:1050; padding-top:60px; left:0; top:0; width:100%; height:100%; overflow:auto; background-color:rgba(0,0,0,0.7);">
    <span class="close" style="position:absolute;top:20px;right:35px;color:#fff;font-size:40px;font-weight:bold;cursor:pointer;">&times;</span>
    <img class="modal-content" id="modalImg" style="margin:auto; display:block; max-width:80%; max-height:80%; border-radius:8px;">
    <div id="caption" style="text-align:center;color:#fff;padding:10px 0;"></div>
</div>

<script src="global.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Lightbox
var modal = document.getElementById("imgModal");
var modalImg = document.getElementById("modalImg");
var captionText = document.getElementById("caption");

document.querySelectorAll(".proof-img").forEach(img => {
    img.onclick = () => {
        modal.style.display = "block";
        modalImg.src = img.src;
        captionText.innerHTML = img.alt || "";
    };
});

document.querySelector(".close").onclick = () => modal.style.display = "none";
</script>

</body>
</html>

