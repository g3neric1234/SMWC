<?php
require_once 'db_connection.php';

if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header("Location: /smwc/index");
    exit;
}
$baseDir = realpath(__DIR__.'/uploads/profiles/files');
if (!$baseDir) {
    die("Base upload directory does not exist or is not accessible.");
}
function formatSize($bytes) {
    if ($bytes === 0) return '0 Bytes';
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes) / log($k));
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}
function deleteDirectoryRecursive($dir) {
    if (!is_dir($dir)) {
        return false;
    }
    $items = array_diff(scandir($dir), array('.', '..'));
    foreach ($items as $item) {
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        is_dir($path) ? deleteDirectoryRecursive($path) : unlink($path);
    }
    return rmdir($dir);
}
$currentDirInput = isset($_GET['dir']) ? $_GET['dir'] : '';
$currentDir = realpath($baseDir . '/' . $currentDirInput);
if (!$currentDir || strpos($currentDir, $baseDir) !== 0 || !is_dir($currentDir)) {
    $currentDir = $baseDir;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create_file':
                $filename = isset($_POST['filename']) ? basename($_POST['filename']) : '';
                if (!empty($filename)) {
                    $filePath = $currentDir.'/'.$filename;
                    if (!file_exists($filePath)) {
                        file_put_contents($filePath, '');
                    } else {
                    }
                }
                break;
                
            case 'create_folder':
                $foldername = isset($_POST['foldername']) ? basename($_POST['foldername']) : '';
                if (!empty($foldername)) {
                    $folderPath = $currentDir.'/'.$foldername;
                    if (!is_dir($folderPath)) {
                        mkdir($folderPath);
                    } else {
                    }
                }
                break;
                
            case 'delete':
                if (isset($_POST['selected_items']) && is_array($_POST['selected_items'])) {
                    foreach ($_POST['selected_items'] as $target_name) {
                        $target_basename = basename($target_name);
                        $targetPath = $currentDir.'/'.$target_basename;
                        if (strpos(realpath(dirname($targetPath)), $baseDir) !== 0 && realpath($targetPath) !== $baseDir) {
                            continue;
                        }

                        if (is_file($targetPath)) {
                            unlink($targetPath);
                        } elseif (is_dir($targetPath)) {
                            deleteDirectoryRecursive($targetPath);
                        }
                    }
                }
                break;
                
            case 'rename':
                $old_name = isset($_POST['old']) ? basename($_POST['old']) : '';
                $new_name = isset($_POST['new']) ? basename($_POST['new']) : '';
                if (!empty($old_name) && !empty($new_name)) {
                    $oldPath = $currentDir.'/'.$old_name;
                    $newPath = $currentDir.'/'.$new_name;
                    if (file_exists($oldPath) && !file_exists($newPath)) {
                        rename($oldPath, $newPath);
                    }
                }
                break;
                
            case 'save_file':
                $filename = isset($_POST['filename']) ? basename($_POST['filename']) : '';
                if (!empty($filename)) {
                    $filePath = $currentDir.'/'.$filename;
                    if (dirname($filePath) === rtrim($currentDir, '/\\')) {
                         file_put_contents($filePath, $content);
                    }
                }
                break;
                
            case 'upload':
                if (isset($_FILES['files'])) {
                    foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {
                        if ($_FILES['files']['error'][$key] == UPLOAD_ERR_OK) {
                            $target_filename = basename($_FILES['files']['name'][$key]);
                            $target = $currentDir.'/'.$target_filename;
                            move_uploaded_file($tmp_name, $target);
                        }
                    }
                }
                break;
                
            case 'move':
                if (isset($_POST['selected_items'], $_POST['destination'])) {
                    $destination_relative = trim($_POST['destination'], '/');
                    $targetDestDir = $baseDir . (empty($destination_relative) ? '' : '/' . $destination_relative);
                    $realTargetDestDir = realpath($targetDestDir);

                    if ($realTargetDestDir && strpos($realTargetDestDir, $baseDir) === 0 && is_dir($realTargetDestDir)) {
                        foreach ($_POST['selected_items'] as $item_name) {
                            $item_basename = basename($item_name); // Sanitize
                            $sourcePath = $currentDir.'/'.$item_basename;
                            $newLocation = $realTargetDestDir.'/'.$item_basename;
                            
                            if (file_exists($sourcePath) && !file_exists($newLocation)) {
                                rename($sourcePath, $newLocation);
                            }
                        }
                    } else {
                    }
                }
                break;
        }
    }
    $redirectRelativeDir = str_replace($baseDir, '', $currentDir);
    $redirectRelativeDir = ltrim($redirectRelativeDir, DIRECTORY_SEPARATOR);
    header("Location: ".$_SERVER['PHP_SELF']."?dir=".urlencode($redirectRelativeDir));
    exit;
}
$files = [];
$folders = [];

if (is_dir($currentDir)) {
    $items = scandir($currentDir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $currentDir.'/'.$item;
        if (is_dir($path)) {
            $folders[] = $item;
        } else {
            $files[] = $item;
        }
    }
}
$allFoldersForMove = [];
$directoryIterator = new RecursiveDirectoryIterator($baseDir, RecursiveDirectoryIterator::SKIP_DOTS);
$flatIterator = new RecursiveIteratorIterator($directoryIterator, RecursiveIteratorIterator::SELF_FIRST);

$currentRelativePathForExclusion = ltrim(str_replace($baseDir, '', $currentDir), DIRECTORY_SEPARATOR);

foreach ($flatIterator as $item) {
    if ($item->isDir()) {
        $itemRelativePath = ltrim(str_replace($baseDir, '', $item->getPathname()), DIRECTORY_SEPARATOR);
        if ($itemRelativePath !== $currentRelativePathForExclusion) {
             $allFoldersForMove[] = $itemRelativePath;
        }
    }
}
$allFoldersForMove = array_unique($allFoldersForMove);
sort($allFoldersForMove);
$displayRelativePath = str_replace($baseDir, '', $currentDir);
if (empty($displayRelativePath) || $displayRelativePath === DIRECTORY_SEPARATOR) {
    $displayRelativePath = '/';
} else {
    $displayRelativePath = '/' . ltrim($displayRelativePath, DIRECTORY_SEPARATOR);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Archivos - SMWC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="images/head.ico" type="image/ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/plyr/3.7.8/plyr.css" rel="stylesheet">
    <style>
        .neon { text-shadow: 0 0 5px #00f, 0 0 10px #00f, 0 0 20px #00f; }
        .neon-text { text-shadow: 0 0 5px #00f, 0 0 10px #00f; }
        .terminal-container { width: 100%; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem; }
        .terminal-window { background: #181818; color:rgb(255, 255, 255); font-family: 'Consolas', 'Courier New', monospace; border-radius: 0.5rem; box-shadow: 0 0 20px #000a; padding: 2rem 1.5rem 1.5rem 1.5rem; height: 80vh; width: 90%; max-width: 1000px; border: 2px solid #222; display: flex; flex-direction: column; }
        .terminal-bar { background: #222; border-radius: 0.5rem 0.5rem 0 0; padding: 0.5rem 1rem; margin: -2rem -1.5rem 1.5rem -1.5rem; display: flex; align-items: center; }
        .terminal-dot { width: 0.75rem; height: 0.75rem; border-radius: 50%; margin-right: 0.5rem; }
        .dot-red { background: #ff5f56; }
        .dot-yellow { background: #ffbd2e; }
        .dot-green { background: #27c93f; }
        .file-item:hover { background-color: #333; cursor: pointer; }
        .editor-container { display: none; height: 100%; flex-grow: 1; }
        #file-content { width: 100%; height: calc(100% - 40px); background: #222; color: white; border: none; padding: 10px; font-family: 'Consolas', 'Courier New', monospace; resize: none; }
        .breadcrumb a:hover { text-decoration: underline; }
        .selected { background-color: #3a3a5a !important; border: 1px solid #6b46c1; }
        .btn-primary { background-color: #7e22ce; } .btn-primary:hover { background-color: #6b21a8; }
        .btn-secondary { background-color: #4f46e5; } .btn-secondary:hover { background-color: #4338ca; }
        .btn-success { background-color: #10b981; } .btn-success:hover { background-color: #059669; }
        .btn-danger { background-color: #ef4444; } .btn-danger:hover { background-color: #dc2626; }
        .btn-warning { background-color: #f59e0b; } .btn-warning:hover { background-color: #d97706; }
        .btn-info { background-color: #06b6d4; } .btn-info:hover { background-color: #0891b2; }
        .btn-animate { transition: all 0.3s ease; transform: translateY(0); }
        .btn-animate:hover { transform: translateY(-2px); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); }
        .btn-pulse:hover { animation: pulse 1.5s infinite; }
        @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(124, 58, 237, 0.7); } 70% { box-shadow: 0 0 0 10px rgba(124, 58, 237, 0); } 100% { box-shadow: 0 0 0 0 rgba(124, 58, 237, 0); } }
        .media-container { width: 100%; max-width: 800px; margin: 0 auto; }
        .preview-container { max-height: 400px; overflow: hidden; display: flex; align-items: center; justify-content: center; background: #000; margin-bottom: 1rem; }
        .preview-container img, .preview-container video { max-width: 100%; max-height: 100%; object-fit: contain; }
        .btn-float { animation: float 3s ease-in-out infinite; }
        @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-5px); } }
        .plyr--video, .plyr--audio { border-radius: 8px; overflow: hidden; }
        .plyr__controls { background: rgba(0, 0, 0, 0.7); }
        .plyr__progress input[type="range"] { color: #7e22ce; }
    </style>
</head>
<body class="bg-black text-white font-sans">
    <?php include 'particles.php'; ?>
    <?php include 'navbar.php'; ?>
    <main class="md:ml-64 min-h-screen">
        <div class="terminal-container">
            <div id="terminal-window" class="terminal-window">
                <div class="terminal-bar">
                    <span class="terminal-dot dot-red"></span>
                    <span class="terminal-dot dot-yellow"></span>
                    <span class="terminal-dot dot-green"></span>
                    <span class="ml-3 text-zinc-400 text-xs select-none">File Explorer - <?php echo htmlspecialchars(getenv("USERNAME") ?: "user"); ?>@<?php echo htmlspecialchars(gethostname()); ?></span>
                </div>
                <div class="breadcrumb mb-4 text-sm text-blue-400">
                    <?php
                    $pathParts = explode('/', trim($displayRelativePath, '/'));
                    $currentLinkPath = '';
                    echo '<a href="?dir=" class="hover:text-purple-300">root</a>';
                    if ($displayRelativePath !== '/') {
                        echo ' / ';
                    }
                    foreach ($pathParts as $i => $part) {
                        if (!empty($part)) {
                            $currentLinkPath .= ($currentLinkPath ? '/' : '') . $part;
                            echo '<a href="?dir='.urlencode($currentLinkPath).'" class="hover:text-purple-300">'.htmlspecialchars($part).'</a>';
                            if ($i < count($pathParts) - 1 && !empty($pathParts[$i+1])) echo ' / ';
                        }
                    }
                    ?>
                </div>
                <div id="explorer-view" class="flex-1 overflow-auto">
                    <div class="flex flex-wrap gap-2 mb-4">
                        <button onclick="showCreateFileModal()" class="btn-primary btn-animate px-3 py-1 rounded text-sm">
                            <i class="fas fa-file"></i> New File
                        </button>
                        <button onclick="showCreateFolderModal()" class="btn-secondary btn-animate px-3 py-1 rounded text-sm">
                            <i class="fas fa-folder"></i> New Folder
                        </button>
                        <button onclick="showMoveModal()" id="move-btn" class="btn-info btn-animate px-3 py-1 rounded text-sm hidden">
                            <i class="fas fa-arrows-alt"></i> Move
                        </button>
                        <button onclick="showDeleteModal(null, false)" id="delete-btn" class="btn-danger btn-animate px-3 py-1 rounded text-sm hidden"> <!-- `null, false` indicates multi-item delete -->
                            <i class="fas fa-trash"></i> Delete
                        </button>
                        <form method="post" enctype="multipart/form-data" class="inline" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?dir=' . urlencode(ltrim($displayRelativePath, '/'))); ?>">
                            <input type="hidden" name="action" value="upload">
                            <label class="btn-success btn-animate px-3 py-1 rounded text-sm cursor-pointer">
                                <i class="fas fa-upload"></i> Upload Files
                                <input type="file" name="files[]" multiple class="hidden" onchange="this.form.submit()">
                            </label>
                        </form>
                    </div>
                    <div class="mb-4">
                        <h3 class="text-lg mb-2 text-blue-400">Folders</h3>
                        <?php if (empty($folders)): ?>
                            <p class="text-gray-500">No folders</p>
                        <?php else: ?>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                                <?php foreach ($folders as $folder): 
                                    $folderPath = $currentDir.'/'.$folder;
                                    $itemCount = count(array_diff(scandir($folderPath), ['.', '..']));
                                    $modified = date("Y-m-d H:i:s", filemtime($folderPath));
                                    $folderLinkRelativePath = ltrim($displayRelativePath === '/' ? $folder : $displayRelativePath.'/'.$folder, '/');
                                ?>
                                    <div class="file-item flex items-center p-2 rounded selectable" data-name="<?php echo htmlspecialchars($folder); ?>" data-type="folder">
                                        <i class="fas fa-folder text-yellow-400 mr-2"></i>
                                        <div class="flex-1">
                                            <a href="?dir=<?php echo urlencode($folderLinkRelativePath); ?>" class="block"><?php echo htmlspecialchars($folder); ?></a>
                                            <div class="text-xs text-gray-400">
                                                Items: <?php echo $itemCount; ?> | Modified: <?php echo $modified; ?>
                                            </div>
                                        </div>
                                        <div class="actions">
                                            <button onclick="event.stopPropagation(); showRenameModal('<?php echo htmlspecialchars($folder); ?>', true)" class="text-gray-400 hover:text-white mr-2">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button onclick="event.stopPropagation(); showDeleteModal('<?php echo htmlspecialchars($folder); ?>', true)" class="text-red-400 hover:text-white">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h3 class="text-lg mb-2 text-blue-400">Files</h3>
                        <?php if (empty($files)): ?>
                            <p class="text-gray-500">No files</p>
                        <?php else: ?>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                                <?php foreach ($files as $file): 
                                    $filePath = $currentDir.'/'.$file;
                                    $size = file_exists($filePath) ? filesize($filePath) : 0;
                                    $modified = file_exists($filePath) ? date("Y-m-d H:i:s", filemtime($filePath)) : 'N/A';
                                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                    $downloadFileRelPath = ltrim(($displayRelativePath === '/' ? $file : $displayRelativePath.'/'.$file), '/');
                                ?>
                                    <div class="file-item flex items-center p-2 rounded selectable" data-name="<?php echo htmlspecialchars($file); ?>" data-type="file">
                                        <?php 
                                        $icon = 'fa-file'; $preview = 'text';
                                        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'ico'])) { $icon = 'fa-file-image'; $preview = 'image'; }
                                        elseif (in_array($ext, ['pdf'])) { $icon = 'fa-file-pdf'; $preview = 'pdf'; }
                                        elseif (in_array($ext, ['doc', 'docx'])) { $icon = 'fa-file-word'; }
                                        elseif (in_array($ext, ['xls', 'xlsx'])) { $icon = 'fa-file-excel'; }
                                        elseif (in_array($ext, ['zip', 'rar', 'tar', 'gz'])) { $icon = 'fa-file-archive'; }
                                        elseif (in_array($ext, ['mp3', 'wav', 'ogg', 'aac', 'flac'])) { $icon = 'fa-file-audio'; $preview = 'audio'; }
                                        elseif (in_array($ext, ['mp4', 'mov', 'avi', 'mkv', 'webm'])) { $icon = 'fa-file-video'; $preview = 'video'; }
                                        elseif (in_array($ext, ['txt', 'md', 'json', 'xml', 'html', 'css', 'js', 'php', 'py', 'c', 'cpp', 'java', 'sh', 'log'])) { $preview = 'text'; }
                                        ?>
                                        <i class="fas <?php echo $icon; ?> text-blue-400 mr-2"></i>
                                        <div class="flex-1">
                                            <a href="javascript:void(0);" onclick="openFile('<?php echo htmlspecialchars($file); ?>', '<?php echo $preview; ?>')" class="block"><?php echo htmlspecialchars($file); ?></a>
                                            <div class="text-xs text-gray-400">
                                                Size: <?php echo formatSize($size); ?> | Modified: <?php echo $modified; ?>
                                            </div>
                                        </div>
                                        <div class="actions flex">
                                            <a href="download.php?file=<?php echo urlencode($downloadFileRelPath); ?>" class="text-gray-400 hover:text-white mr-2" onclick="event.stopPropagation()" download>
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <button onclick="event.stopPropagation(); showRenameModal('<?php echo htmlspecialchars($file); ?>', false)" class="text-gray-400 hover:text-white mr-2">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button onclick="event.stopPropagation(); showDeleteModal('<?php echo htmlspecialchars($file); ?>', false)" class="text-red-400 hover:text-white">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div id="editor-view" class="editor-container flex flex-col">
                    <div class="flex justify-between items-center mb-2">
                        <h3 id="editor-filename" class="text-lg text-blue-400"></h3>
                        <button onclick="closeEditor()" class="btn-danger btn-animate px-2 py-1 rounded text-sm">
                            <i class="fas fa-times"></i> Close
                        </button>
                    </div>
                    <textarea id="file-content" spellcheck="false"></textarea>
                    <div class="mt-2 flex justify-end">
                        <button onclick="saveFile()" class="btn-success btn-animate px-3 py-1 rounded text-sm">
                            <i class="fas fa-save"></i> Save
                        </button>
                    </div>
                </div>
                <div id="media-view" class="editor-container flex flex-col">
                    <div class="flex justify-between items-center mb-2">
                        <h3 id="media-filename" class="text-lg text-blue-400"></h3>
                        <button onclick="closeMediaPlayer()" class="btn-danger btn-animate px-2 py-1 rounded text-sm">
                            <i class="fas fa-times"></i> Close
                        </button>
                    </div>
                    <div class="media-container flex-1 overflow-auto">
                        <div id="media-player-content" class="w-full h-full flex items-center justify-center"></div>
                    </div>
                </div>

            </div>
        </div>
        <?php 
        $form_action_url = htmlspecialchars($_SERVER['PHP_SELF'] . '?dir=' . urlencode(ltrim($displayRelativePath, '/')));
        ?>
        <div id="create-file-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
            <div class="bg-gray-800 p-6 rounded-lg w-96">
                <h3 class="text-lg mb-4">Create New File</h3>
                <form method="post" action="<?php echo $form_action_url; ?>">
                    <input type="hidden" name="action" value="create_file">
                    <div class="mb-4">
                        <label class="block text-sm mb-2">File Name</label>
                        <input type="text" name="filename" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2" required>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="hideModal('create-file-modal')" class="btn-secondary btn-animate px-3 py-1 rounded">Cancel</button>
                        <button type="submit" class="btn-primary btn-animate px-3 py-1 rounded">Create</button>
                    </div>
                </form>
            </div>
        </div>
        <div id="create-folder-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
            <div class="bg-gray-800 p-6 rounded-lg w-96">
                <h3 class="text-lg mb-4">Create New Folder</h3>
                <form method="post" action="<?php echo $form_action_url; ?>">
                    <input type="hidden" name="action" value="create_folder">
                    <div class="mb-4">
                        <label class="block text-sm mb-2">Folder Name</label>
                        <input type="text" name="foldername" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2" required>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="hideModal('create-folder-modal')" class="btn-secondary btn-animate px-3 py-1 rounded">Cancel</button>
                        <button type="submit" class="btn-primary btn-animate px-3 py-1 rounded">Create</button>
                    </div>
                </form>
            </div>
        </div>
        <div id="rename-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
            <div class="bg-gray-800 p-6 rounded-lg w-96">
                <h3 id="rename-title" class="text-lg mb-4">Rename</h3>
                <form method="post" action="<?php echo $form_action_url; ?>">
                    <input type="hidden" name="action" value="rename">
                    <input type="hidden" id="rename-old" name="old">
                    <div class="mb-4">
                        <label class="block text-sm mb-2">New Name</label>
                        <input type="text" id="rename-new" name="new" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2" required>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="hideModal('rename-modal')" class="btn-secondary btn-animate px-3 py-1 rounded">Cancel</button>
                        <button type="submit" class="btn-primary btn-animate px-3 py-1 rounded">Rename</button>
                    </div>
                </form>
            </div>
        </div>
        <div id="delete-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
            <div class="bg-gray-800 p-6 rounded-lg w-96">
                <h3 id="delete-title" class="text-lg mb-4">Confirm Delete</h3>
                <p id="delete-message" class="mb-4">Are you sure you want to delete the selected items?</p>
                <form method="post" action="<?php echo $form_action_url; ?>">
                    <input type="hidden" name="action" value="delete">
                    <div id="delete-items-container"></div>
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="hideModal('delete-modal')" class="btn-secondary btn-animate px-3 py-1 rounded">Cancel</button>
                        <button type="submit" class="btn-danger btn-animate px-3 py-1 rounded">Delete</button>
                    </div>
                </form>
            </div>
        </div>
        <div id="move-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
            <div class="bg-gray-800 p-6 rounded-lg w-96">
                <h3 class="text-lg mb-4">Move Items</h3>
                <form method="post" action="<?php echo $form_action_url; ?>">
                    <input type="hidden" name="action" value="move">
                    <div class="mb-4">
                        <label class="block text-sm mb-2">Destination Folder</label>
                        <select name="destination" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2">
                            <option value="">/ (Root)</option>
                            <?php foreach ($allFoldersForMove as $folderRelPath): ?>
                                <option value="<?php echo htmlspecialchars($folderRelPath); ?>"><?php echo htmlspecialchars(empty($folderRelPath) ? '/' : '/'.$folderRelPath); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div id="move-items-container"></div>
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="hideModal('move-modal')" class="btn-secondary btn-animate px-3 py-1 rounded">Cancel</button>
                        <button type="submit" class="btn-primary btn-animate px-3 py-1 rounded">Move</button>
                    </div>
                </form>
            </div>
        </div>
        <div id="info-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
            <div class="bg-gray-800 p-6 rounded-lg w-96">
                <h3 id="info-title" class="text-lg mb-4">File Information</h3>
                <div id="info-content" class="space-y-2 text-sm"></div>
                <div class="flex justify-end mt-4">
                    <button onclick="hideModal('info-modal')" class="btn-secondary btn-animate px-3 py-1 rounded">Close</button>
                </div>
            </div>
        </div>
        
        <script src="https://cdnjs.cloudflare.com/ajax/libs/plyr/3.7.8/plyr.min.js"></script>
        <script>
            let currentFileBasename = null;
            let selectedItems = [];
            let plyrPlayer = null;
            const WEB_BASE_PATH = '/smwc/uploads/profiles/files';
            const CURRENT_RELATIVE_DIR = '<?php echo ltrim($displayRelativePath, "/"); ?>'; 

            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.selectable').forEach(item => {
                    item.addEventListener('click', function(e) {
                        if (e.target.closest('a, button')) return;
                        const name = this.dataset.name;
                        const type = this.dataset.type;
                        if (e.ctrlKey || e.metaKey) {
                            this.classList.toggle('selected');
                            const index = selectedItems.findIndex(i => i.name === name);
                            if (index > -1) {
                                selectedItems.splice(index, 1);
                            } else {
                                selectedItems.push({ name, type });
                            }
                        } else {
                            document.querySelectorAll('.selectable.selected').forEach(el => el.classList.remove('selected'));
                            this.classList.add('selected');
                            selectedItems = [{ name, type }];
                        }
                        updateActionButtons();
                    });
                });
                ['create-file-modal', 'create-folder-modal', 'rename-modal', 'delete-modal', 'move-modal', 'info-modal'].forEach(id => {
                    const modal = document.getElementById(id);
                    if (modal) {
                        modal.addEventListener('click', function(event) {
                            if (event.target.id === id) hideModal(id);
                        });
                    }
                });
            });

            function updateActionButtons() {
                const moveBtn = document.getElementById('move-btn');
                const deleteBtn = document.getElementById('delete-btn');
                if (selectedItems.length > 0) {
                    moveBtn.classList.remove('hidden');
                    deleteBtn.classList.remove('hidden');
                } else {
                    moveBtn.classList.add('hidden');
                    deleteBtn.classList.add('hidden');
                }
            }
            
            function showModal(id) { document.getElementById(id).classList.remove('hidden'); }
            function hideModal(id) { document.getElementById(id).classList.add('hidden'); }

            function showCreateFileModal() { showModal('create-file-modal'); document.querySelector('#create-file-modal input[name="filename"]').focus(); }
            function showCreateFolderModal() { showModal('create-folder-modal'); document.querySelector('#create-folder-modal input[name="foldername"]').focus();}
            
            function showRenameModal(name, isFolder) {
                document.getElementById('rename-old').value = name;
                document.getElementById('rename-new').value = name;
                document.getElementById('rename-title').textContent = `Rename ${isFolder ? 'Folder' : 'File'}: ${name}`;
                showModal('rename-modal');
                document.getElementById('rename-new').focus();
            }
            
            function showDeleteModal(itemName, isFolder) { 
                const container = document.getElementById('delete-items-container');
                container.innerHTML = '';
                
                let itemsToDelete = [];
                if (itemName) { 
                    itemsToDelete.push({name: itemName});
                    document.getElementById('delete-title').textContent = `Delete ${isFolder ? 'Folder' : 'File'}`;
                    document.getElementById('delete-message').textContent = `Are you sure you want to delete "${itemName}"?`;
                } else {
                    itemsToDelete = [...selectedItems];
                    document.getElementById('delete-title').textContent = `Delete ${itemsToDelete.length} Item(s)`;
                    document.getElementById('delete-message').textContent = `Are you sure you want to delete ${itemsToDelete.length} selected item(s)?`;
                }

                itemsToDelete.forEach(item => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'selected_items[]';
                    input.value = item.name;
                    container.appendChild(input);
                });
                showModal('delete-modal');
            }
            
            function showMoveModal() {
                const container = document.getElementById('move-items-container');
                container.innerHTML = '';
                selectedItems.forEach(item => {
                     container.innerHTML += `<input type="hidden" name="selected_items[]" value="${item.name}">`;
                });
                showModal('move-modal');
            }
            function constructPathRelativeToBase(filename) {
                return (CURRENT_RELATIVE_DIR ? CURRENT_RELATIVE_DIR + '/' : '') + filename;
            }

            function openFile(filenameBasename, previewType) {
                currentFileBasename = filenameBasename;
                const fullRelativePath = constructPathRelativeToBase(filenameBasename);

                if (previewType === 'image' || previewType === 'video' || previewType === 'audio') {
                    openMediaPlayer(filenameBasename, previewType, fullRelativePath);
                    return;
                }
                document.getElementById('editor-filename').textContent = filenameBasename;
                
                fetch('get_file_content.php?file=' + encodeURIComponent(fullRelativePath))
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok: ' + response.statusText);
                        return response.text();
                    })
                    .then(content => {
                        document.getElementById('file-content').value = content;
                        document.getElementById('explorer-view').style.display = 'none';
                        document.getElementById('media-view').style.display = 'none';
                        document.getElementById('editor-view').style.display = 'flex';
                        if (plyrPlayer) { plyrPlayer.destroy(); plyrPlayer = null; }
                    })
                    .catch(error => {
                        console.error('Error loading file content:', error);
                        alert('Error loading file: ' + filenameBasename);
                        closeEditor();
                    });
            }

            function openMediaPlayer(filenameBasename, type, fullRelativePath) {
                currentFileBasename = filenameBasename;
                document.getElementById('media-filename').textContent = filenameBasename;
                const mediaPlayerContent = document.getElementById('media-player-content');
                mediaPlayerContent.innerHTML = '';
                const mediaSrc = WEB_BASE_PATH + (fullRelativePath.startsWith('/') ? '' : '/') + fullRelativePath;

                if (type === 'image') {
                    mediaPlayerContent.innerHTML = `<img src="${mediaSrc}" alt="${filenameBasename}" class="max-w-full max-h-full object-contain">`;
                } else if (type === 'audio' || type === 'video') {
                    const mediaElement = document.createElement(type);
                    mediaElement.controls = true;
                    mediaElement.setAttribute('crossorigin', '');
                    mediaElement.setAttribute('playsinline', '');
                    
                    const sourceElement = document.createElement('source');
                    sourceElement.src = mediaSrc;
                    sourceElement.type = `${type}/${filenameBasename.split('.').pop()}`; 
                    mediaElement.appendChild(sourceElement);
                    mediaPlayerContent.appendChild(mediaElement);
                    
                    if (plyrPlayer) plyrPlayer.destroy();
                    plyrPlayer = new Plyr(mediaElement);
                } else {
                    mediaPlayerContent.innerHTML = `<p class="text-gray-400">Preview for this file type is not supported.</p>`;
                }
                
                document.getElementById('explorer-view').style.display = 'none';
                document.getElementById('editor-view').style.display = 'none';
                document.getElementById('media-view').style.display = 'flex';
            }
            
            function closeEditorOrPlayer() {
                document.getElementById('explorer-view').style.display = 'block';
                document.getElementById('editor-view').style.display = 'none';
                document.getElementById('media-view').style.display = 'none';
                currentFileBasename = null;
                if (plyrPlayer) {
                    plyrPlayer.destroy();
                    plyrPlayer = null;
                }
                selectedItems = [];
                updateActionButtons();
                document.querySelectorAll('.selectable.selected').forEach(el => el.classList.remove('selected'));

            }
            function closeEditor() { closeEditorOrPlayer(); }
            function closeMediaPlayer() { closeEditorOrPlayer(); }

            function saveFile() {
                if (!currentFileBasename) return;
                
                const content = document.getElementById('file-content').value;
                const formData = new FormData();
                formData.append('action', 'save_file');
                formData.append('filename', currentFileBasename);
                formData.append('content', content);
                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (response.ok) {
                        console.log('File saved successfully. Page should reload.');
                         alert('File saved successfully!');

                    } else {
                        alert('Error saving file. Status: ' + response.status);
                    }
                })
                .catch(error => {
                    console.error('Error saving file:', error);
                    alert('Error saving file.');
                });
            }
        </script>
    </main>
</body>
</html>