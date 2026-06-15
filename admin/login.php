<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

// Si ya está logueado, ir directo al panel
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        // Buscar el administrador
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            // Iniciar sesión exitosamente
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_fullname'] = $admin['fullname'];
            
            header("Location: index.php");
            exit;
        } else {
            $error = 'Usuario o contraseña incorrectos.';
        }
    } else {
        $error = 'Por favor complete todos los campos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=device-width, initial-scale=1.0">
    <title>Acceso cPanel - Soporte Master</title>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Admin Styling Sheet -->
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body class="login-body">

<div class="login-card">
    <div class="login-logo">
        <i class="fa-solid fa-gears"></i> Soporte<strong>cPanel</strong>
    </div>
    <div class="login-subtitle">Introduce tus credenciales para administrar la plataforma</div>

    <?php if (!empty($error)): ?>
        <div class="alert danger">
            <i class="fa-solid fa-circle-exclamation mr-2"></i> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form action="login.php" method="POST" class="login-form">
        <div class="admin-form-group">
            <label for="username" class="admin-form-label">Usuario</label>
            <input type="text" name="username" id="username" class="admin-form-input" placeholder="Ej: admin" required autofocus>
        </div>
        
        <div class="admin-form-group">
            <label for="password" class="admin-form-label">Contraseña</label>
            <input type="password" name="password" id="password" class="admin-form-input" placeholder="••••••••" required>
        </div>

        <button type="submit" class="btn-primary btn-login">
            <i class="fa-solid fa-right-to-bracket"></i> Iniciar Sesión
        </button>
    </form>
    
    <div style="margin-top: 24px; font-size: 13px;">
        <a href="../index.php" style="color: var(--text-secondary); text-decoration: underline;">
            <i class="fa-solid fa-arrow-left"></i> Volver a la web pública
        </a>
    </div>
</div>

</body>
</html>
