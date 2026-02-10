<?php
class CategoriasDB {
    private const DB_HOST = 'erronka.c9ig24qucwtm.eu-south-2.rds.amazonaws.com';
    private const DB_USER = 'admin';
    private const DB_PASS = 'Unaijurado23';
    private const DB_NAME = 'erronka';


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

    // Crear tabla si no existe
    public static function crearTablaSiNoExiste() {
        // Las tablas ya deben existir en MySQL
    }

    public static function selectCategorias(): array {
        $db = self::konektatu();
        $stmt = $db->query("SELECT * FROM categorias");
        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $c = new Categorias();
            $c->setId((int)$row['id']);
            $c->setNombre($row['nombre']);
            $result[] = $c;
        }
        return $result;
    }

    public static function selectCategoria(int $id): ?Categorias {
        $db = self::konektatu();
        $stmt = $db->prepare("SELECT * FROM categorias WHERE id=?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;
        $c = new Categorias();
        $c->setId((int)$row['id']);
        $c->setNombre($row['nombre']);
        return $c;
    }

    public static function insertCategoria(Categorias $c): int {
        $db = self::konektatu();
        $stmt = $db->prepare("INSERT INTO categorias(nombre) VALUES(:nombre)");
        $stmt->execute([':nombre' => $c->getNombre()]);
        return (int)$db->lastInsertId();
    }

    public static function updateCategoria(Categorias $c): int {
        $db = self::konektatu();
        $stmt = $db->prepare("UPDATE categorias SET nombre=:nombre WHERE id=:id");
        $stmt->execute([
            ':nombre' => $c->getNombre(),
            ':id' => $c->getId()
        ]);
        return $stmt->rowCount();
    }

    public static function deleteCategoria(int $id): int {
        $db = self::konektatu();
        $stmt = $db->prepare("DELETE FROM categorias WHERE id=?");
        $stmt->execute([$id]);
        return $stmt->rowCount();
    }
}
