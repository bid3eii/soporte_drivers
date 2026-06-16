<?php
$admin_title = "Gestionar Equipos";
$admin_heading = "Gestión de Equipos (Modelos)";
$admin_subheading = "Crea, edita o elimina modelos de computadoras y asócialas a una marca.";

require_once __DIR__ . '/header.php';

// Asegurar carpeta de subida de imágenes de equipos
$upload_dir = __DIR__ . '/../uploads/equipment/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$message = '';
$message_type = '';

// --- PROCESAR ELIMINACIÓN ---
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    // Obtener imagen del equipo para borrar archivo físico
    $img_stmt = $pdo->prepare("SELECT image_url FROM equipment WHERE id = ?");
    $img_stmt->execute([$delete_id]);
    $eq_img = $img_stmt->fetchColumn();
    
    // Eliminar de base de datos
    $del_stmt = $pdo->prepare("DELETE FROM equipment WHERE id = ?");
    if ($del_stmt->execute([$delete_id])) {
        // Borrar el archivo si existe
        if (!empty($eq_img) && file_exists(__DIR__ . '/../' . $eq_img)) {
            unlink(__DIR__ . '/../' . $eq_img);
        }
        $_SESSION['flash_message'] = "Equipo eliminado correctamente.";
        $_SESSION['flash_type'] = "success";
        header("Location: equipment.php");
        exit;
    } else {
        $message = "Error al intentar eliminar el equipo.";
        $message_type = "danger";
    }
}

// --- PROCESAR CREACIÓN / EDICIÓN ---
$edit_equipment = null;
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $edit_stmt = $pdo->prepare("SELECT * FROM equipment WHERE id = ?");
    $edit_stmt->execute([$edit_id]);
    $edit_equipment = $edit_stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $brand_id = intval($_POST['brand_id']);
    $model_name = trim($_POST['model_name']);
    $description = trim($_POST['description']);
    $equipment_id = isset($_POST['equipment_id']) ? intval($_POST['equipment_id']) : 0;
    
    if ($brand_id > 0 && !empty($model_name)) {
        $img_path = null;
        
        // Mantener imagen anterior por defecto
        if ($equipment_id > 0 && $edit_equipment) {
            $img_path = $edit_equipment['image_url'];
        }

        // Subida de imagen nueva del Equipo
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['image']['tmp_name'];
            $file_name = basename($_FILES['image']['name']);
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_exts = ['png', 'jpg', 'jpeg', 'webp'];

            if (in_array($file_ext, $allowed_exts)) {
                $new_file_name = 'eq_' . uniqid() . '.' . $file_ext;
                $target_file = $upload_dir . $new_file_name;

                if (move_uploaded_file($file_tmp, $target_file)) {
                    // Borrar imagen anterior si existía
                    if (!empty($img_path) && file_exists(__DIR__ . '/../' . $img_path)) {
                        unlink(__DIR__ . '/../' . $img_path);
                    }
                    $img_path = 'uploads/equipment/' . $new_file_name;
                }
            } else {
                $message = "Formato de archivo no válido. Solo se permiten imágenes.";
                $message_type = "danger";
            }
        }

        // Registrar cambios en la base de datos
        if ($message_type !== 'danger') {
            if ($equipment_id > 0) {
                // Editar Equipo
                $sql = "UPDATE equipment SET brand_id = ?, model_name = ?, image_url = ?, description = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                if ($stmt->execute([$brand_id, $model_name, $img_path, $description, $equipment_id])) {
                    $_SESSION['flash_message'] = "Equipo actualizado correctamente.";
                    $_SESSION['flash_type'] = "success";
                    header("Location: equipment.php");
                    exit;
                } else {
                    $message = "Error al actualizar el equipo.";
                    $message_type = "danger";
                }
            } else {
                // Crear Equipo
                $sql = "INSERT INTO equipment (brand_id, model_name, image_url, description) VALUES (?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                if ($stmt->execute([$brand_id, $model_name, $img_path, $description])) {
                    $_SESSION['flash_message'] = "Equipo agregado correctamente.";
                    $_SESSION['flash_type'] = "success";
                    header("Location: equipment.php");
                    exit;
                } else {
                    $message = "Error al guardar el equipo.";
                    $message_type = "danger";
                }
            }
        }
    } else {
        $message = "La marca y el modelo del equipo son campos obligatorios.";
        $message_type = "danger";
    }
}

// Obtener lista completa de equipos con el nombre de su marca
$equipments = $pdo->query("SELECT e.*, b.name AS brand_name 
                            FROM equipment e 
                            JOIN brands b ON e.brand_id = b.id 
                            ORDER BY b.name ASC, e.model_name ASC")->fetchAll();

// Obtener todas las marcas para llenar el select del formulario
$brands = $pdo->query("SELECT id, name FROM brands ORDER BY name ASC")->fetchAll();
?>

<?php if (!empty($message)): ?>
    <div class="alert <?php echo $message_type; ?>">
        <i class="fa-solid <?php echo $message_type == 'success' ? 'fa-circle-check' : 'fa-circle-exclamation'; ?> mr-2"></i>
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 360px; gap: 30px; align-items: start;">
    <!-- Tabla de Equipos -->
    <div class="panel-card">
        <div class="panel-card-header">
            <div class="panel-card-title">Modelos Registrados</div>
        </div>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 100px;">Imagen</th>
                        <th>Marca</th>
                        <th>Modelo</th>
                        <th>Descripción</th>
                        <th style="width: 100px; text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($equipments)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-secondary); padding: 30px;">
                                No hay modelos registrados.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($equipments as $eq): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($eq['image_url']) && file_exists(__DIR__ . '/../' . $eq['image_url'])): ?>
                                        <img src="../<?php echo htmlspecialchars($eq['image_url']); ?>" alt="Equipo" style="height: 38px; border-radius: 4px;">
                                    <?php else: ?>
                                        <i class="fa-solid fa-laptop" style="font-size: 22px; color: var(--text-secondary);"></i>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge primary"><?php echo htmlspecialchars($eq['brand_name']); ?></span></td>
                                <td><strong><?php echo htmlspecialchars($eq['model_name']); ?></strong></td>
                                <td style="color: var(--text-secondary); max-width: 260px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    <?php echo htmlspecialchars($eq['description']); ?>
                                </td>
                                <td>
                                    <div class="action-buttons" style="justify-content: center;">
                                        <a href="equipment.php?edit_id=<?php echo $eq['id']; ?>" class="btn-icon edit" title="Editar">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <a href="equipment.php?delete_id=<?php echo $eq['id']; ?>" class="btn-icon delete" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar este modelo? Se borrarán todos sus drivers asociados.');">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Formulario de Agregar / Editar -->
    <div class="panel-card">
        <div class="panel-card-header">
            <div class="panel-card-title">
                <?php echo $edit_equipment ? "Editar Equipo" : "Agregar Equipo"; ?>
            </div>
            <?php if ($edit_equipment): ?>
                <a href="equipment.php" style="font-size: 12px; color: var(--accent);"><i class="fa-solid fa-plus"></i> Nuevo Equipo</a>
            <?php endif; ?>
        </div>
        <div style="padding: 24px;">
            <form action="equipment.php<?php echo $edit_equipment ? '?edit_id=' . $edit_equipment['id'] : ''; ?>" method="POST" enctype="multipart/form-data">
                <?php if ($edit_equipment): ?>
                    <input type="hidden" name="equipment_id" value="<?php echo $edit_equipment['id']; ?>">
                <?php endif; ?>

                <div class="admin-form-group">
                    <label for="brand_id" class="admin-form-label">Marca del Fabricante</label>
                    <select name="brand_id" id="brand_id" class="admin-form-select" required>
                        <option value="">Selecciona una marca</option>
                        <?php foreach ($brands as $brand): ?>
                            <option value="<?php echo $brand['id']; ?>" <?php echo ($edit_equipment && $edit_equipment['brand_id'] == $brand['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($brand['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="admin-form-group">
                    <label for="model_name" class="admin-form-label">Modelo del Equipo</label>
                    <input type="text" name="model_name" id="model_name" class="admin-form-input" placeholder="Ej: ThinkPad T14 Gen 2" value="<?php echo $edit_equipment ? htmlspecialchars($edit_equipment['model_name']) : ''; ?>" required>
                </div>

                <div class="admin-form-group">
                    <label for="image" class="admin-form-label">Foto / Imagen del Equipo</label>
                    <?php if ($edit_equipment && !empty($edit_equipment['image_url']) && file_exists(__DIR__ . '/../' . $edit_equipment['image_url'])): ?>
                        <div style="margin-bottom: 10px; display: flex; align-items: center; gap: 10px;">
                            <img src="../<?php echo htmlspecialchars($edit_equipment['image_url']); ?>" style="height: 40px; background: rgba(255,255,255,0.05); padding: 4px; border-radius: 6px;" alt="Imagen actual">
                            <span style="font-size: 11px; color: var(--text-secondary);">Imagen actual</span>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="image" id="image" class="admin-form-input" accept="image/*">
                    <small style="color: var(--text-secondary); display: block; margin-top: 4px;">PNG, JPG, JPEG, WEBP</small>
                </div>

                <div class="admin-form-group">
                    <label for="description" class="admin-form-label">Especificaciones / Detalles</label>
                    <textarea name="description" id="description" class="admin-form-textarea" placeholder="Especificaciones rápidas (procesador, año, ram soportada, etc.) o descripción del modelo..."><?php echo $edit_equipment ? htmlspecialchars($edit_equipment['description']) : ''; ?></textarea>
                </div>

                <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; margin-top: 10px;">
                    <i class="fa-solid fa-floppy-disk"></i> <?php echo $edit_equipment ? "Actualizar Equipo" : "Guardar Equipo"; ?>
                </button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
