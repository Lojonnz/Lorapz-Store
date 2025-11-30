<?php
session_start();
require 'koneksi.php';

// Ambil role user
$user_role = $_SESSION['role'] ?? 'user'; 

// Sidebar admin
if ($user_role === 'admin') {
    include 'sidebar.php';
}

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
<link rel="stylesheet" href="global.css">
<style>
.card-ebook {
    border-radius: 12px;
    overflow: hidden;
    transition: transform .2s;
}
.card-ebook:hover { transform: translateY(-4px); }
.card-ebook img {
    width: 100%;
    height: 180px;
    object-fit: cover;
    border-bottom: 1px solid #ddd;
}
body.dark .card-ebook {
    background-color: #1e1e2f;
    color: #eee;
    border: 1px solid #333;
}
#previewWrapper { position: relative; width: 100%; text-align:center; }
#previewContainer iframe,
#previewContainer img,
#previewContainer video,
#previewContainer audio {
    max-width: 100%;
    max-height: 78vh;
    object-fit: contain;
    border-radius: 10px;
}
#wm {
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
    z-index:10;
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
            $preview_file = $e['ebook_preview_file'];
        ?>
        <div class="col-md-4">
            <div class="card card-ebook">
                <img src="<?= $img ?>" alt="Cover" onerror="this.src='<?= $fallback ?>'">
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($e['ebook_title']) ?></h5>
                    <p class="text-muted small mb-2"><?= htmlspecialchars($e['ebook_category']) ?></p>
                    <div class="d-flex gap-2">
                        <button 
                            class="btn btn-primary w-50"
                            onclick="openPreview('<?= $preview_file ?>')">
                            Preview
                        </button>
                        <a href="download.php?id=<?= $e['ebook_id'] ?>" 
                           class="btn btn-success w-50" target="_blank">
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
      <div class="modal-body" style="height:80vh; overflow:auto;">
        <div id="previewWrapper">
            <div id="previewContainer"></div>
            <div id="wm">
                Lorapz Store • <?= htmlspecialchars($_SESSION['username'] ?? $_SESSION['name'] ?? 'User') ?>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>
<script src="global.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ============================
// PREVIEW FILE FULL & RESPONSIVE
// ============================
function openPreview(file) {
    const modalEl = document.getElementById("previewModal");
    const modal = new bootstrap.Modal(modalEl);
    const container = document.getElementById("previewContainer");
    const wm = document.getElementById("wm");

    if (!file) {
        container.innerHTML = '<p class="text-muted">Preview tidak tersedia.</p>';
        wm.style.display = 'block';
        modal.show();
        return;
    }

    let ext = file.split('.').pop().toLowerCase();
    let media = "";

    if (["pdf"].includes(ext)) {
        media = `<iframe src="${file}" style="width:100%;height:78vh;border:none;"></iframe>`;
    } else if (["jpg","jpeg","png","gif","webp"].includes(ext)) {
        media = `<img src="${file}" style="max-width:100%;max-height:78vh;object-fit:contain;">`;
    } else if (["mp4","mov","webm","mkv","avi"].includes(ext)) {
        media = `<video controls style="width:100%;max-height:78vh;border-radius:10px;">
                    <source src="${file}">
                 </video>`;
    } else if (["mp3","wav","ogg"].includes(ext)) {
        media = `<audio controls style="width:100%;"><source src="${file}"></audio>`;
    } else {
        media = `<p class="text-muted">Preview tidak tersedia untuk format ini.</p>`;
    }

    container.innerHTML = media;
    wm.style.display = "block";
    modal.show();
}
</script>
</body>
</html>
