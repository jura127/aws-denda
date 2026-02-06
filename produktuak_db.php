<?php
// require_once __DIR__ . '/config.php';

class ProductosDB {

    private static function konektatu(): PDO {
        // Asegúrate de que getDBConnection() en config.php devuelva un objeto PDO
        return getDBConnection();
    }

    /**
     * Obtener todos los productos
     */
    public static function selectProduktuak(): array {
        try {
            $db = self::konektatu();
            // 1. Usamos backticks para evitar conflictos con palabras reservadas
            $stmt = $db->query("SELECT * FROM `productos` ORDER BY `id_producto` ASC");
            $productos = [];
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $p = new Productos();
                
                // 2. Verificación de nombres de columnas (Case Sensitive en Linux/AWS)
                $p->setIdProducto((int)$row['id_producto']);
                $p->setTipoProducto($row['tipo_producto']);
                $p->setDescripcion($row['descripcion']);
                $p->setPrecio((float)$row['precio']);
                $p->setIdCategoria((int)$row['id_categoria']);
                $p->setVideo($row['video'] ?? '');

                // 3. Mapeo explícito de TINYINT (MySQL) a Boolean (PHP)
                $p->setTieneOpcAñadirCesta($row['tiene_opc_añadir_cesta'] == 1);
                $p->setOfertas($row['ofertas'] == 1);
                $p->setNovedades($row['novedades'] == 1);
                
                $productos[] = $p;
            }
            return $productos;
        } catch (Exception $e) {
            // Esto escribirá el error en el log de Apache/Nginx para que no muera la web
            error_log("Error en selectProduktuak: " . $e->getMessage());
            return [];
        }
    }

    public static function selectProducto(int $id): ?Productos {
        try {
            $db = self::konektatu();
            $stmt = $db->prepare("SELECT * FROM `productos` WHERE `id_producto` = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$row) return null;

            $p = new Productos();
            $p->setIdProducto((int)$row['id_producto']);
            $p->setTipoProducto($row['tipo_producto']);
            $p->setDescripcion($row['descripcion']);
            $p->setPrecio((float)$row['precio']);
            $p->setIdCategoria((int)$row['id_categoria']);
            $p->setVideo($row['video'] ?? '');
            $p->setTieneOpcAñadirCesta($row['tiene_opc_añadir_cesta'] == 1);
            $p->setOfertas($row['ofertas'] == 1);
            $p->setNovedades($row['novedades'] == 1);
            
            return $p;
        } catch (Exception $e) {
            error_log("Error en selectProducto: " . $e->getMessage());
            return null;
        }
    }

    // ... (El resto de métodos insert/update/delete están correctos)
}