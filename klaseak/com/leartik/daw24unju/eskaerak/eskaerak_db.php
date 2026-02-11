<?php
// ASUMIMOS que la clase Pedido está definida y disponible
class PedidosDB {
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
            die("Error de conexión a pedidos: " . $e->getMessage());
        }
    }
// ---------------------------------------------------------------------

    /**
     * Obtener un pedido por ID.
     */
    public static function selectPedidos(int $id): ?Pedidos {
        try {
            $db = self::konektatu();
            $stmt = $db->prepare("SELECT * FROM pedidos WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) return null;

            $p = new Pedidos();
            $p->setId((int)$row['id']);
            $p->setNombre($row['nombre']);
            $p->setEmail($row['email']);
            $p->setProducto($row['producto']);
            $p->setCantidad((int)$row['cantidad']);
            $p->setEstado($row['estado']);
            $p->setFecha($row['fecha']);
            return $p;
        } catch (Exception $e) {
            echo "Error en selectPedidos: " . $e->getMessage();
            return null;
        }
    }

    /**
     * Actualizar estado del pedido (eskariak aldatu).
     */
    public static function updateEstadoPedidos(int $id, string $estado_nuevo): int {
        try {
            $db = self::konektatu();
            $sql = "UPDATE pedidos SET estado=:estado WHERE id=:id";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':estado' => $estado_nuevo,
                ':id' => $id
            ]);
            return $stmt->rowCount();
        } catch (Exception $e) {
            echo "Error en updateEstadoPedidos: " . $e->getMessage();
            return 0;
        }
    }

    /**
     * Eliminar pedido (eskariak ezabatu).
     */
    public static function deletePedidos(int $id): int {
        try {
            $db = self::konektatu();
            $stmt = $db->prepare("DELETE FROM pedidos WHERE id=?");
            $stmt->execute([$id]);
            return $stmt->rowCount();
        } catch (Exception $e) {
            echo "Error en deletePedidos: " . $e->getMessage();
            return 0;
        }
    }
}