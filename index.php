<?php
/**
 * ============================================
 * DASHBOARD PRINCIPAL
 * ============================================
 */

require_once __DIR__ . '/env/config.php';
require_once __DIR__ . '/auth.php';

// Requiere autenticación
requireAuth();

// Obtener datos del usuario actual
$usuario = getCurrentUser();

// Obtener conexión a BD
$pdo = getDB();

// Obtener estadísticas del usuario
try {
    // Total de áreas disponibles
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM areas WHERE activo = 1");
    $total_areas = $stmt->fetch()['total'];
    
    // Total de temas disponibles
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM temas WHERE activo = 1");
    $total_temas = $stmt->fetch()['total'];
    
    // Total de documentos disponibles
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM documentos_estudio WHERE activo = 1");
    $total_documentos = $stmt->fetch()['total'];
    
    // Progreso del usuario (si existe)
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(DISTINCT pe.tema_id) as temas_completados,
            SUM(pe.preguntas_correctas) as total_correctas,
            SUM(pe.preguntas_respondidas) as total_respondidas
        FROM progreso_estudiante pe
        WHERE pe.usuario_id = ?
    ");
    $stmt->execute(array($usuario['id']));
    $progreso = $stmt->fetch();
    
    // Últimos simulacros
    $stmt = $pdo->prepare("
        SELECT 
            e.codigo_examen,
            e.estado,
            e.fecha_inicio,
            e.puntaje_porcentaje,
            e.respuestas_correctas,
            e.total_preguntas
        FROM examenes e
        WHERE e.usuario_id = ?
        ORDER BY e.fecha_inicio DESC
        LIMIT 3
    ");
    $stmt->execute(array($usuario['id']));
    $ultimos_simulacros = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("[Dashboard] Error al obtener estadísticas: " . $e->getMessage());
    $total_areas = 0;
    $total_temas = 0;
    $total_documentos = 0;
    $progreso = array('temas_completados' => 0, 'total_correctas' => 0, 'total_respondidas' => 0);
    $ultimos_simulacros = array();
}

// Calcular porcentaje de aciertos
$porcentaje_aciertos = 0;
if ($progreso['total_respondidas'] > 0) {
    $porcentaje_aciertos = round(($progreso['total_correctas'] / $progreso['total_respondidas']) * 100, 1);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?= SITE_NAME ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        /* Header */
        .header {
            background: white;
            border-radius: 15px;
            padding: 25px 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header-left h1 {
            color: #2c3e50;
            font-size: 1.8rem;
            margin-bottom: 5px;
        }
        
        .header-left p {
            color: #7f8c8d;
        }
        
        .header-right {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .user-info {
            text-align: right;
        }
        
        .user-name {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .user-email {
            font-size: 0.85rem;
            color: #7f8c8d;
        }
        
        .btn-logout {
            padding: 10px 20px;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .btn-logout:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }
        
        /* Welcome Banner */
        .welcome-banner {
            background: white;
            border-radius: 15px;
            padding: 40px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .welcome-banner h2 {
            color: #2c3e50;
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .welcome-banner p {
            color: #7f8c8d;
            font-size: 1.1rem;
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #7f8c8d;
            font-size: 0.95rem;
        }
        
        /* Modules Grid */
        .modules-title {
            color: white;
            font-size: 1.8rem;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .module-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s;
            text-decoration: none;
            color: inherit;
        }
        
        .module-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .module-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px;
            text-align: center;
        }
        
        .module-icon {
            font-size: 3rem;
            margin-bottom: 10px;
        }
        
        .module-title {
            color: white;
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .module-body {
            padding: 25px;
        }
        
        .module-description {
            color: #7f8c8d;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        
        .module-btn {
            display: block;
            width: 100%;
            padding: 12px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .module-btn:hover {
            background: #764ba2;
        }
        
        /* Recent Activity */
        .recent-activity {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .recent-activity h3 {
            color: #2c3e50;
            margin-bottom: 20px;
        }
        
        .activity-item {
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .status-finalizado {
            background: #d4edda;
            color: #155724;
        }
        
        .status-en-curso {
            background: #fff3cd;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <h1>🏥 <?= SITE_NAME ?></h1>
                <p>Sistema de Preparación EUNACOM</p>
            </div>
            <div class="header-right">
                <div class="user-info">
                    <div class="user-name">👤 <?= e($usuario['nombre']) ?></div>
                    <div class="user-email"><?= e($usuario['email']) ?></div>
                </div>
                <a href="<?= buildUrl('logout.php') ?>" class="btn-logout">
                    🚪 Cerrar Sesión
                </a>
            </div>
        </div>
        
        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <h2>¡Bienvenido de vuelta, <?= e(explode(' ', $usuario['nombre'])[0]) ?>!</h2>
            <p>Estás a un paso más cerca de aprobar tu examen EUNACOM</p>
        </div>
        
        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📚</div>
                <div class="stat-value"><?= $total_areas ?></div>
                <div class="stat-label">Áreas Médicas</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📝</div>
                <div class="stat-value"><?= $total_temas ?></div>
                <div class="stat-label">Temas Disponibles</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📄</div>
                <div class="stat-value"><?= $total_documentos ?></div>
                <div class="stat-label">Documentos de Estudio</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🎯</div>
                <div class="stat-value"><?= $porcentaje_aciertos ?>%</div>
                <div class="stat-label">Porcentaje de Aciertos</div>
            </div>
        </div>
        
        <!-- Modules -->
        <h2 class="modules-title">📚 Módulos de Estudio</h2>
        
        <div class="modules-grid">
            <!-- Módulo 1: Materiales -->
            <a href="<?= buildUrl('materiales.php') ?>" class="module-card">
                <div class="module-header">
                    <div class="module-icon">📖</div>
                    <div class="module-title">Materiales de Estudio</div>
                </div>
                <div class="module-body">
                    <div class="module-description">
                        Accede a PDFs y documentos organizados por área, especialidad y tema. Material completo para tu preparación.
                    </div>
                    <div class="module-btn">Explorar Materiales →</div>
                </div>
            </a>
            
            <!-- Módulo 2: Entrenamiento -->
            <a href="<?= buildUrl('entrenamiento.php') ?>" class="module-card">
                <div class="module-header">
                    <div class="module-icon">💪</div>
                    <div class="module-title">Entrenamiento por Temas</div>
                </div>
                <div class="module-body">
                    <div class="module-description">
                        Practica con preguntas tipo test organizadas por tema. Revisa tus respuestas y aprende de tus errores.
                    </div>
                    <div class="module-btn">Comenzar Entrenamiento →</div>
                </div>
            </a>
            
            <!-- Módulo 3: Simulacro -->
            <a href="<?= buildUrl('simulacro_inicio.php') ?>" class="module-card">
                <div class="module-header">
                    <div class="module-icon">🎯</div>
                    <div class="module-title">Simulacro Real</div>
                </div>
                <div class="module-body">
                    <div class="module-description">
                        Simula el examen oficial: 180 preguntas aleatorias en 2 sesiones de 90 minutos. ¡Pon a prueba tu conocimiento!
                    </div>
                    <div class="module-btn">Iniciar Simulacro →</div>
                </div>
            </a>
        </div>
        
        <!-- Recent Activity -->
        <?php if (!empty($ultimos_simulacros)): ?>
        <div class="recent-activity">
            <h3>📊 Tus Últimos Simulacros</h3>
            <?php foreach ($ultimos_simulacros as $sim): ?>
                <div class="activity-item">
                    <strong><?= e($sim['codigo_examen']) ?></strong>
                    <span class="activity-status status-<?= $sim['estado'] ?>">
                        <?= ucfirst(str_replace('_', ' ', $sim['estado'])) ?>
                    </span>
                    <?php if ($sim['estado'] === 'finalizado'): ?>
                        <br>
                        <small>
                            Puntaje: <?= $sim['puntaje_porcentaje'] ?>% 
                            (<?= $sim['respuestas_correctas'] ?>/<?= $sim['total_preguntas'] ?> correctas)
                        </small>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>