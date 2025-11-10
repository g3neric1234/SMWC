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

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="'.basename($filePath).'"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filePath));
readfile($filePath);
exit;
?>