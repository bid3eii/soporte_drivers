<?php
$admin_title = "Gestionar Marcas";
$admin_heading = "Gestión de Marcas";
$admin_subheading = "Crea, edita o elimina las marcas de fabricantes (HP, Dell, Lenovo, etc.)";

require_once __DIR__ . '/header.php';

// Asegurar que la carpeta de subida de logos exista
$upload_dir = __DIR__ . '/../uploads/logos/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$message = '';
$message_type = '';

// --- PROCESAR ELIMINACIÓN ---
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    // Obtener logo para borrar el archivo
    $logo_stmt = $pdo->prepare("SELECT logo_url FROM brands WHERE id = ?");
    $logo_stmt->execute([$delete_id]);
    $brand_logo = $logo_stmt->fetchColumn();
    
    // Eliminar de base de datos (ON DELETE CASCADE se encargará de borrar equipos y drivers vinculados)
    $del_stmt = $pdo->prepare("DELETE FROM brands WHERE id = ?");
    if ($del_stmt->execute([$delete_id])) {
        // Borrar el archivo físico si existe
        if (!empty($brand_logo) && file_exists(__DIR__ . '/../' . $brand_logo)) {
            unlink(__DIR__ . '/../' . $brand_logo);
        }
        $message = "Marca eliminada correctamente.";
        $message_type = "success";
    } else {
        $message = "Error al intentar eliminar la marca.";
        $message_type = "danger";
    }
}

// --- PROCESAR CREACIÓN / EDICIÓN ---
$edit_brand = null;
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $edit_stmt = $pdo->prepare("SELECT * FROM brands WHERE id = ?");
    $edit_stmt->execute([$edit_id]);
    $edit_brand = $edit_stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $brand_id = isset($_POST['brand_id']) ? intval($_POST['brand_id']) : 0;
    
    if (!empty($name)) {
        $logo_path = null;
        
        // Si estamos editando, mantenemos el logo actual por defecto
        if ($brand_id > 0 && $edit_brand) {
            $logo_path = $edit_brand['logo_url'];
        }

        // Subida de nuevo Logo
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['logo']['tmp_name'];
            $file_name = basename($_FILES['logo']['name']);
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_exts = ['png', 'jpg', 'jpeg', 'gif', 'webp'];

            if (in_array($file_ext, $allowed_exts)) {
                // Generar nombre de archivo único
                $new_file_name = 'logo_' . uniqid() . '.' . $file_ext;
                $target_file = $upload_dir . $new_file_name;

                if (move_uploaded_file($file_tmp, $target_file)) {
                    // Borrar logo anterior si existía
                    if (!empty($logo_path) && file_exists(__DIR__ . '/../' . $logo_path)) {
                        unlink(__DIR__ . '/../' . $logo_path);
                    }
                    $logo_path = 'uploads/logos/' . $new_file_name;
                }
            } else {
                $message = "Formato de archivo no válido. Solo se permiten imágenes.";
                $message_type = "danger";
            }
        }

        // Ejecutar Operación SQL si no hay errores previos
        if ($message_type !== 'danger') {
            if ($brand_id > 0) {
                // Editar marca
                $sql = "UPDATE brands SET name = ?, logo_url = ?, description = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                if ($stmt->execute([$name, $logo_path, $description, $brand_id])) {
                    $message = "Marca actualizada correctamente.";
                    $message_type = "success";
                    // Recargar marca editada
                    $edit_stmt = $pdo->prepare("SELECT * FROM brands WHERE id = ?");
                    $edit_stmt->execute([$brand_id]);
                    $edit_brand = $edit_stmt->fetch();
                } else {
                    $message = "Error al actualizar la marca.";
                    $message_type = "danger";
                }
            } else {
                // Crear nueva marca
                try {
                    $sql = "INSERT INTO brands (name, logo_url, description) VALUES (?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    if ($stmt->execute([$name, $logo_path, $description])) {
                        $message = "Marca agregada correctamente.";
                        $message_type = "success";
                    } else {
                        $message = "Error al guardar la marca.";
                        $message_type = "danger";
                    }
                } catch (PDOException $e) {
                    if ($e->getCode() == 23000) {
                        $message = "Ya existe una marca con ese nombre.";
                    } else {
                        $message = "Error: " . $e->getMessage();
                    }
                    $message_type = "danger";
                }
            }
        }
    } else {
        $message = "El nombre de la marca es requerido.";
        $message_type = "danger";
    }
}

// Obtener todas las marcas
$brands = $pdo->query("SELECT * FROM brands ORDER BY name ASC")->fetchAll();
?>

<?php if (!empty($message)): ?>
    <div class="alert <?php echo $message_type; ?>">
        <i class="fa-solid <?php echo $message_type == 'success' ? 'fa-circle-check' : 'fa-circle-exclamation'; ?> mr-2"></i>
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 350px; gap: 30px; align-items: start;">
    <!-- Tabla de Marcas -->
    <div class="panel-card">
        <div class="panel-card-header">
            <div class="panel-card-title">Marcas Registradas</div>
        </div>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 80px;">Logo</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th style="width: 100px; text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($brands)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--text-secondary); padding: 30px;">
                                No hay marcas registradas.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($brands as $brand): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($brand['logo_url']) && file_exists(__DIR__ . '/../' . $brand['logo_url'])): ?>
                                        <img src="../<?php echo htmlspecialchars($brand['logo_url']); ?>" alt="Logo">
                                    <?php else: ?>
                                        <span class="badge primary"><?php echo htmlspecialchars(substr($brand['name'], 0, 2)); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?php echo htmlspecialchars($brand['name']); ?></strong></td>
                                <td style="color: var(--text-secondary); max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    <?php echo htmlspecialchars($brand['description']); ?>
                                </td>
                                <td>
                                    <div class="action-buttons" style="justify-content: center;">
                                        <a href="brands.php?edit_id=<?php echo $brand['id']; ?>" class="btn-icon edit" title="Editar">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <a href="brands.php?delete_id=<?php echo $brand['id']; ?>" class="btn-icon delete" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar esta marca? Se borrarán todos sus equipos y drivers asociados.');">
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
                <?php echo $edit_brand ? "Editar Marca" : "Agregar Marca"; ?>
            </div>
            <?php if ($edit_brand): ?>
                <a href="brands.php" style="font-size: 12px; color: var(--accent);"><i class="fa-solid fa-plus"></i> Nueva Marca</a>
            <?php endif; ?>
        </div>
        <div style="padding: 24px;">
            <form action="brands.php<?php echo $edit_brand ? '?edit_id=' . $edit_brand['id'] : ''; ?>" method="POST" enctype="multipart/form-data">
                <?php if ($edit_brand): ?>
                    <input type="hidden" name="brand_id" value="<?php echo $edit_brand['id']; ?>">
                <?php endif; ?>

                <div class="admin-form-group">
                    <label for="name" class="admin-form-label">Nombre de la Marca</label>
                    <input type="text" name="name" id="name" class="admin-form-input" placeholder="Ej: Lenovo" value="<?php echo $edit_brand ? htmlspecialchars($edit_brand['name']) : ''; ?>" required>
                </div>

                <div class="admin-form-group">
                    <label for="logo" class="admin-form-label">Logo de la Marca (Imagen)</label>
                    <?php if ($edit_brand && !empty($edit_brand['logo_url']) && file_exists(__DIR__ . '/../' . $edit_brand['logo_url'])): ?>
                        <div style="margin-bottom: 10px; display: flex; align-items: center; gap: 10px;">
                            <img src="../<?php echo htmlspecialchars($edit_brand['logo_url']); ?>" style="height: 40px; background: rgba(255,255,255,0.05); padding: 4px; border-radius: 6px;" alt="Logo actual">
                            <span style="font-size: 11px; color: var(--text-secondary);">Logo actual</span>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="logo" id="logo" class="admin-form-input" accept="image/*">
                    <small style="color: var(--text-secondary); display: block; margin-top: 4px;">Archivos permitidos: PNG, JPG, JPEG, GIF, WEBP</small>
                </div>

                <div class="admin-form-group">
                    <label for="description" class="admin-form-label">Descripción</label>
                    <textarea name="description" id="description" class="admin-form-textarea" placeholder="Breve reseña sobre el fabricante de equipos..."><?php echo $edit_brand ? htmlspecialchars($edit_brand['description']) : ''; ?></textarea>
                </div>

                <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; margin-top: 10px;">
                    <i class="fa-solid fa-floppy-disk"></i> <?php echo $edit_brand ? "Actualizar Marca" : "Guardar Marca"; ?>
                </button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
