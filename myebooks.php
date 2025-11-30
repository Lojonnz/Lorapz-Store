<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil semua ebook yang sudah dibayar user
$sql = $db->prepare("
    SELECT 
        e.ebook_id,
        e.ebook_title,
        e.ebook_cover_image,
        e.ebook_preview_file,
        e.ebook_full_file,
        e.ebook_price,
        e.ebook_category,
        MAX(o.order_created_at) AS last_purchase
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.order_id
    JOIN ebooks e ON oi.ebook_id = e.ebook_id
    WHERE o.user_id = ?
      AND o.order_payment_status = 'paid'
    GROUP BY e.ebook_id
    ORDER BY last_purchase DESC
");

$sql->bind_param("i", $user_id);
$sql->execute();
$result = $sql->get_result();

$fallback = "assets/img/noimage.png";
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>E-book Saya</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.card-ebook img {
    width: 100%;
    height: 180px;
    object-fit: cover;
}
</style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container my-5">
    <h3 class="mb-4">E-book Saya</h3>

    <?php if ($result->num_rows == 0): ?>
        <div class="alert alert-info">Belum ada e-book yang dibeli.</div>
    <?php endif; ?>

    <div class="row g-4">
        <?php while ($e = $result->fetch_assoc()): 
            $img = ($e['ebook_cover_image'] && file_exists($e['ebook_cover_image']))
                ? $e['ebook_cover_image']
                : $fallback;

            // file preview langsung dari DB
            $preview_file = $e['ebook_preview_file'];
        ?>
        <div class="col-md-4">
            <div class="card card-ebook">
                <img src="<?= $img ?>" alt="Cover" onerror="this.src='<?= $fallback ?>'">

                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($e['ebook_title']) ?></h5>
                    <p class="text-muted small mb-2"><?= htmlspecialchars($e['ebook_category']) ?></p>

                    <div class="d-flex gap-2">

                        <!-- KIRIM FILE PREVIEW KE JAVASCRIPT -->
                        <button 
                            class="btn btn-primary w-50"
                            onclick="openPreview('<?= $preview_file ?>')">
                            Preview
                        </button>

                        <a href="download.php?id=<?= $e['ebook_id'] ?>" 
                           class="btn btn-success w-50">
                           Download
                        </a>

                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<!-- ================= PREVIEW MODAL ================= -->
<div class="modal fade" id="previewModal" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Preview</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body text-center" style="height:80vh; overflow:auto;">
        <div id="previewWrapper" style="position:relative;">
    <div id="previewContainer"></div>

    <!-- WATERMARK -->
    <div id="wm"
         style="
            position:absolute;
            bottom:10px;
            left:10px;
            opacity:0.45;
            font-size:14px;
            color:white;
            padding:4px 10px;
            background:rgba(0,0,0,0.35);
            border-radius:6px;
            pointer-events:none;
        ">
        Lorapz Store • <?= htmlspecialchars($_SESSION['username'] ?? $_SESSION['name'] ?? 'User') ?>
    </div>
</div>

      </div>

    </div>
  </div>
</div>

<script>
// ============================
// PREVIEW DIRECT DARI FILE DB
// ============================
function openPreview(file) {
    const modal = new bootstrap.Modal(document.getElementById("previewModal"));
    const container = document.getElementById("previewContainer");

    let ext = file.split('.').pop().toLowerCase();
    let media = "";

    if (["pdf"].includes(ext)) {
        media = `<iframe src="${file}" style="width:100%;height:100%;border:none;"></iframe>`;
    } 
    else if (["jpg","jpeg","png","gif","webp"].includes(ext)) {
        media = `<img src="${file}" style="max-width:100%;max-height:100%;border-radius:10px;">`;
    }
    else if (["mp4","mov","webm","mkv","avi"].includes(ext)) {
        media = `<video controls style="width:100%;max-height:78vh;border-radius:10px;">
                    <source src="${file}">
                 </video>`;
    }
    else if (["mp3","wav","ogg"].includes(ext)) {
        media = `<audio controls style="width:100%;"><source src="${file}"></audio>`;
    }
    else {
        media = `<p class="text-muted">Preview tidak tersedia untuk format ini.</p>`;
    }

    const wrapper = document.getElementById("previewWrapper");
document.getElementById("previewContainer").innerHTML = media;

// Pastikan watermark berada paling atas
document.getElementById("wm").style.display = "block";

modal.show();

    modal.show();
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
