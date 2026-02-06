<?php
// config.php

// 1. Iniciar la sesión solo si no está ya iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 2. Variables globales para usar en el Header
$esta_logueado = isset($_SESSION['usuario_logueado']) && $_SESSION['usuario_logueado'] === true;
$nombre_usuario = $_SESSION['nombre_usuario'] ?? 'Invitado';

// 3. Función para proteger páginas privadas
function checkAcceso() {
    if (!isset($_SESSION['usuario_logueado'])) {
        header('Location: login.php');
        exit;
    }
}

// 4. NUEVO: Conexión a Base de Datos AWS RDS
function getDBConnection(): PDO {
    // Definimos los datos de conexión
    // NOTA: Para subir a GitHub, lo ideal es que estos valores vengan de un archivo .env
    // o un archivo externo que esté en el .gitignore
    $host = 'erronka.c9ig24qucwtm.eu-south-2.rds.amazonaws.com';
    $db   = 'erronka';
    $user = 'admin';
    $pass = 'Unaijurado23'; // <--- BORRA ESTO ANTES DE SUBIR A GITHUB

    try {
        $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        return new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        // No mostramos el error real al usuario por seguridad
        die("Error crítico: No se pudo conectar con el servidor de datos.");
    }
}
?>