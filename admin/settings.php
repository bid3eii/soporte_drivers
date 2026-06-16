<?php
$admin_title = "Ajustes del Sitio";
$admin_heading = "Ajustes del Sitio Web";
$admin_subheading = "Configura el nombre de tu plataforma y el logotipo principal.";

require_once __DIR__ . '/header.php';

// Asegurar que la carpeta de subida exista
$upload_dir = __DIR__ . '/../uploads/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $site_name = trim($_POST['site_name'] ?? '');
    
    // Guardar ajustes de texto generales
    $text_keys = ['site_name', 'hero_title', 'hero_subtitle'];
    foreach ($text_keys as $key) {
        if (isset($_POST[$key])) {
            $val = trim($_POST[$key]);
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->execute([$key, $val, $val]);
        }
    }
    
    // Guardar botones rápidos en su nuevo orden
    if (isset($_POST['quick_text']) && is_array($_POST['quick_text']) && isset($_POST['quick_icon']) && is_array($_POST['quick_icon'])) {
        for ($i = 0; $i < 5; $i++) {
            $idx = $i + 1;
            $text_val = trim($_POST['quick_text'][$i] ?? '');
            $icon_val = trim($_POST['quick_icon'][$i] ?? '');
            
            // Text
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->execute(["quick_$idx", $text_val, $text_val]);
            
            // Icon
            $stmt->execute(["quick_{$idx}_icon", $icon_val, $icon_val]);
        }
    }

    $msg = "Ajustes guardados correctamente.";
    $type = "success";

    // Subida de logo
    if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['site_logo']['tmp_name'];
        $file_name = basename($_FILES['site_logo']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_exts = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'];

        if (in_array($file_ext, $allowed_exts)) {
            $new_file_name = 'logo_main_' . uniqid() . '.' . $file_ext;
            $target_file = $upload_dir . $new_file_name;

            if (move_uploaded_file($file_tmp, $target_file)) {
                $logo_path = 'uploads/' . $new_file_name;
                
                $stmt = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'site_logo'");
                $old_logo = $stmt->fetchColumn();
                if ($old_logo && file_exists(__DIR__ . '/../' . $old_logo)) {
                    unlink(__DIR__ . '/../' . $old_logo);
                }

                $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('site_logo', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                $stmt->execute([$logo_path, $logo_path]);
                
                $msg = "Ajustes y logo guardados correctamente.";
                $type = "success";
            }
        } else {
            $msg = "Formato de archivo no válido para el logo.";
            $type = "danger";
        }
    }
    
    header("Location: settings.php?msg=" . urlencode($msg) . "&type=" . $type);
    exit;
}

if (isset($_GET['msg'])) {
    $message = $_GET['msg'];
    $message_type = isset($_GET['type']) ? $_GET['type'] : 'success';
}

// Obtener ajustes actuales
$settings_query = $pdo->query("SELECT setting_key, setting_value FROM settings");
$settings = [];
while ($row = $settings_query->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

$current_name = isset($settings['site_name']) ? $settings['site_name'] : 'SoporteMaster';
$current_logo = isset($settings['site_logo']) ? $settings['site_logo'] : '';
$hero_title = isset($settings['hero_title']) ? $settings['hero_title'] : 'Identifica tu Producto';
$hero_subtitle = isset($settings['hero_subtitle']) ? $settings['hero_subtitle'] : 'Busca tu equipo o componente para encontrar actualizaciones de controladores y software oficial.';
$quick_1 = isset($settings['quick_1']) ? $settings['quick_1'] : 'Laptops';
$quick_2 = isset($settings['quick_2']) ? $settings['quick_2'] : 'Desktops';
$quick_3 = isset($settings['quick_3']) ? $settings['quick_3'] : 'Impresoras';
$quick_4 = isset($settings['quick_4']) ? $settings['quick_4'] : 'Red';
$quick_5 = isset($settings['quick_5']) ? $settings['quick_5'] : 'Gráficos';

$quick_1_icon = isset($settings['quick_1_icon']) ? $settings['quick_1_icon'] : 'fa-laptop';
$quick_2_icon = isset($settings['quick_2_icon']) ? $settings['quick_2_icon'] : 'fa-desktop';
$quick_3_icon = isset($settings['quick_3_icon']) ? $settings['quick_3_icon'] : 'fa-print';
$quick_4_icon = isset($settings['quick_4_icon']) ? $settings['quick_4_icon'] : 'fa-network-wired';
$quick_5_icon = isset($settings['quick_5_icon']) ? $settings['quick_5_icon'] : 'fa-microchip';
?>

<?php if (!empty($message)): ?>
    <div class="alert <?php echo $message_type; ?>" style="padding: 10px 15px; font-size: 14px; max-width: 900px; margin: 0 auto 20px auto; display: flex; align-items: center; justify-content: center; border-radius: 6px;">
        <i class="fa-solid <?php echo $message_type == 'success' ? 'fa-circle-check' : 'fa-circle-exclamation'; ?>" style="margin-right: 8px;"></i>
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<form action="settings.php" method="POST" enctype="multipart/form-data">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(380px, 1fr)); max-width: 1600px; margin: 0 auto; gap: 25px; align-items: stretch;">
        
        <!-- Tarjeta 1: Identidad -->
        <div class="panel-card" style="display: flex; flex-direction: column;">
            <div class="panel-card-header">
                <div class="panel-card-title"><i class="fa-solid fa-palette" style="color: var(--primary); margin-right: 8px;"></i> Identidad del Sitio</div>
            </div>
            <div style="padding: 24px; flex: 1;">
                <div class="admin-form-group">
                    <label for="site_name" class="admin-form-label">Nombre del Sitio Web</label>
                    <input type="text" name="site_name" id="site_name" class="admin-form-input" value="<?php echo htmlspecialchars($current_name); ?>">
                    <small style="color: var(--text-secondary); display: block; margin-top: 4px;">Este texto aparecerá en la barra de navegación y títulos. Déjalo en blanco si solo quieres mostrar el logo.</small>
                </div>

                <div class="admin-form-group" style="margin-top: 20px; border-top: 1px solid var(--border); padding-top: 20px;">
                    <label for="site_logo" class="admin-form-label">Logotipo Principal</label>
                    
                    <?php if (!empty($current_logo) && file_exists(__DIR__ . '/../' . $current_logo)): ?>
                        <div style="margin-bottom: 15px; padding: 15px; background: rgba(0,0,0,0.2); border-radius: 8px; border: 1px dashed var(--border); text-align: center;">
                            <img src="../<?php echo htmlspecialchars($current_logo); ?>" style="max-height: 80px; max-width: 100%; object-fit: contain;" alt="Logo actual">
                        </div>
                    <?php else: ?>
                        <div style="margin-bottom: 15px; padding: 15px; background: rgba(0,0,0,0.2); border-radius: 8px; border: 1px dashed var(--border); text-align: center; color: var(--text-muted);">
                            <i class="fa-solid fa-laptop-code" style="font-size: 40px; margin-bottom: 10px;"></i><br>
                            Sin logo personalizado (Se usa icono por defecto)
                        </div>
                    <?php endif; ?>
                    
                    <input type="file" name="site_logo" id="site_logo" class="admin-form-input" accept="image/*">
                    <small style="color: var(--text-secondary); display: block; margin-top: 4px;">Sube una imagen (PNG transparente recomendado). Deja vacío para conservar el actual.</small>
                </div>
            </div>
        </div>

        <!-- Tarjeta 2: Textos Portada -->
        <div class="panel-card" style="display: flex; flex-direction: column;">
            <div class="panel-card-header">
                <div class="panel-card-title"><i class="fa-solid fa-heading" style="color: var(--primary); margin-right: 8px;"></i> Textos de Portada</div>
            </div>
            <div style="padding: 24px; flex: 1;">
                <div class="admin-form-group">
                    <label for="hero_title" class="admin-form-label">Título Principal</label>
                    <input type="text" name="hero_title" id="hero_title" class="admin-form-input" value="<?php echo htmlspecialchars($hero_title); ?>" required>
                </div>
                
                <div class="admin-form-group" style="margin-top: 20px;">
                    <label for="hero_subtitle" class="admin-form-label">Subtítulo Descriptivo</label>
                    <textarea name="hero_subtitle" id="hero_subtitle" class="admin-form-textarea" style="height: 120px;" required><?php echo htmlspecialchars($hero_subtitle); ?></textarea>
                </div>
            </div>
        </div>

        <!-- Tarjeta 3: Botones Rápida -->
        <div class="panel-card" style="display: flex; flex-direction: column;">
            <div class="panel-card-header">
                <div class="panel-card-title"><i class="fa-solid fa-bolt" style="color: var(--primary); margin-right: 8px;"></i> Botones de Búsqueda Rápida</div>
            </div>
            <div style="padding: 24px; flex: 1;">
                <small style="color: var(--text-secondary); display: block; margin-bottom: 15px;">Arrastra para reordenar. Edita ícono o nombre.</small>
                
                <div id="sortable-quick-links" style="display: flex; flex-direction: column; gap: 10px;">
                    <?php 
                    $defaults = [
                        1 => ['text' => 'Laptops', 'icon' => 'fa-laptop'],
                        2 => ['text' => 'Desktops', 'icon' => 'fa-desktop'],
                        3 => ['text' => 'Impresoras', 'icon' => 'fa-print'],
                        4 => ['text' => 'Red', 'icon' => 'fa-network-wired'],
                        5 => ['text' => 'Gráficos', 'icon' => 'fa-microchip']
                    ];
                    for ($i = 1; $i <= 5; $i++) { 
                        $q_text = isset($settings["quick_$i"]) ? $settings["quick_$i"] : $defaults[$i]['text'];
                        $q_icon = isset($settings["quick_{$i}_icon"]) ? $settings["quick_{$i}_icon"] : $defaults[$i]['icon'];
                    ?>
                    <div class="quick-link-row" style="display: flex; align-items: center; gap: 10px; background: rgba(0,0,0,0.2); padding: 10px; border-radius: 8px; border: 1px solid var(--border); cursor: grab; transition: transform 0.2s;">
                        <i class="fa-solid fa-grip-vertical" style="color: var(--text-muted); padding: 0 5px;"></i>
                        <div style="flex: 1; display: flex; gap: 10px;">
                            <div style="position: relative; width: 110px;">
                                <div style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"><i class="fa-solid <?php echo htmlspecialchars($q_icon); ?> fallback-icon"></i></div>
                                <input type="text" name="quick_icon[]" class="admin-form-input icon-input" style="padding-left: 35px;" value="<?php echo htmlspecialchars($q_icon); ?>" placeholder="fa-laptop" required>
                            </div>
                            <input type="text" name="quick_text[]" class="admin-form-input" style="flex: 1;" value="<?php echo htmlspecialchars($q_text); ?>" placeholder="Nombre" required>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        
    </div>

    <!-- Barra de Guardado Inferior -->
    <div style="max-width: 1600px; margin: 30px auto; padding: 20px; background: rgba(0,0,0,0.2); border-radius: 12px; border: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
        <div style="display: flex; align-items: center; gap: 15px; color: var(--text-secondary); font-size: 14px;">
            <i class="fa-solid fa-circle-info" style="font-size: 18px; color: var(--primary);"></i>
            <span>Revisa que toda la configuración esté correcta. Los cambios se reflejarán instantáneamente en la portada pública.</span>
        </div>
        <button type="submit" class="btn-primary" style="font-size: 16px; padding: 14px 35px; border-radius: 8px; background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); border: none; box-shadow: 0 4px 15px rgba(99,102,241,0.3); font-weight: 600; cursor: pointer; transition: transform 0.2s, box-shadow 0.2s; min-width: 250px; justify-content: center;">
            <i class="fa-solid fa-save" style="margin-right: 8px;"></i> Guardar Todos los Cambios
        </button>
    </div>
</form>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    var sortableList = document.getElementById('sortable-quick-links');
    if (sortableList) {
        new Sortable(sortableList, {
            animation: 150,
            ghostClass: 'sortable-ghost',
            handle: '.fa-grip-vertical'
        });
    }

    // Dynamic icon preview
    const iconInputs = document.querySelectorAll('.icon-input');
    iconInputs.forEach(input => {
        input.addEventListener('input', function() {
            const previewIcon = this.previousElementSibling.querySelector('.fallback-icon');
            if(previewIcon) {
                previewIcon.className = 'fa-solid ' + this.value + ' fallback-icon';
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
