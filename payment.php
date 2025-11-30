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

if (!isset($_GET['order_id'])) die("Order tidak ditemukan.");

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

if (!$order) die("Order tidak valid atau tidak ditemukan.");

// Proses pembayaran
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = $_POST['payment_method'];
    $extra_info = $_POST['payment_info'] ?? null;
    $proofName = null;

    if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === 0) {
        $ext = pathinfo($_FILES['payment_proof']['name'], PATHINFO_EXTENSION);
        $proofName = "proof_" . time() . "_" . rand(1000, 9999) . "." . $ext;
        $folder = "uploads/payment/";
        if (!is_dir($folder)) mkdir($folder, 0777, true);
        move_uploaded_file($_FILES['payment_proof']['tmp_name'], $folder . $proofName);
    }

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
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Payment | Lorapz Store</title>
<link rel="stylesheet" href="global.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.pcontainer {
    max-width: 500px;
    margin: 40px auto;
    padding: 25px;
    border-radius: 16px;
    background: var(--bg-card,#fff);
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}
h2 { margin-bottom: 20px; }
.order-box {
    background: var(--bg-item,#f0f2f5);
    padding: 15px;
    border-radius: 12px;
    margin-bottom: 20px;
}
label { font-weight: 600; margin-top: 10px; display: block; color: var(--text,#333); }
select, input[type=file] { width: 100%; padding: 12px; border-radius: 10px; border: 1px solid #ccc; margin-bottom: 10px; }
.pbutton {
    width: 100%; padding: 14px; font-weight: 600; border-radius: 12px; border: none;
    cursor: pointer; margin-top: 15px; background: var(--btn-primary,#4f46e5); color: #fff;
}
.pbutton:hover { opacity: 0.9; }
.hidden { display: none; }
.info-box { background: var(--bg-item,#eef1ff); padding: 12px; border-radius: 10px; margin-top: 10px; border: 1px solid #cfd3ff; }
#qris-box img { width: 230px; border-radius: 10px; margin: 10px auto; display: block; box-shadow: 0 2px 8px rgba(0,0,0,0.1);}
</style>
<script>
function updatePaymentUI() {
    let method = document.getElementById("method").value;
    let info = document.getElementById("payment_info");

    ['qris-box','dana-box','gopay-box','bank-box'].forEach(id=>document.getElementById(id).style.display='none');

    if(method==='QRIS') document.getElementById("qris-box").style.display='block', info.value='QRIS';
    if(method==='DANA') document.getElementById("dana-box").style.display='block', info.value='DANA';
    if(method==='Gopay') document.getElementById("gopay-box").style.display='block', info.value='Gopay';
    if(method==='Bank Transfer') document.getElementById("bank-box").style.display='block', info.value='Bank Transfer';
}
</script>
</head>
<body class="page">

<?php include 'navbar.php'; ?>

<div class="pcontainer">
    <h2>Payment</h2>

    <div class="order-box">
        <p><strong>Order ID:</strong> #<?= $order['order_id'] ?></p>
        <p class="price"><strong>Total:</strong> Rp <?= number_format($order['order_total_price'],0,',','.') ?></p>
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

        <div id="qris-box" class="hidden">
            <p><strong>Scan QRIS untuk pembayaran:</strong></p>
            <img src="assets/qris.png">
        </div>
        <div id="dana-box" class="hidden info-box">
            <p><b>No Dana: 085847513022 (a/n Lorapz Store)</b></p>
        </div>
        <div id="gopay-box" class="hidden info-box">
            <p><b>BCA: 085847513022 (a/n Lorapz Store)</b></p>
        </div>
        <div id="bank-box" class="hidden info-box">
            <p><strong>Transfer ke Rekening:</strong></p>
            <p><b>BCA: 123456789 (a/n Lorapz Store)</b></p>
        </div>

        <input type="hidden" id="payment_info" name="payment_info">
        <label>Upload Bukti Pembayaran:</label>
        <input type="file" name="payment_proof" required>

        <button type="submit" class="pbutton">Kirim Pembayaran</button>
    </form>
</div>

<?php include 'footer.php'; ?>
<script src="global.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
