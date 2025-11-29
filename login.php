<?php
require 'koneksi.php';
session_start();

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // SELECT berdasarkan kolom baru
    $q = $db->prepare("
        SELECT user_id, user_name, user_email, user_password, user_role
        FROM users 
        WHERE user_email = ?
        LIMIT 1
    ");
    $q->bind_param("s", $email);
    $q->execute();
    $result = $q->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // verify password berdasarkan field baru
        if (password_verify($password, $user['user_password'])) {

            // session pakai key baru
            $_SESSION['username'] = $user['user_name'];
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['user_role'];

            // redirect berdasarkan role
            if ($user['user_role'] === 'admin') {
                header("Location: dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit;
        }
    }

    $error = "Email atau password salah!";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - Lorapz Store</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container d-flex justify-content-center align-items-center" style="height: 100vh;">
    <div class="card p-4 shadow" style="width: 380px; border-radius: 12px;">
        <h3 class="text-center mb-3">Login</h3>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">Akun berhasil dibuat. Silakan login.</div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                
                <div class="input-group">
                    <input type="password" name="password" id="passwordField" class="form-control" required>
                    <span class="input-group-text" style="cursor:pointer;" id="togglePassword">
                        <i class="bi bi-eye-slash" id="toggleIcon"></i>
                    </span>
                </div>
            </div>

            <button class="btn btn-primary w-100">Login</button>
        </form>

        <p class="text-center mt-3">
            Belum punya akun? <a href="register.php">Daftar</a>
        </p>
    </div>
</div>

<script>
document.getElementById("togglePassword").addEventListener("click", function () {
    const passField = document.getElementById("passwordField");
    const icon = document.getElementById("toggleIcon");

    if (passField.type === "password") {
        passField.type = "text";
        icon.classList.replace("bi-eye-slash", "bi-eye");
    } else {
        passField.type = "password";
        icon.classList.replace("bi-eye", "bi-eye-slash");
    }
});
</script>

</body>
</html>
