<?php
session_start();
require 'admin_check.php';
require 'koneksi.php';

// Ambil role
$user_role = $_SESSION['role'] ?? 'user';

// Sidebar hanya muncul jika admin
if ($user_role === 'admin') {
    include 'sidebar.php';
}

// Cek admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$fallback = 'assets/img/noimage.png';

// Ambil parameter produk
$id   = $_GET['id']   ?? null;
$type = $_GET['type'] ?? 'ebook';

if (!$id) die("Produk tidak ditemukan.");

// Ambil data produk
if ($type === 'ebook') {
    $stmt = $db->prepare("SELECT * FROM ebooks WHERE ebook_id=?");
} else {
    $stmt = $db->prepare("SELECT * FROM services WHERE service_id=?");
}

$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) die("Produk tidak ditemukan.");

// Fungsi preview universal
function renderPreview($file) {
    if (!$file) return '<p class="text-muted mt-2">Tidak ada file.</p>';

    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

    if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
        return "<img src='$file' class='img-fluid rounded mt-2' style='max-height:200px;object-fit:cover'>";
    }

    if ($ext === 'pdf') {
        return "<iframe src='$file' class='mt-2' style='width:100%;height:300px;border-radius:10px;border:1px solid #ddd;'></iframe>";
    }

    if (in_array($ext, ['mp4','webm','ogg','mov'])) {
        return "<video controls class='w-100 mt-2 rounded'><source src='$file'></video>";
    }

    if (in_array($ext, ['mp3','wav','ogg'])) {
        return "<audio controls class='w-100 mt-2'><source src='$file'></audio>";
    }

    if ($ext === 'txt') {
        $txt = htmlspecialchars(file_get_contents($file));
        return "<pre class='bg-light p-2 rounded mt-2' style='max-height:200px;overflow:auto'>$txt</pre>";
    }

    return "<p class='mt-2'>File tidak dapat dipreview.</p>";
}

// Proses update produk
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($type === 'ebook') {
        $title       = trim($_POST['title']);
        $author      = trim($_POST['author']);
        $category    = trim($_POST['category']);
        $price       = floatval($_POST['price']);
        $description = trim($_POST['description']);

        $stmt = $db->prepare("UPDATE ebooks SET 
            ebook_title=?, ebook_author=?, ebook_category=?, ebook_price=?, ebook_description=?
            WHERE ebook_id=?");

        $stmt->bind_param("sssdis", $title, $author, $category, $price, $description, $id);
        $stmt->execute();

        // Upload cover
        if (!empty($_FILES['cover']['name'])) {
            $ext = pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION);
            $filename = 'uploads/cover_'.$id.'_'.time().'.'.$ext;
            move_uploaded_file($_FILES['cover']['tmp_name'], $filename);

            $db->query("UPDATE ebooks SET ebook_cover_image='$filename' WHERE ebook_id=$id");
        }

        // Upload preview
        if (!empty($_FILES['preview']['name'])) {
            $ext = pathinfo($_FILES['preview']['name'], PATHINFO_EXTENSION);
            $filename = 'uploads/ebooks/previews/preview_'.$id.'_'.time().'.'.$ext;
            move_uploaded_file($_FILES['preview']['tmp_name'], $filename);

            $db->query("UPDATE ebooks SET ebook_preview_file='$filename' WHERE ebook_id=$id");
        }

        // Upload full file
        if (!empty($_FILES['full']['name'])) {
            $ext = pathinfo($_FILES['full']['name'], PATHINFO_EXTENSION);
            $filename = 'uploads/ebooks/full/full_'.$id.'_'.time().'.'.$ext;
            move_uploaded_file($_FILES['full']['tmp_name'], $filename);

            $db->query("UPDATE ebooks SET ebook_full_file='$filename' WHERE ebook_id=$id");
        }

    } else {
        // SERVICE UPDATE
        $title       = trim($_POST['title']);
        $category    = trim($_POST['category']);
        $price       = floatval($_POST['price']);
        $description = trim($_POST['description']);

        $stmt = $db->prepare("UPDATE services SET 
            service_name=?, service_category=?, service_price=?, service_description=?
            WHERE service_id=?");

        $stmt->bind_param("ssdsi", $title, $category, $price, $description, $id);
        $stmt->execute();

        // Upload thumbnail
        if (!empty($_FILES['cover']['name'])) {
            $ext = pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION);
            $filename = 'uploads/services/service_'.$id.'_'.time().'.'.$ext;

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

<style>
.main {
    margin-left: 220px;
    padding: 30px;
}
.card {
    border-radius: 14px;
    background: var(--card);
    box-shadow: 0 6px 18px rgba(0,0,0,0.06);
}
</style>
</head>

<body>

<?php include 'navbar.php'; ?>

<div class="main">
    <div class="card p-4">

        <h3 class="mb-3">Edit <?= ucfirst($type) ?></h3>

        <?php if($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">

            <div class="mb-3">
                <label class="form-label">Judul</label>
                <input type="text" name="title" class="form-control"
                value="<?= htmlspecialchars($data[$type==='ebook'?'ebook_title':'service_name']) ?>" required>
            </div>

            <?php if ($type==='ebook'): ?>
            <div class="mb-3">
                <label class="form-label">Author</label>
                <input type="text" name="author" class="form-control"
                value="<?= htmlspecialchars($data['ebook_author']) ?>">
            </div>
            <?php endif; ?>

            <div class="mb-3">
                <label class="form-label">Kategori</label>
                <input type="text" name="category" class="form-control"
                value="<?= htmlspecialchars($data[$type==='ebook'?'ebook_category':'service_category']) ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Harga</label>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="number" step="0.01" name="price" class="form-control"
                    value="<?= htmlspecialchars($data[$type==='ebook'?'ebook_price':'service_price']) ?>">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" class="form-control" rows="4"><?= 
                    htmlspecialchars($data[$type==='ebook'?'ebook_description':'service_description']) 
                ?></textarea>
            </div>

            <!-- COVER IMAGE -->
            <div class="mb-3">
                <label class="form-label">Cover</label><br>
                <?= renderPreview($data[$type==='ebook'?'ebook_cover_image':'service_thumbnail']) ?>
                <input type="file" name="cover" class="form-control mt-2">
            </div>

            <?php if($type==='ebook'): ?>

            <div class="mb-3">
                <label class="form-label">Preview File</label><br>
                <?= renderPreview($data['ebook_preview_file']) ?>
                <input type="file" name="preview" class="form-control mt-2">
            </div>

            <div class="mb-3">
                <label class="form-label">File Lengkap</label><br>
                <?= renderPreview($data['ebook_full_file']) ?>
                <input type="file" name="full" class="form-control mt-2">
            </div>

            <?php endif; ?>

            <button class="btn btn-primary mt-2">Simpan Perubahan</button>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>

<script src="global.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
