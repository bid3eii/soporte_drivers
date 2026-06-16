<?php
$page_title = "Inicio - Descarga de Drivers";
$active_tab = "home";

// Incluir conexión a base de datos
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';

// Obtener parámetros de búsqueda y filtrado
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$selected_brand_id = isset($_GET['brand_id']) ? intval($_GET['brand_id']) : 0;

$is_filtering = (!empty($search_query) || $selected_brand_id > 0);

// Construir la consulta para Equipment
$sql = "SELECT e.*, b.name AS brand_name 
        FROM equipment e 
        JOIN brands b ON e.brand_id = b.id";
$params = [];
$where_clauses = [];

if ($selected_brand_id > 0) {
    $where_clauses[] = "e.brand_id = ?";
    $params[] = $selected_brand_id;
}

if (!empty($search_query)) {
    $where_clauses[] = "(e.model_name LIKE ? OR e.description LIKE ? OR b.name LIKE ?)";
    $search_param = "%" . $search_query . "%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql .= " ORDER BY e.created_at DESC";

// Si no hay filtros aplicados, mostrar solo los 3 más recientes
if (!$is_filtering) {
    $sql .= " LIMIT 3";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$equipments = $stmt->fetchAll();

// Obtener todas las marcas
$brands_stmt = $pdo->query("SELECT b.*, (SELECT COUNT(*) FROM equipment WHERE brand_id = b.id) AS eq_count FROM brands b ORDER BY b.name ASC");
$brands = $brands_stmt->fetchAll();

// Obtener los 3 últimos controladores cargados para la sección de actualizaciones
$recent_drivers_stmt = $pdo->query("SELECT d.*, e.model_name, b.name AS brand_name, c.name AS category_name, c.icon_class
                                    FROM drivers d
                                    JOIN equipment e ON d.equipment_id = e.id
                                    JOIN brands b ON e.brand_id = b.id
                                    JOIN categories c ON d.category_id = c.id
                                    ORDER BY d.uploaded_at DESC
                                    LIMIT 3");
$recent_drivers = $recent_drivers_stmt->fetchAll();
?>

<?php
$hero_title = isset($site_settings['hero_title']) ? $site_settings['hero_title'] : 'Identifica tu Producto';
$hero_subtitle = isset($site_settings['hero_subtitle']) ? $site_settings['hero_subtitle'] : 'Busca tu equipo o componente para encontrar actualizaciones de controladores y software oficial.';
$quick_1 = isset($site_settings['quick_1']) ? $site_settings['quick_1'] : 'Laptops';
$quick_2 = isset($site_settings['quick_2']) ? $site_settings['quick_2'] : 'Desktops';
$quick_3 = isset($site_settings['quick_3']) ? $site_settings['quick_3'] : 'Impresoras';
$quick_4 = isset($site_settings['quick_4']) ? $site_settings['quick_4'] : 'Red';
$quick_5 = isset($site_settings['quick_5']) ? $site_settings['quick_5'] : 'Gráficos';

$quick_1_icon = isset($site_settings['quick_1_icon']) ? $site_settings['quick_1_icon'] : 'fa-laptop';
$quick_2_icon = isset($site_settings['quick_2_icon']) ? $site_settings['quick_2_icon'] : 'fa-desktop';
$quick_3_icon = isset($site_settings['quick_3_icon']) ? $site_settings['quick_3_icon'] : 'fa-print';
$quick_4_icon = isset($site_settings['quick_4_icon']) ? $site_settings['quick_4_icon'] : 'fa-network-wired';
$quick_5_icon = isset($site_settings['quick_5_icon']) ? $site_settings['quick_5_icon'] : 'fa-microchip';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <h1><?php echo htmlspecialchars($hero_title); ?></h1>
        <p><?php echo htmlspecialchars($hero_subtitle); ?></p>
        
        <div class="search-container">
            <form action="index.php" method="GET" class="search-form">
                <?php if ($selected_brand_id > 0): ?>
                    <input type="hidden" name="brand_id" value="<?php echo $selected_brand_id; ?>">
                <?php endif; ?>
                <input type="text" name="q" class="search-input" placeholder="Ej: HP Pavilion 15, Dell Inspiron, Intel Wi-Fi..." value="<?php echo htmlspecialchars($search_query); ?>" required>
                <button type="submit" class="search-btn">
                    <i class="fa-solid fa-magnifying-glass"></i> Buscar
                </button>
            </form>
        </div>

        <div class="quick-links">
            <a href="index.php?q=<?php echo urlencode($quick_1); ?>" class="quick-link-btn"><i class="fa-solid <?php echo htmlspecialchars($quick_1_icon); ?>"></i> <?php echo htmlspecialchars($quick_1); ?></a>
            <a href="index.php?q=<?php echo urlencode($quick_2); ?>" class="quick-link-btn"><i class="fa-solid <?php echo htmlspecialchars($quick_2_icon); ?>"></i> <?php echo htmlspecialchars($quick_2); ?></a>
            <a href="index.php?q=<?php echo urlencode($quick_3); ?>" class="quick-link-btn"><i class="fa-solid <?php echo htmlspecialchars($quick_3_icon); ?>"></i> <?php echo htmlspecialchars($quick_3); ?></a>
            <a href="index.php?q=<?php echo urlencode($quick_4); ?>" class="quick-link-btn"><i class="fa-solid <?php echo htmlspecialchars($quick_4_icon); ?>"></i> <?php echo htmlspecialchars($quick_4); ?></a>
            <a href="index.php?q=<?php echo urlencode($quick_5); ?>" class="quick-link-btn"><i class="fa-solid <?php echo htmlspecialchars($quick_5_icon); ?>"></i> <?php echo htmlspecialchars($quick_5); ?></a>
        </div>
    </div>
</section>

<main class="container" style="padding-bottom: 60px;">

    <?php if (!$is_filtering): ?>
        <!-- SECCIÓN INFORMATIVA: Banner de Características -->
        <section class="info-section" style="padding-top: 40px; padding-bottom: 20px; border-bottom: none;">
            <div class="info-banner">
                <div class="info-banner-item">
                    <div class="info-banner-icon"><i class="fa-solid fa-shield-halved"></i></div>
                    <div class="info-banner-text">
                        <h4>Descargas Seguras</h4>
                        <p>Libres de malware garantizado.</p>
                    </div>
                </div>
                <div class="info-banner-item">
                    <div class="info-banner-icon"><i class="fa-solid fa-gauge-high"></i></div>
                    <div class="info-banner-text">
                        <h4>Velocidad Máxima</h4>
                        <p>Servidores sin publicidad.</p>
                    </div>
                </div>
                <div class="info-banner-item">
                    <div class="info-banner-icon"><i class="fa-solid fa-circle-check"></i></div>
                    <div class="info-banner-text">
                        <h4>Compatibilidad</h4>
                        <p>Filtros por sistema operativo.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECCIÓN INFORMATIVA: Cómo Instalar Drivers (Paso a Paso) -->
        <section class="info-section" style="padding-top: 20px;">
            <h2 class="section-title"><span></span> Guía de Instalación en 3 Pasos</h2>
            <div class="steps-grid">
                <div class="step-card">
                    <span class="step-number">01</span>
                    <span class="step-badge">Paso 1</span>
                    <h3>Busca tu Dispositivo</h3>
                    <p>Escribe el modelo exacto de tu equipo en la barra superior o filtra por tu marca correspondiente.</p>
                </div>
                
                <div class="step-card">
                    <span class="step-number">02</span>
                    <span class="step-badge">Paso 2</span>
                    <h3>Filtra tu Sistema</h3>
                    <p>Selecciona tu versión del sistema operativo y haz clic en el botón de descargar del driver requerido.</p>
                </div>
                
                <div class="step-card">
                    <span class="step-number">03</span>
                    <span class="step-badge">Paso 3</span>
                    <h3>Descomprime e Instala</h3>
                    <p>Abre el archivo descargado, extrae su contenido y ejecuta el instalador (generalmente <code>setup.exe</code>).</p>
                </div>
            </div>
        </section>
    <!-- SECCIÓN: Filtrar por Marca (Siempre útil) -->
    <section id="fabricantes" class="info-section" style="padding-bottom: 30px;">
        <h2 class="section-title"><span></span> Directorio de Fabricantes</h2>
        
        <div style="position: relative;">
            <div id="brandsGrid" class="brands-grid" style="padding-top: 10px; margin-top: -10px; <?php echo (!$is_filtering && count($brands) > 4) ? 'max-height: 230px; overflow: hidden; transition: max-height 0.25s ease;' : ''; ?>">
                <a href="index.php<?php echo !empty($search_query) ? '?q=' . urlencode($search_query) : ''; ?>" class="brand-card" style="<?php echo $selected_brand_id === 0 ? 'border-color: var(--primary); background: rgba(79, 70, 229, 0.08);' : ''; ?>">
                    <div class="brand-logo-container">
                        <div class="brand-logo-placeholder">
                            <i class="fa-solid fa-border-all"></i>
                        </div>
                    </div>
                    <span class="brand-name">Todas las Marcas</span>
                </a>

                <?php foreach ($brands as $brand): ?>
                    <a href="index.php?brand_id=<?php echo $brand['id']; ?><?php echo !empty($search_query) ? '&q=' . urlencode($search_query) : ''; ?>" class="brand-card" style="<?php echo $selected_brand_id === $brand['id'] ? 'border-color: var(--primary); background: rgba(79, 70, 229, 0.08);' : ''; ?>">
                        <div class="brand-logo-container">
                            <?php if (!empty($brand['logo_url']) && file_exists(__DIR__ . '/' . $brand['logo_url'])): ?>
                                <img src="<?php echo htmlspecialchars($brand['logo_url']); ?>" alt="<?php echo htmlspecialchars($brand['name']); ?>" class="brand-logo-img">
                            <?php else: ?>
                                <div class="brand-logo-placeholder">
                                    <?php echo htmlspecialchars(substr($brand['name'], 0, 2)); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <span class="brand-name"><?php echo htmlspecialchars($brand['name']); ?> (<?php echo $brand['eq_count']; ?>)</span>
                    </a>
                <?php endforeach; ?>
            </div>

            <?php if (!$is_filtering && count($brands) > 4): ?>
                <div id="brandsFade" style="position: absolute; bottom: 0; left: 0; right: 0; height: 100px; background: linear-gradient(to bottom, transparent, var(--bg-main)); pointer-events: none;"></div>
            <?php endif; ?>
        </div>

        <?php if (!$is_filtering && count($brands) > 4): ?>
            <div id="brandsExpandBtn" style="text-align: center; margin-top: 15px;">
                <button onclick="toggleBrands()" class="bouncing-arrow" style="background: none; border: none; cursor: pointer; padding: 10px;" title="Ver todas las marcas">
                    <i id="brandsToggleIcon" class="fa-solid fa-angles-down"></i>
                </button>
            </div>
            <script>
            function toggleBrands() {
                var grid = document.getElementById('brandsGrid');
                var fade = document.getElementById('brandsFade');
                var icon = document.getElementById('brandsToggleIcon');
                var sectionTitle = grid.parentElement.parentElement; // The section container
                
                if (grid.style.maxHeight === '5000px') {
                    // Colapsar
                    grid.style.maxHeight = '230px';
                    fade.style.display = 'block';
                    icon.className = 'fa-solid fa-angles-down';
                    
                    // Hacer scroll suave hacia el título de la sección
                    setTimeout(function() {
                        sectionTitle.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }, 50); // Pequeño retraso para que el navegador registre el cambio de altura
                    
                } else {
                    // Expandir
                    grid.style.maxHeight = '5000px';
                    fade.style.display = 'none';
                    icon.className = 'fa-solid fa-angles-up';
                }
            }
            </script>
        <?php endif; ?>

    </section>
    <?php endif; ?>

    <!-- SECCIÓN: Equipos / Modelos -->
    <section id="catalogo" class="info-section">
        <div class="flex-row-between" style="margin-bottom: 24px;">
            <h2 class="section-title" style="margin-bottom: 0;">
                <span></span> 
                <?php 
                if ($selected_brand_id > 0) {
                    $selected_brand_name = '';
                    foreach ($brands as $b) {
                        if ($b['id'] == $selected_brand_id) {
                            $selected_brand_name = $b['name'];
                            break;
                        }
                    }
                    echo "Modelos de " . htmlspecialchars($selected_brand_name);
                } else {
                    echo "Catálogo de Equipos";
                }
                ?>
            </h2>
            <?php if ($is_filtering): ?>
                <a href="index.php" class="btn-clear-search">
                    <i class="fa-solid fa-xmark"></i> Limpiar Búsqueda
                </a>
            <?php endif; ?>
        </div>

        <?php if (empty($equipments)): ?>
            <div class="no-drivers">
                <i class="fa-solid fa-computer"></i>
                <h3>No se encontraron equipos</h3>
                <p>Prueba buscando con otro término o marca, o limpia los filtros activos.</p>
            </div>
        <?php else: ?>
            <div class="equipment-grid">
                <?php foreach ($equipments as $eq): 
                    $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM drivers WHERE equipment_id = ?");
                    $count_stmt->execute([$eq['id']]);
                    $drivers_count = $count_stmt->fetchColumn();
                ?>
                    <a href="equipment.php?id=<?php echo $eq['id']; ?>" class="equipment-card" style="display: flex; text-decoration: none; color: inherit;">
                        <div class="equipment-img-container">
                            <span class="equipment-badge"><?php echo htmlspecialchars($eq['brand_name']); ?></span>
                            <?php if (!empty($eq['image_url']) && file_exists(__DIR__ . '/' . $eq['image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($eq['image_url']); ?>" alt="<?php echo htmlspecialchars($eq['model_name']); ?>" class="equipment-img">
                            <?php else: ?>
                                <i class="fa-solid fa-laptop" style="font-size: 54px; color: var(--text-muted);"></i>
                            <?php endif; ?>
                        </div>
                        <div class="equipment-info">
                            <h3 class="equipment-title"><?php echo htmlspecialchars($eq['model_name']); ?></h3>
                            <p class="equipment-desc"><?php echo htmlspecialchars(mb_strimwidth($eq['description'], 0, 110, "...")); ?></p>
                            <div class="equipment-footer">
                                <span class="equipment-drivers-count">
                                    <i class="fa-solid fa-cloud-arrow-down"></i> <?php echo $drivers_count; ?> Controladores
                                </span>
                                <span class="btn-view">
                                    Ver Drivers <i class="fa-solid fa-arrow-right"></i>
                                </span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <?php if (!$is_filtering): ?>
        <!-- SECCIÓN: Últimos Drivers Subidos (Novedades) -->
        <section id="actualizaciones" class="info-section" style="padding-bottom: 30px;">
            <h2 class="section-title"><span></span> Últimas Actualizaciones</h2>
            <p style="color: var(--text-muted); font-size: 14px; margin-top: -10px; margin-bottom: 20px;">Controladores agregados recientemente a nuestra plataforma.</p>
            
            <div class="timeline">
                <?php if (empty($recent_drivers)): ?>
                    <div style="text-align: center; color: var(--text-secondary); padding: 20px;">
                        No hay actualizaciones recientes.
                    </div>
                <?php else: ?>
                    <?php foreach ($recent_drivers as $r_dr): ?>
                        <div class="timeline-item">
                            <div class="timeline-icon">
                                <i class="fa-solid <?php echo htmlspecialchars($r_dr['icon_class']); ?>"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-info">
                                    <h4><?php echo htmlspecialchars($r_dr['name']); ?></h4>
                                    <p>
                                        Para: <strong><?php echo htmlspecialchars($r_dr['brand_name'] . ' ' . $r_dr['model_name']); ?></strong> 
                                        • S.O: <?php echo htmlspecialchars($r_dr['os']); ?>
                                    </p>
                                </div>
                                <a href="equipment.php?id=<?php echo $r_dr['equipment_id']; ?>" class="btn-view" style="font-size: 13px; font-weight: 600;">
                                    Detalles <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <?php if (!empty($recent_drivers)): ?>
            <div style="text-align: center; margin-top: 10px;">
                <a href="updates.php" class="bouncing-arrow" title="Ver todas las actualizaciones">
                    <i class="fa-solid fa-angles-down"></i>
                </a>
            </div>
            <?php endif; ?>
        </section>


        <!-- SECCIÓN: Preguntas Frecuentes (FAQ) -->
        <section class="info-section" style="border-bottom: none; padding-top: 20px;">
            <h2 class="section-title"><span></span> Preguntas Frecuentes</h2>
            
            <?php
            $faqs_stmt = $pdo->query("SELECT * FROM faqs ORDER BY order_index ASC, id ASC");
            $all_faqs = $faqs_stmt->fetchAll();
            ?>
            
            <div class="faq-grid">
                <?php if (empty($all_faqs)): ?>
                    <div style="text-align: center; grid-column: 1 / -1; color: var(--text-muted); padding: 20px;">
                        No hay preguntas frecuentes disponibles en este momento.
                    </div>
                <?php else: ?>
                    <?php foreach ($all_faqs as $faq): ?>
                        <div class="faq-card">
                            <div class="faq-question">
                                <i class="fa-solid fa-circle-question"></i>
                                <span><?php echo htmlspecialchars($faq['question']); ?></span>
                            </div>
                            <div class="faq-answer">
                                <?php echo nl2br(htmlspecialchars($faq['answer'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
