<?php
// config.php

// 1. Iniciar la sesión solo si no está ya iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 2. Configuración de la base de datos MySQL
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'daw24unju');

// 3. Variables globales para usar en el Header de cualquier página
$esta_logueado = isset($_SESSION['usuario_logueado']) && $_SESSION['usuario_logueado'] === true;
$nombre_usuario = $_SESSION['nombre_usuario'] ?? 'Invitado';

// 4. (Opcional) Función para proteger páginas privadas
function checkAcceso() {
    if (!isset($_SESSION['usuario_logueado'])) {
        header('Location: login.php');
        exit;
    }
}
?>