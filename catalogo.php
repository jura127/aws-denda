<?php
require_once 'config.php';
// --------------------
// INCLUSIÓN DE CLASES
// --------------------
require_once __DIR__ . '/klaseak/com/leartik/daw24unju/produktuak/produktuak.php';
require_once __DIR__ . '/klaseak/com/leartik/daw24unju/produktuak/produktuak_db.php';
require_once __DIR__ . '/klaseak/com/leartik/daw24unju/kategoriak/kategoriak.php';
require_once __DIR__ . '/klaseak/com/leartik/daw24unju/kategoriak/kategoriak_db.php';

// --------------------
// OBTENER Y AGRUPAR DATOS
// --------------------
$productos_todos = ProductosDB::selectProduktuak() ?? [];
$categorias_list = CategoriasDB::selectCategorias() ?? [];

$productos_por_categoria = [];

foreach ($categorias_list as $cat) {
    $id_cat = $cat->getId();
    $productos_por_categoria[$id_cat] = [
        'nombre' => $cat->getNombre(),
        'productos' => []
    ];
}

foreach ($productos_todos as $p) {
    $id_cat = $p->getIdCategoria();
    
    if (isset($productos_por_categoria[$id_cat])) {
        $producto_data = [
            'id' => $p->getIdProducto(),
            'nombre' => $p->getTipoProducto(),
            'descripcion' => $p->getDescripcion(),
            'precio' => $p->getPrecio(),
            'tiene_opc_añadir_cesta' => $p->getTieneOpcAñadirCesta(),
            'ofertas' => $p->getOfertas(),
            'novedades' => $p->getNovedades(),
            'imagen_url' => 'img/placeholder.png' 
        ];

        if ($producto_data['ofertas']) {
            $producto_data['precio_original'] = $p->getPrecio();
            $producto_data['precio_oferta'] = $p->getPrecio() * 0.8; 
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
                <?php if (isset($esta_logueado) && $esta_logueado): ?>
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
    <h1>Catálogo Completo</h1>

    <div class="search-wrapper">
        <label for="buscar-nombre">Buscar nombres:</label>
        <input type="text" id="buscar-nombre" placeholder="Busca lo que necesitas..." autocomplete="off">
        <div id="sugerencias"></div>
    </div>
    
    <?php if (empty($productos_todos)): ?>
        <div class="section-container">
            <p class="no-productos">Actualmente no hay productos en el catálogo.</p>
        </div>
    <?php else: ?>
        <?php foreach ($productos_por_categoria as $categoria): 
            $nombre_cat = $categoria['nombre'];
            $productos_en_cat = $categoria['productos'];
        ?>
            <div class="section-container">
                <h2><?= htmlspecialchars($nombre_cat) ?></h2>
                <div class="productos-seccion-grid">
                    
                    <?php if (empty($productos_en_cat)): ?>
                        <p class="no-productos">No hay productos disponibles en esta sección.</p>
                    <?php else: ?>
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
                    <?php endif; ?>

                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</main>

<footer>
    <p>&copy; Tienda de Magia 2025. Todos los derechos reservados.</p>
</footer>

<script>
$(document).ready(function() {
    $('#buscar-nombre').on('keyup', function() {
        let texto = $(this).val().toLowerCase();
        
       
        $('.producto-card').each(function() {
            let nombre = $(this).data('nombre');
            $(this).toggle(nombre.includes(texto));
        });

       
        if (texto.length > 0) {
            let sugerenciasHtml = '<ul>';
            let encontradas = 0;

            $('.producto-nombre').each(function() {
                let nombre = $(this).text();
                if (nombre.toLowerCase().includes(texto) && encontradas < 5) {
                    sugerenciasHtml += '<li>' + nombre + '</li>';
                    encontradas++;
                }
            });
            sugerenciasHtml += '</ul>';

            if (encontradas > 0) {
                $('#sugerencias').html(sugerenciasHtml).fadeIn(200);
            } else {
                $('#sugerencias').hide();
            }
        } else {
            $('#sugerencias').fadeOut(200);
        }
    });

   
    $(document).on('click', '#sugerencias li', function() {
        $('#buscar-nombre').val($(this).text());
        $('#sugerencias').fadeOut(200);
        $('#buscar-nombre').trigger('keyup'); 
    });

    // Cerrar al hacer clic fuera
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.search-wrapper').length) {
            $('#sugerencias').hide();
        }
    });
});
</script>

</body>
</html>