<?php
session_start();

require_once __DIR__ . '/../config.php';
// ---------------------------------------------

// CÓDIGO PHP para gestionar el envío del formulario
require_once __DIR__ . '/../klaseak/com/leartik/daw24unju/mezuak/mezuak.php';
require_once __DIR__ . '/../klaseak/com/leartik/daw24unju/mezuak/mezuak_db.php';

// Si recibimos datos por POST (llamada AJAX)
if (isset($_POST['izena']) && isset($_POST['email']) && isset($_POST['mezuaren_testua'])) {
    
    // 1. Recoger y sanear los datos
    $izena = trim($_POST['izena']);
    $email = trim($_POST['email']);
    $mezuaren_testua = trim($_POST['mezuaren_testua']);

    // 2. Crear y configurar el objeto Mensajes
    $mezua = new Mensajes();
    $mezua->setNombre($izena);
    $mezua->setEmail($email);
    $mezua->setMensaje($mezuaren_testua);

    // 3. Insertar en la base de datos
    if (MensajesDB::insertMensajes($mezua) > 0) {
        // Éxito: La respuesta AJAX será el HTML de éxito.
        include 'mezua_gorde_da.php'; 
    } else {
        // Fallo: La respuesta AJAX será el HTML de error.
        include 'mezua_ez_da_gorde.php';
    }

} else {
    // Si NO recibe datos por POST (es la carga inicial de la página)
    
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Mezuak (Contacto)</title>
        <link rel="stylesheet" href="../css/styles.css">
        <script type="text/javascript" src="api.js"></script>
    </head>
    <body>
    
<header class="grid-header">
    <div class="logo-container">
        <img src="img/cajaconcartas-modified.jpg" alt="Logo Tienda de Magia" class="logo">
    </div>
    <nav class="main-nav">
        <ul>
            <li><a href="../index.php" >Inicio</a></li>
            <li><a href="../catalogo.php">Catálogo</a></li>
            <li><a href="../accesorios.php">Accesorios</a></li>
            <li><a href="contacto/index.php"  class="active">Contacto</a></li>
            <li><a href="../mediateka/mediateka.html">Mediateka</a></li>
            <li><a href="../cesta.php">Cesta</a></li>
            <li>
                <?php if ($esta_logueado): ?>
                    <div class="user-info-nav">
                        <a href="login.php?action=logout" class="logout-link" title="Cerrar Sesión">Logout</a>
                        <span class="user-name">(<?= htmlspecialchars($nombre_usuario) ?>)</span>
                        
                    </div>
                <?php else: ?>
                    <a href="../login.php">Login/Reg</a>
                <?php endif; ?>
            </li>
        </ul>
    </nav>
</header>
    
    <main> 
        <div class="section-container"> 
            <h1>Mezuak (Mensajes Secretos)</h1>
            
            <?php
            include 'mezua_berria.php'; 
            ?>

        </div>
    </main>
    
    <footer>
        <p>&copy; Tienda de Magia 2025. Todos los derechos reservados.</p>
    </footer>
    </body>
    </html>
    <?php
}
?>