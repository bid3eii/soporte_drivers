<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/db.php';

$current_page = basename($_SERVER['PHP_SELF']);

// Obtener logo y nombre para el cPanel
$stmt_hdr = $pdo->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('site_name', 'site_logo')");
$hdr_settings = [];
while ($row = $stmt_hdr->fetch()) {
    $hdr_settings[$row['setting_key']] = $row['setting_value'];
}
$hdr_site_name = !empty($hdr_settings['site_name']) ? $hdr_settings['site_name'] : 'cPanel';
$hdr_site_logo = !empty($hdr_settings['site_logo']) ? $hdr_settings['site_logo'] : '';

// Flash messages para redirecciones POST (PRG pattern)
if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    $message_type = $_SESSION['flash_type'];
    unset($_SESSION['flash_message']);
    unset($_SESSION['flash_type']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($admin_title) ? $admin_title . " - " . htmlspecialchars($hdr_site_name) : htmlspecialchars($hdr_site_name) . " - cPanel"; ?></title>
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="../favicon.svg">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Admin styling sheet -->
    <link rel="stylesheet" href="../css/admin.css?v=<?php echo time(); ?>">
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-brand" style="display: flex; justify-content: center; align-items: center; padding: 20px 0; border-bottom: 1px solid rgba(255,255,255,0.05); margin-bottom: 10px;">
        <?php if (!empty($hdr_site_logo) && file_exists(__DIR__ . '/../' . $hdr_site_logo)): ?>
            <img src="../<?php echo htmlspecialchars($hdr_site_logo); ?>" alt="Logo" style="max-height: 60px; max-width: 85%; object-fit: contain;">
        <?php else: ?>
            <i class="fa-solid fa-gears" style="color: var(--primary);"></i>
            <span style="margin-left: 10px;"><?php echo htmlspecialchars($hdr_site_name); ?></span>
        <?php endif; ?>
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
        <li style="margin: 15px 0 5px 15px; font-size: 11px; text-transform: uppercase; color: var(--text-muted); font-weight: 800; letter-spacing: 1px;">Configuración</li>
        <li class="sidebar-item <?php echo $current_page == 'settings.php' ? 'active' : ''; ?>">
            <a href="settings.php">
                <i class="fa-solid fa-sliders"></i>
                <span>Ajustes del Sitio</span>
            </a>
        </li>
        <li class="sidebar-item <?php echo $current_page == 'faqs.php' ? 'active' : ''; ?>">
            <a href="faqs.php">
                <i class="fa-solid fa-circle-question"></i>
                <span>Preguntas Frecuentes</span>
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
