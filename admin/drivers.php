<?php
$admin_title = "Gestionar Controladores";
$admin_heading = "Gestión de Controladores";
$admin_subheading = "Carga archivos de drivers o pega links externos de descarga para cada equipo.";

require_once __DIR__ . '/header.php';

// Asegurar carpeta de subida de archivos
$upload_dir = __DIR__ . '/../uploads/files/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Función auxiliar para convertir bytes en formato legible
function formatSizeUnits($bytes) {
    if ($bytes >= 1073741824) {
        $bytes = number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        $bytes = number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        $bytes = number_format($bytes / 1024, 2) . ' KB';
    } elseif ($bytes > 1) {
        $bytes = $bytes . ' bytes';
    } elseif ($bytes == 1) {
        $bytes = $bytes . ' byte';
    } else {
        $bytes = '0 bytes';
    }
    return $bytes;
}

$message = '';
$message_type = '';

// --- PROCESAR ELIMINACIÓN ---
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    // Obtener ruta del archivo para ver si se borra físicamente
    $file_stmt = $pdo->prepare("SELECT download_url, is_local FROM drivers WHERE id = ?");
    $file_stmt->execute([$delete_id]);
    $dr_file = $file_stmt->fetch();
    
    // Eliminar de base de datos
    $del_stmt = $pdo->prepare("DELETE FROM drivers WHERE id = ?");
    if ($del_stmt->execute([$delete_id])) {
        // Borrar archivo físico si era local
        if ($dr_file && $dr_file['is_local'] == 1) {
            if (!empty($dr_file['download_url']) && file_exists(__DIR__ . '/../' . $dr_file['download_url'])) {
                unlink(__DIR__ . '/../' . $dr_file['download_url']);
            }
        }
        $_SESSION['flash_message'] = "Controlador eliminado correctamente.";
        $_SESSION['flash_type'] = "success";
        header("Location: drivers.php");
        exit;
    } else {
        $message = "Error al intentar eliminar el controlador.";
        $message_type = "danger";
    }
}

// --- PROCESAR ELIMINAR TODOS ---
if (isset($_GET['delete_all']) && $_GET['delete_all'] == 1) {
    // Obtener todos los archivos locales para borrarlos físicamente
    $local_stmt = $pdo->query("SELECT download_url FROM drivers WHERE is_local = 1 AND download_url IS NOT NULL");
    while ($dr_file = $local_stmt->fetch()) {
        if (file_exists(__DIR__ . '/../' . $dr_file['download_url'])) {
            unlink(__DIR__ . '/../' . $dr_file['download_url']);
        }
    }
    
    // Vaciar la tabla de drivers por completo
    $pdo->query("DELETE FROM drivers");
    
    $_SESSION['flash_message'] = "Todos los controladores han sido eliminados de la base de datos.";
    $_SESSION['flash_type'] = "success";
    header("Location: drivers.php");
    exit;
}

// --- PROCESAR CREACIÓN / EDICIÓN ---
$edit_driver = null;
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $edit_stmt = $pdo->prepare("SELECT * FROM drivers WHERE id = ?");
    $edit_stmt->execute([$edit_id]);
    $edit_driver = $edit_stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $equipment_id = intval($_POST['equipment_id']);
    $category_id = intval($_POST['category_id']);
    $name = trim($_POST['name']);
    $version = trim($_POST['version']);
    $os = trim($_POST['os']);
    $driver_type = intval($_POST['driver_type']); // 1 = Local, 0 = Externo
    $driver_id = isset($_POST['driver_id']) ? intval($_POST['driver_id']) : 0;
    
    if ($equipment_id > 0 && $category_id > 0 && !empty($name) && !empty($version) && !empty($os)) {
        
        $download_url = '';
        $file_size = '';
        
        // Conservar datos anteriores si estamos editando
        if ($driver_id > 0 && $edit_driver) {
            $download_url = $edit_driver['download_url'];
            $file_size = $edit_driver['file_size'];
        }

        if ($driver_type === 1) {
            // --- TIPO: LOCAL ---
            if (isset($_FILES['driver_file']) && $_FILES['driver_file']['error'] === UPLOAD_ERR_OK) {
                $file_tmp = $_FILES['driver_file']['tmp_name'];
                $file_name = basename($_FILES['driver_file']['name']);
                $file_size_bytes = $_FILES['driver_file']['size'];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                
                // Formatos aceptados
                $allowed_exts = ['zip', 'rar', 'exe', 'msi', 'inf', 'sys', 'gz', 'tar', '7z'];
                
                if (in_array($file_ext, $allowed_exts)) {
                    // Mantener el nombre original pero limpiar caracteres extraños
                    $safe_name = preg_replace('/[^a-zA-Z0-9.\-_]/', '_', $file_name);
                    $target_file = $upload_dir . $safe_name;
                    
                    // Si ya existe un archivo con ese nombre, agregarle un hash corto
                    if (file_exists($target_file)) {
                        $name_without_ext = pathinfo($safe_name, PATHINFO_FILENAME);
                        $safe_name = $name_without_ext . '_' . substr(uniqid(), -5) . '.' . $file_ext;
                        $target_file = $upload_dir . $safe_name;
                    }
                    $new_file_name = $safe_name;
                    
                    if (move_uploaded_file($file_tmp, $target_file)) {
                        // Borrar archivo anterior si existía localmente
                        if ($driver_id > 0 && $edit_driver && $edit_driver['is_local'] == 1) {
                            if (!empty($download_url) && file_exists(__DIR__ . '/../' . $download_url)) {
                                unlink(__DIR__ . '/../' . $download_url);
                            }
                        }
                        
                        $download_url = 'uploads/files/' . $new_file_name;
                        $file_size = formatSizeUnits($file_size_bytes);
                    } else {
                        $message = "Error al mover el archivo subido.";
                        $message_type = "danger";
                    }
                } else {
                    $message = "Formato de archivo no permitido (ZIP, RAR, EXE, MSI, 7Z, etc.).";
                    $message_type = "danger";
                }
            } else {
                // Si estamos creando un driver local, es obligatorio subir el archivo
                if ($driver_id <= 0) {
                    $message = "Debes subir un archivo para el controlador local.";
                    $message_type = "danger";
                }
            }
        } else {
            // --- TIPO: EXTERNO ---
            $external_url = trim($_POST['external_url']);
            $manual_size = trim($_POST['manual_size']);
            
            if (!empty($external_url)) {
                // Si antes era local, borrar archivo físico viejo
                if ($driver_id > 0 && $edit_driver && $edit_driver['is_local'] == 1) {
                    if (!empty($download_url) && file_exists(__DIR__ . '/../' . $download_url)) {
                        unlink(__DIR__ . '/../' . $download_url);
                    }
                }
                
                $download_url = $external_url;
                $file_size = !empty($manual_size) ? $manual_size : 'Enlace Externo';
            } else {
                $message = "Debes ingresar una dirección URL de descarga externa.";
                $message_type = "danger";
            }
        }

        // Ejecutar inserción o edición
        if ($message_type !== 'danger') {
            if ($driver_id > 0) {
                // Editar
                $sql = "UPDATE drivers 
                        SET equipment_id = ?, category_id = ?, name = ?, version = ?, os = ?, download_url = ?, is_local = ?, file_size = ? 
                        WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                if ($stmt->execute([$equipment_id, $category_id, $name, $version, $os, $download_url, $driver_type, $file_size, $driver_id])) {
                    $_SESSION['flash_message'] = "Controlador actualizado correctamente.";
                    $_SESSION['flash_type'] = "success";
                    header("Location: drivers.php");
                    exit;
                } else {
                    $message = "Error al actualizar el controlador.";
                    $message_type = "danger";
                }
            } else {
                // Crear nuevo
                $sql = "INSERT INTO drivers (equipment_id, category_id, name, version, os, download_url, is_local, file_size) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                if ($stmt->execute([$equipment_id, $category_id, $name, $version, $os, $download_url, $driver_type, $file_size])) {
                    $_SESSION['flash_message'] = "Controlador registrado con éxito.";
                    $_SESSION['flash_type'] = "success";
                    header("Location: drivers.php");
                    exit;
                } else {
                    $message = "Error al guardar el controlador.";
                    $message_type = "danger";
                }
            }
        }
    } else {
        $message = "Por favor complete todos los campos requeridos.";
        $message_type = "danger";
    }
}

// Obtener lista completa de controladores con marca, modelo y categoría
$drivers = $pdo->query("SELECT d.*, e.model_name, b.name AS brand_name, c.name AS category_name 
                        FROM drivers d 
                        JOIN equipment e ON d.equipment_id = e.id 
                        JOIN brands b ON e.brand_id = b.id 
                        JOIN categories c ON d.category_id = c.id 
                        ORDER BY b.name ASC, e.model_name ASC, c.name ASC")->fetchAll();

// Obtener equipos
$equipments = $pdo->query("SELECT e.id, e.model_name, b.name AS brand_name 
                            FROM equipment e 
                            JOIN brands b ON e.brand_id = b.id 
                            ORDER BY b.name ASC, e.model_name ASC")->fetchAll();

// Obtener categorías
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();
?>

<?php if (!empty($message)): ?>
    <div class="alert <?php echo $message_type; ?>">
        <i class="fa-solid <?php echo $message_type == 'success' ? 'fa-circle-check' : 'fa-circle-exclamation'; ?> mr-2"></i>
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 380px; gap: 30px; align-items: start;">
    <!-- Lista de Controladores -->
    <div class="panel-card">
        <div class="panel-card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div class="panel-card-title">Controladores Cargados</div>
            <?php if (!empty($drivers)): ?>
                <a href="drivers.php?delete_all=1" class="delete" onclick="return confirm('ATENCIÓN: ¿Estás seguro de que deseas ELIMINAR TODOS los controladores y sus archivos físicos? Las marcas y equipos se mantendrán.')" style="color: #ef4444; font-size: 13px; text-decoration: none; padding: 4px 8px; border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 6px; background: rgba(239, 68, 68, 0.05); transition: all 0.3s;"><i class="fa-solid fa-trash-can"></i> Vaciar Todo</a>
            <?php endif; ?>
        </div>
        <div class="table-responsive">
            <table class="admin-table" style="table-layout: fixed; width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 18%;">Modelo / Marca</th>
                        <th style="width: 32%;">Nombre / Componente</th>
                        <th style="width: 20%;">Sistema Op.</th>
                        <th style="width: 12%;">Origen</th>
                        <th style="width: 8%; text-align: center;">Peso</th>
                        <th style="width: 10%; text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($drivers)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--text-secondary); padding: 30px;">
                                No hay controladores registrados.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        // Agrupar por equipo
                        $grouped_drivers = [];
                        foreach ($drivers as $dr) {
                            $grouped_drivers[$dr['equipment_id']][] = $dr;
                        }
                        ?>
                        <?php foreach ($grouped_drivers as $eq_id => $group): ?>
                            <?php $first_dr = $group[0]; ?>
                            <!-- Header del Equipo -->
                            <tr onclick="toggleDrivers('<?php echo $eq_id; ?>')" style="cursor: pointer; background: rgba(255,255,255,0.02); transition: background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.05)'" onmouseout="this.style.background='rgba(255,255,255,0.02)'">
                                <td colspan="6" style="padding: 14px 24px;">
                                    <div style="display: flex; align-items: center; justify-content: space-between;">
                                        <div>
                                            <i id="icon-<?php echo $eq_id; ?>" class="fa-solid fa-chevron-right mr-2" style="width: 15px; color: var(--accent); transition: transform 0.2s;"></i>
                                            <strong style="font-size: 15px;"><?php echo htmlspecialchars($first_dr['brand_name']); ?></strong> 
                                            <span style="color: var(--text-secondary); margin-left: 8px;"><?php echo htmlspecialchars($first_dr['model_name']); ?></span>
                                        </div>
                                        <div>
                                            <span class="badge primary"><?php echo count($group); ?> Controladores</span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            
                            <!-- Controladores del equipo -->
                            <?php foreach ($group as $dr): ?>
                                <tr class="driver-row-<?php echo $eq_id; ?>" style="display: none; opacity: 0; transform: translateY(-10px); transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1); background: rgba(0,0,0,0.15);">
                                    <td style="border-right: 1px solid var(--border); text-align: center;">
                                        <i class="fa-solid fa-level-up-alt fa-rotate-90" style="color: var(--text-secondary); opacity: 0.4;"></i>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($dr['name']); ?></strong><br>
                                        <span class="badge primary" style="font-size: 10px;"><?php echo htmlspecialchars($dr['category_name']); ?></span>
                                        <small style="color: var(--text-secondary);">Ver. <?php echo htmlspecialchars($dr['version']); ?></small>
                                    </td>
                                    <td><span style="font-size: 13px;"><?php echo htmlspecialchars($dr['os']); ?></span></td>
                                    <td>
                                        <?php if ($dr['is_local'] == 1): ?>
                                            <span class="badge success" style="margin-bottom: 4px; display: inline-block;">Local (Subido)</span>
                                        <?php else: ?>
                                            <span class="badge warning" style="margin-bottom: 4px; display: inline-block;">Externo (Link)</span>
                                        <?php endif; ?>
                                        <div style="font-size: 11px; color: var(--text-secondary); margin-top: 4px;">
                                            <i class="fa-regular fa-calendar" style="margin-right: 3px;"></i><?php echo date('d M Y', strtotime($dr['uploaded_at'])); ?>
                                        </div>
                                    </td>
                                    <td style="text-align: center;"><span style="font-family: monospace; font-size: 13px;"><?php echo htmlspecialchars($dr['file_size']); ?></span></td>
                                    <td>
                                        <div class="action-buttons" style="justify-content: center;">
                                            <a href="drivers.php?edit_id=<?php echo $dr['id']; ?>" class="btn-icon edit" title="Editar">
                                                <i class="fa-solid fa-pen"></i>
                                            </a>
                                            <a href="drivers.php?delete_id=<?php echo $dr['id']; ?>" class="btn-icon delete" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar este controlador? Se borrará el archivo de almacenamiento local.');">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
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
                <?php echo $edit_driver ? "Editar Controlador" : "Agregar Controlador"; ?>
            </div>
            <?php if ($edit_driver): ?>
                <a href="drivers.php" style="font-size: 12px; color: var(--accent);"><i class="fa-solid fa-plus"></i> Nuevo Controlador</a>
            <?php endif; ?>
        </div>
        <div style="padding: 24px;">
            <form action="drivers.php<?php echo $edit_driver ? '?edit_id=' . $edit_driver['id'] : ''; ?>" method="POST" enctype="multipart/form-data" id="driverForm">
                <?php if ($edit_driver): ?>
                    <input type="hidden" name="driver_id" value="<?php echo $edit_driver['id']; ?>">
                <?php endif; ?>

                <div class="admin-form-group">
                    <label for="equipment_id" class="admin-form-label">Equipo Compatible</label>
                    <select name="equipment_id" id="equipment_id" class="admin-form-select" required>
                        <option value="">Selecciona el equipo</option>
                        <?php foreach ($equipments as $eq): ?>
                            <option value="<?php echo $eq['id']; ?>" <?php echo ($edit_driver && $edit_driver['equipment_id'] == $eq['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($eq['brand_name'] . ' ' . $eq['model_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="admin-form-group">
                    <label for="category_id" class="admin-form-label">Categoría / Componente</label>
                    <select name="category_id" id="category_id" class="admin-form-select" required>
                        <option value="">Selecciona la categoría</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo ($edit_driver && $edit_driver['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="admin-form-group">
                    <label for="name" class="admin-form-label">Nombre del Controlador</label>
                    <input type="text" name="name" id="name" class="admin-form-input" placeholder="Ej: Realtek Audio HD Controller" value="<?php echo $edit_driver ? htmlspecialchars($edit_driver['name']) : ''; ?>" required>
                </div>

                <div class="admin-form-group">
                    <label for="version" class="admin-form-label">Versión</label>
                    <input type="text" name="version" id="version" class="admin-form-input" placeholder="Ej: 6.0.9126.1" value="<?php echo $edit_driver ? htmlspecialchars($edit_driver['version']) : ''; ?>" required>
                </div>

                <div class="admin-form-group">
                    <label for="os" class="admin-form-label">Compatibilidad S.O.</label>
                    <select name="os" id="os" class="admin-form-input" required>
                        <option value="">Selecciona el Sistema Operativo</option>
                        <option value="Windows 11" <?php echo ($edit_driver && $edit_driver['os'] == 'Windows 11') ? 'selected' : ''; ?>>Windows 11</option>
                        <option value="Windows 10 64-bit" <?php echo ($edit_driver && $edit_driver['os'] == 'Windows 10 64-bit') ? 'selected' : ''; ?>>Windows 10 64-bit</option>
                        <option value="Windows 10 32-bit" <?php echo ($edit_driver && $edit_driver['os'] == 'Windows 10 32-bit') ? 'selected' : ''; ?>>Windows 10 32-bit</option>
                        <option value="Windows 10 / Windows 11" <?php echo ($edit_driver && $edit_driver['os'] == 'Windows 10 / Windows 11') ? 'selected' : ''; ?>>Windows 10 / Windows 11</option>
                        <option value="Windows 8.1 64-bit" <?php echo ($edit_driver && $edit_driver['os'] == 'Windows 8.1 64-bit') ? 'selected' : ''; ?>>Windows 8.1 64-bit</option>
                        <option value="Windows 8.1 32-bit" <?php echo ($edit_driver && $edit_driver['os'] == 'Windows 8.1 32-bit') ? 'selected' : ''; ?>>Windows 8.1 32-bit</option>
                        <option value="Windows 7 64-bit" <?php echo ($edit_driver && $edit_driver['os'] == 'Windows 7 64-bit') ? 'selected' : ''; ?>>Windows 7 64-bit</option>
                        <option value="Windows 7 32-bit" <?php echo ($edit_driver && $edit_driver['os'] == 'Windows 7 32-bit') ? 'selected' : ''; ?>>Windows 7 32-bit</option>
                        <option value="macOS" <?php echo ($edit_driver && $edit_driver['os'] == 'macOS') ? 'selected' : ''; ?>>macOS</option>
                        <option value="Linux" <?php echo ($edit_driver && $edit_driver['os'] == 'Linux') ? 'selected' : ''; ?>>Linux</option>
                    </select>
                </div>

                <!-- TIPO DE ALMACENAMIENTO -->
                <div class="admin-form-group">
                    <label class="admin-form-label">Tipo de Origen</label>
                    <div style="display: flex; gap: 20px; padding: 6px 0;">
                        <label style="cursor: pointer; display: flex; align-items: center; gap: 6px;">
                            <input type="radio" name="driver_type" value="1" <?php echo (!$edit_driver || $edit_driver['is_local'] == 1) ? 'checked' : ''; ?> onchange="toggleDriverType(1)"> Archivo Local
                        </label>
                        <label style="cursor: pointer; display: flex; align-items: center; gap: 6px;">
                            <input type="radio" name="driver_type" value="0" <?php echo ($edit_driver && $edit_driver['is_local'] == 0) ? 'checked' : ''; ?> onchange="toggleDriverType(0)"> Link Externo
                        </label>
                    </div>
                </div>

                <!-- SECCIÓN ARCHIVO LOCAL -->
                <div id="localFileGroup" class="admin-form-group" style="display: <?php echo (!$edit_driver || $edit_driver['is_local'] == 1) ? 'block' : 'none'; ?>;">
                    <label for="driver_file" class="admin-form-label">Archivo del Driver</label>
                    <?php if ($edit_driver && $edit_driver['is_local'] == 1 && !empty($edit_driver['download_url'])): ?>
                        <div style="margin-bottom: 10px; font-size: 12px; color: var(--text-secondary);">
                            <i class="fa-solid fa-file mr-2"></i> Archivo actual: <code><?php echo basename($edit_driver['download_url']); ?></code>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="driver_file" id="driver_file" class="admin-form-input" accept=".zip,.rar,.exe,.msi,.inf,.sys,.tar,.gz,.7z">
                    <small style="color: var(--text-secondary); display: block; margin-top: 4px;">ZIP, RAR, EXE, MSI, INF, SYS, 7Z (Max XAMPP upload limit)</small>
                </div>

                <!-- SECCIÓN LINK EXTERNO -->
                <div id="externalLinkGroup" style="display: <?php echo ($edit_driver && $edit_driver['is_local'] == 0) ? 'block' : 'none'; ?>;">
                    <div class="admin-form-group">
                        <label for="external_url" class="admin-form-label">URL de Descarga Externa</label>
                        <input type="url" name="external_url" id="external_url" class="admin-form-input" placeholder="Ej: https://download.lenovo.com/..." value="<?php echo ($edit_driver && $edit_driver['is_local'] == 0) ? htmlspecialchars($edit_driver['download_url']) : ''; ?>">
                    </div>
                    
                    <div class="admin-form-group">
                        <label for="manual_size" class="admin-form-label">Peso / Tamaño del Archivo</label>
                        <input type="text" name="manual_size" id="manual_size" class="admin-form-input" placeholder="Ej: 45.8 MB o 1.2 GB" value="<?php echo ($edit_driver && $edit_driver['is_local'] == 0) ? htmlspecialchars($edit_driver['file_size']) : ''; ?>">
                    </div>
                </div>

                <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; margin-top: 20px;">
                    <i class="fa-solid fa-floppy-disk"></i> <?php echo $edit_driver ? "Actualizar Controlador" : "Guardar Controlador"; ?>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function toggleDriverType(type) {
    const localGroup = document.getElementById('localFileGroup');
    const externalGroup = document.getElementById('externalLinkGroup');
    const fileInput = document.getElementById('driver_file');
    const urlInput = document.getElementById('external_url');
    
    if (type === 1) {
        localGroup.style.display = 'block';
        externalGroup.style.display = 'none';
        urlInput.required = false;
    } else {
        localGroup.style.display = 'none';
        externalGroup.style.display = 'block';
        urlInput.required = true;
    }
}

function toggleDrivers(eqId) {
    const allIcons = document.querySelectorAll('[id^="icon-"]');
    const allRows = document.querySelectorAll('[class^="driver-row-"]');
    const rows = document.querySelectorAll('.driver-row-' + eqId);
    const icon = document.getElementById('icon-' + eqId);
    
    let isHidden = false;
    if (rows.length > 0) {
        isHidden = rows[0].style.display === 'none' || rows[0].style.display === '';
    }
    
    // Primero, cerrar todos los demás
    allIcons.forEach(i => {
        if(i.id !== 'icon-' + eqId) i.style.transform = 'rotate(0deg)';
    });
    
    allRows.forEach(row => {
        if(!row.classList.contains('driver-row-' + eqId) && row.style.display !== 'none') {
            row.style.opacity = '0';
            row.style.transform = 'translateY(-10px)';
            setTimeout(() => {
                if(row.style.opacity === '0') row.style.display = 'none';
            }, 350);
        }
    });
    
    // Ahora hacer el toggle del que clickeamos
    if (isHidden) {
        icon.style.transform = 'rotate(90deg)';
        rows.forEach((row, index) => {
            row.style.display = 'table-row';
            row.style.transform = 'translateY(-10px)';
            void row.offsetWidth; // Reflow
            
            // Un pequeño retraso escalonado (stagger) para cada fila lo hace ver súper premium
            setTimeout(() => {
                row.style.opacity = '1';
                row.style.transform = 'translateY(0)';
            }, index * 30);
        });
        // Guardar el estado
        sessionStorage.setItem('openDriverAccordion', eqId);
    } else {
        icon.style.transform = 'rotate(0deg)';
        rows.forEach((row, index) => {
            setTimeout(() => {
                row.style.opacity = '0';
                row.style.transform = 'translateY(-10px)';
            }, (rows.length - index - 1) * 30);
            
            setTimeout(() => {
                if(row.style.opacity === '0') row.style.display = 'none';
            }, 350 + (rows.length * 30));
        });
        // Limpiar el estado si se cierra manual
        sessionStorage.removeItem('openDriverAccordion');
    }
}

// Abrir automáticamente el acordeón guardado al cargar la página
document.addEventListener('DOMContentLoaded', () => {
    // Si el usuario viene de otra página distinta a drivers.php, limpiamos el estado del acordeón
    if (!document.referrer.includes('drivers.php')) {
        sessionStorage.removeItem('openDriverAccordion');
    }

    const savedEqId = sessionStorage.getItem('openDriverAccordion');
    if (savedEqId && document.getElementById('icon-' + savedEqId)) {
        // En lugar de llamar toggleDrivers directo, forzamos la apertura silenciosa
        const rows = document.querySelectorAll('.driver-row-' + savedEqId);
        const icon = document.getElementById('icon-' + savedEqId);
        if(rows.length > 0) {
            icon.style.transform = 'rotate(90deg)';
            rows.forEach((row) => {
                row.style.display = 'table-row';
                row.style.opacity = '1';
                row.style.transform = 'translateY(0)';
            });
        }
    }
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
