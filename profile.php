<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data user
$stmt = $db->prepare("SELECT user_name, user_email, user_password FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $user_name  = trim($_POST['user_name']);
    $user_email = trim($_POST['user_email']);

    $stmt = $db->prepare("UPDATE users SET user_name = ?, user_email = ? WHERE user_id = ?");
    $stmt->bind_param("ssi", $user_name, $user_email, $user_id);
    $stmt->execute();
    $_SESSION['username'] = $user_name;
    $success = "Profil berhasil diperbarui!";

    // Ganti password
    if (isset($_POST['current_password'], $_POST['new_password'], $_POST['confirm_password'])) {
        $current = $_POST['current_password'];
        $new     = $_POST['new_password'];
        $confirm = $_POST['confirm_password'];

        if (!password_verify($current, $user['user_password'])) {
            $error = "Password lama salah!";
        } elseif ($new !== $confirm) {
            $error = "Password baru dan konfirmasi tidak cocok!";
        } else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET user_password = ? WHERE user_id = ?");
            $stmt->bind_param("si", $hash, $user_id);
            $stmt->execute();
            $success .= " Password berhasil diperbarui!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Profil Saya</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
body {background:#f4f6f9;}
.card {border-radius:12px; box-shadow:0 6px 20px rgba(0,0,0,0.08);}
.change-password-text {color:#0d6efd; cursor:pointer; display:inline-block; margin-top:5px;}
.change-password-text:hover {text-decoration:underline;}
</style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container my-5">
    <h3>Profil Saya</h3>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <div class="card p-4">
        <form method="POST">
            <!-- Nama -->
            <div class="mb-3">
                <label class="form-label">Nama Lengkap</label>
                <input class="form-control" name="user_name" value="<?= htmlspecialchars($user['user_name']) ?>">
            </div>

            <!-- Email -->
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input class="form-control" name="user_email" value="<?= htmlspecialchars($user['user_email']) ?>">
            </div>

            <!-- Password (readonly) -->
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" class="form-control" value="********" readonly>
                <span class="change-password-text" data-bs-toggle="modal" data-bs-target="#passwordModal">
                    Change Password
                </span>
            </div>

            <button class="btn btn-primary">Simpan Perubahan</button>
        </form>
    </div>
</div>

<!-- Modal Ganti Password -->
<div class="modal fade" id="passwordModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h5 class="modal-title">Ganti Password</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="user_name" value="<?= htmlspecialchars($user['user_name']) ?>">
          <input type="hidden" name="user_email" value="<?= htmlspecialchars($user['user_email']) ?>">

          <div class="mb-3">
            <label class="form-label">Password Lama</label>
            <div class="input-group">
                <input type="password" class="form-control" name="current_password" id="currentPassword" required>
                <span class="input-group-text toggle-password" data-target="currentPassword" style="cursor:pointer">
                    <i class="bi bi-eye-slash" id="icon-currentPassword"></i>
                </span>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Password Baru</label>
            <div class="input-group">
                <input type="password" class="form-control" name="new_password" id="newPassword" required>
                <span class="input-group-text toggle-password" data-target="newPassword" style="cursor:pointer">
                    <i class="bi bi-eye-slash" id="icon-newPassword"></i>
                </span>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Konfirmasi Password Baru</label>
            <div class="input-group">
                <input type="password" class="form-control" name="confirm_password" id="confirmPassword" required>
                <span class="input-group-text toggle-password" data-target="confirmPassword" style="cursor:pointer">
                    <i class="bi bi-eye-slash" id="icon-confirmPassword"></i>
                </span>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Ganti Password</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.toggle-password').forEach(span=>{
    span.addEventListener('click',()=>{
        const targetId = span.getAttribute('data-target');
        const input = document.getElementById(targetId);
        const icon = document.getElementById('icon-'+targetId);
        if(input.type==='password'){
            input.type='text';
            icon.classList.replace('bi-eye-slash','bi-eye');
        } else {
            input.type='password';
            icon.classList.replace('bi-eye','bi-eye-slash');
        }
    });
});
</script>
</body>
</html>
