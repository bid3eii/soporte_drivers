<?php
// PHP session starts for possible messaging or user details
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fetch site settings
$site_settings = [];
if (isset($pdo)) {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
    while ($row = $stmt->fetch()) {
        $site_settings[$row['setting_key']] = $row['setting_value'];
    }
}
$site_name = isset($site_settings['site_name']) ? $site_settings['site_name'] : 'SoporteMaster';
$site_logo = isset($site_settings['site_logo']) ? $site_settings['site_logo'] : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . " - " . htmlspecialchars($site_name) : htmlspecialchars($site_name) . " - Descarga de Drivers"; ?></title>
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Main Custom CSS -->
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
</head>
<body>

<header>
    <div class="container navbar">
        <a href="index.php" class="logo" style="display: flex; align-items: center; gap: 10px;">
            <?php if (!empty($site_logo) && file_exists(__DIR__ . '/../' . $site_logo)): ?>
                <img src="<?php echo htmlspecialchars($site_logo); ?>" alt="Logo" style="height: 75px; max-width: 100%; object-fit: contain;">
            <?php else: ?>
                <i class="fa-solid fa-laptop-code"></i>
            <?php endif; ?>
            <?php if (!empty($site_name)): ?>
                <span><?php echo htmlspecialchars($site_name); ?></span>
            <?php endif; ?>
        </a>

        <!-- Sticky Search Bar (Aparece al hacer scroll) -->
        <form action="index.php" method="GET" class="nav-search-form" id="navSearchForm">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" name="q" placeholder="Buscar controladores, equipos, marcas..." required>
        </form>
        <nav class="nav-links">
            <a href="index.php#fabricantes" class="nav-link">
                <i class="fa-solid fa-tags"></i> Marcas
            </a>
            <a href="index.php#catalogo" class="nav-link">
                <i class="fa-solid fa-laptop"></i> Equipos
            </a>
            <a href="index.php#actualizaciones" class="nav-link">
                <i class="fa-solid fa-clock-rotate-left"></i> Novedades
            </a>
        </nav>
    </div>
</header>

<script>
// Script para mostrar/ocultar el buscador del nav al hacer scroll
document.addEventListener('DOMContentLoaded', () => {
    const header = document.querySelector('header');
    
    // Función para revisar el scroll
    const checkScroll = () => {
        if (window.scrollY > 300) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    };
    
    // Revisar al cargar por si recargan a mitad de página
    checkScroll();
    
    // Revisar al hacer scroll
    window.addEventListener('scroll', checkScroll);
});
</script>
