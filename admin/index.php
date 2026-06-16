<?php
$admin_title = "Resumen del Portal";
$admin_heading = "Resumen del Sistema";
$admin_subheading = "Panel general con estadísticas de contenido y descargas.";

require_once __DIR__ . '/header.php';

// Obtener estadísticas
$count_brands = $pdo->query("SELECT COUNT(*) FROM brands")->fetchColumn();
$count_categories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$count_equipment = $pdo->query("SELECT COUNT(*) FROM equipment")->fetchColumn();
$count_drivers = $pdo->query("SELECT COUNT(*) FROM drivers")->fetchColumn();
$sum_downloads = $pdo->query("SELECT SUM(download_count) FROM drivers")->fetchColumn();
$sum_downloads = $sum_downloads ? $sum_downloads : 0;

// Obtener últimos 5 controladores subidos
$recent_stmt = $pdo->query("SELECT d.*, e.model_name, b.name AS brand_name, c.name AS category_name
                            FROM drivers d
                            JOIN equipment e ON d.equipment_id = e.id
                            JOIN brands b ON e.brand_id = b.id
                            JOIN categories c ON d.category_id = c.id
                            ORDER BY d.uploaded_at DESC
                            LIMIT 5");
$recent_drivers = $recent_stmt->fetchAll();
?>

<!-- Grid de Estadísticas -->
<div class="dashboard-grid">
    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-value"><?php echo $count_brands; ?></div>
            <div class="stat-label">Marcas</div>
        </div>
        <div class="stat-icon primary">
            <i class="fa-solid fa-copyright"></i>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-value"><?php echo $count_categories; ?></div>
            <div class="stat-label">Categorías</div>
        </div>
        <div class="stat-icon accent">
            <i class="fa-solid fa-tags"></i>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-value"><?php echo $count_equipment; ?></div>
            <div class="stat-label">Equipos</div>
        </div>
        <div class="stat-icon warning">
            <i class="fa-solid fa-laptop"></i>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-value"><?php echo $count_drivers; ?></div>
            <div class="stat-label">Drivers</div>
        </div>
        <div class="stat-icon success">
            <i class="fa-solid fa-file-code"></i>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-value"><?php echo $sum_downloads; ?></div>
            <div class="stat-label">Descargas</div>
        </div>
        <div class="stat-icon" style="background: rgba(99, 102, 241, 0.1); color: #818cf8;">
            <i class="fa-solid fa-cloud-arrow-down"></i>
        </div>
    </div>
</div>

<!-- Contenedor de Últimas Cargas -->
<div class="panel-card">
    <div class="panel-card-header">
        <div class="panel-card-title">Últimos Controladores Cargados</div>
        <a href="drivers.php" class="btn-primary" style="font-size: 13px; padding: 6px 12px;">
            Ver Todos <i class="fa-solid fa-arrow-right"></i>
        </a>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Controlador</th>
                    <th>Equipo</th>
                    <th>Categoría</th>
                    <th>S.O.</th>
                    <th style="text-align: center;">Peso</th>
                    <th style="text-align: center;">Descargas</th>
                    <th>Fecha Carga</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recent_drivers)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--text-secondary); padding: 30px;">
                            No hay controladores cargados todavía.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recent_drivers as $dr): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($dr['name']); ?></strong><br><small style="color: var(--text-secondary);">Ver. <?php echo htmlspecialchars($dr['version']); ?></small></td>
                            <td><?php echo htmlspecialchars($dr['brand_name'] . ' ' . $dr['model_name']); ?></td>
                            <td><span class="badge primary"><?php echo htmlspecialchars($dr['category_name']); ?></span></td>
                            <td><?php echo htmlspecialchars($dr['os']); ?></td>
                            <td style="text-align: center;"><?php echo htmlspecialchars($dr['file_size']); ?></td>
                            <td style="text-align: center;"><span class="badge success"><?php echo $dr['download_count']; ?></span></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($dr['uploaded_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
