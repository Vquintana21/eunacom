<?php
require_once 'auth.php';

// Requerir autenticación
requireAuth();

$usuario = getUsuarioActual();
$pdo = getDBConnection();

// Obtener estadísticas del usuario
$stmt = $pdo->prepare("
    SELECT COUNT(*) as total_examenes,
           SUM(CASE WHEN estado = 'finalizado' THEN 1 ELSE 0 END) as examenes_completados,
           AVG(CASE WHEN estado = 'finalizado' THEN puntaje_porcentaje ELSE NULL END) as promedio_puntaje
    FROM examenes
    WHERE usuario_id = ?
");
$stmt->execute([$usuario['id']]);
$stats_examenes = $stmt->fetch();

// Obtener progreso por área
$stmt = $pdo->prepare("
    SELECT 
        a.nombre as area_nombre,
        COALESCE(p.temas_completados, 0) as temas_completados,
        COALESCE(p.total_preguntas_respondidas, 0) as preguntas_respondidas,
        COALESCE(p.porcentaje_aciertos, 0) as porcentaje_aciertos
    FROM areas a
    LEFT JOIN progreso_estudiante p ON a.id = p.area_id AND p.usuario_id = ?
    WHERE a.activo = 1
    ORDER BY a.id
");
$stmt->execute([$usuario['id']]);
$progreso_areas = $stmt->fetchAll();

// Obtener últimos exámenes
$stmt = $pdo->prepare("
    SELECT 
        codigo_examen,
        estado,
        fecha_inicio,
        fecha_finalizacion,
        puntaje_porcentaje,
        respuestas_correctas,
        total_preguntas
    FROM examenes
    WHERE usuario_id = ?
    ORDER BY fecha_inicio DESC
    LIMIT 5
");
$stmt->execute([$usuario['id']]);
$ultimos_examenes = $stmt->fetchAll();

// Mensaje de bienvenida para nuevos usuarios
$mostrar_bienvenida = isset($_GET['bienvenida']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - EUNACOM</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            min-height: 100vh;
        }
        
        /* Header */
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 1.5rem;
            font-weight: bold;
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .user-name {
            font-weight: 600;
        }
        
        .btn-logout {
            background: rgba(255,255,255,0.2);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-logout:hover {
            background: rgba(255,255,255,0.3);
        }
        
        /* Container */
        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        /* Bienvenida */
        .welcome-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }
        
        .welcome-banner h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .welcome-banner p {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        
        /* Stats Cards */
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
            font-size: 1.8rem;
            color: #2c3e50;
            margin-bottom: 20px;
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
            display: block;
        }
        
        .module-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .module-header {
            padding: 30px;
            text-align: center;
            color: white;
        }
        
        .module-header.materials {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        }
        
        .module-header.training {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        }
        
        .module-header.exam {
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
        }
        
        .module-icon {
            font-size: 4rem;
            margin-bottom: 15px;
        }
        
        .module-title {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .module-body {
            padding: 25px;
        }
        
        .module-description {
            color: #7f8c8d;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        
        .module-features {
            list-style: none;
            margin-bottom: 20px;
        }
        
        .module-features li {
            padding: 8px 0;
            color: #2c3e50;
        }
        
        .module-features li::before {
            content: '✓';
            color: #27ae60;
            font-weight: bold;
            margin-right: 10px;
        }
        
        .module-btn {
            display: block;
            width: 100%;
            padding: 12px;
            background: #f8f9fa;
            color: #2c3e50;
            text-align: center;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .module-btn:hover {
            background: #e9ecef;
        }
        
        /* Progress Section */
        .section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .section-title {
            font-size: 1.5rem;
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
        }
        
        .progress-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #f8f9fa;
        }
        
        .progress-item:last-child {
            border-bottom: none;
        }
        
        .progress-info {
            flex: 1;
        }
        
        .progress-name {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .progress-stats {
            font-size: 0.9rem;
            color: #7f8c8d;
        }
        
        .progress-bar-container {
            width: 200px;
            height: 8px;
            background: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
            margin-left: 20px;
        }
        
        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            transition: width 0.3s;
        }
        
        /* Exam History */
        .exam-history-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            margin-bottom: 10px;
        }
        
        .exam-info {
            flex: 1;
        }
        
        .exam-code {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .exam-date {
            font-size: 0.9rem;
            color: #7f8c8d;
        }
        
        .exam-score {
            font-size: 1.5rem;
            font-weight: bold;
            color: #27ae60;
        }
        
        .exam-status {
            display: inline-block;
            padding: 5px 15px;
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
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
        }
        
        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .stats-grid,
            .modules-grid {
                grid-template-columns: 1fr;
            }
            
            .progress-bar-container {
                width: 100px;
            }
            
            .exam-history-item {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <div class="logo">🎓 EUNACOM</div>
            <div class="user-menu">
                <span class="user-name">👤 <?= htmlspecialchars($usuario['nombre']) ?></span>
                <a href="<?= buildUrl('logout.php') ?>" class="btn-logout">Cerrar Sesión</a>
            </div>
        </div>
    </div>
    
    <!-- Container -->
    <div class="container">
        
        <!-- Bienvenida -->
        <?php if ($mostrar_bienvenida): ?>
            <div class="welcome-banner">
                <h1>🎉 ¡Bienvenido, <?= htmlspecialchars($usuario['nombre']) ?>!</h1>
                <p>Tu cuenta ha sido creada exitosamente. Comienza tu preparación para el EUNACOM.</p>
            </div>
        <?php else: ?>
            <div class="welcome-banner">
                <h1>👋 Hola, <?= htmlspecialchars($usuario['nombre']) ?></h1>
                <p>Continúa tu preparación para el EUNACOM</p>
            </div>
        <?php endif; ?>
        
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📝</div>
                <div class="stat-value"><?= $stats_examenes['total_examenes'] ?></div>
                <div class="stat-label">Simulacros Iniciados</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-value"><?= $stats_examenes['examenes_completados'] ?></div>
                <div class="stat-label">Simulacros Completados</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-value">
                    <?= $stats_examenes['promedio_puntaje'] ? number_format($stats_examenes['promedio_puntaje'], 1) . '%' : '-' ?>
                </div>
                <div class="stat-label">Promedio de Puntaje</div>
            </div>
        </div>
        
        <!-- Modules -->
        <h2 class="modules-title">🎯 Módulos de Estudio</h2>
        <div class="modules-grid">
            
            <!-- Módulo 1: Materiales -->
            <a href="<?= buildUrl('materiales.php') ?>" class="module-card" target='_blank'>
                <div class="module-header materials">
                    <div class="module-icon">📚</div>
                    <div class="module-title">Materiales de Estudio</div>
                </div>
                <div class="module-body">
                    <p class="module-description">
                        Accede a material de estudio organizado por áreas y especialidades. PDFs descargables con contenido actualizado.
                    </p>
                    <ul class="module-features">
                        <li>452 documentos disponibles</li>
                        <li>Organizados por categorías</li>
                        <li>Descarga y visualización online</li>
                    </ul>
                    <div class="module-btn">Ir a Materiales →</div>
                </div>
            </a>
            
            <!-- Módulo 2: Entrenamiento -->
            <a href="<?= buildUrl('entrenamiento.php') ?>" class="module-card" target='_blank'>
                <div class="module-header training">
                    <div class="module-icon">💪</div>
                    <div class="module-title">Entrenamiento por Temas</div>
                </div>
                <div class="module-body">
                    <p class="module-description">
                        Practica con preguntas tipo test organizadas por temas. Recibe retroalimentación inmediata.
                    </p>
                    <ul class="module-features">
                        <li>Preguntas por especialidad</li>
                        <li>Explicaciones detalladas</li>
                        <li>Seguimiento de progreso</li>
                    </ul>
                    <div class="module-btn">Comenzar Entrenamiento →</div>
                </div>
            </a>
            
            <!-- Módulo 3: Simulacro -->
            <a href="<?= buildUrl('simulacro_inicio.php') ?>" class="module-card" target='_blank'>
                <div class="module-header exam">
                    <div class="module-icon">🎯</div>
                    <div class="module-title">Simulacro de Examen</div>
                </div>
                <div class="module-body">
                    <p class="module-description">
                        Simula el examen real EUNACOM. 180 preguntas en 2 sesiones de 90 minutos cada una.
                    </p>
                    <ul class="module-features">
                        <li>Formato de examen real</li>
                        <li>Timer cronometrado</li>
                        <li>Resultados detallados</li>
                    </ul>
                    <div class="module-btn">Iniciar Simulacro →</div>
                </div>
            </a>
            
        </div>
        
        <!-- Progreso por Área -->
        <?php if (!empty($progreso_areas)): ?>
        <div class="section">
            <h2 class="section-title">📈 Tu Progreso por Área</h2>
            <?php foreach ($progreso_areas as $area): ?>
                <?php if ($area['preguntas_respondidas'] > 0): ?>
                <div class="progress-item">
                    <div class="progress-info">
                        <div class="progress-name"><?= htmlspecialchars($area['area_nombre']) ?></div>
                        <div class="progress-stats">
                            <?= $area['preguntas_respondidas'] ?> preguntas • 
                            <?= number_format($area['porcentaje_aciertos'], 1) ?>% de aciertos
                        </div>
                    </div>
                    <div class="progress-bar-container">
                        <div class="progress-bar" style="width: <?= $area['porcentaje_aciertos'] ?>%"></div>
                    </div>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>
            
            <?php if (array_sum(array_column($progreso_areas, 'preguntas_respondidas')) == 0): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📊</div>
                    <p>Aún no has comenzado a entrenar. ¡Empieza ahora!</p>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <!-- Historial de Exámenes -->
        <div class="section">
            <h2 class="section-title">📋 Últimos Simulacros</h2>
            
            <?php if (empty($ultimos_examenes)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📝</div>
                    <p>No has realizado ningún simulacro aún.</p>
                    <p><a href="<?= buildUrl('simulacro_inicio.php') ?>" style="color: #667eea; text-decoration: none; font-weight: 600;">Iniciar tu primer simulacro →</a></p>
                </div>
            <?php else: ?>
                <?php foreach ($ultimos_examenes as $examen): ?>
                <div class="exam-history-item">
                    <div class="exam-info">
                        <div class="exam-code">
                            📄 <?= htmlspecialchars($examen['codigo_examen']) ?>
                        </div>
                        <div class="exam-date">
                            <?= date('d/m/Y H:i', strtotime($examen['fecha_inicio'])) ?>
                        </div>
                        <span class="exam-status status-<?= $examen['estado'] ?>">
                            <?= ucfirst(str_replace('_', ' ', $examen['estado'])) ?>
                        </span>
                    </div>
                    <?php if ($examen['estado'] === 'finalizado'): ?>
                        <div class="exam-score">
                            <?= number_format($examen['puntaje_porcentaje'], 1) ?>%
                            <div style="font-size: 0.7rem; color: #7f8c8d;">
                                (<?= $examen['respuestas_correctas'] ?>/<?= $examen['total_preguntas'] ?>)
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
    </div>
</body>
</html>