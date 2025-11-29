<?php
session_start();
require 'admin_check.php'; // pastikan hanya admin
require 'koneksi.php';

$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['product_type'];

    if ($type === 'ebook') {
        $title = $_POST['ebook_title'] ?? '';
        $author = $_POST['ebook_author'] ?? '';
        $description = $_POST['ebook_description'] ?? '';
        $price = floatval($_POST['ebook_price'] ?? 0);
        $category = $_POST['ebook_category'] ?? '';

        // Upload file
        $cover = null;
        $preview = null;
        $full_file = null;
        $file_size = 0;

        if (!empty($_FILES['ebook_cover']['tmp_name'])) {
            $ext = pathinfo($_FILES['ebook_cover']['name'], PATHINFO_EXTENSION);
            if(!is_dir('uploads/ebooks')) mkdir('uploads/ebooks', 0777, true);
            $cover = 'uploads/ebooks/' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['ebook_cover']['tmp_name'], $cover);
        }

        if (!empty($_FILES['ebook_preview']['tmp_name'])) {
            $ext = pathinfo($_FILES['ebook_preview']['name'], PATHINFO_EXTENSION);
            if(!is_dir('uploads/ebooks/previews')) mkdir('uploads/ebooks/previews', 0777, true);
            $preview = 'uploads/ebooks/previews/' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['ebook_preview']['tmp_name'], $preview);
        }

        if (!empty($_FILES['ebook_full_file']['tmp_name'])) {
            $ext = pathinfo($_FILES['ebook_full_file']['name'], PATHINFO_EXTENSION);
            if(!is_dir('uploads/ebooks/full')) mkdir('uploads/ebooks/full', 0777, true);
            $full_file = 'uploads/ebooks/full/' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['ebook_full_file']['tmp_name'], $full_file);
            $file_size = $_FILES['ebook_full_file']['size'];
        }

        // Insert ke database
        $stmt = $db->prepare("INSERT INTO ebooks 
            (ebook_title, ebook_author, ebook_description, ebook_price, ebook_preview_file, ebook_full_file, ebook_cover_image, ebook_category, ebook_file_size, ebook_created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssdssssi", $title, $author, $description, $price, $preview, $full_file, $cover, $category, $file_size);
        $msg = $stmt->execute() ? "Ebook berhasil ditambahkan!" : "Gagal menambahkan ebook: " . $stmt->error;

    } elseif ($type === 'service') {
        $name = $_POST['service_name'] ?? '';
        $description = $_POST['service_description'] ?? '';
        $price = floatval($_POST['service_price'] ?? 0);
        $category = $_POST['service_category'] ?? '';
        $delivery_time = $_POST['service_delivery_time'] ?? '';

        $thumbnail = null;
        if (!empty($_FILES['service_thumbnail']['tmp_name'])) {
            $ext = pathinfo($_FILES['service_thumbnail']['name'], PATHINFO_EXTENSION);
            if(!is_dir('uploads/services')) mkdir('uploads/services', 0777, true);
            $thumbnail = 'uploads/services/' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['service_thumbnail']['tmp_name'], $thumbnail);
        }

        $stmt = $db->prepare("INSERT INTO services 
            (service_name, service_description, service_price, service_thumbnail, service_category, service_delivery_time, service_created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssdsss", $name, $description, $price, $thumbnail, $category, $delivery_time);
        $msg = $stmt->execute() ? "Service berhasil ditambahkan!" : "Gagal menambahkan service: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tambah Produk | Lorapz Store</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
.main { margin-left: 220px; padding: 30px; }
.card { border: none; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
input:disabled, textarea:disabled { background-color: #e9ecef; }
</style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main">
<div class="card p-4">
    <h3>Tambah Produk</h3>
    <?php if($msg) echo "<div class='alert alert-info'>$msg</div>"; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="product_type" class="form-label">Tipe Produk:</label>
            <select class="form-select" id="product_type" name="product_type" onchange="toggleForm()">
                <option value="ebook">Ebook</option>
                <option value="service">Service</option>
            </select>
        </div>

        <!-- Ebook Form -->
        <div id="ebook_form">
            <div class="mb-3">
                <label class="form-label">Judul Ebook</label>
                <input type="text" class="form-control" name="ebook_title">
            </div>
            <div class="mb-3">
                <label class="form-label">Author</label>
                <input type="text" class="form-control" name="ebook_author">
            </div>
            <div class="mb-3">
                <label class="form-label">Deskripsi</label>
                <textarea class="form-control" name="ebook_description"></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Harga</label>
                <div class="input-group">
                    <span class="input-group-text">Rp.</span>
                    <input type="number" class="form-control" name="ebook_price" step="0.01">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Kategori</label>
                <input type="text" class="form-control" name="ebook_category">
            </div>
            <div class="mb-3">
                <label class="form-label">Cover</label>
                <input type="file" class="form-control" name="ebook_cover">
            </div>
            <div class="mb-3">
                <label class="form-label">File Preview</label>
                <input type="file" class="form-control" name="ebook_preview">
            </div>
            <div class="mb-3">
                <label class="form-label">File Lengkap</label>
                <input type="file" class="form-control" name="ebook_full_file">
            </div>
        </div>

        <!-- Service Form -->
        <div id="service_form">
            <div class="mb-3">
                <label class="form-label">Nama Service</label>
                <input type="text" class="form-control" name="service_name">
            </div>
            <div class="mb-3">
                <label class="form-label">Deskripsi</label>
                <textarea class="form-control" name="service_description"></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Harga</label>
                <div class="input-group">
                    <span class="input-group-text">Rp.</span>
                    <input type="number" class="form-control" name="service_price" step="0.01">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Kategori</label>
                <input type="text" class="form-control" name="service_category">
            </div>
            <div class="mb-3">
                <label class="form-label">Thumbnail</label>
                <input type="file" class="form-control" name="service_thumbnail">
            </div>
            <div class="mb-3">
                <label class="form-label">Estimasi Waktu Penyelesaian</label>
                <input type="text" class="form-control" name="service_delivery_time">
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Tambah Produk</button>
    </form>
</div>
</div>

<script>
function toggleForm() {
    const type = document.getElementById('product_type').value;
    const ebookInputs = document.querySelectorAll('#ebook_form input, #ebook_form textarea');
    const serviceInputs = document.querySelectorAll('#service_form input, #service_form textarea');
    if(type === 'ebook') {
        ebookInputs.forEach(i=>i.disabled=false);
        serviceInputs.forEach(i=>i.disabled=true);
    } else {
        ebookInputs.forEach(i=>i.disabled=true);
        serviceInputs.forEach(i=>i.disabled=false);
    }
}
toggleForm();
</script>

</body>
</html>
