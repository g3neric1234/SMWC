<?php
// error.php - Página única para todos los errores

// Obtener el código de error
$error_code = isset($_GET['code']) ? (int)$_GET['code'] : http_response_code();

// Validar códigos permitidos
$allowed_codes = [400, 401, 403, 404, 405, 408, 429, 500, 502, 503, 504, 1234];
if (!in_array($error_code, $allowed_codes)) {
    $error_code = 404;
}

// Configurar encabezado HTTP
http_response_code($error_code);
header("HTTP/1.0 $error_code");

// Mensajes personalizados
$error_messages = [
    400 => ['Solicitud incorrecta', 'El servidor no puede procesar tu solicitud'],
    401 => ['Acceso no autorizado', 'Debes iniciar sesión para acceder'],
    403 => ['Prohibido', 'No tienes permisos para este contenido'],
    404 => ['Página no encontrada', 'La URL solicitada no existe'],
    500 => ['Error del servidor', 'Estamos trabajando para solucionarlo'],
    1234 => ['nose que putas paso', 'ahora cuando me de ganas arreglo esto'],
    503 => ['En mantenimiento', 'Volveremos pronto']
];

$error = $error_messages[$error_code] ?? ['Error', 'Ocurrió un problema inesperado'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error <?= $error_code ?> | <?= $error[0] ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="../images/head.ico" type="image/ico">
    <style>
        body {
            background: #0d0d0d;
            color: white;
            font-family: sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }
        .error-card {
            width: 100%;
            max-width: 600px;
            text-align: center;
            animation: fadeIn 0.6s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .error-code {
            font-size: 6rem;
            font-weight: bold;
            margin-bottom: 1rem;
            color: <?= match(true) {
                $error_code >= 500 => '#ef4444',
                $error_code == 429 => '#f59e0b',
                default => '#9333ea'
            } ?>;
        }
        .error-actions {
            margin-top: 2rem;
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            transition: all 0.2s;
        }
        .btn-primary {
            background: #9333ea;
            color: white;
        }
        .btn-primary:hover {
            background: #7e22ce;
            transform: translateY(-2px);
        }
        .btn-secondary {
            background: #1f2937;
            color: white;
        }
        .btn-secondary:hover {
            background: #111827;
        }
        .error-details {
            margin-top: 2rem;
            font-size: 0.875rem;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-code"><?= $error_code ?></div>
        <h1 class="text-3xl font-bold mb-2"><?= $error[0] ?></h1>
        <p class="text-gray-400 text-lg"><?= $error[1] ?></p>
        
        <div class="error-actions">
            <a href="/smwc/menu.php" class="btn btn-primary">Inicio</a>
            <?php if(in_array($error_code, [401, 403])): ?>
                <a href="../login.php" class="btn btn-secondary">Iniciar sesión</a>
            <?php endif; ?>
        </div>
        
        <div class="error-details">
            <p>URI solicitada: <?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '') ?></p>
            <?php if($error_code == 503): ?>
                <p class="mt-1">Tiempo estimado: 30 minutos</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>