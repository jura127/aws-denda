<?php
// 1. Cargar configuración y sesión (vital para AWS)
if (!file_exists('config.php')) {
    die("Error: El archivo 'config.php' no existe. Créalo con tus datos de AWS RDS.");
}
require_once 'config.php';

// --------------------
// INCLUSIÓN DE CLASES
// --------------------
// Asegúrate de que las rutas sean correctas según tu estructura de carpetas
require_once __DIR__ . '/klaseak/com/leartik/daw24unju/produktuak/produktuak.php';
require_once __DIR__ . '/klaseak/com/leartik/daw24unju/produktuak/produktuak_db.php';
require_once __DIR__ . '/klaseak/com/leartik/daw24unju/kategoriak/kategoriak.php';
require_once __DIR__ . '/klaseak/com/leartik/daw24unju/kategoriak/kategoriak_db.php';

// --------------------
// OBTENER Y AGRUPAR DATOS
// --------------------
// Obtenemos los datos de AWS RDS
$productos_todos = ProductosDB::selectProduktuak() ?? [];
$categorias_list = CategoriasDB::selectCategorias() ?? [];

$productos_por_categoria = [];

// Preparamos el array de categorías usando el ID como llave
foreach ($categorias_list as $cat) {
    $id_cat = $cat->getId(); // Asegúrate de que este ID coincida con el de MySQL
    $productos_por_categoria[$id_cat] = [
        'nombre' => $cat->getNombre(),
        'productos' => []
    ];
}

// Repartimos los productos en sus respectivas categorías
foreach ($productos_todos as $p) {
    $id_cat = $p->getIdCategoria();
    
    if (isset($productos_por_categoria[$id_cat])) {
        $producto_data = [
            'id' => $p->getIdProducto(),
            'nombre' => $p->getTipoProducto(),
            'descripcion' => $p->getDescripcion(),
            'precio' => (float)$p->getPrecio(),
            'tiene_opc_añadir_cesta' => $p->getTieneOpcAñadirCesta(),
            'ofertas' => $p->getOfertas(),
            'novedades' => $p->getNovedades(),
            'imagen_url' => 'img/placeholder.png' 
        ];

        // Lógica de precios para ofertas
        if ($producto_data['ofertas']) {
            $producto_data['precio_original'] = $producto_data['precio'];
            $producto_data['precio_oferta'] = $producto_data['precio'] * 0.8; // 20% descuento
        }

        $productos_por_categoria[$id_cat]['productos'][] = $producto_data;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <title>Catálogo Completo | Tienda de Magia</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    
<header class="grid-header">
    <div class="logo-container">
        <img src="img/cajaconcartas-modified.jpg" alt="Logo Tienda de Magia" class="logo">
    </div>
    <nav class="main-nav">
        <ul>
            <li><a href="index.php">Inicio</a></li>
            <li><a href="catalogo.php" class="active">Catálogo</a></li>
            <li><a href="accesorios.php">Accesorios</a></li>
            <li><a href="contacto/index.php">Contacto</a></li>
            <li><a href="mediateka/mediateka.html">Mediateka</a></li>
            <li><a href="cesta.php">Cesta</a></li>
            <li>
                <?php if ($esta_logueado): ?>
                    <div class="user-info-nav">
                        <a href="login.php?action=logout" class="logout-link">Logout</a>
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
    <h1>Catálogo Completo</h1>

    <div class="search-wrapper">
        <label for="buscar-nombre">Buscar productos:</label>
        <input type="text" id="buscar-nombre" placeholder="Escribe para buscar..." autocomplete="off">
        <div id="sugerencias"></div>
    </div>
    
    <?php if (empty($productos_todos)): ?>
        <div class="section-container">
            <p class="no-productos">Actualmente no hay productos en el catálogo de AWS.</p>
        </div>
    <?php else: ?>
        <?php foreach ($productos_por_categoria as $categoria): 
            if (empty($categoria['productos'])) continue; // No mostramos categorías vacías
            
            $nombre_cat = $categoria['nombre'];
            $productos_en_cat = $categoria['productos'];
        ?>
            <div class="section-container">
                <h2><?= htmlspecialchars($nombre_cat) ?></h2>
                <div class="productos-seccion-grid">
                    
                    <?php foreach ($productos_en_cat as $producto): ?>
                        <div class="producto-card" data-nombre="<?= strtolower(htmlspecialchars($producto['nombre'])) ?>">
                            <p class="producto-nombre"><?= htmlspecialchars($producto['nombre']) ?></p>
                            
                            <div class="prices-container">
                                <?php if ($producto['ofertas']): ?>
                                    <del class="original-price"><?= number_format($producto['precio_original'], 2) ?> €</del>
                                    <span class="sale-price"><?= number_format($producto['precio_oferta'], 2) ?> €</span>
                                <?php else: ?>
                                    <p class="producto-precio"><?= number_format($producto['precio'], 2) ?> €</p>
                                <?php endif; ?>
                            </div>

                            <form action="cesta.php" method="post">
                                <input type="hidden" name="producto_id" value="<?= $producto['id'] ?>">
                                <button type="submit" class="add-to-cart-btn">AÑADIR A LA CESTA</button>
                            </form>
                        </div> 
                    <?php endforeach; ?>

                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</main>

<footer>
    <p>&copy; Tienda de Magia 2026. AWS RDS Cloud Version.</p>
</footer>

<script>
$(document).ready(function() {
    // Lógica de filtrado en tiempo real
    $('#buscar-nombre').on('keyup', function() {
        let texto = $(this).val().toLowerCase();
        
        $('.producto-card').each(function() {
            let nombre = $(this).data('nombre');
            $(this).toggle(nombre.includes(texto));
        });
    });
});
</script>

</body>
</html>
