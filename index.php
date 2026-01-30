<?php
// 1. Cargamos la configuración de sesión (asegúrate de que el archivo config.php existe)
require_once 'config.php';

// --------------------
// INCLUSIÓN DE CLASES
// --------------------
// Mantenemos tus rutas originales
require_once __DIR__ . '/klaseak/com/leartik/daw24unju/produktuak/produktuak.php';
require_once __DIR__ . '/klaseak/com/leartik/daw24unju/produktuak/produktuak_db.php';
require_once __DIR__ . '/klaseak/com/leartik/daw24unju/kategoriak/kategoriak.php';
require_once __DIR__ . '/klaseak/com/leartik/daw24unju/kategoriak/kategoriak_db.php';

// --------------------
// OBTENER PRODUCTOS
// --------------------
$productos = ProductosDB::selectProduktuak() ?? [];

// Inicializamos arrays para filtrar por novedades y ofertas
$novedades = [];
$ofertas = [];

foreach ($productos as $p) {
    $producto_array = [
        'id' => $p->getIdProducto(),
        'nombre' => $p->getTipoProducto(),
        'precio' => $p->getPrecio(),
        'imagen_url' => 'img/placeholder.png'
    ];

    if ($p->getNovedades()) {
        $novedades[] = $producto_array;
    }

    if ($p->getOfertas()) {
        $producto_array['precio_original'] = $p->getPrecio();
        $producto_array['precio_oferta'] = $p->getPrecio() * 0.8;
        $ofertas[] = $producto_array;
    }
}

// --------------------
// MANEJO DE MENSAJES DE ÉXITO
// --------------------
$mensaje_exito = '';
if (isset($_SESSION['add_to_cart_success']) && $_SESSION['add_to_cart_success']) {
    $mensaje_exito = '✅ Producto añadido a la cesta correctamente.';
    unset($_SESSION['add_to_cart_success']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda de Magia | Inicio</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<header class="grid-header">
    <div class="logo-container">
        <img src="https://erronkadenda.s3.eu-south-2.amazonaws.com/cajaconcartas-modified.jpg" alt="Logo Tienda de Magia" class="logo">
    </div>
    <nav class="main-nav">
        <ul>
            <li><a href="index.php" class="active">Inicio</a></li>
            <li><a href="catalogo.php">Catálogo</a></li>
            <li><a href="accesorios.php">Accesorios</a></li>
            <li><a href="contacto/index.php">Contacto</a></li>
            <li><a href="mediateka/mediateka.html">Mediateka</a></li>
            <li><a href="cesta.php">Cesta</a></li>
            <li>
                <?php if ($esta_logueado): ?>
                    <div class="user-info-nav">
                        <a href="login.php?action=logout" class="logout-link" title="Cerrar Sesión">Logout</a>
                        <span class="user-name">(<?= htmlspecialchars($nombre_usuario) ?>)</span>
                        
                    </div>
                <?php else: ?>
                    <a href="login.php">Login/Reg</a>
                <?php endif; ?>
            </li>
        </ul>
    </nav>
</header>

<main> 
    <div class="hero-section"> 
        <div class="hero-image"> 
            <img src="img/sombrero_neon.png" alt="Sombrero de Mago y Varita" class="magic-hat"> 
        </div> 
        <div class="hero-text"> 
            <h1><?= $esta_logueado ? "BIENVENIDO, " . strtoupper(htmlspecialchars($nombre_usuario)) : "DESCUBRE LA MAGIA EN TUS MANOS" ?></h1> 
            <a href="accesorios.php" class="explore-button">Explora la tienda</a> 
        </div> 
    </div>

    <?php if ($mensaje_exito): ?>
        <div class="cart-notification success active-notification">
            <?= htmlspecialchars($mensaje_exito) ?>
        </div>
    <?php endif; ?>

    <div class="section-container">
        <h2>Novedades</h2>
        <div class="productos-seccion-grid">
            <?php if (empty($novedades)): ?>
                <p class="no-productos">No hay productos nuevos disponibles en este momento.</p>
            <?php else: ?>
                <?php foreach ($novedades as $producto): ?>
                    <div class="producto-card">
                        <p class="producto-nombre"><?= htmlspecialchars($producto['nombre']) ?></p>
                        <p class="producto-precio"><?= number_format($producto['precio'], 2) ?> €</p>
                        <form action="cesta.php" method="post">
                            <input type="hidden" name="producto_id" value="<?= $producto['id'] ?>">
                            <button type="submit" class="add-to-cart-btn">AÑADIR A LA CESTA</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="section-container">
        <h2>Ofertas Destacadas</h2>
        <div class="productos-seccion-grid">
            <?php if (empty($ofertas)): ?>
                <p class="no-productos">No hay productos en oferta actualmente.</p>
            <?php else: ?>
                <?php foreach ($ofertas as $producto): ?>
                    <div class="producto-card">
                        <p class="producto-nombre"><?= htmlspecialchars($producto['nombre']) ?></p>
                        <div class="prices-container">
                            <del class="original-price"><?= number_format($producto['precio_original'], 2) ?> €</del>
                            <span class="sale-price"><?= number_format($producto['precio_oferta'], 2) ?> €</span>
                        </div>
                        <form action="cesta.php" method="post">
                            <input type="hidden" name="producto_id" value="<?= $producto['id'] ?>">
                            <button type="submit" class="add-to-cart-btn">AÑADIR A LA CESTA</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<footer>
    <p>&copy; Tienda de Magia 2025. Todos los derechos reservados.</p>
</footer>

</body>
</html>
