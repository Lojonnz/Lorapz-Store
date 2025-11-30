<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Toggle button -->
<button class="btn btn-primary" id="sidebarToggleBtn" style="position:fixed; top:10px; left:10px; z-index:1050;">
    ☰
</button>

<div class="sidebar bg-primary" id="sidebar">
    <a href="index.php" class="<?= $current_page == 'index.php' ? 'bg-white text-primary' : '' ?>">Halaman Utama</a>
    <a href="admin_dashboard.php" class="<?= $current_page == 'admin_dashboard.php' ? 'bg-white text-primary' : '' ?>">Dashboard</a>
    <a href="admin_products.php" class="<?= $current_page == 'admin_products.php' ? 'bg-white text-primary' : '' ?>">Produk</a>
    <a href="admin_addproduct.php" class="<?= $current_page == 'admin_addproduct.php' ? 'bg-white text-primary' : '' ?>">Tambah Produk</a>
    <a href="admin_orders.php" class="<?= $current_page == 'admin_orders.php' ? 'bg-white text-primary' : '' ?>">Orders</a>
    <hr class="text-white">
    <a href="logout.php" class="text-white">Keluar</a>
</div>

<style>
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 220px;
    height: 100vh;
    padding-top: 20px;
    overflow-y: auto;
    transition: transform 0.3s ease;
    z-index: 1040;
}
.sidebar a {
    color: white;
    display: block;
    padding: 12px 20px;
    text-decoration: none;
    border-radius: 8px;
    margin-bottom: 2px;
}
.sidebar a:hover { background: rgba(255,255,255,0.2); }
.sidebar a.bg-white { background: #fff !important; color: #0d6efd !important; }
.sidebar a:first-child {
    margin-top: 40px;
}

/* Collapsed total */
.sidebar-collapsed { transform: translateX(-220px); }

.main { transition: margin-left 0.3s ease; margin-left: 220px; }

@media (max-width: 767px) {
    .sidebar { transform: translateX(-220px); width: 200px; }
    .sidebar.active { transform: translateX(0); }
}
</style>

<script>
const sidebar = document.getElementById('sidebar');
const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
const mainContent = document.querySelector('.main');

// Toggle collapse total
sidebarToggleBtn.addEventListener('click', () => {
    if(window.innerWidth <= 767){
        sidebar.classList.toggle('active'); // mobile
    } else {
        sidebar.classList.toggle('sidebar-collapsed'); // PC
        mainContent.style.marginLeft = sidebar.classList.contains('sidebar-collapsed') ? '0' : '220px';
    }
});

// Reset margin saat resize
window.addEventListener('resize', () => {
    if(window.innerWidth <= 767){
        mainContent.style.marginLeft = '0';
        sidebar.classList.remove('sidebar-collapsed');
        sidebar.classList.remove('active');
    } else {
        mainContent.style.marginLeft = sidebar.classList.contains('sidebar-collapsed') ? '0' : '220px';
    }
});
</script>
