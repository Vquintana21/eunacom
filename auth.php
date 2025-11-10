<?php
// ============================================
// SISTEMA DE AUTENTICACIÓN - PHP 5.6 Compatible
// ============================================

require_once __DIR__ . '/env/config.php';

session_start();

// ============================================
// CONEXIÓN A BASE DE DATOS
// ============================================
function getDBConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                )
            );
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }
    
    return $pdo;
}

// ============================================
// VERIFICAR SI USUARIO ESTÁ AUTENTICADO
// ============================================
function isLoggedIn() {
    return isset($_SESSION['usuario_id']) && isset($_SESSION['token']);
}

// ============================================
// REQUERIR AUTENTICACIÓN
// ============================================
function requireAuth() {
    if (!isLoggedIn()) {
        header('Location: ' . buildUrl('login.php'));
        exit;
    }
    
    // Verificar que la sesión sea válida
    if (!verificarSesion()) {
        cerrarSesion();
        header('Location: ' . buildUrl('login.php?expired=1'));
        exit;
    }
}

// ============================================
// REGISTRAR USUARIO
// ============================================
function registrarUsuario($nombre, $email, $password) {
    $pdo = getDBConnection();
    
    // Validar email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return array('success' => false, 'mensaje' => 'Email inválido');
    }
    
    // Validar contraseña
    if (strlen($password) < 6) {
        return array('success' => false, 'mensaje' => 'La contraseña debe tener al menos 6 caracteres');
    }
    
    // Verificar si el email ya existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute(array($email));
    
    if ($stmt->fetch()) {
        return array('success' => false, 'mensaje' => 'El email ya está registrado');
    }
    
    // Hash de contraseña
    $password_hash = password_hash($password, PASSWORD_BCRYPT);
    
    // Insertar usuario
    try {
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (nombre, email, password_hash, tipo_usuario, activo)
            VALUES (?, ?, ?, 'estudiante', 1)
        ");
        $stmt->execute(array($nombre, $email, $password_hash));
        
        $usuario_id = $pdo->lastInsertId();
        
        // Registrar en log
        registrarActividad($usuario_id, 'registro', "Usuario registrado: $email");
        
        return array(
            'success' => true,
            'mensaje' => 'Registro exitoso',
            'usuario_id' => $usuario_id
        );
        
    } catch (PDOException $e) {
        return array('success' => false, 'mensaje' => 'Error al registrar: ' . $e->getMessage());
    }
}

// ============================================
// INICIAR SESIÓN
// ============================================
function iniciarSesion($email, $password) {
    $pdo = getDBConnection();
    
    // Buscar usuario
    $stmt = $pdo->prepare("
        SELECT id, nombre, email, password_hash, tipo_usuario, activo
        FROM usuarios
        WHERE email = ? AND activo = 1
    ");
    $stmt->execute(array($email));
    $usuario = $stmt->fetch();
    
    if (!$usuario) {
        registrarActividad(null, 'login_fallido', "Email no encontrado: $email", getClientIP());
        return array('success' => false, 'mensaje' => 'Usuario o contraseña incorrectos');
    }
    
    // Verificar contraseña
    if (!password_verify($password, $usuario['password_hash'])) {
        registrarActividad($usuario['id'], 'login_fallido', "Contraseña incorrecta", getClientIP());
        return array('success' => false, 'mensaje' => 'Usuario o contraseña incorrectos');
    }
    
    // Generar token de sesión (Compatible con PHP 5.6)
    $token = bin2hex(openssl_random_pseudo_bytes(32));
    $fecha_expiracion = date('Y-m-d H:i:s', strtotime('+24 hours'));
    
    // Guardar sesión en BD
    $stmt = $pdo->prepare("
        INSERT INTO sesiones (usuario_id, token, ip_address, user_agent, fecha_expiracion)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute(array(
        $usuario['id'],
        $token,
        getClientIP(),
        getUserAgent(),
        $fecha_expiracion
    ));
    
    // Actualizar último acceso
    $stmt = $pdo->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?");
    $stmt->execute(array($usuario['id']));
    
    // Guardar en sesión PHP
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['nombre'] = $usuario['nombre'];
    $_SESSION['email'] = $usuario['email'];
    $_SESSION['tipo_usuario'] = $usuario['tipo_usuario'];
    $_SESSION['token'] = $token;
    
    // Registrar en log
    registrarActividad($usuario['id'], 'login', "Inicio de sesión exitoso", getClientIP());
    
    return array(
        'success' => true,
        'mensaje' => 'Inicio de sesión exitoso',
        'usuario' => array(
            'id' => $usuario['id'],
            'nombre' => $usuario['nombre'],
            'email' => $usuario['email']
        )
    );
}

// ============================================
// VERIFICAR SESIÓN
// ============================================
function verificarSesion() {
    if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['token'])) {
        return false;
    }
    
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("
        SELECT id FROM sesiones
        WHERE usuario_id = ?
        AND token = ?
        AND activa = 1
        AND fecha_expiracion > NOW()
    ");
    $stmt->execute(array($_SESSION['usuario_id'], $_SESSION['token']));
    
    return (bool) $stmt->fetch();
}

// ============================================
// CERRAR SESIÓN
// ============================================
function cerrarSesion() {
    if (isset($_SESSION['usuario_id']) && isset($_SESSION['token'])) {
        $pdo = getDBConnection();
        
        // Desactivar sesión en BD
        $stmt = $pdo->prepare("
            UPDATE sesiones
            SET activa = 0
            WHERE usuario_id = ? AND token = ?
        ");
        $stmt->execute(array($_SESSION['usuario_id'], $_SESSION['token']));
        
        // Registrar en log
        registrarActividad($_SESSION['usuario_id'], 'logout', "Cierre de sesión");
    }
    
    // Destruir sesión PHP
    session_unset();
    session_destroy();
}

// ============================================
// OBTENER USUARIO ACTUAL
// ============================================
function getUsuarioActual() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return array(
        'id' => $_SESSION['usuario_id'],
        'nombre' => $_SESSION['nombre'],
        'email' => $_SESSION['email'],
        'tipo_usuario' => $_SESSION['tipo_usuario']
    );
}

// ============================================
// REGISTRAR ACTIVIDAD
// ============================================
function registrarActividad($usuario_id, $accion, $descripcion = '', $ip = null) {
    try {
        $pdo = getDBConnection();
        
        $stmt = $pdo->prepare("
            INSERT INTO log_actividad (usuario_id, accion, descripcion, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute(array(
            $usuario_id,
            $accion,
            $descripcion,
            $ip ? $ip : getClientIP(),
            getUserAgent()
        ));
    } catch (PDOException $e) {
        // Silencioso - no interrumpir el flujo
        error_log("Error al registrar actividad: " . $e->getMessage());
    }
}

// ============================================
// HELPERS
// ============================================
function getClientIP() {
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
    
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    
    return $ip;
}

function getUserAgent() {
    return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'unknown';
}

// ============================================
// LIMPIAR SESIONES EXPIRADAS (Ejecutar periódicamente)
// ============================================
function limpiarSesionesExpiradas() {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("
        UPDATE sesiones
        SET activa = 0
        WHERE fecha_expiracion < NOW() AND activa = 1
    ");
    $stmt->execute();
    
    return $stmt->rowCount();
}