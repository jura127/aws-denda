<?php
// Asegúrate de iniciar la sesión
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ruta al archivo de almacenamiento de usuarios simulado (JSON)
const USERS_FILE = __DIR__ . '/users.json';

// ----------------------------------------------------
// FUNCIONES DE GESTIÓN DE USUARIOS (Simulación DB)
// ----------------------------------------------------

function loadUsers(): array {
    if (!file_exists(USERS_FILE)) {
        file_put_contents(USERS_FILE, json_encode([]));
        return [];
    }
    $content = file_get_contents(USERS_FILE);
    if (empty($content)) {
        return [];
    }
    return json_decode($content, true) ?? [];
}

function saveUsers(array $users): void {
    file_put_contents(USERS_FILE, json_encode($users, JSON_PRETTY_PRINT));
}

// ----------------------------------------------------
// 2. MANEJO DEL REGISTRO
// ----------------------------------------------------
$mensaje_registro = '';
$mensaje_login = '';
$clase_registro = ''; 

if (isset($_POST['action']) && $_POST['action'] === 'register') {
    $username = trim($_POST['reg_username'] ?? '');
    $email = trim($_POST['reg_email'] ?? '');
    $password = $_POST['reg_password'] ?? '';
    
    $reg_username_val = htmlspecialchars($username);
    $reg_email_val = htmlspecialchars($email);
    
    if (empty($username) || empty($email) || empty($password)) {
        $mensaje_registro = '⚠️ Error: Todos los campos de registro son obligatorios.';
        $clase_registro = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje_registro = '⚠️ Error: Formato de email inválido.';
        $clase_registro = 'error';
    } else {
        $users = loadUsers();
        
        if (isset($users[$username])) {
            $mensaje_registro = '⚠️ Error: El nombre de usuario ya existe.';
            $clase_registro = 'error';
        } else {
            $users[$username] = [
                'password' => password_hash($password, PASSWORD_BCRYPT),
                'email' => $email,
            ];
            saveUsers($users);
            
            $mensaje_registro = '🎉 ¡Registro exitoso! Ya puedes iniciar sesión.';
            $clase_registro = 'exito';
            $reg_username_val = $reg_email_val = '';
        }
    }
} else {
    $reg_username_val = '';
    $reg_email_val = '';
}


// ----------------------------------------------------
// 3. MANEJO DEL LOGIN
// ----------------------------------------------------
$log_username_val = '';

if (isset($_POST['action']) && $_POST['action'] === 'login') {
    $username = trim($_POST['log_username'] ?? '');
    $password = $_POST['log_password'] ?? '';
    $log_username_val = htmlspecialchars($username);

    if (empty($username) || empty($password)) {
        $mensaje_login = 'Por favor, introduce tu usuario y contraseña.';
    } else {
        $users = loadUsers();
        
        if (isset($users[$username])) {
            if (password_verify($password, $users[$username]['password'])) {
                $_SESSION['usuario_logueado'] = true;
                $_SESSION['nombre_usuario'] = $username;
                
                header('Location: index.php');
                exit;
            } else {
                $mensaje_login = 'Contraseña incorrecta.';
            }
        } else {
            $mensaje_login = 'Usuario no encontrado.';
        }
    }
} 

// ----------------------------------------------------
// 4. MANEJO DEL LOGOUT
// ----------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['usuario_logueado']);
    unset($_SESSION['nombre_usuario']);
    
    header('Location: login.php');
    exit;
}

// ----------------------------------------------------
// 5. LÓGICA DE VISTA
// ----------------------------------------------------
$esta_logueado = $_SESSION['usuario_logueado'] ?? false;
$nombre_usuario = $_SESSION['nombre_usuario'] ?? 'Invitado';

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login/Registro | Tienda de Magia</title>
    <link rel="stylesheet" href="css/styles.css"> 
</head>
<body>

<header class="grid-header">
    <div class="logo-container">
        <img src="img/cajaconcartas-modified.jpg" alt="Logo Tienda de Magia" class="logo">
    </div>
    <nav class="main-nav">
        <ul>
            <li><a href="index.php">Inicio</a></li>
            <li><a href="catalogo.php">Catálogo</a></li>
            <li><a href="accesorios.php">Accesorios</a></li>
            <li><a href="contacto/index.php">Contacto</a></li>
            <li><a href="mediateka/mediateka.html">Mediateka</a></li>
            <li><a href="cesta.php">Cesta</a></li>
            <li>
                <?php if ($esta_logueado): ?>
                    <a href="login.php?action=logout" title="Cerrar Sesión">Logout (<?= htmlspecialchars($nombre_usuario) ?>)</a>
                <?php else: ?>
                    <a href="login.php" class="active">Login/Reg</a>
                <?php endif; ?>
            </li> 
        </ul>
    </nav>
</header>

<main>
    <div class="section-container" style="padding-top: 20px; padding-bottom: 20px;">
        <h1>Acceso al Portal Mágico</h1>
    </div>

    <div class="section-container">
    
        <?php if ($esta_logueado): ?>
            <div class="logout-panel">
                <h2>¡Hola, <?= htmlspecialchars($nombre_usuario) ?>!</h2>
                <p>Tu sesión está activa. ¿Qué magia harás hoy?</p>
                <a href="index.php" class="explore-button">Volver al Inicio</a>
                <a href="login.php?action=logout" class="logout-link explore-button">Cerrar Sesión</a>
            </div>
        <?php else: ?>
            <div class="auth-container">
                
                <div class="auth-form login-form">
                    <h2>Iniciar Sesión</h2>
                    
                    <?php if (isset($_POST['action']) && $_POST['action'] === 'login' && $mensaje_login): ?>
                        <p class="message error"><?= htmlspecialchars($mensaje_login) ?></p>
                    <?php endif; ?>

                    <form action="login.php" method="POST">
                        <input type="hidden" name="action" value="login">
                        <input type="text" name="log_username" placeholder="Nombre de Usuario" required 
                               value="<?= $log_username_val ?>">
                        <input type="password" name="log_password" placeholder="Contraseña" required>
                        <button type="submit">Entrar al Portal</button>
                    </form>
                </div>
                
                <div class="auth-form register-form">
                    <h2>Registrarse</h2>

                    <?php if (isset($_POST['action']) && $_POST['action'] === 'register' && $mensaje_registro): ?>
                        <p class="message <?= $clase_registro ?>"><?= htmlspecialchars($mensaje_registro) ?></p>
                    <?php endif; ?>

                    <form action="login.php" method="POST">
                        <input type="hidden" name="action" value="register">
                        <input type="text" name="reg_username" placeholder="Nombre de Usuario" required 
                               value="<?= $reg_username_val ?>">
                        <input type="email" name="reg_email" placeholder="Correo Electrónico" required
                               value="<?= $reg_email_val ?>">
                        <input type="password" name="reg_password" placeholder="Contraseña" required>
                        <button type="submit">Crear Cuenta Mágica</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
        
    </div>
</main>

<footer>
    <p>&copy; Tienda de Magia 2025. Todos los derechos reservados.</p>
</footer>

</body>
</html>