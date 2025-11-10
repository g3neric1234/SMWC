<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'admin') {
    header("Location: ../smwc/errors/error.php?code=403");
    exit;
}

/*$pdo = new PDO("mysql:host=localhost;dbname=smwc", "root", "");*/
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido'] ?? '');
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;

    if (empty($nombre) || empty($username) || empty($password) || empty($confirm_password)) {
        $error = "Todos los campos requeridos deben ser completados";
    } elseif ($password !== $confirm_password) {
        $error = "Las contraseñas no coinciden";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            
            if ($stmt->fetch()) {
                $error = "El nombre de usuario ya existe";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, password, nombre, apellido, is_admin) VALUES (?, ?, ?, ?, ?)");
                
                if ($stmt->execute([$username, $hashed_password, $nombre, $apellido, $is_admin])) {
                    $success = "Usuario registrado exitosamente";
                    $_POST = [];
                } else {
                    $error = "Error al registrar el usuario";
                }
            }
        } catch (PDOException $e) {
            $error = "Error de base de datos: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Registro de Usuarios</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="icon" href="images/head.ico" type="image/ico">
  <style>
    .neon {
        text-shadow: 0 0 5px #00f, 0 0 10px #00f, 0 0 20px #00f;
    }

    .eye-container svg {
      position: fixed;
      top: 0;
      left: 0;
      width: 1.25rem; 
      height: 1.25rem; 
      transition: all 0.3s ease-in-out;
    }
    @keyframes fadeInDown {
      from {
        opacity: 0;
        transform: translateY(-10px);
        height: 0;
        margin-bottom: 0;
        padding: 0;
      }
      to {
        opacity: 1;
        transform: translateY(0);
        height: auto;
        margin-bottom: 1rem;
        padding: 0.75rem;
      }
    }
    
    .login-container {
      transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
      min-height: 500px;
    }
    
    .login-container.with-error {
      min-height: 550px;
    }
    
    .login-container.with-success {
      min-height: 550px;
    }
    
    .error-message, .success-message {
      animation: fadeInDown 0.5s ease-out forwards;
      overflow: hidden;
    }
  </style>
</head>
<body class="bg-[#0d0d0d] text-white min-h-screen flex items-center justify-center font-sans">
  <div id="loginContainer" class="login-container w-full max-w-sm bg-[#1a1a1a] p-8 rounded-2xl shadow-lg <?php echo $error ? 'with-error' : ($success ? 'with-success' : '') ?>">
    <h1 class="text-2xl font-bold text-center mb-6">Registro de Usuarios</h1>

    <?php include 'particles.php'; ?>
    <?php include 'navbar.php'; ?>
    
    <?php if ($error): ?>
      <div class="error-message bg-red-900/80 text-red-200 rounded-lg border border-red-700/50 mb-4"><?php echo $error ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
      <div class="success-message bg-green-900/80 text-green-200 rounded-lg border border-green-700/50 mb-4"><?php echo $success ?></div>
    <?php endif; ?>
    <?php include 'particles.php'; ?>
    
    <form class="space-y-5" method="POST" action="/smwc/register">
      <div>
        <label for="nombre" class="block text-sm mb-1">Nombre <span class="text-red-500">*</span></label>
        <input type="text" id="nombre" name="nombre" required
          class="w-full bg-[#262626] border border-[#333] text-white rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500"
          value="<?php echo htmlspecialchars($_POST['nombre'] ?? '') ?>"
          placeholder="Nombre" />
      </div>

      <div>
        <label for="apellido" class="block text-sm mb-1">Apellido</label>
        <input type="text" id="apellido" name="apellido"
          class="w-full bg-[#262626] border border-[#333] text-white rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500"
          value="<?php echo htmlspecialchars($_POST['apellido'] ?? '') ?>"
          placeholder="Apellido (opcional)" />
      </div>

      <div>
      <label for="username" class="block text-sm mb-1">Username <span class="text-red-500">*</span></label>
      <div class="relative">
      <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">@</span>
      <input type="text" id="username" name="username" required
      class="w-full bg-[#262626] border border-[#333] text-white rounded-xl pl-7 pr-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500"
      placeholder="username" />
      </div>
      </div>

      <div>
        <label for="password" class="block text-sm mb-1">Contraseña <span class="text-red-500">*</span></label>
        <div class="relative">
          <input type="password" id="password" name="password" required
            class="w-full bg-[#262626] border border-[#333] text-white rounded-xl px-4 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-purple-500"
            placeholder="******" />
          <button type="button" onclick="togglePassword('password')"
            class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white w-5 h-5">
            <div class="relative eye-container">
              <svg id="eye-password" xmlns="http://www.w3.org/2000/svg" class="opacity-100 scale-100" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
              <svg id="eye-password-off" xmlns="http://www.w3.org/2000/svg" class="opacity-0 scale-90" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.044 10.044 0 012.442-4.233M9.88 9.88a3 3 0 104.24 4.24M6.1 6.1l11.8 11.8" />
              </svg>
            </div>
          </button>
        </div>
      </div>

      <div>
        <label for="confirm_password" class="block text-sm mb-1">Repetir Contraseña <span class="text-red-500">*</span></label>
        <div class="relative">
          <input type="password" id="confirm_password" name="confirm_password" required
            class="w-full bg-[#262626] border border-[#333] text-white rounded-xl px-4 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-purple-500"
            placeholder="******" />
          <button type="button" onclick="togglePassword('confirm_password')"
            class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white w-5 h-5">
            <div class="relative eye-container">
              <svg id="eye-confirm_password" xmlns="http://www.w3.org/2000/svg" class="opacity-100 scale-100" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
              <svg id="eye-confirm_password-off" xmlns="http://www.w3.org/2000/svg" class="opacity-0 scale-90" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.044 10.044 0 012.442-4.233M9.88 9.88a3 3 0 104.24 4.24M6.1 6.1l11.8 11.8" />
              </svg>
            </div>
          </button>
        </div>
      </div>

      <div class="flex items-center justify-center">
        <input type="checkbox" id="is_admin" name="is_admin" value="1"
           class="w-4 h-4 text-purple-600 bg-gray-700 border-gray-600 rounded focus:ring-purple-500 focus:ring-2"
           <?php echo isset($_POST['is_admin']) ? 'checked' : '' ?>>
        <label for="is_admin" class="ml-2 text-sm">Administrador</label>
      </div>

      <div class="flex space-x-3">
        <button type="submit"
          class="flex-1 bg-purple-600 hover:bg-purple-700 transform hover:-translate-y-1 hover:scale-105 transition-all duration-300 ease-in-out text-white py-2 rounded-xl font-semibold">
          Registrar
        </button>
        <a href="/smwc/menu" class="flex-1 bg-gray-600 hover:bg-gray-700 transform hover:-translate-y-1 hover:scale-105 transition-all duration-300 ease-in-out text-white py-2 rounded-xl font-semibold text-center">
          Volver
        </a>
      </div>
    </form>
  </div>

  <script>
    function togglePassword(id) {
      const input = document.getElementById(id);
      const eye = document.getElementById(`eye-${id}`);
      const eyeOff = document.getElementById(`eye-${id}-off`);

      if (input.type === "password") {
        input.type = "text";
        eye.classList.replace("opacity-100", "opacity-0");
        eye.classList.replace("scale-100", "scale-90");
        eyeOff.classList.replace("opacity-0", "opacity-100");
        eyeOff.classList.replace("scale-90", "scale-100");
      } else {
        input.type = "password";
        eye.classList.replace("opacity-0", "opacity-100");
        eye.classList.replace("scale-90", "scale-100");
        eyeOff.classList.replace("opacity-100", "opacity-0");
        eyeOff.classList.replace("scale-100", "scale-90");
      }
    }
    document.addEventListener('DOMContentLoaded', function() {
      const errorElement = document.querySelector('.error-message');
      const successElement = document.querySelector('.success-message');
      const container = document.getElementById('loginContainer');
      
      if (errorElement || successElement) {
        void container.offsetWidth;
        container.classList.add(errorElement ? 'with-error' : 'with-success');
      }
    });
  </script>
</body>
</html>
