<?php
session_start();
require 'koneksi.php';

// Cek apakah cart kosong
if (!isset($_SESSION['cart']) || count($_SESSION['cart']) === 0) {
    header("Location: cart.php?error=empty");
    exit;
}

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$cart = $_SESSION['cart'];

// Hitung total harga
$total_price = 0;
foreach ($cart as $item) {
    $total_price += $item['price'] * $item['qty'];
}

/* ===========================================
   1) INSERT KE TABLE ORDERS
=========================================== */

// Tanggal sekarang (format SQL)
$created_at = date("Y-m-d H:i:s");

$stmt = $db->prepare("
    INSERT INTO orders (user_id, order_total_price, order_payment_status, order_created_at)
    VALUES (?, ?, 'pending', ?)
");

$stmt->bind_param("ids", $user_id, $total_price, $created_at);
$stmt->execute();

$order_id = $stmt->insert_id;
$stmt->close();


/* ===========================================
   2) INSERT ITEM KE TABLE order_items
=========================================== */

$stmtItem = $db->prepare("
    INSERT INTO order_items (order_id, ebook_id, service_id, item_quantity, item_price)
    VALUES (?, ?, ?, ?, ?)
");

foreach ($cart as $item) {

    // Ebook -> ebook_id terisi, service_id = NULL
    $ebook_id = ($item['type'] === 'ebook') ? $item['id'] : null;

    // Service -> service_id terisi, ebook_id = NULL
    $service_id = ($item['type'] === 'service') ? $item['id'] : null;

    $qty   = $item['qty'];
    $price = $item['price'];

    // Pastikan NULL bisa masuk
    $stmtItem->bind_param(
        "iiiid",
        $order_id,
        $ebook_id,
        $service_id,
        $qty,
        $price
    );

    $stmtItem->execute();
}

$stmtItem->close();

// Hapus cart setelah berhasil checkout
unset($_SESSION['cart']);

// Redirect ke halaman orders
header("Location: orders.php?success=1");
exit;

?>
