<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si la sesión del administrador está activa
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
