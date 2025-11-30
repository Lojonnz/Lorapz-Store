<?php
session_start();
require 'koneksi.php';

// ===== CEK ADMIN =====
$username = $_SESSION['username'] ?? "Lorapz Store";

// ===== APPROVE / REJECT ORDER =====
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

// ===== AMBIL SEMUA ORDER =====
$orders = $db->query("
    SELECT order_id, user_id, order_total_price, order_payment_status, 
           order_created_at, order_payment_method, order_payment_proof, order_approved
    FROM orders
    ORDER BY order_created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Orders</title>

<style>
    body {
        font-family: Poppins, sans-serif;
        background: #f3f4f6;
        margin: 0;
        padding: 0;
    }

    .container {
        max-width: 980px;
        padding: 20px;
        margin: 40px auto;
        background: white;
        border-radius: 20px;
        box-shadow: 0 5px 25px rgba(0,0,0,0.08);
    }

    h2 {
        margin: 0 0 20px;
        font-size: 26px;
        font-weight: 600;
        color: #333;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
        font-size: 15px;
    }

    table th {
        background: #eef1ff;
        padding: 12px;
        border-radius: 10px;
        text-align: left;
    }

    table td {
        padding: 12px;
        border-bottom: 1px solid #eee;
    }

    .status {
        padding: 6px 10px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        display: inline-block;
    }

    .pending { background: #fff3cd; color: #856404; }
    .paid { background: #d4edda; color: #155724; }
    .failed { background: #f8d7da; color: #721c24; }

    .btn {
        padding: 8px 14px;
        border-radius: 8px;
        font-size: 13px;
        cursor: pointer;
        border: none;
        text-decoration: none;
        font-weight: 600;
        transition: 0.2s;
    }

    .approve { background: #4ade80; color: #065f46; }
    .reject { background: #f87171; color: #7f1d1d; }
    .view { background: #60a5fa; color: white; }

    .btn:hover { opacity: 0.85; }

    .proof-img {
        width: 60px;
        border-radius: 6px;
        cursor: pointer;
        transition: 0.2s;
    }

    .proof-img:hover {
        transform: scale(1.2);
    }

    .empty {
        text-align: center;
        padding: 40px;
        color: #666;
        font-size: 16px;
    }

    /* ===== Modal / Lightbox ===== */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        padding-top: 60px;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0,0,0,0.9);
    }

    .modal-content {
        margin: auto;
        display: block;
        max-width: 90%;
        max-height: 90%;
        transition: transform 0.25s ease;
        cursor: zoom-in;
    }

    .modal-content.zoomed {
        transform: scale(2);
        cursor: zoom-out;
    }

    #caption {
        margin: auto;
        display: block;
        width: 80%;
        text-align: center;
        color: #ccc;
        padding: 10px 0;
    }

    .close {
        position: absolute;
        top: 20px;
        right: 35px;
        color: #fff;
        font-size: 40px;
        font-weight: bold;
        cursor: pointer;
        transition: 0.2s;
    }

    .close:hover {
        color: #bbb;
    }
</style>

</head>
<body>

<div class="container">
    <h2>Admin – Manage Orders</h2>

    <?php if ($orders->num_rows == 0): ?>
        <p class="empty">Belum ada order.</p>
    <?php else: ?>

    <table>
        <tr>
            <th>ID</th>
            <th>User</th>
            <th>Total</th>
            <th>Status</th>
            <th>Metode</th>
            <th>Bukti</th>
            <th>Tanggal</th>
            <th>Aksi</th>
        </tr>

        <?php while ($o = $orders->fetch_assoc()): ?>
        <tr>
            <td>#<?= $o['order_id'] ?></td>
            <td><?= $o['user_id'] ?></td>
            <td>Rp <?= number_format($o['order_total_price'],0,',','.') ?></td>

            <td>
                <span class="status <?= $o['order_payment_status'] ?>">
                    <?= ucfirst($o['order_payment_status']) ?>
                </span>
            </td>

            <td><?= $o['order_payment_method'] ?: '-' ?></td>

            <td>
                <?php if ($o['order_payment_proof']): ?>
                    <img class="proof-img" src="uploads/payment/<?= $o['order_payment_proof'] ?>" alt="Bukti Pembayaran #<?= $o['order_id'] ?>">
                <?php else: ?>
                    -
                <?php endif; ?>
            </td>

            <td><?= $o['order_created_at'] ?></td>

            <td>
                <?php if ($o['order_payment_status'] === "pending"): ?>
                    <a class="btn approve" href="admin_orders.php?action=approve&order_id=<?= $o['order_id'] ?>">Approve</a>
                    <a class="btn reject" href="admin_orders.php?action=reject&order_id=<?= $o['order_id'] ?>">Reject</a>
                <?php else: ?>
                    <span style="color: #555; font-weight: 600;">—</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>

    </table>

    <?php endif; ?>
</div>

<!-- ===== Modal / Lightbox ===== -->
<div id="imgModal" class="modal">
    <span class="close">&times;</span>
    <img class="modal-content" id="modalImg">
    <div id="caption"></div>
</div>

<script>
// Modal & zoom logic
var modal = document.getElementById("imgModal");
var modalImg = document.getElementById("modalImg");
var captionText = document.getElementById("caption");

// Klik semua gambar
var imgs = document.querySelectorAll(".proof-img");
imgs.forEach(function(img){
    img.onclick = function(){
        modal.style.display = "block";
        modalImg.src = this.src;
        captionText.innerHTML = this.alt || "Bukti Pembayaran";
        modalImg.classList.remove("zoomed");
    }
});

// Close modal
var span = document.getElementsByClassName("close")[0];
span.onclick = function() { 
    modal.style.display = "none";
}

// Klik gambar modal → zoom
modalImg.onclick = function() {
    this.classList.toggle("zoomed");
}
</script>

</body>
</html>
