<?php
require_once 'db_connection.php';

if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header("HTTP/1.1 403 Forbidden");
    exit;
}
function formatFileSize($bytes) {
    if ($bytes === 0) return '0 Bytes';
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes) / log($k));
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}

$baseDir = realpath(__DIR__.'/uploads/profiles/files');
$filePath = realpath($baseDir.$_GET['file']);
if (strpos($filePath, $baseDir) !== 0 || !file_exists($filePath)) {
    header("HTTP/1.1 404 Not Found");
    exit;
}

$isFolder = $_GET['type'] === 'folder';
$name = basename($filePath);
$size = $isFolder ? count(glob($filePath.'/*')) : filesize($filePath);
$modified = date("Y-m-d H:i:s", filemtime($filePath));
$created = date("Y-m-d H:i:s", filectime($filePath));
$perms = substr(sprintf('%o', fileperms($filePath)), -4);
?>

<div class="space-y-2">
    <div class="flex justify-between">
        <span class="text-gray-400">Name:</span>
        <span><?php echo htmlspecialchars($name); ?></span>
    </div>
    <div class="flex justify-between">
        <span class="text-gray-400">Type:</span>
        <span><?php echo $isFolder ? 'Folder' : 'File'; ?></span>
    </div>
    <div class="flex justify-between">
        <span class="text-gray-400">Size:</span>
        <span><?php echo $isFolder ? "$size items" : formatFileSize($size); ?></span>
    </div>
    <div class="flex justify-between">
        <span class="text-gray-400">Modified:</span>
        <span><?php echo $modified; ?></span>
    </div>
    <div class="flex justify-between">
        <span class="text-gray-400">Created:</span>
        <span><?php echo $created; ?></span>
    </div>
    <div class="flex justify-between">
        <span class="text-gray-400">Permissions:</span>
        <span><?php echo $perms; ?></span>
    </div>
    <?php if (!$isFolder): ?>
    <div class="flex justify-between">
        <span class="text-gray-400">Extension:</span>
        <span><?php echo pathinfo($name, PATHINFO_EXTENSION); ?></span>
    </div>
    <?php endif; ?>
</div>