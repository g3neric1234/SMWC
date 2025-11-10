<?php
if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'admin') {
    header("Location: ../smwc/errors/error.php?code=403");
    exit;
}
$pdo = new PDO("mysql:host=localhost;dbname=smwc", "root", "");
$pass = password_hash("1234", PASSWORD_DEFAULT);
$pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)")
    ->execute(["admin", $pass]);
echo "Usuario creado.";
?>
