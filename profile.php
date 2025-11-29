<?php
session_start();
require 'koneksi.php';

// harus login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data user (kolom baru)
$stmt = $db->prepare("SELECT user_name, user_email FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Update profil
$success = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $user_name  = trim($_POST['user_name']);
    $user_email = trim($_POST['user_email']);

    $stmt = $db->prepare("UPDATE users 
                          SET user_name = ?, user_email = ?
                          WHERE user_id = ?");
    $stmt->bind_param("ssi", $user_name, $user_email, $user_id);
    $stmt->execute();

    $success = "Profil berhasil diperbarui!";
    $_SESSION['username'] = $user_name;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Profil Saya</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
:root{--accent:#0d6efd;--bg:#f4f6f9;--card:#fff;--radius:12px}
body{background:var(--bg)}
.card{border-radius:var(--radius);box-shadow:0 6px 20px rgba(0,0,0,0.08)}
</style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container my-5">
    <h3>Profil Saya</h3>

    <?php if ($success): ?>
    <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <div class="card p-4">
        <form method="POST">

            <div class="mb-3">
                <label class="form-label">Nama Lengkap</label>
                <input class="form-control" 
                       name="user_name" 
                       value="<?= htmlspecialchars($user['user_name']) ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input class="form-control" 
                       name="user_email" 
                       value="<?= htmlspecialchars($user['user_email']) ?>">
            </div>

            <button class="btn btn-primary">Simpan Perubahan</button>
        </form>
    </div>
</div>

</body>
</html>
