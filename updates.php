<?php
$page_title = "Últimas Actualizaciones - Soporte Master";
$active_tab = "updates";

// Incluir conexión a base de datos
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';

// Obtener todos los controladores cargados para la sección de actualizaciones
$recent_drivers_stmt = $pdo->query("SELECT d.*, e.model_name, b.name AS brand_name, c.name AS category_name, c.icon_class
                                    FROM drivers d
                                    JOIN equipment e ON d.equipment_id = e.id
                                    JOIN brands b ON e.brand_id = b.id
                                    JOIN categories c ON d.category_id = c.id
                                    ORDER BY d.uploaded_at DESC");
$all_recent_drivers = $recent_drivers_stmt->fetchAll();
?>

<div class="container" style="padding: 40px 20px 80px 20px;">
    <h2 class="section-title"><span></span> Todas las Actualizaciones</h2>
    <p style="color: var(--text-muted); margin-bottom: 30px;">Aquí puedes ver el historial completo de controladores y actualizaciones que hemos añadido a nuestra plataforma recientemente.</p>
    
    <div class="timeline">
        <?php if (empty($all_recent_drivers)): ?>
            <div style="text-align: center; color: var(--text-secondary); padding: 20px;">
                No hay actualizaciones.
            </div>
        <?php else: ?>
            <?php foreach ($all_recent_drivers as $r_dr): ?>
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
                                <br>
                                <small style="color: var(--text-muted); opacity: 0.8;"><i class="fa-regular fa-clock"></i> <?php echo date('d/m/Y', strtotime($r_dr['uploaded_at'])); ?></small>
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
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
