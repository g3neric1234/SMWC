<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}
$upload_dir = __DIR__ . '/uploads/profiles/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
    file_put_contents($upload_dir . '.htaccess', "php_flag engine off\nDeny from all");
}
$stmt = $pdo->prepare("SELECT nombre, apellido, profile_pic, descripcion, password FROM users WHERE username = ?");
$stmt->execute([$_SESSION['user']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$error = null;
$success = null;
$form_type = $_POST['form_type'] ?? '';

if ($form_type === 'profile_pic') {
    $profile_pic = $user['profile_pic'] ?? null;

    if (isset($_POST['delete_pic']) && $_POST['delete_pic'] === '1') {
        if ($profile_pic && file_exists(__DIR__ . '/' . $profile_pic)) {
            unlink(__DIR__ . '/' . $profile_pic);
        }
        $profile_pic = null;
        $_SESSION['profile_pic'] = null;

        $stmt = $pdo->prepare("UPDATE users SET profile_pic = NULL WHERE username = ?");
        $stmt->execute([$_SESSION['user']]);
        $success = "Foto de perfil eliminada correctamente.";
    } 
    elseif (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $max_size = 25 * 1024 * 1024; // 25MB
        $allowed_types = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif'
        ];

        if ($_FILES['profile_pic']['size'] > $max_size) {
            $error = "La imagen es demasiado grande (máximo 25MB)";
        } else {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $_FILES['profile_pic']['tmp_name']);
            finfo_close($finfo);

            if (!array_key_exists($mime_type, $allowed_types)) {
                $error = "Solo se permiten imágenes JPG, PNG o GIF";
            } elseif (!@getimagesize($_FILES['profile_pic']['tmp_name'])) {
                $error = "El archivo no es una imagen válida";
            } else {
                $extension = $allowed_types[$mime_type];
                $file_name = bin2hex(random_bytes(16)) . '.' . $extension;
                $file_path = $upload_dir . $file_name;

                if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $file_path)) {
                    chmod($file_path, 0644);
                    if ($profile_pic && file_exists(__DIR__ . '/' . $profile_pic)) {
                        unlink(__DIR__ . '/' . $profile_pic);
                    }
                    $profile_pic = 'uploads/profiles/' . $file_name;
                    $stmt = $pdo->prepare("UPDATE users SET profile_pic = ? WHERE username = ?");
                    $stmt->execute([$profile_pic, $_SESSION['user']]);
                    $_SESSION['profile_pic'] = $profile_pic;
                    $success = "Foto de perfil actualizada correctamente.";
                } else {
                    $error = "Error al subir la imagen.";
                }
            }
        }
    }
    $stmt = $pdo->prepare("SELECT nombre, apellido, profile_pic, descripcion FROM users WHERE username = ?");
    $stmt->execute([$_SESSION['user']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

elseif ($form_type === 'personal_info') {
    $nombre = trim($_POST['nombre'] ?? $user['nombre']);
    $apellido = trim($_POST['apellido'] ?? $user['apellido']);
    $descripcion = trim($_POST['descripcion'] ?? $user['descripcion']);
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // allow basic html
    $descripcion = strip_tags($descripcion, '<br><p><a><strong><em><ul><ol><li>');

    if (!empty($new_password) && $new_password !== $confirm_password) {
        $error = "Las nuevas contraseñas no coinciden.";
    } elseif (!empty($new_password)) {
        if (!password_verify($current_password, $user['password'])) {
            $error = "La contraseña actual es incorrecta.";
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET nombre = ?, apellido = ?, descripcion = ?, password = ? WHERE username = ?");
            $stmt->execute([$nombre, $apellido, $descripcion, $hashed_password, $_SESSION['user']]);
            $success = "Perfil y contraseña actualizados correctamente.";
        }
    } else {
        $stmt = $pdo->prepare("UPDATE users SET nombre = ?, apellido = ?, descripcion = ? WHERE username = ?");
        $stmt->execute([$nombre, $apellido, $descripcion, $_SESSION['user']]);
        $success = "Perfil actualizado correctamente.";
    }

    $_SESSION['nombre'] = $nombre;

    // re-update user data
    $stmt = $pdo->prepare("SELECT nombre, apellido, profile_pic, descripcion FROM users WHERE username = ?");
    $stmt->execute([$_SESSION['user']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>User Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="images/head.ico" type="image/ico">
    <style>
        .neon {
            text-shadow: 0 0 5px #00f, 0 0 10px #00f, 0 0 20px #00f;
        }
        .profile-pic {
            transition: all 0.3s ease;
        }
        .profile-pic:hover {
            transform: scale(1.05);
            box-shadow: 0 0 15px rgba(0, 255, 255, 0.5);
        }
        .neon-text {
            text-shadow: 0 0 5px #00f, 0 0 10px #00f;
        }
        .profile-container {
            position: relative;
            width: 160px;
            height: 160px;
        }
        .profile-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid #9333ea;
        }
        .profile-placeholder {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background-color: #1f2937;
            border: 4px solid #9333ea;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .descripcion-textarea {
            min-height: 120px;
            resize: vertical;
            transition: all 0.3s ease;
        }
        .descripcion-textarea:focus {
            border-color: #9333ea;
            box-shadow: 0 0 0 3px rgba(147, 51, 234, 0.3);
        }
        .descripcion-container {
            border: 1px solid #374151;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }
        .descripcion-container:hover {
            border-color: #6b21a8;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
    </style>
</head>
<body class="bg-black text-white font-sans min-h-screen">
    <?php include 'particles.php'; ?>
    <?php include 'navbar.php'; ?>
    
    <main class="md:ml-64 min-h-screen p-10">
        <section class="max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold neon-text mb-8">User Profile: @<?php echo htmlspecialchars($_SESSION['user'] ?? ''); ?></h1>
            
            <?php if ($error): ?>
                <div class="bg-red-900/80 text-red-200 rounded-lg border border-red-700/50 p-4 mb-6">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="bg-green-900/80 text-green-200 rounded-lg border border-green-700/50 p-4 mb-6">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <div class="flex flex-col md:flex-row gap-8">
                <div class="w-full md:w-1/3 flex flex-col items-center">
                    <div class="profile-container mb-4">
                        <?php if (isset($_SESSION['profile_pic']) && !empty($_SESSION['profile_pic'])): ?>
                            <img src="<?php echo htmlspecialchars($_SESSION['profile_pic']); ?>" 
                                 class="w-full h-full rounded-full object-cover transition-transform duration-300 group-hover:scale-110">
                        <?php else: ?>
                            <div class="profile-placeholder">
                                <svg class="w-20 h-20 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                        <?php endif; ?>
                    </div>
                    <form method="POST" enctype="multipart/form-data" class="w-full space-y-3" id="profile-pic-form" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                        <div>
                            <input type="file" id="profile_pic" name="profile_pic" accept="image/jpeg,image/png,image/gif" class="hidden">
                            <label for="profile_pic" class="block w-full bg-purple-600 hover:bg-purple-700 text-white text-center py-2 px-4 rounded-lg cursor-pointer transition-colors">
                                Cambiar foto
                            </label>
                            <p class="text-xs text-gray-400 mt-1 text-center">Formatos: JPG, PNG, GIF (max. 25MB)</p>
                        </div>
                                                
                        <div id="upload-button" class="hidden">
                            <button type="submit" name="upload_pic" class="w-full bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg transition-colors">
                                Subir foto
                            </button>
                        </div>
                                                
                        <?php if (!empty($_SESSION['profile_pic'])): ?>
                            <div>
                                <input type="hidden" name="delete_pic" value="0" id="delete_pic_hidden">
                                <button type="button" onclick="confirmDelete()" class="w-full bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded-lg transition-colors">
                                    Eliminar foto
                                </button>
                            </div>
                        <?php endif; ?>
                    </form>
                    
             </div>
                <div class="w-full md:w-2/3 flex flex-col gap-6">
                    <div class="w-full bg-zinc-800 rounded-lg p-4 descripcion-container">
                        <h3 class="text-lg font-semibold mb-2 text-center">Descripción</h3>

                <?php
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $nuevaDescripcion = trim($_POST['descripcion'] ?? '');
                    $stmt = $pdo->prepare("UPDATE users SET descripcion = ? WHERE username = ?");
                    $stmt->execute([$nuevaDescripcion, $_SESSION['user']]);
                    $user['descripcion'] = $nuevaDescripcion;
                } else {
                    $stmt = $pdo->prepare("SELECT descripcion FROM users WHERE username = ?");
                    $stmt->execute([$_SESSION['user']]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                }
                $desc = trim($user['descripcion'] ?? '');
                ?>

                <form method="POST" class="space-y-2">
                    <textarea 
                        id="descripcion" 
                        name="descripcion" 
                        rows="5" 
                        maxlength="120"
                        class="w-full bg-zinc-700 border border-zinc-600 text-white rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500 descripcion-textarea"
                        placeholder="<?php echo $desc === '' ? 'Cuéntanos algo sobre ti...' : ''; ?>"><?php 
                            echo htmlspecialchars($desc, ENT_QUOTES, 'UTF-8'); 
                        ?></textarea>

                    <div class="flex justify-between items-center">
                        <span id="char-counter" class="text-xs text-gray-400">
                            <?php echo strlen($desc); ?>/120 caracteres
                        </span>
                        <button 
                            type="submit" 
                            class="bg-purple-600 hover:bg-purple-700 text-white py-1 px-3 rounded-lg transition-colors text-sm">
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
                    <div class="bg-zinc-800 rounded-xl p-6 shadow-lg">
                    <form method="POST" class="space-y-6">
                        <div>
                            <h3 class="text-lg font-semibold mb-4">Información Personal</h3>
                            <label for="nombre" class="block text-sm mb-2">Nombre</label>
                            <input type="text" id="nombre" name="nombre"
                                   class="w-full bg-zinc-700 border border-zinc-600 text-white rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500"
                                   value="<?php echo htmlspecialchars($user['nombre'] ?? ''); ?>">
                        </div>
                        
                        <div>
                            <label for="apellido" class="block text-sm mb-2">Apellido</label>
                            <input type="text" id="apellido" name="apellido"
                                   class="w-full bg-zinc-700 border border-zinc-600 text-white rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500"
                                   value="<?php echo htmlspecialchars($user['apellido'] ?? ''); ?>">
                        </div>
                        
                        <div class="pt-4 border-t border-zinc-700">
                            <h3 class="text-lg font-semibold mb-4">Cambiar Contraseña</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label for="current_password" class="block text-sm mb-2">Contraseña actual</label>
                                    <input type="password" id="current_password" name="current_password"
                                           class="w-full bg-zinc-700 border border-zinc-600 text-white rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                                </div>
                                
                                <div>
                                    <label for="new_password" class="block text-sm mb-2">Nueva contraseña</label>
                                    <input type="password" id="new_password" name="new_password"
                                           class="w-full bg-zinc-700 border border-zinc-600 text-white rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                                </div>
                                
                                <div>
                                    <label for="confirm_password" class="block text-sm mb-2">Confirmar nueva contraseña</label>
                                    <input type="password" id="confirm_password" name="confirm_password"
                                           class="w-full bg-zinc-700 border border-zinc-600 text-white rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white py-3 rounded-lg font-semibold transition-colors">
                            Guardar cambios
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <script>
        function confirmDelete() {
            if (confirm('¿Estás seguro de que quieres eliminar tu foto de perfil?')) {
                document.getElementById('delete_pic_hidden').value = '1';
                document.getElementById('profile-pic-form').submit();
            }
        }
        document.getElementById('profile_pic')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            const uploadButton = document.getElementById('upload-button');
            const maxSize = 25 * 1024 * 1024; // 25MB
            if (file) {
                if (file.size > maxSize) {
                    alert('El archivo es demasiado grande. Máximo 25MB permitidos.');
                    e.target.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(event) {
                    const container = document.querySelector('.profile-container');
                    container.innerHTML = `<img src="${event.target.result}" class="profile-image" id="profile-pic-preview">`;
                    uploadButton.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
        });
        
        document.addEventListener("DOMContentLoaded", () => {
            const textarea = document.getElementById('descripcion');
            const charCounter = document.getElementById('char-counter');
        
            const updateCounter = () => {
                const length = textarea.value.length;
                charCounter.textContent = `${length}/120 caracteres`;
                charCounter.classList.toggle('text-red-400', length > 120);
            };
        
            textarea.addEventListener('input', updateCounter);
            updateCounter();
        });

    </script>
</body>
</html>