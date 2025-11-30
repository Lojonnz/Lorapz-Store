<?php
session_start();
require 'koneksi.php';

// cek admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// fallback image
$fallback = 'assets/img/noimage.png';

// ambil parameter
$id = $_GET['id'] ?? null;
$type = $_GET['type'] ?? 'ebook';
if (!$id) die("Produk tidak ditemukan.");

// ambil data
if ($type === 'ebook') {
    $stmt = $db->prepare("SELECT * FROM ebooks WHERE ebook_id=?");
} else {
    $stmt = $db->prepare("SELECT * FROM services WHERE service_id=?");
}
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
if (!$data) die("Produk tidak ditemukan.");

// fungsi preview (sama seperti product.php)
function renderPreview($file) {
    if (!$file) return '';
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

    if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
        return "<img src='$file' class='img-fluid rounded mt-2'>";
    }
    if ($ext === 'pdf') {
        return "<iframe src='$file' style='width:100%; height:300px;' class='mt-2'></iframe>";
    }
    if ($ext === 'txt') {
        $txt = htmlspecialchars(file_get_contents($file));
        return "<pre class='bg-light p-2 rounded mt-2' style='max-height:200px; overflow:auto;'>$txt</pre>";
    }
    if (in_array($ext, ['mp4','webm','ogg','mov'])) {
        return "<video controls class='w-100 mt-2 rounded'><source src='$file'></video>";
    }
    if (in_array($ext, ['mp3','wav','ogg'])) {
        return "<audio controls class='w-100 mt-2'><source src='$file'></audio>";
    }
    return "<p class='mt-2'>Preview tidak tersedia untuk file ini.</p>";
}

// proses update
$success = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($type === 'ebook') {
        $title       = trim($_POST['title']);
        $author      = trim($_POST['author']);
        $category    = trim($_POST['category']);
        $price       = floatval($_POST['price']);
        $description = trim($_POST['description']);

        // update text info
        $stmt = $db->prepare("UPDATE ebooks SET ebook_title=?, ebook_author=?, ebook_category=?, ebook_price=?, ebook_description=? WHERE ebook_id=?");
        $stmt->bind_param("sssdis", $title, $author, $category, $price, $description, $id);
        $stmt->execute();

        // upload cover
        if (!empty($_FILES['cover']['name'])) {
            $ext = pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION);
            $filename = 'uploads/cover_'.$id.'_'.time().'.'.$ext;
            move_uploaded_file($_FILES['cover']['tmp_name'], $filename);
            $db->query("UPDATE ebooks SET ebook_cover_image='$filename' WHERE ebook_id=$id");
        }

        // upload preview
        if (!empty($_FILES['preview']['name'])) {
            $ext = pathinfo($_FILES['preview']['name'], PATHINFO_EXTENSION);
            $filename = 'uploads/preview_'.$id.'_'.time().'.'.$ext;
            move_uploaded_file($_FILES['preview']['tmp_name'], $filename);
            $db->query("UPDATE ebooks SET ebook_preview_file='$filename' WHERE ebook_id=$id");
        }

        // upload full file
        if (!empty($_FILES['full']['name'])) {
            $ext = pathinfo($_FILES['full']['name'], PATHINFO_EXTENSION);
            $filename = 'uploads/full_'.$id.'_'.time().'.'.$ext;
            move_uploaded_file($_FILES['full']['tmp_name'], $filename);
            $db->query("UPDATE ebooks SET ebook_full_file='$filename' WHERE ebook_id=$id");
        }

    } else {
        // service
        $title       = trim($_POST['title']);
        $category    = trim($_POST['category']);
        $price       = floatval($_POST['price']);
        $description = trim($_POST['description']);

        $stmt = $db->prepare("UPDATE services SET service_name=?, service_category=?, service_price=?, service_description=? WHERE service_id=?");
        $stmt->bind_param("ssdsi", $title, $category, $price, $description, $id);
        $stmt->execute();

        // upload thumbnail
        if (!empty($_FILES['cover']['name'])) {
            $ext = pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION);
            $filename = 'uploads/service_'.$id.'_'.time().'.'.$ext;
            move_uploaded_file($_FILES['cover']['tmp_name'], $filename);
            $db->query("UPDATE services SET service_thumbnail='$filename' WHERE service_id=$id");
        }
    }

    $success = "Produk berhasil diperbarui!";
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Edit Produk | Admin</title>
<link rel="stylesheet" href="global.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container my-5">
    <h3>Edit <?= ucfirst($type) ?></h3>
    <?php if($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label>Judul</label>
            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($data[$type==='ebook'?'ebook_title':'service_name']) ?>" required>
        </div>

        <?php if($type==='ebook'): ?>
            <div class="mb-3">
                <label>Author</label>
                <input type="text" name="author" class="form-control" value="<?= htmlspecialchars($data['ebook_author']) ?>" required>
            </div>
        <?php endif; ?>

        <div class="mb-3">
            <label>Kategori</label>
            <input type="text" name="category" class="form-control" value="<?= htmlspecialchars($data[$type==='ebook'?'ebook_category':'service_category']) ?>">
        </div>

        <div class="mb-3">
            <label>Harga (Rp)</label>
            <input type="number" name="price" class="form-control" value="<?= htmlspecialchars($data[$type==='ebook'?'ebook_price':'service_price']) ?>" step="0.01">
        </div>

        <div class="mb-3">
            <label>Deskripsi</label>
            <textarea name="description" class="form-control"><?= htmlspecialchars($data[$type==='ebook'?'ebook_description':'service_description']) ?></textarea>
        </div>

        <!-- Cover Image -->
        <div class="mb-3">
            <label>Cover Image</label><br>
            <?= renderPreview($data[$type==='ebook'?'ebook_cover_image':'service_thumbnail']) ?>
            <input type="file" name="cover" class="form-control mt-1">
        </div>

        <?php if($type==='ebook'): ?>
            <!-- Preview File -->
            <div class="mb-3">
                <label>Preview File</label><br>
                <?= renderPreview($data['ebook_preview_file']) ?>
                <input type="file" name="preview" class="form-control mt-1">
            </div>

            <!-- Full File -->
            <div class="mb-3">
                <label>Full File</label><br>
                <?= renderPreview($data['ebook_full_file']) ?>
                <input type="file" name="full" class="form-control mt-1">
            </div>
        <?php endif; ?>

        <button class="btn btn-primary">Simpan Perubahan</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
