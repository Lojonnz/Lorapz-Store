<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}
?>

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
        <h4 class="text-center mb-4">Lorapz Store</h4>
        <a href="#">Dashboard</a>
        <a href="#">Produk</a>
        <a href="#">Transaksi</a>
        <a href="#">Pelanggan</a>
        <a href="#">Laporan</a>
        <a href="#">Pengaturan</a>
        <hr>
        <a href="#">Keluar</a>
    </div>

    <!-- Main Content -->
    <div class="main">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Selamat Datang, <?= htmlspecialchars($username) ?> 👋</h2>
            <span class="text-muted"><?= date('d F Y') ?></span>
        </div>

        <!-- Row Statistik -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card p-3">
                    <h6 class="text-muted">Total Pengguna</h6>
                    <h3>
                        <?php
                        require 'koneksi.php';
                        $q = $db->query("SELECT COUNT(*) AS total FROM users");
                        echo $q->fetch_assoc()['total'];
                        ?>
                    </h3>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card p-3">
                    <h6 class="text-muted">Total E-Book</h6>
                    <h3>
                        <?php
                        $q = $db->query("SELECT COUNT(*) AS total FROM ebooks");
                        echo $q->fetch_assoc()['total'];
                        ?>
                    </h3>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card p-3">
                    <h6 class="text-muted">Total Pesanan</h6>
                    <h3>
                        <?php
                        $q = $db->query("SELECT COUNT(*) AS total FROM orders");
                        echo $q->fetch_assoc()['total'];
                        ?>
                    </h3>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card p-3">
                    <h6 class="text-muted">Total Pendapatan</h6>
                    <h3 class="text-success">
                        Rp 
                        <?php
                        $q = $db->query("SELECT SUM(total_price) AS revenue FROM orders WHERE payment_status='paid'");
                        echo number_format($q->fetch_assoc()['revenue'] ?? 0, 0, ',', '.');
                        ?>
                    </h3>
                </div>
            </div>
        </div>

        <!-- Grafik Penjualan -->
        <div class="card mb-4">
            <div class="card-body">
                <h5>Grafik Penjualan Bulanan</h5>
                <div class="chart-container">
                    <canvas id="salesChart"></canvas>
                </div>

                <?php
                    $penjualan = $db->query("
                        SELECT 
                            DATE_FORMAT(created_at, '%Y-%m') AS bulan,
                            SUM(total_price) AS total
                        FROM orders
                        WHERE payment_status='paid'
                        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                        ORDER BY bulan
                    ");

                    $labels = [];
                    $values = [];

                while ($row = $penjualan->fetch_assoc()) {
                    $labels[] = $row['bulan'];
                    $values[] = $row['total'];
                }
                ?>


                <script>
                    const ctx = document.getElementById('salesChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: <?= json_encode($labels) ?>,
                            datasets: [{
                                label: 'Total Pendapatan',
                                data: <?= json_encode($values) ?>,
                                borderWidth: 3,
                                tension: 0.3
                            }]
                        }
                    });
                </script>

            </div>
        </div>

        <!-- Tabel Transaksi Terbaru -->
        <div class="card">
            <div class="card-body">
                <h5 class="mb-3">Transaksi Terbaru</h5>

                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Pembeli</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php
                        $q = $db->query("
                            SELECT o.*, u.name 
                            FROM orders o
                            JOIN users u ON u.user_id = o.user_id
                            ORDER BY o.created_at DESC
                            LIMIT 10
                        ");

                        while ($r = $q->fetch_assoc()) {
                            echo "
                                <tr>
                                    <td>{$r['order_id']}</td>
                                    <td>{$r['name']}</td>
                                    <td>Rp " . number_format($r['total_price'], 0, ',', '.') . "</td>
                                    <td>{$r['payment_status']}</td>
                                    <td>{$r['created_at']}</td>
                                </tr>
                            ";
                        }
                        ?>

                    </tbody>
                </table>

            </div>
        </div>

    </div> <!-- end main -->
</body>
</html>
