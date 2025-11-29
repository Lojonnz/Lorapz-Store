<?php
require 'koneksi.php';
session_start();

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password_raw = $_POST['password'];
    $confirm_raw = $_POST['confirm_password'];

    // Cek password sama
    if ($password_raw !== $confirm_raw) {
        $error = "Password tidak sama!";
    } else {
        // Hash password
        $password = password_hash($password_raw, PASSWORD_DEFAULT);

        // Cek email terdaftar (gunakan kolom baru user_email)
        $cek = $db->prepare("SELECT user_id FROM users WHERE user_email = ?");
        $cek->bind_param("s", $email);
        $cek->execute();
        $result = $cek->get_result();

        if ($result->num_rows > 0) {
            $error = "Email sudah terdaftar!";
        } else {

            // Insert user (kolom baru: user_name, user_email, user_password, user_role)
            $q = $db->prepare("
                INSERT INTO users (user_name, user_email, user_password, user_role, user_created_at)
                VALUES (?, ?, ?, 'user', NOW())
            ");
            $q->bind_param("sss", $name, $email, $password);
            $q->execute();

            header("Location: login.php?success=1");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register - Lorapz Store</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

<style>
.password-strength {
    height: 6px;
    border-radius: 4px;
}
</style>

</head>
<body class="bg-light">

<div class="container d-flex justify-content-center align-items-center" style="height: 100vh;">
    <div class="card p-4 shadow" style="width: 400px; border-radius: 12px;">
        <h3 class="text-center mb-3">Daftar Akun</h3>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">

            <div class="mb-3">
                <label>Nama Lengkap</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <!-- PASSWORD -->
            <div class="mb-2">
                <label>Password</label>
                <div class="input-group">
                    <input type="password" name="password" id="password" class="form-control" required>
                    <span class="input-group-text" id="togglePassword" style="cursor: pointer;">
                        <i class="bi bi-eye-slash" id="iconPass"></i>
                    </span>
                </div>

                <!-- Strength bar -->
                <div class="mt-2 password-strength bg-danger" id="strengthBar"></div>

                <small id="strengthText" class="text-muted"></small>
            </div>

            <!-- CONFIRM PASSWORD -->
            <div class="mb-3">
                <label>Konfirmasi Password</label>
                <div class="input-group">
                    <input type="password" name="confirm_password" id="confirmPassword" class="form-control" required>
                    <span class="input-group-text" id="toggleConfirm" style="cursor: pointer;">
                        <i class="bi bi-eye-slash" id="iconConfirm"></i>
                    </span>
                </div>
                <small id="confirmText" class="text-danger"></small>
            </div>

            <button id="btnSubmit" class="btn btn-primary w-100">Daftar</button>
        </form>

        <p class="text-center mt-3">
            Sudah punya akun? <a href="login.php">Login</a>
        </p>
    </div>
</div>

<script>
// ==== SHOW / HIDE PASSWORD ====

document.getElementById("togglePassword").onclick = function () {
    const pwd = document.getElementById("password");
    const icon = document.getElementById("iconPass");
    pwd.type = pwd.type === "password" ? "text" : "password";
    icon.classList.toggle("bi-eye");
    icon.classList.toggle("bi-eye-slash");
};

document.getElementById("toggleConfirm").onclick = function () {
    const pwd = document.getElementById("confirmPassword");
    const icon = document.getElementById("iconConfirm");
    pwd.type = pwd.type === "password" ? "text" : "password";
    icon.classList.toggle("bi-eye");
    icon.classList.toggle("bi-eye-slash");
};


// ==== PASSWORD STRENGTH CHECK ====

document.getElementById("password").addEventListener("input", function () {
    const pwd = this.value;
    const bar = document.getElementById("strengthBar");
    const text = document.getElementById("strengthText");

    let score = 0;

    if (pwd.length >= 8) score++;
    if (/[A-Z]/.test(pwd)) score++;
    if (/[a-z]/.test(pwd)) score++;
    if (/[0-9]/.test(pwd)) score++;

    if (score === 0) {
        bar.style.width = "0%";
        text.innerHTML = "";
    } else if (score === 1) {
        bar.style.width = "25%";
        bar.className = "password-strength bg-danger";
        text.innerHTML = "Sangat Lemah";
    } else if (score === 2) {
        bar.style.width = "50%";
        bar.className = "password-strength bg-warning";
        text.innerHTML = "Lemah";
    } else if (score === 3) {
        bar.style.width = "75%";
        bar.className = "password-strength bg-primary";
        text.innerHTML = "Cukup Kuat";
    } else if (score === 4) {
        bar.style.width = "100%";
        bar.className = "password-strength bg-success";
        text.innerHTML = "Sangat Kuat";
    }
});

// ==== CONFIRM PASSWORD REAL-TIME ====

document.getElementById("confirmPassword").addEventListener("input", function () {
    const pwd = document.getElementById("password").value;
    const confirm = this.value;
    const text = document.getElementById("confirmText");

    if (confirm === "") {
        text.innerHTML = "";
    } else if (confirm !== pwd) {
        text.innerHTML = "Password tidak cocok!";
    } else {
        text.innerHTML = "";
    }
});
</script>

</body>
</html>
