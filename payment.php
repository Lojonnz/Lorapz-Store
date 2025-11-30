<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['order_id'])) {
    die("Order tidak ditemukan.");
}

$order_id = intval($_GET['order_id']);
$user_id = $_SESSION['user_id'];

// Ambil order
$q = $db->prepare("
    SELECT order_id, order_total_price, order_payment_status, order_created_at
    FROM orders
    WHERE order_id = ? AND user_id = ?
");
$q->bind_param("ii", $order_id, $user_id);
$q->execute();
$order = $q->get_result()->fetch_assoc();

if (!$order) {
    die("Order tidak valid atau tidak ditemukan.");
}

// Proses pembayaran
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $payment_method = $_POST['payment_method'];
    $extra_info = $_POST['payment_info'] ?? null;

    // Upload file bukti pembayaran
    $proofName = null;

    if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === 0) {

        $ext = pathinfo($_FILES['payment_proof']['name'], PATHINFO_EXTENSION);
        $proofName = "proof_" . time() . "_" . rand(1000, 9999) . "." . $ext;

        $folder = "uploads/payment/";

        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

        move_uploaded_file($_FILES['payment_proof']['tmp_name'], $folder . $proofName);
    }

    // Update database
    $update = $db->prepare("
        UPDATE orders 
        SET order_payment_status = 'pending',
            order_payment_method = ?,
            order_payment_proof = ?
        WHERE order_id = ?
    ");
    $update->bind_param("ssi", $payment_method, $proofName, $order_id);
    $update->execute();

    header("Location: orders.php?paid=1");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Payment</title>

<style>
    body {
        font-family: Poppins, sans-serif;
        background: #f5f7fa;
        margin: 0;
        padding: 0;
    }

    .container {
        max-width: 500px;
        margin: 40px auto;
        background: white;
        padding: 25px;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }

    h2 {
        margin-bottom: 10px;
        font-size: 24px;
        color: #333;
    }

    .order-box {
        background: #f0f2f5;
        padding: 15px;
        border-radius: 12px;
        margin-bottom: 20px;
    }

    label {
        display: block;
        margin: 10px 0 6px;
        font-weight: 600;
        color: #444;
    }

    select, input[type=file], input[type=text] {
        width: 100%;
        padding: 12px;
        border-radius: 10px;
        border: 1px solid #ccc;
        font-size: 15px;
    }

    button {
        width: 100%;
        padding: 14px;
        background: #4f46e5;
        border: none;
        color: white;
        font-size: 16px;
        font-weight: 600;
        border-radius: 12px;
        cursor: pointer;
        margin-top: 15px;
        transition: 0.2s;
    }

    button:hover {
        background: #4338ca;
    }

    .hidden { display: none; }

    #qris-box img {
        width: 230px;
        border-radius: 10px;
        margin: 10px auto;
        display: block;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .info-box {
        background: #eef1ff;
        padding: 12px;
        border-radius: 10px;
        margin-top: 10px;
        border: 1px solid #cfd3ff;
    }
</style>

<script>
function updatePaymentUI() {
    let method = document.getElementById("method").value;
    let info = document.getElementById("payment_info");

    // Semua area disembunyikan dulu
    document.getElementById("qris-box").style.display = "none";
    document.getElementById("dana-box").style.display = "none";
    document.getElementById("gopay-box").style.display = "none";
    document.getElementById("bank-box").style.display = "none";

    if (method === "QRIS") {
        document.getElementById("qris-box").style.display = "block";
        info.value = "QRIS";
    }
    if (method === "DANA") {
        document.getElementById("dana-box").style.display = "block";
        info.value = "DANA";
    }
    if (method === "Gopay") {
        document.getElementById("gopay-box").style.display = "block";
        info.value = "Gopay";
    }
    if (method === "Bank Transfer") {
        document.getElementById("bank-box").style.display = "block";
        info.value = "Bank Transfer";
    }
}
</script>

</head>

<body>

<div class="container">
    <h2>Payment</h2>

    <div class="order-box">
        <p><strong>Order ID:</strong> #<?= $order['order_id'] ?></p>
        <p class="price">Total: Rp <?= number_format($order['order_total_price'], 0, ',', '.') ?></p>
        <p><strong>Tanggal:</strong> <?= $order['order_created_at'] ?></p>
    </div>

    <form method="POST" enctype="multipart/form-data">

        <label>Pilih Metode Pembayaran:</label>
        <select name="payment_method" id="method" onchange="updatePaymentUI()" required>
            <option value="" disabled selected>-- Pilih --</option>
            <option value="QRIS">QRIS</option>
            <option value="DANA">DANA</option>
            <option value="Gopay">GoPay</option>
            <option value="Bank Transfer">Bank Transfer</option>
        </select>

        <!-- QRIS -->
        <div id="qris-box" class="hidden">
            <p><strong>Scan QRIS untuk pembayaran:</strong></p>
            <img src="assets/qris.png">
        </div>

        <!-- DANA -->
        <div id="dana-box" class="hidden info-box">
            <p><b>No Dana: 085847513022 (a/n Lorapz Store)</b></p>
        </div>

        <!-- GoPay -->
        <div id="gopay-box" class="hidden info-box">
            <p><b>BCA: 085847513022 (a/n Lorapz Store)</b></p>
        </div>

        <!-- Bank -->
        <div id="bank-box" class="hidden info-box">
            <p><strong>Transfer ke Rekening:</strong></p>
            <p><b>BCA: 123456789 (a/n Lorapz Store)</b></p>
        </div>

        <input type="hidden" id="payment_info" name="payment_info">

        <label>Upload Bukti Pembayaran:</label>
        <input type="file" name="payment_proof" required>

        <button type="submit">Kirim Pembayaran</button>
    </form>
</div>

</body>
</html>
