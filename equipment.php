<?php
require_once __DIR__ . '/includes/db.php';

// Validar ID de equipo
$equipment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($equipment_id <= 0) {
    header("Location: index.php");
    exit;
}

// Obtener datos del equipo
$eq_stmt = $pdo->prepare("SELECT e.*, b.name AS brand_name 
                          FROM equipment e 
                          JOIN brands b ON e.brand_id = b.id 
                          WHERE e.id = ?");
$eq_stmt->execute([$equipment_id]);
$equipment = $eq_stmt->fetch();

if (!$equipment) {
    header("Location: index.php");
    exit;
}

$page_title = "Drivers " . $equipment['brand_name'] . " " . $equipment['model_name'];
require_once __DIR__ . '/includes/header.php';

// Obtener filtros seleccionados
$selected_category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
$selected_os = isset($_GET['os']) ? trim($_GET['os']) : '';

// 1. Obtener Sistemas Operativos únicos para este equipo
$os_stmt = $pdo->prepare("SELECT DISTINCT os FROM drivers WHERE equipment_id = ? ORDER BY os ASC");
$os_stmt->execute([$equipment_id]);
$available_os = $os_stmt->fetchAll(PDO::FETCH_COLUMN);

// 2. Obtener Categorías únicas que tienen drivers para este equipo
$cat_stmt = $pdo->prepare("SELECT DISTINCT c.id, c.name, c.icon_class 
                           FROM categories c 
                           JOIN drivers d ON d.category_id = c.id 
                           WHERE d.equipment_id = ? 
                           ORDER BY c.name ASC");
$cat_stmt->execute([$equipment_id]);
$available_categories = $cat_stmt->fetchAll();

// 3. Construir la consulta de drivers filtrados
$sql = "SELECT d.*, c.name AS category_name, c.icon_class 
        FROM drivers d 
        JOIN categories c ON d.category_id = c.id 
        WHERE d.equipment_id = ?";
$params = [$equipment_id];

if ($selected_category_id > 0) {
    $sql .= " AND d.category_id = ?";
    $params[] = $selected_category_id;
}

if (!empty($selected_os)) {
    $sql .= " AND d.os = ?";
    $params[] = $selected_os;
}

$sql .= " ORDER BY c.name ASC, d.name ASC";
$drivers_stmt = $pdo->prepare($sql);
$drivers_stmt->execute($params);
$drivers = $drivers_stmt->fetchAll();
?>

<!-- Equipment Banner -->
<section class="eq-banner">
    <div class="container eq-banner-content">
        <div class="eq-banner-img">
            <?php if (!empty($equipment['image_url']) && file_exists(__DIR__ . '/' . $equipment['image_url'])): ?>
                <img src="<?php echo htmlspecialchars($equipment['image_url']); ?>" alt="<?php echo htmlspecialchars($equipment['model_name']); ?>">
            <?php else: ?>
                <i class="fa-solid fa-laptop" style="font-size: 72px; color: var(--text-muted);"></i>
            <?php endif; ?>
        </div>
        <div class="eq-banner-info">
            <span class="brand-tag"><?php echo htmlspecialchars($equipment['brand_name']); ?></span>
            <h1><?php echo htmlspecialchars($equipment['model_name']); ?></h1>
            <p><?php echo htmlspecialchars($equipment['description']); ?></p>
        </div>
    </div>
</section>

<div class="container driver-page-container">
    <!-- Sidebar Filters -->
    <aside class="filter-sidebar">
        <h3 class="filter-title">
            <i class="fa-solid fa-filter"></i> Filtrar Controladores
        </h3>

        <!-- Categorías -->
        <div class="filter-group">
            <span class="filter-group-label">Por Componente</span>
            <div class="filter-options">
                <a href="equipment.php?id=<?php echo $equipment_id; ?><?php echo !empty($selected_os) ? '&os=' . urlencode($selected_os) : ''; ?>" 
                   class="filter-option <?php echo $selected_category_id === 0 ? 'active' : ''; ?>">
                    <span><i class="fa-solid fa-folder-open"></i> Todos</span>
                </a>
                <?php foreach ($available_categories as $cat): 
                    // Contar drivers para este equipo en esta categoría
                    $c_count_stmt = $pdo->prepare("SELECT COUNT(*) FROM drivers WHERE equipment_id = ? AND category_id = ?");
                    $c_count_stmt->execute([$equipment_id, $cat['id']]);
                    $cat_count = $c_count_stmt->fetchColumn();
                ?>
                    <a href="equipment.php?id=<?php echo $equipment_id; ?>&category_id=<?php echo $cat['id']; ?><?php echo !empty($selected_os) ? '&os=' . urlencode($selected_os) : ''; ?>" 
                       class="filter-option <?php echo $selected_category_id === $cat['id'] ? 'active' : ''; ?>">
                        <span><i class="fa-solid <?php echo htmlspecialchars($cat['icon_class']); ?>"></i> <?php echo htmlspecialchars($cat['name']); ?></span>
                        <span class="filter-count"><?php echo $cat_count; ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Sistemas Operativos -->
        <div class="filter-group">
            <span class="filter-group-label">Por Sistema Operativo</span>
            <div class="filter-options">
                <a href="equipment.php?id=<?php echo $equipment_id; ?><?php echo $selected_category_id > 0 ? '&category_id=' . $selected_category_id : ''; ?>" 
                   class="filter-option <?php echo empty($selected_os) ? 'active' : ''; ?>">
                    <span><i class="fa-solid fa-microchip"></i> Todos los S.O.</span>
                </a>
                <?php foreach ($available_os as $os_name): 
                    // Contar drivers para este equipo con este OS
                    $os_count_stmt = $pdo->prepare("SELECT COUNT(*) FROM drivers WHERE equipment_id = ? AND os = ?");
                    $os_count_stmt->execute([$equipment_id, $os_name]);
                    $os_count = $os_count_stmt->fetchColumn();
                ?>
                    <a href="equipment.php?id=<?php echo $equipment_id; ?><?php echo $selected_category_id > 0 ? '&category_id=' . $selected_category_id : ''; ?>&os=<?php echo urlencode($os_name); ?>" 
                       class="filter-option <?php echo $selected_os === $os_name ? 'active' : ''; ?>">
                        <span><i class="fa-brands fa-windows"></i> <?php echo htmlspecialchars($os_name); ?></span>
                        <span class="filter-count"><?php echo $os_count; ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if ($selected_category_id > 0 || !empty($selected_os)): ?>
            <a href="equipment.php?id=<?php echo $equipment_id; ?>" class="btn-download" style="width: 100%; justify-content: center; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: var(--text-main);">
                Limpiar Filtros
            </a>
        <?php endif; ?>
    </aside>

    <!-- Drivers List -->
    <main>
        <div class="flex-row-between" style="margin-bottom: 20px;">
            <h2 style="font-size: 22px; font-weight: 700;">Drivers Disponibles (<?php echo count($drivers); ?>)</h2>
        </div>

        <?php if (empty($drivers)): ?>
            <div class="no-drivers">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <h3>No hay drivers para mostrar</h3>
                <p>No se encontraron controladores cargados bajo los criterios de filtrado seleccionados.</p>
            </div>
        <?php else: ?>
            <div class="drivers-list">
                <?php foreach ($drivers as $dr): ?>
                    <div class="driver-item-card">
                        <div class="driver-item-icon">
                            <i class="fa-solid <?php echo htmlspecialchars($dr['icon_class']); ?>"></i>
                        </div>
                        <div class="driver-item-info">
                            <h3 class="driver-item-name"><?php echo htmlspecialchars($dr['name']); ?></h3>
                            <div class="driver-item-meta">
                                <span><i class="fa-solid fa-code-branch"></i> Versión: <?php echo htmlspecialchars($dr['version']); ?></span>
                                <span><i class="fa-brands fa-windows"></i> S.O.: <?php echo htmlspecialchars($dr['os']); ?></span>
                                <span><i class="fa-solid fa-weight-hanging"></i> Peso: <?php echo htmlspecialchars($dr['file_size']); ?></span>
                                <span><i class="fa-solid fa-eye"></i> Descargas: <?php echo $dr['download_count']; ?></span>
                            </div>
                        </div>
                        <div>
                            <!-- Botón que apunta al manejador de descargas -->
                            <a href="download.php?id=<?php echo $dr['id']; ?>" class="btn-download" target="_blank">
                                <i class="fa-solid fa-cloud-arrow-down"></i> Descargar
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
