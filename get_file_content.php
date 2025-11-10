<?php
require_once 'db_connection.php';

if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header("HTTP/1.1 403 Forbidden");
    exit;
}

$baseDir = realpath(__DIR__.'/uploads/profiles/files');
$filePath = realpath($baseDir.$_GET['file']);
if (strpos($filePath, $baseDir) !== 0 || !file_exists($filePath)) {
    header("HTTP/1.1 404 Not Found");
    exit;
}
$allowedExtensions = ['txt', 'php', 'html', 'css', 'js', 'json', 'xml', 'md'];
$ext = pathinfo($filePath, PATHINFO_EXTENSION);
if (!in_array($ext, $allowedExtensions)) {
    header("HTTP/1.1 400 Bad Request");
    echo "File type not supported for editing";
    exit;
}

header("Content-Type: text/plain");
readfile($filePath);
?>