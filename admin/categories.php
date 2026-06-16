<?php
$admin_title = "Gestionar Categorías";
$admin_heading = "Gestión de Categorías";
$admin_subheading = "Crea, edita o elimina las categorías de drivers (Audio, Video, Wifi, Chipset, etc.)";

require_once __DIR__ . '/header.php';

$message = '';
$message_type = '';

// --- PROCESAR ELIMINACIÓN ---
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    // Eliminar de base de datos
    $del_stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    if ($del_stmt->execute([$delete_id])) {
        $_SESSION['flash_message'] = "Categoría eliminada correctamente.";
        $_SESSION['flash_type'] = "success";
        header("Location: categories.php");
        exit;
    } else {
        $message = "Error al intentar eliminar la categoría.";
        $message_type = "danger";
    }
}

// --- PROCESAR CREACIÓN / EDICIÓN ---
$edit_category = null;
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $edit_stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $edit_stmt->execute([$edit_id]);
    $edit_category = $edit_stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $icon_class = trim($_POST['icon_class']);
    $description = trim($_POST['description']);
    $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
    
    // Default class if empty
    if (empty($icon_class)) {
        $icon_class = 'fa-file';
    }

    if (!empty($name)) {
        if ($category_id > 0) {
            // Editar categoría
            $sql = "UPDATE categories SET name = ?, icon_class = ?, description = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$name, $icon_class, $description, $category_id])) {
                $_SESSION['flash_message'] = "Categoría actualizada correctamente.";
                $_SESSION['flash_type'] = "success";
                header("Location: categories.php");
                exit;
            } else {
                $message = "Error al actualizar la categoría.";
                $message_type = "danger";
            }
        } else {
            // Crear nueva categoría
            try {
                $sql = "INSERT INTO categories (name, icon_class, description) VALUES (?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                if ($stmt->execute([$name, $icon_class, $description])) {
                    $_SESSION['flash_message'] = "Categoría agregada correctamente.";
                    $_SESSION['flash_type'] = "success";
                    header("Location: categories.php");
                    exit;
                } else {
                    $message = "Error al guardar la categoría.";
                    $message_type = "danger";
                }
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $message = "Ya existe una categoría con ese nombre.";
                } else {
                    $message = "Error: " . $e->getMessage();
                }
                $message_type = "danger";
            }
        }
    } else {
        $message = "El nombre de la categoría es requerido.";
        $message_type = "danger";
    }
}

// Obtener todas las categorías
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
?>

<?php if (!empty($message)): ?>
    <div class="alert <?php echo $message_type; ?>">
        <i class="fa-solid <?php echo $message_type == 'success' ? 'fa-circle-check' : 'fa-circle-exclamation'; ?> mr-2"></i>
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 350px; gap: 30px; align-items: start;">
    <!-- Tabla de Categorías -->
    <div class="panel-card">
        <div class="panel-card-header">
            <div class="panel-card-title">Categorías Registradas</div>
        </div>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 80px; text-align: center;">Icono</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th style="width: 100px; text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categories)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--text-secondary); padding: 30px;">
                                No hay categorías registradas.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td style="text-align: center; font-size: 18px; color: var(--primary);">
                                    <i class="fa-solid <?php echo htmlspecialchars($cat['icon_class']); ?>"></i>
                                </td>
                                <td><strong><?php echo htmlspecialchars($cat['name']); ?></strong><br><small style="color: var(--text-secondary); font-family: monospace;">fa-<?php echo htmlspecialchars(str_replace('fa-', '', $cat['icon_class'])); ?></small></td>
                                <td style="color: var(--text-secondary); max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    <?php echo htmlspecialchars($cat['description']); ?>
                                </td>
                                <td>
                                    <div class="action-buttons" style="justify-content: center;">
                                        <a href="categories.php?edit_id=<?php echo $cat['id']; ?>" class="btn-icon edit" title="Editar">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <a href="categories.php?delete_id=<?php echo $cat['id']; ?>" class="btn-icon delete" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar esta categoría? Se borrarán todos los drivers asociados.');">
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
                <?php echo $edit_category ? "Editar Categoría" : "Agregar Categoría"; ?>
            </div>
            <?php if ($edit_category): ?>
                <a href="categories.php" style="font-size: 12px; color: var(--accent);"><i class="fa-solid fa-plus"></i> Nueva Categoría</a>
            <?php endif; ?>
        </div>
        <div style="padding: 24px;">
            <form action="categories.php<?php echo $edit_category ? '?edit_id=' . $edit_category['id'] : ''; ?>" method="POST">
                <?php if ($edit_category): ?>
                    <input type="hidden" name="category_id" value="<?php echo $edit_category['id']; ?>">
                <?php endif; ?>

                <div class="admin-form-group">
                    <label for="name" class="admin-form-label">Nombre de la Categoría</label>
                    <input type="text" name="name" id="name" class="admin-form-input" placeholder="Ej: Wi-Fi / Conectividad" value="<?php echo $edit_category ? htmlspecialchars($edit_category['name']) : ''; ?>" required>
                </div>

                <div class="admin-form-group">
                    <label for="icon_class" class="admin-form-label">Clase de Icono (FontAwesome)</label>
                    <input type="text" name="icon_class" id="icon_class" class="admin-form-input" placeholder="Ej: fa-wifi" value="<?php echo $edit_category ? htmlspecialchars($edit_category['icon_class']) : ''; ?>">
                    <small style="color: var(--text-secondary); display: block; margin-top: 6px;">
                        Clases comunes de FontAwesome 6:<br>
                        • <code>fa-volume-up</code> (Audio)<br>
                        • <code>fa-desktop</code> (Video)<br>
                        • <code>fa-ethernet</code> (Tarjeta Red)<br>
                        • <code>fa-wifi</code> (Wi-Fi / BT)<br>
                        • <code>fa-microchip</code> (Chipset / Proc)<br>
                        • <code>fa-hdd</code> (Discos / SATA)<br>
                        • <code>fa-keyboard</code> (Teclados / Mouse)
                    </small>
                </div>

                <div class="admin-form-group">
                    <label for="description" class="admin-form-label">Descripción</label>
                    <textarea name="description" id="description" class="admin-form-textarea" placeholder="Descripción de los controladores de esta categoría..."><?php echo $edit_category ? htmlspecialchars($edit_category['description']) : ''; ?></textarea>
                </div>

                <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; margin-top: 10px;">
                    <i class="fa-solid fa-floppy-disk"></i> <?php echo $edit_category ? "Actualizar Categoría" : "Guardar Categoría"; ?>
                </button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
