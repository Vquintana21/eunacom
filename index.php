<?php
/**
 * ============================================
 * DASHBOARD PRINCIPAL - VERSIÓN MEJORADA
 * ============================================
 * Estadísticas combinadas: Sistema + Usuario
 */

require_once __DIR__ . '/env/config.php';
require_once __DIR__ . '/auth.php';

// Requiere autenticación
requireAuth();

// Obtener datos del usuario actual
$usuario = getCurrentUser();

// Obtener conexión a BD
$pdo = getDB();

// ==================================================
// ESTADÍSTICAS DEL SISTEMA (Generales)
// ==================================================
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
    
    // Total de preguntas disponibles
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM preguntas WHERE activa = 1");
    $total_preguntas = $stmt->fetch()['total'];
    
} catch (PDOException $e) {
    error_log("[Dashboard] Error al obtener estadísticas del sistema: " . $e->getMessage());
    $total_areas = 0;
    $total_temas = 0;
    $total_documentos = 0;
    $total_preguntas = 0;
}

// ==================================================
// ESTADÍSTICAS DEL USUARIO (Personalizadas)
// ==================================================
try {
    // Estadísticas de SIMULACROS (tabla examenes)
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_simulacros,
            SUM(CASE WHEN estado = 'finalizado' THEN 1 ELSE 0 END) as simulacros_completados,
            MAX(puntaje_porcentaje) as mejor_puntaje
        FROM examenes
        WHERE usuario_id = ?
    ");
    $stmt->execute(array($usuario['id']));
    $stats_simulacros = $stmt->fetch();
    
    // Estadísticas de RESPUESTAS (tabla respuestas_usuario)
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_respondidas,
            SUM(CASE WHEN es_correcta = 1 THEN 1 ELSE 0 END) as total_correctas,
            SUM(CASE WHEN alternativa_seleccionada IS NULL THEN 1 ELSE 0 END) as total_omitidas
        FROM respuestas_usuario ru
        INNER JOIN examenes e ON ru.examen_id = e.id
        WHERE e.usuario_id = ?
    ");
    $stmt->execute(array($usuario['id']));
    $stats_respuestas = $stmt->fetch();
    
    // Estadísticas de PROGRESO POR ÁREA (tabla progreso_estudiante)
    $stmt = $pdo->prepare("
        SELECT 
            SUM(total_preguntas_respondidas) as preguntas_respondidas_areas,
            SUM(preguntas_correctas) as correctas_areas,
            SUM(temas_completados) as temas_completados,
            SUM(tiempo_total_estudio_min) as tiempo_total_min
        FROM progreso_estudiante
        WHERE usuario_id = ?
    ");
    $stmt->execute(array($usuario['id']));
    $stats_progreso = $stmt->fetch();
    
    // Últimos simulacros realizados
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
    error_log("[Dashboard] Error al obtener estadísticas del usuario: " . $e->getMessage());
    $stats_simulacros = array('total_simulacros' => 0, 'simulacros_completados' => 0, 'mejor_puntaje' => 0);
    $stats_respuestas = array('total_respondidas' => 0, 'total_correctas' => 0, 'total_omitidas' => 0);
    $stats_progreso = array('preguntas_respondidas_areas' => 0, 'correctas_areas' => 0, 'temas_completados' => 0, 'tiempo_total_min' => 0);
    $ultimos_simulacros = array();
}

// ==================================================
// CALCULAR MÉTRICAS FINALES
// ==================================================

// Para las tarjetas principales, priorizamos datos de SIMULACROS
$preguntas_respondidas_display = ($stats_respuestas['total_respondidas'] !== false && $stats_respuestas['total_respondidas'] > 0) 
    ? $stats_respuestas['total_respondidas'] 
    : 0;

$preguntas_correctas_display = ($stats_respuestas['total_correctas'] !== false && $stats_respuestas['total_correctas'] > 0) 
    ? $stats_respuestas['total_correctas'] 
    : 0;

// Calcular porcentaje de aciertos
$porcentaje_aciertos = 0;
if ($preguntas_respondidas_display > 0) {
    $porcentaje_aciertos = round(($preguntas_correctas_display / $preguntas_respondidas_display) * 100, 1);
}

// Simulacros completados
$simulacros_completados = ($stats_simulacros['simulacros_completados'] !== false) 
    ? $stats_simulacros['simulacros_completados'] 
    : 0;

// Mejor puntaje
$mejor_puntaje = ($stats_simulacros['mejor_puntaje'] !== false && $stats_simulacros['mejor_puntaje'] !== null) 
    ? round($stats_simulacros['mejor_puntaje'], 1) 
    : 0;

// Formatear tiempo total de estudio
$tiempo_estudio_horas = 0;
$tiempo_estudio_min = 0;
if ($stats_progreso['tiempo_total_min'] !== false && $stats_progreso['tiempo_total_min'] > 0) {
    $tiempo_estudio_horas = floor($stats_progreso['tiempo_total_min'] / 60);
    $tiempo_estudio_min = $stats_progreso['tiempo_total_min'] % 60;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo SITE_NAME; ?></title>
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
            flex-wrap: wrap;
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
            background: #e74c3c;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-logout:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }
        
        /* Welcome Section */
        .welcome {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .welcome h2 {
            color: #2c3e50;
            font-size: 1.8rem;
            margin-bottom: 10px;
        }
        
        .welcome p {
            color: #7f8c8d;
            font-size: 1.1rem;
        }
        
        /* Stats Section */
        .stats-section {
            margin-bottom: 30px;
        }
        
        .stats-title {
            color: white;
            font-size: 1.3rem;
            margin-bottom: 15px;
            padding-left: 10px;
            font-weight: 600;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
        
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 8px;
        }
        
        .stat-label {
            color: #7f8c8d;
            font-size: 0.95rem;
            font-weight: 500;
        }
        
        .stat-sublabel {
            color: #95a5a6;
            font-size: 0.8rem;
            margin-top: 5px;
        }
        
        /* Diferentes colores para cada tipo de estadística */
        .stat-card.sistema {
            border-left: 4px solid #3498db;
        }
        
        .stat-card.usuario {
            border-left: 4px solid #2ecc71;
        }
        
        /* Modules */
        .modules-title {
            color: white;
            font-size: 1.5rem;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .module-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        
        .module-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 25px rgba(0,0,0,0.2);
        }
        
        .module-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 2px solid #ecf0f1;
        }
        
        .module-icon {
            font-size: 2.5rem;
        }
        
        .module-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #2c3e50;
        }
        
        .module-body {
            color: #7f8c8d;
            line-height: 1.6;
        }
        
        .module-description {
            margin-bottom: 15px;
        }
        
        .module-btn {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            text-align: center;
            margin-top: 10px;
        }
        
        .module-card:hover .module-btn {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }
        
        /* Recent Activity */
        .recent-activity {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .recent-activity h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 1.3rem;
        }
        
        .activity-list {
            list-style: none;
        }
        
        .activity-item {
            padding: 15px;
            border-left: 3px solid #3498db;
            background: #f8f9fa;
            margin-bottom: 10px;
            border-radius: 5px;
        }
        
        .activity-item:last-child {
            margin-bottom: 0;
        }
        
        .activity-title {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .activity-meta {
            font-size: 0.85rem;
            color: #7f8c8d;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #95a5a6;
        }
        
        .empty-state-icon {
            font-size: 3rem;
            margin-bottom: 10px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                text-align: center;
            }
            
            .header-left, .header-right {
                width: 100%;
            }
            
            .header-right {
                margin-top: 15px;
                flex-direction: column;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .modules-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <h1>🏥 <?php echo SITE_NAME; ?></h1>
                <p>Sistema de Preparación EUNACOM</p>
            </div>
            <div class="header-right">
                <div class="user-info">
                    <div class="user-name"><?php echo e($usuario['nombre']); ?></div>
                    <div class="user-email"><?php echo e($usuario['email']); ?></div>
                </div>
                <a href="<?php echo buildUrl('logout.php'); ?>" class="btn-logout">Cerrar Sesión</a>
            </div>
        </div>
        
        <!-- Welcome -->
        <div class="welcome">
            <h2>¡Bienvenido de vuelta, <?php echo e(explode(' ', $usuario['nombre'])[0]); ?>!</h2>
            <p>Estás a un paso más cerca de aprobar tu examen EUNACOM</p>
        </div>
        
        <!-- Estadísticas del Sistema -->
        <div class="stats-section">
            <h3 class="stats-title">📊 Contenido Disponible en la Plataforma</h3>
            <div class="stats-grid">
                <div class="stat-card sistema">
                    <div class="stat-icon">📚</div>
                    <div class="stat-value"><?php echo $total_areas; ?></div>
                    <div class="stat-label">Áreas Médicas</div>
                </div>
                
                <div class="stat-card sistema">
                    <div class="stat-icon">📝</div>
                    <div class="stat-value"><?php echo number_format($total_temas); ?></div>
                    <div class="stat-label">Temas Disponibles</div>
                </div>
                
                <div class="stat-card sistema">
                    <div class="stat-icon">📄</div>
                    <div class="stat-value"><?php echo number_format($total_documentos); ?></div>
                    <div class="stat-label">Documentos de Estudio</div>
                </div>
                
                <div class="stat-card sistema">
                    <div class="stat-icon">❓</div>
                    <div class="stat-value"><?php echo number_format($total_preguntas); ?></div>
                    <div class="stat-label">Preguntas en el Banco</div>
                </div>
            </div>
        </div>
        
        <!-- Estadísticas del Usuario -->
        <div class="stats-section">
            <h3 class="stats-title">🎯 Tu Progreso Personal</h3>
            <div class="stats-grid">
                <div class="stat-card usuario">
                    <div class="stat-icon">💪</div>
                    <div class="stat-value"><?php echo number_format($preguntas_respondidas_display); ?></div>
                    <div class="stat-label">Preguntas Respondidas</div>
                    <div class="stat-sublabel">En simulacros</div>
                </div>
                
                <div class="stat-card usuario">
                    <div class="stat-icon">✅</div>
                    <div class="stat-value"><?php echo number_format($preguntas_correctas_display); ?></div>
                    <div class="stat-label">Respuestas Correctas</div>
                    <div class="stat-sublabel">¡Sigue así!</div>
                </div>
                
                <div class="stat-card usuario">
                    <div class="stat-icon">📊</div>
                    <div class="stat-value"><?php echo $porcentaje_aciertos; ?>%</div>
                    <div class="stat-label">Porcentaje de Aciertos</div>
                    <div class="stat-sublabel">
                        <?php 
                        if ($porcentaje_aciertos >= 70) {
                            echo '¡Excelente! 🎉';
                        } elseif ($porcentaje_aciertos >= 50) {
                            echo 'Buen progreso 👍';
                        } elseif ($porcentaje_aciertos > 0) {
                            echo 'Sigue practicando 💪';
                        } else {
                            echo 'Comienza tu práctica';
                        }
                        ?>
                    </div>
                </div>
                
                <div class="stat-card usuario">
                    <div class="stat-icon">🎯</div>
                    <div class="stat-value"><?php echo $simulacros_completados; ?></div>
                    <div class="stat-label">Simulacros Completados</div>
                    <div class="stat-sublabel">
                        <?php if ($mejor_puntaje > 0): ?>
                            Mejor: <?php echo $mejor_puntaje; ?>%
                        <?php else: ?>
                            Realiza tu primer simulacro
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Modules -->
        <h2 class="modules-title">📚 Módulos de Estudio</h2>
        
        <div class="modules-grid">
            <!-- Módulo 1: Materiales -->
            <a href="<?php echo buildUrl('materiales.php'); ?>" class="module-card">
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
            <a href="<?php echo buildUrl('entrenamiento.php'); ?>" class="module-card">
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
            <a href="<?php echo buildUrl('simulacro.php'); ?>" class="module-card">
                <div class="module-header">
                    <div class="module-icon">🎯</div>
                    <div class="module-title">Simulacro EUNACOM</div>
                </div>
                <div class="module-body">
                    <div class="module-description">
                        Realiza una simulación completa del examen oficial con 180 preguntas en 2 sesiones de 90 minutos.
                    </div>
                    <div class="module-btn">Iniciar Simulacro →</div>
                </div>
            </a>
        </div>
        
        <!-- Recent Activity -->
        <?php if (count($ultimos_simulacros) > 0): ?>
        <div class="recent-activity">
            <h3>📈 Últimos Simulacros</h3>
            <ul class="activity-list">
                <?php foreach ($ultimos_simulacros as $simulacro): ?>
                <li class="activity-item">
                    <div class="activity-title">
                        Simulacro #<?php echo e($simulacro['codigo_examen']); ?>
                        <?php if ($simulacro['estado'] === 'finalizado'): ?>
                            - <?php echo number_format($simulacro['puntaje_porcentaje'], 1); ?>%
                        <?php else: ?>
                            - <?php echo ($simulacro['estado'] === 'en_curso') ? 'En Curso' : 'Pendiente'; ?>
                        <?php endif; ?>
                    </div>
                    <div class="activity-meta">
                        <?php echo date('d/m/Y H:i', strtotime($simulacro['fecha_inicio'])); ?>
                        <?php if ($simulacro['estado'] === 'finalizado'): ?>
                            - <?php echo $simulacro['respuestas_correctas']; ?>/<?php echo $simulacro['total_preguntas']; ?> correctas
                        <?php endif; ?>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php else: ?>
        <div class="recent-activity">
            <div class="empty-state">
                <div class="empty-state-icon">📝</div>
                <p>Aún no has realizado ningún simulacro</p>
                <p style="font-size: 0.9rem; margin-top: 10px;">¡Comienza tu preparación ahora!</p>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Footer Info -->
        <?php if ($tiempo_estudio_horas > 0 || $tiempo_estudio_min > 0): ?>
        <div style="background: white; border-radius: 15px; padding: 20px; text-align: center; margin-top: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <p style="color: #7f8c8d;">
                ⏱️ Tiempo total de estudio: 
                <strong style="color: #2c3e50;">
                    <?php 
                    if ($tiempo_estudio_horas > 0) {
                        echo $tiempo_estudio_horas . 'h ';
                    }
                    echo $tiempo_estudio_min . 'min';
                    ?>
                </strong>
            </p>
        </div>
        <?php endif; ?>
        
    </div>
</body>
</html>
