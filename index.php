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

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <h1>Centro de Controladores Soporte Master</h1>
        <p>Descarga controladores oficiales, seguros y actualizados para tus laptops y computadoras de escritorio. Búsqueda rápida sin registros molestos.</p>
        
        <div class="search-container">
            <form action="index.php" method="GET" class="search-form">
                <?php if ($selected_brand_id > 0): ?>
                    <input type="hidden" name="brand_id" value="<?php echo $selected_brand_id; ?>">
                <?php endif; ?>
                <input type="text" name="q" class="search-input" placeholder="Escribe la marca, modelo de tu equipo o componente..." value="<?php echo htmlspecialchars($search_query); ?>" required>
                <button type="submit" class="search-btn">
                    <i class="fa-solid fa-magnifying-glass"></i> Buscar Drivers
                </button>
            </form>
        </div>
    </div>
</section>

<main class="container" style="padding-bottom: 60px;">

    <?php if (!$is_filtering): ?>
        <!-- SECCIÓN INFORMATIVA: Beneficios / Características del Portal -->
        <section class="info-section">
            <h2 class="section-title"><span></span> ¿Por qué usar Soporte Master?</h2>
            <div class="info-grid">
                <div class="info-card">
                    <div class="info-icon">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <h3>Descargas Seguras</h3>
                    <p>Todos nuestros archivos locales pasan por filtros de seguridad para garantizar que estén libres de malware y software no deseado.</p>
                </div>
                
                <div class="info-card">
                    <div class="info-icon">
                        <i class="fa-solid fa-gauge-high"></i>
                    </div>
                    <h3>Velocidad Máxima</h3>
                    <p>Servimos los archivos directamente desde nuestro servidor de alto rendimiento sin redirecciones publicitarias molestas.</p>
                </div>
                
                <div class="info-card">
                    <div class="info-icon">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <h3>Compatibilidad Verificada</h3>
                    <p>Clasificamos cada controlador según la versión y arquitectura de tu sistema operativo (Windows 10, Windows 11, etc.).</p>
                </div>
            </div>
        </section>

        <!-- SECCIÓN INFORMATIVA: Cómo Instalar Drivers (Paso a Paso) -->
        <section class="info-section">
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
    <?php endif; ?>

    <!-- SECCIÓN: Filtrar por Marca (Siempre útil) -->
    <section class="info-section">
        <h2 class="section-title"><span></span> Directorio de Fabricantes</h2>
        <div class="brands-grid">
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
    </section>

    <!-- SECCIÓN: Equipos / Modelos -->
    <section class="info-section">
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
                <a href="index.php" class="btn-cpanel" style="background: rgba(239, 68, 68, 0.1); border-color: rgba(239, 68, 68, 0.2); color: #ef4444;">
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
                    <div class="equipment-card">
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
                                <a href="equipment.php?id=<?php echo $eq['id']; ?>" class="btn-view">
                                    Ver Drivers <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <?php if (!$is_filtering): ?>
        <!-- SECCIÓN: Últimos Drivers Subidos (Novedades) -->
        <section class="info-section">
            <h2 class="section-title"><span></span> Últimas Actualizaciones</h2>
            <p style="color: var(--text-muted); font-size: 14px; margin-top: -10px; margin-bottom: 20px;">Controladores agregados recientemente a nuestra plataforma.</p>
            
            <div class="recent-drivers-grid">
                <?php if (empty($recent_drivers)): ?>
                    <div style="grid-column: 1 / -1; text-align: center; color: var(--text-secondary); padding: 20px;">
                        No hay actualizaciones recientes.
                    </div>
                <?php else: ?>
                    <?php foreach ($recent_drivers as $r_dr): ?>
                        <div class="recent-driver-card">
                            <div class="recent-driver-info">
                                <div class="recent-driver-logo">
                                    <i class="fa-solid <?php echo htmlspecialchars($r_dr['icon_class']); ?>"></i>
                                </div>
                                <div>
                                    <h4 style="font-size: 15px; font-weight: 600; margin-bottom: 2px;"><?php echo htmlspecialchars($r_dr['name']); ?></h4>
                                    <p style="font-size: 12px; color: var(--text-muted);">
                                        Para: <strong><?php echo htmlspecialchars($r_dr['brand_name'] . ' ' . $r_dr['model_name']); ?></strong> 
                                        • S.O: <?php echo htmlspecialchars($r_dr['os']); ?>
                                    </p>
                                </div>
                            </div>
                            <a href="equipment.php?id=<?php echo $r_dr['equipment_id']; ?>" class="btn-view" style="font-size: 12px; white-space: nowrap;">
                                Descargar <i class="fa-solid fa-arrow-down"></i>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- SECCIÓN: Preguntas Frecuentes (FAQ) -->
        <section class="info-section" style="border-bottom: none;">
            <h2 class="section-title"><span></span> Preguntas Frecuentes</h2>
            <div class="faq-grid">
                <div class="faq-card">
                    <div class="faq-question">
                        <i class="fa-solid fa-circle-question"></i>
                        <span>¿Qué es un controlador o driver?</span>
                    </div>
                    <div class="faq-answer">
                        Es un software especializado que permite que el sistema operativo de tu computadora (como Windows) se comunique correctamente con el hardware interno (tarjeta gráfica, chip de sonido, tarjeta de red, etc.). Sin él, los dispositivos no funcionarán o lo harán a rendimiento reducido.
                    </div>
                </div>

                <div class="faq-card">
                    <div class="faq-question">
                        <i class="fa-solid fa-circle-question"></i>
                        <span>¿Cómo identifico el modelo de mi laptop en Windows?</span>
                    </div>
                    <div class="faq-answer">
                        Presiona la combinación de teclas <code>Windows + R</code>, escribe <code>msinfo32</code> y presiona Enter. En la ventana que aparece, busca la línea que dice <strong>Modelo del sistema</strong> y <strong>Fabricante del sistema</strong>. Esa información es la que debes introducir en nuestro buscador.
                    </div>
                </div>

                <div class="faq-card">
                    <div class="faq-question">
                        <i class="fa-solid fa-circle-question"></i>
                        <span>¿Qué controlador debo descargar si tengo varios?</span>
                    </div>
                    <div class="faq-answer">
                        Descarga aquellos que resuelvan la falla específica que tienes (ej: si no tienes audio, descarga el controlador de Audio; si no te detecta redes inalámbricas, el de Wi-Fi). Te recomendamos descargar las versiones más recientes verificando que correspondan con tu sistema operativo actual.
                    </div>
                </div>

                <div class="faq-card">
                    <div class="faq-question">
                        <i class="fa-solid fa-circle-question"></i>
                        <span>¿Es seguro descargar controladores desde aquí?</span>
                    </div>
                    <div class="faq-answer">
                        Sí. Todos los archivos locales subidos por nuestros administradores son analizados meticulosamente. En el caso de links externos, proveemos enlaces que dirigen directamente a repositorios de descarga oficiales o repositorios técnicos confiables de los fabricantes.
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
