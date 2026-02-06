<?php
// Asegúrate de que config.php esté incluido antes de usar esta clase
// require_once __DIR__ . '/config.php';

class CategoriasDB {

    /**
     * Ya no necesitamos definir HOST, USER o PASS aquí.
     * Delegamos la conexión a la función global de config.php
     */
    private static function konektatu(): PDO {
        // Llamamos a la función que centraliza la conexión a AWS RDS
        return getDBConnection();
    }

    public static function selectCategorias(): array {
        $db = self::konektatu();
        // Usamos 'id' porque así lo definiste en el CREATE TABLE de MySQL
        $stmt = $db->query("SELECT id, nombre FROM categorias");
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
        $stmt = $db->prepare("SELECT id, nombre FROM categorias WHERE id = ?");
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
        $stmt = $db->prepare("INSERT INTO categorias (nombre) VALUES (:nombre)");
        $stmt->execute([':nombre' => $c->getNombre()]);
        return (int)$db->lastInsertId();
    }

    public static function updateCategoria(Categorias $c): int {
        $db = self::konektatu();
        $stmt = $db->prepare("UPDATE categorias SET nombre = :nombre WHERE id = :id");
        $stmt->execute([
            ':nombre' => $c->getNombre(),
            ':id' => $c->getId()
        ]);
        return $stmt->rowCount();
    }

    public static function deleteCategoria(int $id): int {
        $db = self::konektatu();
        $stmt = $db->prepare("DELETE FROM categorias WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount();
    }
}