<?php
// Detectar entorno (Local vs Producción)
$is_local = in_array($_SERVER['HTTP_HOST'] ?? 'localhost', ['localhost', '127.0.0.1', '::1']);

if ($is_local) {
    // Configuración de base de datos para XAMPP (Local)
    $db_host = 'localhost';
    $db_user = 'root';
    $db_pass = '';
    $db_name = 'soporte_master_db';
} else {
    // Configuración para InfinityFree (Producción)
    // NOTA: Reemplaza "sqlXXX.infinityfree.com" y "if0_42409574_nombrebd" por tus datos reales
    $db_host = 'sqlXXX.infinityfree.com'; // El MySQL Hostname que te muestra InfinityFree
    $db_user = 'if0_42409574';           // El MySQL Username
    $db_pass = '0BmRHIIdj22Nug';         // La contraseña (misma que el FTP)
    $db_name = 'if0_42409574_nombrebd';  // El nombre de la BD creada en el panel
}

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    // Si la base de datos no existe o el servidor está apagado
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=device-width, initial-scale=1.0">
        <title>Error de Configuración - Soporte Master</title>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">
        <style>
            body {
                font-family: 'Outfit', sans-serif;
                background-color: #0b0f19;
                color: #f3f4f6;
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                margin: 0;
                padding: 20px;
            }
            .error-card {
                background: rgba(255, 255, 255, 0.03);
                border: 1px solid rgba(255, 255, 255, 0.08);
                border-radius: 20px;
                padding: 40px;
                max-width: 520px;
                width: 100%;
                text-align: center;
                backdrop-filter: blur(16px);
                box-shadow: 0 30px 60px rgba(0, 0, 0, 0.6);
            }
            .error-icon {
                font-size: 54px;
                color: #f59e0b;
                margin-bottom: 24px;
            }
            h1 {
                margin-top: 0;
                font-size: 26px;
                font-weight: 800;
                background: linear-gradient(135deg, #ffffff, #9ca3af);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                margin-bottom: 12px;
            }
            p {
                color: #9ca3af;
                line-height: 1.6;
                margin-bottom: 24px;
                font-size: 15px;
            }
            .steps {
                text-align: left;
                background: rgba(0, 0, 0, 0.25);
                padding: 24px;
                border-radius: 14px;
                font-size: 14px;
                margin-bottom: 28px;
                border: 1px solid rgba(255, 255, 255, 0.06);
            }
            .steps ol {
                margin: 0;
                padding-left: 20px;
            }
            .steps li {
                margin-bottom: 12px;
                color: #e5e7eb;
                line-height: 1.5;
            }
            .steps li strong {
                color: #ffffff;
            }
            .steps li code {
                background: rgba(255, 255, 255, 0.1);
                padding: 3px 7px;
                border-radius: 5px;
                font-family: monospace;
                color: #60a5fa;
                font-size: 13px;
            }
            .btn {
                display: inline-block;
                background: linear-gradient(135deg, #3b82f6, #4f46e5);
                color: white;
                text-decoration: none;
                padding: 14px 28px;
                border-radius: 10px;
                font-weight: 600;
                transition: all 0.25s ease;
                border: none;
                cursor: pointer;
                box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
            }
            .btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(79, 70, 229, 0.5);
                filter: brightness(1.1);
            }
        </style>
    </head>
    <body>
        <div class="error-card">
            <div class="error-icon">🛠️</div>
            <h1>Configuración de Base de Datos Requerida</h1>
            <p>No logramos conectarnos a la base de datos <strong>soporte_master_db</strong>. Sigue estas instrucciones para dejarlo listo en tu XAMPP:</p>
            <div class="steps">
                <ol>
                    <li>Abre el panel de control de <strong>XAMPP</strong> e inicia los módulos <strong>Apache</strong> y <strong>MySQL</strong>.</li>
                    <li>Ve a tu navegador e ingresa a <strong><a href="http://localhost/phpmyadmin" style="color: #3b82f6; text-decoration: underline;" target="_blank">phpMyAdmin</a></strong>.</li>
                    <li>Crea una nueva base de datos llamada: <code>soporte_master_db</code>.</li>
                    <li>Haz clic en la pestaña <strong>Importar</strong>, selecciona el archivo <code>db.sql</code> de la raíz del proyecto y dale a <strong>Importar / Continuar</strong>.</li>
                </ol>
            </div>
            <a href="" class="btn" onclick="window.location.reload(); return false;">Reintentar Conectar</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}
