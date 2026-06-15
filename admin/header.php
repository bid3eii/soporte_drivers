<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/db.php';

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=device-width, initial-scale=1.0">
    <title><?php echo isset($admin_title) ? $admin_title . " - cPanel Soporte Master" : "cPanel - Soporte Master"; ?></title>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Admin styling sheet -->
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-brand">
        <i class="fa-solid fa-gears"></i>
        <span>Soporte<strong>cPanel</strong></span>
    </div>
    <ul class="sidebar-menu">
        <li class="sidebar-item <?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
            <a href="index.php">
                <i class="fa-solid fa-chart-pie"></i>
                <span>Resumen</span>
            </a>
        </li>
        <li class="sidebar-item <?php echo $current_page == 'brands.php' ? 'active' : ''; ?>">
            <a href="brands.php">
                <i class="fa-solid fa-copyright"></i>
                <span>Marcas</span>
            </a>
        </li>
        <li class="sidebar-item <?php echo $current_page == 'categories.php' ? 'active' : ''; ?>">
            <a href="categories.php">
                <i class="fa-solid fa-tags"></i>
                <span>Categorías</span>
            </a>
        </li>
        <li class="sidebar-item <?php echo $current_page == 'equipment.php' ? 'active' : ''; ?>">
            <a href="equipment.php">
                <i class="fa-solid fa-laptop"></i>
                <span>Equipos (Modelos)</span>
            </a>
        </li>
        <li class="sidebar-item <?php echo $current_page == 'drivers.php' ? 'active' : ''; ?>">
            <a href="drivers.php">
                <i class="fa-solid fa-cloud-arrow-down"></i>
                <span>Controladores</span>
            </a>
        </li>
    </ul>
    <div class="sidebar-footer">
        <div style="font-size: 11px; color: var(--text-secondary); margin-bottom: 10px; padding-left: 10px;">
            <i class="fa-solid fa-user-shield"></i> <?php echo htmlspecialchars($_SESSION['admin_fullname']); ?>
        </div>
        <a href="logout.php" class="btn-logout">
            <i class="fa-solid fa-right-from-bracket"></i>
            <span>Cerrar Sesión</span>
        </a>
    </div>
</aside>

<div class="admin-container">
    <div class="admin-header">
        <div class="admin-title">
            <h1><?php echo isset($admin_heading) ? $admin_heading : "Panel de Administración"; ?></h1>
            <p><?php echo isset($admin_subheading) ? $admin_subheading : "Administra las marcas, equipos y controladores del portal público."; ?></p>
        </div>
        <div>
            <a href="../index.php" target="_blank" class="btn-primary" style="background: rgba(255,255,255,0.05); border: 1px solid var(--border); color: var(--text-primary);">
                <i class="fa-solid fa-square-arrow-up-right"></i> Ver Sitio Web
            </a>
        </div>
    </div>
