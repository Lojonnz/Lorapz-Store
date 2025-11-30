<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['user_id'])) exit("Unauthorized");

$user_id = $_SESSION['user_id'];
$id = intval($_GET['id']);

// Cek apakah user membeli ebook ini
$q = $db->prepare("
    SELECT e.ebook_full_file 
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.order_id
    JOIN ebooks e ON oi.ebook_id = e.ebook_id
    WHERE o.user_id = ?
    AND o.order_payment_status = 'paid'
    AND e.ebook_id = ?
    LIMIT 1
");
$q->bind_param("ii", $user_id, $id);
$q->execute();
$res = $q->get_result()->fetch_assoc();

if (!$res) exit("No access.");
$file = $res['ebook_full_file'];

if (!file_exists($file)) exit("File not found.");

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"" . basename($file) . "\"");
readfile($file);
exit;
