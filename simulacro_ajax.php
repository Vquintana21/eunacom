<?php
header('Content-Type: application/json');

// Conexión a BD
$db_host = 'localhost';
$db_user = 'dpimeduchile_vquintana';           // TU USUARIO
$db_pass = 'Vq_09875213';               // TU CONTRASEÑA
$db_name = 'dpimeduchile_eunacom';

try {
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}

// Leer datos de la petición
$input = json_decode(file_get_contents('php://input'), true);
$action = isset($input['action']) ? $input['action'] : null;
$codigo_examen = isset($_GET['examen']) ? $_GET['examen'] : null;

// ============================================
// GUARDAR RESPUESTA
// ============================================
if ($action === 'guardar_respuesta') {
$examen_id = isset($input['examen_id']) ? $input['examen_id'] : null;
$pregunta_id = isset($input['pregunta_id']) ? $input['pregunta_id'] : null;
$alternativa = isset($input['alternativa']) ? $input['alternativa'] : null;
    
    // Verificar si es correcta
    $sql = "SELECT es_correcta FROM alternativas WHERE pregunta_id = ? AND opcion = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$pregunta_id, $alternativa]);
    $alt_data = $stmt->fetch(PDO::FETCH_ASSOC);
    $es_correcta = isset($alt_data['es_correcta']) ? $alt_data['es_correcta'] : 0;
    
    // Actualizar respuesta
    $sql = "
        UPDATE respuestas_usuario 
        SET alternativa_seleccionada = ?, es_correcta = ?, updated_at = NOW()
        WHERE examen_id = ? AND pregunta_id = ?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$alternativa, $es_correcta, $examen_id, $pregunta_id]);
    
    echo json_encode(['success' => true]);
    exit;
}

// ============================================
// MARCAR PARA REVISIÓN
// ============================================
if ($action === 'marcar_revision') {
    $examen_id = $input['examen_id'];
    $pregunta_id = $input['pregunta_id'];
    $marcada = $input['marcada'] ? 1 : 0;
    
    $sql = "
        UPDATE respuestas_usuario 
        SET marcada_revision = ?
        WHERE examen_id = ? AND pregunta_id = ?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$marcada, $examen_id, $pregunta_id]);
    
    echo json_encode(['success' => true]);
    exit;
}

// ============================================
// GUARDAR TIEMPO
// ============================================
if ($action === 'guardar_tiempo') {
    $examen_id = $input['examen_id'];
    $tiempo_restante = $input['tiempo_restante'];
    $campo = $input['campo']; // 'tiempo_restante_sesion1' o 'tiempo_restante_sesion2'
    
    // Validar que el campo sea uno de los permitidos
    if (!in_array($campo, ['tiempo_restante_sesion1', 'tiempo_restante_sesion2'])) {
        echo json_encode(['success' => false, 'error' => 'Campo inválido']);
        exit;
    }
    
    // Construir query de forma segura
    if ($campo === 'tiempo_restante_sesion1') {
        $sql = "UPDATE examenes SET tiempo_restante_sesion1 = ? WHERE id = ?";
    } else {
        $sql = "UPDATE examenes SET tiempo_restante_sesion2 = ? WHERE id = ?";
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$tiempo_restante, $examen_id]);
    
    echo json_encode(['success' => true]);
    exit;
}

// ============================================
// FINALIZAR SESIÓN
// ============================================
if ($action === 'finalizar_sesion') {
    $examen_id = $input['examen_id'];
    $sesion = $input['sesion'];
    $tiempo_restante = $input['tiempo_restante'];
    
    try {
        $pdo->beginTransaction();
        
        if ($sesion == 1) {
            // Finalizar sesión 1
            $sql = "
                UPDATE examenes 
                SET estado = 'sesion1_completa',
                    sesion_actual = 2,
                    fecha_fin_sesion1 = NOW(),
                    tiempo_restante_sesion1 = ?
                WHERE id = ?
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$tiempo_restante, $examen_id]);
            
        } else {
            // Finalizar examen completo
            
            // Calcular estadísticas
            $sql = "
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN alternativa_seleccionada IS NOT NULL THEN 1 ELSE 0 END) as respondidas,
                    SUM(CASE WHEN es_correcta = 1 THEN 1 ELSE 0 END) as correctas
                FROM respuestas_usuario
                WHERE examen_id = ?
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$examen_id]);
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $total = $stats['total'];
            $respondidas = $stats['respondidas'];
            $correctas = $stats['correctas'];
            $incorrectas = $respondidas - $correctas;
            $omitidas = $total - $respondidas;
            $porcentaje = ($correctas / $total) * 100;
            
            // Actualizar examen
            $sql = "
                UPDATE examenes 
                SET estado = 'finalizado',
                    fecha_finalizacion = NOW(),
                    tiempo_restante_sesion2 = ?,
                    preguntas_respondidas = ?,
                    respuestas_correctas = ?,
                    respuestas_incorrectas = ?,
                    preguntas_omitidas = ?,
                    puntaje_porcentaje = ?
                WHERE id = ?
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $tiempo_restante,
                $respondidas,
                $correctas,
                $incorrectas,
                $omitidas,
                $porcentaje,
                $examen_id
            ]);
        }
        
        $pdo->commit();
        echo json_encode(['success' => true]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    
    exit;
}

echo json_encode(['success' => false, 'error' => 'Acción no válida']);