<?php
// PHP session starts for possible messaging or user details
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . " - Soporte Master" : "Soporte Master - Descarga de Drivers"; ?></title>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Main Custom CSS -->
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
</head>
<body>

<header>
    <div class="container navbar">
        <a href="index.php" class="logo">
            <i class="fa-solid fa-laptop-code"></i>
            <span>Soporte<strong>Master</strong></span>
        </a>
        <nav class="nav-links">
            <a href="index.php" class="nav-link <?php echo (!isset($active_tab) || $active_tab == 'home') ? 'active' : ''; ?>">
                <i class="fa-solid fa-house"></i> Inicio
            </a>
            <a href="admin/login.php" class="btn-cpanel">
                <i class="fa-solid fa-gears"></i> Control Panel
            </a>
        </nav>
    </div>
</header>
