<?php
// ASUMIMOS que la clase Mensajes está definida y disponible
// Y que la conexión a SQLite se gestiona internamente.
class MensajesDB {
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
            die("Error de conexión a mensajes: " . $e->getMessage());
        }
    }
// ---------------------------------------------------------------------

    /**
     * Obtener TODOS los mensajes (para el panel de administración).
     * ESTA ES LA FUNCIÓN FALTANTE.
     */
 // Dentro de la clase MensajesDB...
public static function selectAllMensajes(): array {
        try {
            $db = self::konektatu();
            
            // CLÁUSULA CLAVE: ORDER BY id ASC para ordenar del ID más pequeño al más grande.
            $sql = "SELECT * FROM mensajes ORDER BY id ASC"; 
            
            $stmt = $db->query($sql);
            
            // Se asume que la clase de la entidad se llama 'Mensajes'
            $mensajes = $stmt->fetchAll(PDO::FETCH_CLASS, "Mensajes");
            
            return $mensajes;

        } catch (Exception $e) {
            echo "Error en selectAllMensajes: " . $e->getMessage();
            return [];
        }
    }

    /**
     * Obtener un mensaje por ID.
     */
    public static function selectMensajes(int $id): ?Mensajes {
        try {
            $db = self::konektatu();
            $stmt = $db->prepare("SELECT * FROM mensajes WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) return null;

            $m = new Mensajes();
            $m->setId((int)$row['id']);
            $m->setNombre($row['nombre']);
            $m->setEmail($row['email']);
            $m->setMensaje($row['mensaje']);
            $m->setFecha($row['fecha']);
            $m->setLeido((int)$row['leido']);
            return $m;
        } catch (Exception $e) {
            echo "Error en selectMensaje: " . $e->getMessage();
            return null;
        }
    }

    /**
     * Insertar nuevo mensaje (berria).
     */
    public static function insertMensajes(Mensajes $m): int {
        try {
            $db = self::konektatu();
            // NOTA: No hace falta incluir la fecha, ya que la columna tiene DEFAULT CURRENT_TIMESTAMP
            $sql = "INSERT INTO mensajes (nombre, email, mensaje, leido) 
                     VALUES (:nombre, :email, :mensaje, :leido)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':nombre' => $m->getNombre(),
                ':email' => $m->getEmail(),
                ':mensaje' => $m->getMensaje(),
                ':leido' => $m->getLeido()
            ]);
            return (int)$db->lastInsertId();
        } catch (Exception $e) {
            echo "Error en insertMensajes: " . $e->getMessage();
            return 0;
        }
    }

    /**
     * Actualizar mensaje (mezua aldatu).
     */
    public static function updateMensajes(Mensajes $m): int {
        try {
            $db = self::konektatu();
            $sql = "UPDATE mensajes SET nombre=:nombre, email=:email, mensaje=:mensaje, leido=:leido WHERE id=:id";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':nombre' => $m->getNombre(),
                ':email' => $m->getEmail(),
                ':mensaje' => $m->getMensaje(),
                ':leido' => $m->getLeido(),
                ':id' => $m->getId()
            ]);
            return $stmt->rowCount();
        } catch (Exception $e) {
            echo "Error en updateMensaje: " . $e->getMessage();
            return 0;
        }
    }

    /**
     * Eliminar mensaje (mezua ezabatu).
     */
    public static function deleteMensajes(int $id): int {
        try {
            $db = self::konektatu();
            $stmt = $db->prepare("DELETE FROM mensajes WHERE id=?");
            $stmt->execute([$id]);
            return $stmt->rowCount();
        } catch (Exception $e) {
            echo "Error en deleteMensaje: " . $e->getMessage();
            return 0;
        }
    }
}