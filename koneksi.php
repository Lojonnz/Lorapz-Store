<?php
$host = "localhost"; 
$user = "root";    
$pass = "";        
$dbname = "lorapz_store";

$db = new mysqli($host, $user, $pass, $dbname);

if ($db->connect_error) {
    die("Koneksi gagal: " . $db->connect_error);
}

$db->set_charset("utf8mb4");
?>
