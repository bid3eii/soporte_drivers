<?php
$admin_title = "Preguntas Frecuentes";
$admin_heading = "Preguntas Frecuentes (FAQ)";
$admin_subheading = "Administra las preguntas y respuestas que se muestran en la portada del sitio.";

require_once __DIR__ . '/header.php';

$message = '';
$message_type = '';

// --- PROCESAR ELIMINACIÓN ---
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $del_stmt = $pdo->prepare("DELETE FROM faqs WHERE id = ?");
    if ($del_stmt->execute([$delete_id])) {
        $message = "Pregunta eliminada correctamente.";
        $message_type = "success";
    } else {
        $message = "Error al intentar eliminar la pregunta.";
        $message_type = "danger";
    }
}

// --- PROCESAR CREACIÓN / EDICIÓN ---
$edit_faq = null;
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $edit_stmt = $pdo->prepare("SELECT * FROM faqs WHERE id = ?");
    $edit_stmt->execute([$edit_id]);
    $edit_faq = $edit_stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question = trim($_POST['question']);
    $answer = trim($_POST['answer']);
    $order_index = isset($_POST['order_index']) ? intval($_POST['order_index']) : 0;
    $faq_id = isset($_POST['faq_id']) ? intval($_POST['faq_id']) : 0;
    
    if (!empty($question) && !empty($answer)) {
        if ($faq_id > 0) {
            // Editar
            $sql = "UPDATE faqs SET question = ?, answer = ?, order_index = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$question, $answer, $order_index, $faq_id])) {
                $message = "Pregunta actualizada correctamente.";
                $message_type = "success";
                // Recargar edit_faq
                $edit_stmt = $pdo->prepare("SELECT * FROM faqs WHERE id = ?");
                $edit_stmt->execute([$faq_id]);
                $edit_faq = $edit_stmt->fetch();
            } else {
                $message = "Error al actualizar la pregunta.";
                $message_type = "danger";
            }
        } else {
            // Crear
            $sql = "INSERT INTO faqs (question, answer, order_index) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$question, $answer, $order_index])) {
                $message = "Pregunta agregada correctamente.";
                $message_type = "success";
            } else {
                $message = "Error al guardar la pregunta.";
                $message_type = "danger";
            }
        }
    } else {
        $message = "La pregunta y la respuesta son requeridas.";
        $message_type = "danger";
    }
}

// Obtener FAQs
$faqs = $pdo->query("SELECT * FROM faqs ORDER BY order_index ASC, id ASC")->fetchAll();
?>

<?php if (!empty($message)): ?>
    <div class="alert <?php echo $message_type; ?>">
        <i class="fa-solid <?php echo $message_type == 'success' ? 'fa-circle-check' : 'fa-circle-exclamation'; ?> mr-2"></i>
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 350px; gap: 30px; align-items: start;">
    <!-- Tabla de FAQs -->
    <div class="panel-card">
        <div class="panel-card-header">
            <div class="panel-card-title">Preguntas Registradas</div>
        </div>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 60px; text-align: center;">Orden</th>
                        <th>Pregunta</th>
                        <th>Respuesta</th>
                        <th style="width: 100px; text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($faqs)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--text-secondary); padding: 30px;">
                                No hay preguntas frecuentes registradas.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($faqs as $faq): ?>
                            <tr>
                                <td style="text-align: center;">
                                    <span class="badge" style="background: rgba(255,255,255,0.1);"><?php echo $faq['order_index']; ?></span>
                                </td>
                                <td><strong><?php echo htmlspecialchars($faq['question']); ?></strong></td>
                                <td style="color: var(--text-secondary); max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    <?php echo htmlspecialchars($faq['answer']); ?>
                                </td>
                                <td>
                                    <div class="action-buttons" style="justify-content: center;">
                                        <a href="faqs.php?edit_id=<?php echo $faq['id']; ?>" class="btn-icon edit" title="Editar">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <a href="faqs.php?delete_id=<?php echo $faq['id']; ?>" class="btn-icon delete" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar esta pregunta?');">
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

    <!-- Formulario -->
    <div class="panel-card">
        <div class="panel-card-header">
            <div class="panel-card-title">
                <?php echo $edit_faq ? "Editar Pregunta" : "Agregar Pregunta"; ?>
            </div>
            <?php if ($edit_faq): ?>
                <a href="faqs.php" style="font-size: 12px; color: var(--accent);"><i class="fa-solid fa-plus"></i> Nueva</a>
            <?php endif; ?>
        </div>
        <div style="padding: 24px;">
            <form action="faqs.php<?php echo $edit_faq ? '?edit_id=' . $edit_faq['id'] : ''; ?>" method="POST">
                <?php if ($edit_faq): ?>
                    <input type="hidden" name="faq_id" value="<?php echo $edit_faq['id']; ?>">
                <?php endif; ?>

                <div class="admin-form-group">
                    <label for="question" class="admin-form-label">Pregunta</label>
                    <input type="text" name="question" id="question" class="admin-form-input" placeholder="Ej: ¿Qué es un controlador?" value="<?php echo $edit_faq ? htmlspecialchars($edit_faq['question']) : ''; ?>" required>
                </div>

                <div class="admin-form-group">
                    <label for="answer" class="admin-form-label">Respuesta</label>
                    <textarea name="answer" id="answer" class="admin-form-textarea" style="height: 120px;" placeholder="Escribe la respuesta detallada..." required><?php echo $edit_faq ? htmlspecialchars($edit_faq['answer']) : ''; ?></textarea>
                </div>
                
                <div class="admin-form-group">
                    <label for="order_index" class="admin-form-label">Orden (Prioridad)</label>
                    <input type="number" name="order_index" id="order_index" class="admin-form-input" placeholder="0" value="<?php echo $edit_faq ? $edit_faq['order_index'] : '0'; ?>">
                    <small style="color: var(--text-secondary); display: block; margin-top: 4px;">Las preguntas con menor número aparecerán primero.</small>
                </div>

                <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; margin-top: 10px;">
                    <i class="fa-solid fa-floppy-disk"></i> <?php echo $edit_faq ? "Actualizar" : "Guardar"; ?>
                </button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
