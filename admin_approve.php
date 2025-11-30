<?php
require 'koneksi.php';

// Validasi input
if (!isset($_POST['order_id'])) {
    die("Invalid request.");
}

$order_id = intval($_POST['order_id']);

// Ambil user_id dari order
$getUser = $db->query("SELECT user_id FROM orders WHERE order_id = $order_id");
if ($getUser->num_rows == 0) {
    die("Order not found.");
}

$userData = $getUser->fetch_assoc();
$user_id = intval($userData['user_id']);

// 1️⃣ Update status order menjadi paid + approved
$db->query("
    UPDATE orders 
    SET order_approved = 1,
        order_payment_status = 'paid'
    WHERE order_id = $order_id
");

// 2️⃣ Ambil semua item dalam order dari order_items
$items = $db->query("
    SELECT ebook_id, service_id 
    FROM order_items 
    WHERE order_id = $order_id
");

// 3️⃣ Masukkan ebook yang dibeli ke tabel user_ebooks
while ($it = $items->fetch_assoc()) {
    if (!empty($it['ebook_id'])) {

        // Pastikan ebook belum pernah ditambahkan agar tidak duplikat
        $cek = $db->query("
            SELECT * FROM user_ebooks 
            WHERE user_id = $user_id 
            AND ebook_id = {$it['ebook_id']}
            AND order_id = $order_id
        ");

        if ($cek->num_rows == 0) {
            $db->query("
                INSERT INTO user_ebooks (user_id, ebook_id, order_id, is_download_allowed, granted_at)
                VALUES ($user_id, {$it['ebook_id']}, $order_id, 1, NOW())
            ");
        }
    }
}

// 4️⃣ Redirect kembali ke halaman admin
header("Location: admin_orders.php");
exit;
