<?php
session_start();
require_once 'db_connection.php';

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user'] = $user['username'];
    $_SESSION['nombre'] = $user['nombre'];
    $_SESSION['profile_pic'] = $user['profile_pic'];
    header("Location: /smwc/menu");
    exit;
} else {
    header("Location: index.php?error=1");
    exit;
}
?>
