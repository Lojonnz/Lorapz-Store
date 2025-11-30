<?php
session_start();
require 'admin_check.php'; // pastikan hanya admin
require 'koneksi.php';

// Ambil role user
$user_role = $_SESSION['role'] ?? 'user';

// Sidebar tampil hanya untuk admin
if ($user_role === 'admin') {
    include 'sidebar.php';
}

$msg = "";

// Handle Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $type = $_POST['product_type'];

    if ($type === 'ebook') {

        $title = $_POST['ebook_title'] ?? '';
        $author = $_POST['ebook_author'] ?? '';
        $description = $_POST['ebook_description'] ?? '';
        $price = floatval($_POST['ebook_price'] ?? 0);
        $category = $_POST['ebook_category'] ?? '';

        $cover = null;
        $preview = null;
        $full_file = null;
        $file_size = 0;

        // Upload cover
        if (!empty($_FILES['ebook_cover']['tmp_name'])) {
            $ext = pathinfo($_FILES['ebook_cover']['name'], PATHINFO_EXTENSION);
            if(!is_dir('uploads/ebooks')) mkdir('uploads/ebooks', 0777, true);
            $cover = 'uploads/ebooks/' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['ebook_cover']['tmp_name'], $cover);
        }

        // Upload preview
        if (!empty($_FILES['ebook_preview']['tmp_name'])) {
            $ext = pathinfo($_FILES['ebook_preview']['name'], PATHINFO_EXTENSION);
            if(!is_dir('uploads/ebooks/previews')) mkdir('uploads/ebooks/previews', 0777, true);
            $preview = 'uploads/ebooks/previews/' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['ebook_preview']['tmp_name'], $preview);
        }

        // Upload full file
        if (!empty($_FILES['ebook_full_file']['tmp_name'])) {
            $ext = pathinfo($_FILES['ebook_full_file']['name'], PATHINFO_EXTENSION);
            if(!is_dir('uploads/ebooks/full')) mkdir('uploads/ebooks/full', 0777, true);
            $full_file = 'uploads/ebooks/full/' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['ebook_full_file']['tmp_name'], $full_file);
            $file_size = $_FILES['ebook_full_file']['size'];
        }

        // Insert DB
        $stmt = $db->prepare("INSERT INTO ebooks 
            (ebook_title, ebook_author, ebook_description, ebook_price, ebook_preview_file, ebook_full_file, ebook_cover_image, ebook_category, ebook_file_size, ebook_created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

        $stmt->bind_param("sssdssssi", 
            $title, $author, $description, $price, 
            $preview, $full_file, $cover, $category, $file_size
        );

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

<!-- BOOTSTRAP -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- GLOBAL STYLE -->
<link rel="stylesheet" href="global.css">

<style>
.main {
  margin-left: 220px;
  padding: 30px;
}

</style>
</head>

<body>

<?php include 'navbar.php'; ?>

<div class="main">

  <div class="card p-4">

      <h3 class="mb-3">Tambah Produk</h3>

      <?php if($msg): ?>
        <div class="alert alert-info"><?= $msg ?></div>
      <?php endif; ?>

      <form method="POST" enctype="multipart/form-data">

        <!-- TYPE -->
        <div class="mb-3">
          <label class="form-label">Tipe Produk</label>
          <select class="form-select" name="product_type" id="product_type" onchange="toggleForm()">
            <option value="ebook">Ebook</option>
            <option value="service">Service</option>
          </select>
        </div>

        <!-- EBOOK FORM -->
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
              <span class="input-group-text">Rp</span>
              <input type="number" step="0.01" class="form-control" name="ebook_price">
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

        <!-- SERVICE FORM -->
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
              <span class="input-group-text">Rp</span>
              <input type="number" step="0.01" class="form-control" name="service_price">
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
            <label class="form-label">Estimasi Waktu</label>
            <input type="text" class="form-control" name="service_delivery_time">
          </div>

        </div>

        <button class="btn btn-primary mt-2">Tambah Produk</button>

      </form>

  </div>

</div>

<?php include 'footer.php'; ?>

<script src="global.js"></script>

<script>
function toggleForm(){
    const type = document.getElementById('product_type').value;

    const ebookFields = document.querySelectorAll('#ebook_form input, #ebook_form textarea');
    const serviceFields = document.querySelectorAll('#service_form input, #service_form textarea');

    if(type === 'ebook'){
        ebookFields.forEach(x => x.disabled = false);
        serviceFields.forEach(x => x.disabled = true);
    } else {
        ebookFields.forEach(x => x.disabled = true);
        serviceFields.forEach(x => x.disabled = false);
    }
}

toggleForm(); // run on load
</script>

</body>
</html>
