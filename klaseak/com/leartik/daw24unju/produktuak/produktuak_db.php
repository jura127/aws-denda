<?php
class ProductosDB {
    private const DB_HOST = 'erronka.c9ig24qucwtm.eu-south-2.rds.amazonaws.com';
    private const DB_USER = 'admin';
    private const DB_PASS = 'Unaijurado23';
    private const DB_NAME = 'mysql';

    // Conexión a la base de datos MySQL
    private static function konektatu(): PDO {
        try {
            $dsn = "mysql:host=" . self::DB_HOST . ";dbname=" . self::DB_NAME . ";charset=utf8mb4";
            $db = new PDO($dsn, self::DB_USER, self::DB_PASS);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $db;
        } catch (PDOException $e) {
            die("Error de conexión a DB: " . $e->getMessage());
        }
    }

    // Obtener todos los productos
    public static function selectProduktuak(): ?array {
        try {
            $db = self::konektatu();
            $stmt = $db->query("SELECT * FROM productos");
            $productos = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $p = new Productos();
                $p->setIdProducto((int)$row['id_producto']);
                $p->setTipoProducto($row['tipo_producto']);
                $p->setDescripcion($row['descripcion']);
                $p->setPrecio((float)$row['precio']);
                $p->setIdCategoria((int)$row['id_categoria']);
                $p->setVideo($row['video'] ?? '');
                $p->setTieneOpcAñadirCesta((bool)$row['tiene_opc_añadir_cesta']);
                $p->setOfertas((bool)$row['ofertas']);
                $p->setNovedades((bool)$row['novedades']);
                $productos[] = $p;
            }
            return $productos;
        } catch (Exception $e) {
            echo "Error en selectProduktuak: " . $e->getMessage();
            return null;
        }
    }

    // Obtener producto por ID
    public static function selectProducto(int $id): ?Productos {
        try {
            $db = self::konektatu();
            $stmt = $db->prepare("SELECT * FROM productos WHERE id_producto = ?");
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
            $p->setTieneOpcAñadirCesta((bool)$row['tiene_opc_añadir_cesta']);
            $p->setOfertas((bool)$row['ofertas']);
            $p->setNovedades((bool)$row['novedades']);
            return $p;
        } catch (Exception $e) {
            echo "Error en selectProducto: " . $e->getMessage();
            return null;
        }
    }

    // Insertar producto
    public static function insertProducto(Productos $p): int {
        try {
            $db = self::konektatu();
            $sql = "INSERT INTO productos 
                (tipo_producto, descripcion, precio, id_categoria, video, tiene_opc_añadir_cesta, ofertas, novedades)
                VALUES (:tipo, :desc, :precio, :cat, :video, :addcart, :ofertas, :novedades)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':tipo' => $p->getTipoProducto(),
                ':desc' => $p->getDescripcion(),
                ':precio' => $p->getPrecio(),
                ':cat' => $p->getIdCategoria(),
                ':video' => $p->getVideo(),
                ':addcart' => $p->getTieneOpcAñadirCesta() ? 1 : 0,
                ':ofertas' => $p->getOfertas() ? 1 : 0,
                ':novedades' => $p->getNovedades() ? 1 : 0
            ]);
            return (int)$db->lastInsertId();
        } catch (Exception $e) {
            echo "Error en insertProducto: " . $e->getMessage();
            return 0;
        }
    }

    // Actualizar producto
    public static function updateProducto(Productos $p): int {
        try {
            $db = self::konektatu();
            $sql = "UPDATE productos SET
                tipo_producto=:tipo,
                descripcion=:desc,
                precio=:precio,
                id_categoria=:cat,
                video=:video,
                tiene_opc_añadir_cesta=:addcart,
                ofertas=:ofertas,
                novedades=:novedades
                WHERE id_producto=:id";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':tipo' => $p->getTipoProducto(),
                ':desc' => $p->getDescripcion(),
                ':precio' => $p->getPrecio(),
                ':cat' => $p->getIdCategoria(),
                ':video' => $p->getVideo(),
                ':addcart' => $p->getTieneOpcAñadirCesta() ? 1 : 0,
                ':ofertas' => $p->getOfertas() ? 1 : 0,
                ':novedades' => $p->getNovedades() ? 1 : 0,
                ':id' => $p->getIdProducto()
            ]);
            return $stmt->rowCount();
        } catch (Exception $e) {
            echo "Error en updateProducto: " . $e->getMessage();
            return 0;
        }
    }

    // Eliminar producto
    public static function deleteProducto(int $id): int {
        try {
            $produktua = self::selectProducto($id);
            if (!$produktua) return 0;

            // Borrar vídeo si existe
            if ($produktua->getVideo() && file_exists(__DIR__ . '/../../' . $produktua->getVideo())) {
                @unlink(__DIR__ . '/../../' . $produktua->getVideo());
            }

            $db = self::konektatu();
            $stmt = $db->prepare("DELETE FROM productos WHERE id_producto=?");
            $stmt->execute([$id]);
            return $stmt->rowCount();
        } catch (Exception $e) {
            echo "Error en deleteProducto: " . $e->getMessage();
            return 0;
        }
    }
}
?>
