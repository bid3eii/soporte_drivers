<?php
require_once __DIR__ . '/includes/db.php';

$driver_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($driver_id <= 0) {
    header("Location: index.php");
    exit;
}

// Obtener detalles del controlador
$stmt = $pdo->prepare("SELECT * FROM drivers WHERE id = ?");
$stmt->execute([$driver_id]);
$driver = $stmt->fetch();

if (!$driver) {
    header("Location: index.php");
    exit;
}

// Incrementar el contador de descargas en la base de datos
$update_stmt = $pdo->prepare("UPDATE drivers SET download_count = download_count + 1 WHERE id = ?");
$update_stmt->execute([$driver_id]);

// Determinar URL de descarga
$download_path = $driver['download_url'];

// Redireccionar al recurso (ya sea archivo local subido o link externo oficial)
header("Location: " . $download_path);
exit;
