<?php
session_start();
if (!isset($_SESSION['username'])) {
    $_SESSION['username'] = "Lorapz Store";
}
$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Lorapz Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .sidebar {
            background-color: #0d6efd;
            height: 100vh;
            color: #fff;
            position: fixed;
            width: 220px;
            padding-top: 20px;
        }
        .sidebar a {
            color: white;
            display: block;
            padding: 12px 20px;
            text-decoration: none;
            border-radius: 8px;
        }
        .sidebar a:hover {
            background: rgba(255,255,255,0.2);
        }
        .main {
            margin-left: 220px;
            padding: 30px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        .chart-container {
            position: relative;
            height: 300px;
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <h4 class="text-center mb-4">🛒 Lorapz Store</h4>
        <a href="#">🏠 Dashboard</a>
        <a href="#">📦 Produk</a>
        <a href="#">🧾 Transaksi</a>
        <a href="#">👥 Pelanggan</a>
        <a href="#">📊 Laporan</a>
        <a href="#">⚙️ Pengaturan</a>
        <hr>
        <a href="#">🚪 Keluar</a>
    </div>

    <!-- Main Content -->
    <div class="main">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Selamat Datang, <?= htmlspecialchars($username) ?> 👋</h2>
            <span class="text-muted"><?= date('d F Y') ?></spa
