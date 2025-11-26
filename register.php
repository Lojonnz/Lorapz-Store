<?php
require 'koneksi.php';
session_start();


$error = "";


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
$name = trim($_POST['name']);
$email = trim($_POST['email']);
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);


// Cek apakah email sudah dipakai
$cek = $db->prepare("SELECT * FROM users WHERE email = ?");
$cek->bind_param("s", $email);
$cek->execute();
$result = $cek->get_result();


if ($result->num_rows > 0) {
$error = "Email sudah terdaftar!";
} else {
$q = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
$q->bind_param("sss", $name, $email, $password);
$q->execute();


header("Location: login.php?success=1");
exit;
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
</head>
<body class="bg-light">


<div class="container d-flex justify-content-center align-items-center" style="height: 100vh;">
<div class="card p-4 shadow" style="width: 380px; border-radius: 12px;">
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


<div class="mb-3">
<label>Password</label>
<input type="password" name="password" class="form-control" required>
</div>


<button class="btn btn-primary w-100">Daftar</button>
</form>


<p class="text-center mt-3">Sudah punya akun? <a href="login.php">Login</a></p>
</div>
</div>
</body>
</html>