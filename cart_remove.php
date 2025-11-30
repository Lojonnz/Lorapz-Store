<?php
session_start();

if (isset($_GET['index'])) {
    $i = intval($_GET['index']);

    if (isset($_SESSION['cart'][$i])) {
        unset($_SESSION['cart'][$i]);
        $_SESSION['cart'] = array_values($_SESSION['cart']);
    }
}

header("Location: cart.php");
exit;
