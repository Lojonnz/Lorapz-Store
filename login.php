<?php
session_start();

$username = $_POST['username'];
$password = $_POST['password'];

if ($username === "admin" && $password === "8AcVk=!+E'X,8h") {
    $_SESSION['role'] = "admin";
    header("Location: dashboard.php");
    exit;
} else {
    $_SESSION['role'] = "user";
    header("Location: index.php");
    exit;
}
?>
