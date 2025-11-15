<?php
session_start();

require_once __DIR__ . '/env/config.php';
require_once __DIR__ . '/auth.php';

// Requiere autenticación
requireAuth();

// Obtener usuario actual
$usuario = getCurrentUser();
$usuario_id = $usuario['id'];

// Obtener conexión a BD
$pdo = getDB();

// Obtener código del examen
$codigo_examen = isset($_GET['examen']) ? $_GET['examen'] : null;

if (!$codigo_examen) {
    header("Location: simulacro_inicio.php");
    exit;
}

// Obtener datos del examen
$sql = "
    SELECT * FROM examenes 
    WHERE codigo_examen = ? AND usuario_id = ?
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$codigo_examen, $usuario_id]);
$examen = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$examen) {
    die("Examen no encontrado");
}

// Verificar si ya finalizó
if ($examen['estado'] === 'finalizado') {
    header("Location: simulacro_resultados.php?examen=" . $codigo_examen);
    exit;
}

// Determinar sesión actual
$sesion_actual = $examen['sesion_actual'];
$tiempo_restante = $sesion_actual == 1 ? $examen['tiempo_restante_sesion1'] : $examen['tiempo_restante_sesion2'];

// Obtener preguntas de la sesión actual
$sql = "
    SELECT 
        ep.id as examen_pregunta_id,
        ep.orden,
        p.id as pregunta_id,
        p.numero_pregunta,
        p.texto_pregunta,
        ru.alternativa_seleccionada,
        ru.marcada_revision
    FROM examen_preguntas ep
    INNER JOIN preguntas p ON ep.pregunta_id = p.id
    LEFT JOIN respuestas_usuario ru ON ru.examen_id = ep.examen_id AND ru.pregunta_id = p.id
    WHERE ep.examen_id = ? AND ep.sesion = ?
    ORDER BY ep.orden
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$examen['id'], $sesion_actual]);
$preguntas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener alternativas para cada pregunta
foreach ($preguntas as &$pregunta) {
    $sql = "
        SELECT opcion, texto_alternativa
        FROM alternativas
        WHERE pregunta_id = ?
        ORDER BY orden
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$pregunta['pregunta_id']]);
    $pregunta['alternativas'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Calcular estadísticas de progreso
$sql = "
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN alternativa_seleccionada IS NOT NULL THEN 1 ELSE 0 END) as respondidas,
        SUM(CASE WHEN marcada_revision = 1 THEN 1 ELSE 0 END) as marcadas
    FROM respuestas_usuario ru
    INNER JOIN examen_preguntas ep ON ru.examen_id = ep.examen_id AND ru.pregunta_id = ep.pregunta_id
    WHERE ru.examen_id = ? AND ep.sesion = ?
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$examen['id'], $sesion_actual]);
$progreso = $stmt->fetch(PDO::FETCH_ASSOC);

// Obtener información del tema de cada pregunta (para navegación)
$sql = "
    SELECT 
        p.id as pregunta_id,
        t.nombre as tema_nombre,
        a.nombre as area_nombre
    FROM examen_preguntas ep
    INNER JOIN preguntas p ON ep.pregunta_id = p.id
    INNER JOIN temas t ON p.tema_id = t.id
    INNER JOIN especialidades e ON t.especialidad_id = e.id
    INNER JOIN areas a ON e.area_id = a.id
    WHERE ep.examen_id = ? AND ep.sesion = ?
    ORDER BY ep.orden
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$examen['id'], $sesion_actual]);
$info_preguntas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulacro EUNACOM - Sesión <?= $sesion_actual ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        /* BARRA SUPERIOR FIJA */
        .header-bar {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 15px 30px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header-info {
            display: flex;
            gap: 30px;
            align-items: center;
        }
        
        .exam-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .session-badge {
            background: #3498db;
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
        }
        
        /* TIMER */
        .timer-container {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .timer {
            font-size: 2rem;
            font-weight: bold;
            color: #2c3e50;
            font-family: 'Courier New', monospace;
            min-width: 150px;
            text-align: center;
        }
        
        .timer.warning {
            color: #f39c12;
            animation: pulse 1s infinite;
        }
        
        .timer.danger {
            color: #e74c3c;
            animation: pulse 0.5s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        /* LAYOUT PRINCIPAL */
        .main-content {
            margin-top: 80px;
            display: flex;
            flex: 1;
        }
        
        /* PANEL LATERAL DE NAVEGACIÓN */
        .sidebar {
            width: 300px;
            background: white;
            padding: 20px;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            overflow-y: auto;
            max-height: calc(100vh - 80px);
            position: fixed;
            left: 0;
            top: 80px;
        }
        
        .sidebar h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 1.1rem;
        }
        
        .progress-info {
            background: #ecf0f1;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .progress-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }
        
        .progress-label {
            color: #7f8c8d;
        }
        
        .progress-value {
            font-weight: 600;
            color: #2c3e50;
        }
        
        /* GRID DE NAVEGACIÓN DE PREGUNTAS */
        .questions-grid {
            display: grid;
            grid-template-columns: repeat(10, 1fr);
            gap: 8px;
            margin-top: 15px;
        }
        
        .question-nav-btn {
            aspect-ratio: 1;
            border: 2px solid #bdc3c7;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .question-nav-btn:hover {
            transform: scale(1.1);
        }
        
        .question-nav-btn.answered {
            background: #d4edda;
            border-color: #28a745;
            color: #155724;
        }
        
        .question-nav-btn.marked {
            background: #fff3cd;
            border-color: #ffc107;
            color: #856404;
        }
		
		.question-nav-btn.marked-empty {
				background: #ffe0b2;
				border-color: #ff9800;
				color: #e65100;
			}
        
        .question-nav-btn.current {
            background: #3498db;
            border-color: #2980b9;
            color: white;
            transform: scale(1.15);
        }
        
        /* ÁREA DE PREGUNTA */
        .question-area {
            margin-left: 320px;
            padding: 30px;
            flex: 1;
        }
        
        .question-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .question-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 2px solid #ecf0f1;
        }
        
        .question-number {
            font-size: 1.5rem;
            font-weight: 600;
            color: #3498db;
        }
        
        .question-meta {
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        
        .question-text {
            font-size: 1.2rem;
            color: #2c3e50;
            line-height: 1.6;
            margin-bottom: 25px;
        }
        
        .alternatives {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .alternative {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
        }
        
        .alternative:hover {
            background: #e3f2fd;
            border-color: #3498db;
        }
        
        .alternative input[type="radio"] {
            margin-right: 15px;
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        
        .alternative.selected {
            background: #e3f2fd;
            border-color: #3498db;
            border-width: 3px;
        }
        
        .option-letter {
            display: inline-block;
            width: 35px;
            height: 35px;
            background: #3498db;
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 35px;
            font-weight: bold;
            margin-right: 15px;
            flex-shrink: 0;
        }
        
        /* BOTONES DE NAVEGACIÓN */
        .navigation-buttons {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-previous {
            background: #95a5a6;
            color: white;
        }
        
        .btn-previous:hover {
            background: #7f8c8d;
        }
        
        .btn-next {
            background: #3498db;
            color: white;
        }
        
        .btn-next:hover {
            background: #2980b9;
        }
        
        .btn-mark {
            background: #f39c12;
            color: white;
        }
        
        .btn-mark:hover {
            background: #e67e22;
        }
        
        .btn-mark.marked {
            background: #e67e22;
        }
        
        .btn-finish {
            background: #27ae60;
            color: white;
            flex: 1;
        }
        
        .btn-finish:hover {
            background: #229954;
        }
        
        /* MODAL DE FINALIZACIÓN */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.7);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }
        
        .modal.show {
            display: flex;
        }
        
        .modal-content {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 500px;
            width: 90%;
        }
        
        .modal-content h2 {
            color: #2c3e50;
            margin-bottom: 20px;
        }
        
        .modal-stats {
            background: #ecf0f1;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        
        .modal-stat-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .modal-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn-cancel {
            background: #95a5a6;
            color: white;
            flex: 1;
        }
        
        .btn-confirm {
            background: #27ae60;
            color: white;
            flex: 1;
        }
		
		.btn-cancel {
            background: #e74c3c;
            color: white;
        }
        
        .btn-cancel:hover {
            background: #c0392b;
        }
		
		.btn-danger {
            background: #e74c3c;
            color: white;
            flex: 1;
        }
        
        .btn-danger:hover {
            background: #c0392b;
        }
    </style>
</head>
<body>
    
<!-- BARRA SUPERIOR -->
    <div class="header-bar">
        <div class="header-info">
            <div class="exam-title">Simulacro EUNACOM</div>
            <div class="session-badge">Sesión <?= $sesion_actual ?></div>
        </div>
        
        <div class="timer-container">
            <div class="timer" id="timer">
                <?= sprintf('%02d:%02d:%02d', floor($tiempo_restante / 3600), floor(($tiempo_restante % 3600) / 60), $tiempo_restante % 60) ?>
            </div>
            <button class="btn btn-finish" onclick="mostrarModalFinalizacion()">
                <?= $sesion_actual == 1 ? 'Finalizar Sesión 1' : 'Finalizar Examen' ?>
            </button>
            <button class="btn btn-cancel" onclick="mostrarModalCancelacion()">
                ❌ Cancelar Examen
            </button>
        </div>
    </div>
    
    <!-- CONTENIDO PRINCIPAL -->
    <div class="main-content">
        
        <!-- PANEL LATERAL -->
        <div class="sidebar">
            <h3>📊 Progreso</h3>
            <div class="progress-info">
                <div class="progress-item">
                    <span class="progress-label">Respondidas:</span>
                    <span class="progress-value" id="progress-answered"><?= $progreso['respondidas'] ?>/90</span>
                </div>
                <div class="progress-item">
                    <span class="progress-label">Omitidas:</span>
                    <span class="progress-value" id="progress-omitted"><?= 90 - $progreso['respondidas'] ?></span>
                </div>
                <div class="progress-item">
                    <span class="progress-label">Marcadas:</span>
                    <span class="progress-value" id="progress-marked"><?= $progreso['marcadas'] ?></span>
                </div>
            </div>
            
            <h3>🗂️ Navegación</h3>
            <div class="questions-grid" id="questions-grid">
                <?php foreach ($preguntas as $index => $pregunta): ?>
                    <button 
                        class="question-nav-btn <?= $pregunta['alternativa_seleccionada'] ? 'answered' : '' ?> <?= $pregunta['marcada_revision'] ? 'marked' : '' ?> <?= $index == 0 ? 'current' : '' ?>"
                        onclick="irAPregunta(<?= $index ?>)"
                        id="nav-btn-<?= $index ?>"
                    >
                        <?= $index + 1 ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- ÁREA DE PREGUNTA -->
        <div class="question-area">
            <div class="question-card" id="question-card">
                <!-- Las preguntas se cargan dinámicamente con JavaScript -->
            </div>
            
            <div class="navigation-buttons">
                <button class="btn btn-previous" id="btn-previous" onclick="preguntaAnterior()">
                    ← Anterior
                </button>
                
                <button class="btn btn-mark" id="btn-mark" onclick="toggleMarcar()">
					🚩 Revisar después
				</button>
								
                <button class="btn btn-next" id="btn-next" onclick="preguntaSiguiente()">
                    Siguiente →
                </button>
            </div>
        </div>
    </div>
    
    <!-- MODAL DE FINALIZACIÓN -->
    <div class="modal" id="modal-finalizacion">
        <div class="modal-content">
            <h2>¿Finalizar <?= $sesion_actual == 1 ? 'Sesión 1' : 'Examen' ?>?</h2>
            
            <div class="modal-stats">
                <div class="modal-stat-item">
                    <span>Respondidas:</span>
                    <strong id="modal-respondidas">0</strong>
                </div>
                <div class="modal-stat-item">
                    <span>Omitidas:</span>
                    <strong id="modal-omitidas">0</strong>
                </div>
                <div class="modal-stat-item">
                    <span>Marcadas para revisión:</span>
                    <strong id="modal-marcadas">0</strong>
                </div>
            </div>
            
            <p style="color: #7f8c8d;">
                <?php if ($sesion_actual == 1): ?>
                    Una vez finalizada la Sesión 1, podrás tomar un descanso antes de iniciar la Sesión 2.
                <?php else: ?>
                    Una vez finalizado el examen, verás tus resultados detallados.
                <?php endif; ?>
            </p>
            
            <div class="modal-buttons">
                <button class="btn btn-cancel" onclick="cerrarModal()">Cancelar</button>
                <button class="btn btn-confirm" onclick="confirmarFinalizacion()">
                    Confirmar
                </button>
            </div>
        </div>
    </div>
	
	<!-- MODAL DE CANCELACIÓN -->
    <div class="modal" id="modal-cancelacion">
        <div class="modal-content">
            <h2 style="color: #e74c3c;">⚠️ ¿Cancelar Examen?</h2>
            
            <div style="background: #fff3cd; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #f39c12;">
                <p style="color: #856404; margin-bottom: 10px;">
                    <strong>Importante:</strong>
                </p>
                <ul style="color: #856404; margin-left: 20px;">
                    <li>Este examen será cancelado definitivamente</li>
                    <li>No se guardará en tu historial de rendimiento</li>
                    <li>Podrás iniciar un nuevo simulacro cuando desees</li>
                    <li>Todo tu progreso actual se perderá</li>
                </ul>
            </div>
            
            <p style="color: #7f8c8d; text-align: center; margin: 20px 0;">
                ¿Estás seguro de que deseas cancelar este examen?
            </p>
            
            <div class="modal-buttons">
                <button class="btn btn-secondary" onclick="cerrarModalCancelacion()">
                    No, continuar examen
                </button>
                <button class="btn btn-danger" onclick="confirmarCancelacion()">
                    Sí, cancelar examen
                </button>
            </div>
        </div>
    </div>
    
    <script>
        // ============================================
        // DATOS DEL EXAMEN
        // ============================================
        const EXAMEN_ID = <?= $examen['id'] ?>;
        const CODIGO_EXAMEN = '<?= $codigo_examen ?>';
        const SESION_ACTUAL = <?= $sesion_actual ?>;
        let TIEMPO_RESTANTE = <?= $tiempo_restante ?>; // Segundos
        
        const PREGUNTAS = <?= json_encode($preguntas) ?>;
        let preguntaActual = 0;
        
        // ============================================
        // TIMER
        // ============================================
        let timerInterval;
        
        function iniciarTimer() {
            timerInterval = setInterval(() => {
                TIEMPO_RESTANTE--;
                
                if (TIEMPO_RESTANTE <= 0) {
                    clearInterval(timerInterval);
                    autoEnviar();
                    return;
                }
                
                // Actualizar display
                actualizarTimer();
                
                // Guardar tiempo cada 10 segundos
                if (TIEMPO_RESTANTE % 10 === 0) {
                    guardarTiempo();
                }
                
                // Alertas
                if (TIEMPO_RESTANTE === 600) { // 10 minutos
                    alert('⏰ Quedan 10 minutos');
                } else if (TIEMPO_RESTANTE === 300) { // 5 minutos
                    alert('⏰ Quedan 5 minutos');
                } else if (TIEMPO_RESTANTE === 60) { // 1 minuto
                    alert('⚠️ ¡Queda 1 minuto!');
                }
                
            }, 1000);
        }
        
        function actualizarTimer() {
            const horas = Math.floor(TIEMPO_RESTANTE / 3600);
            const minutos = Math.floor((TIEMPO_RESTANTE % 3600) / 60);
            const segundos = TIEMPO_RESTANTE % 60;
            
            const display = `${String(horas).padStart(2, '0')}:${String(minutos).padStart(2, '0')}:${String(segundos).padStart(2, '0')}`;
            
            const timerEl = document.getElementById('timer');
            timerEl.textContent = display;
            
            // Cambiar color según tiempo
            timerEl.classList.remove('warning', 'danger');
            if (TIEMPO_RESTANTE <= 300) { // 5 minutos
                timerEl.classList.add('danger');
            } else if (TIEMPO_RESTANTE <= 600) { // 10 minutos
                timerEl.classList.add('warning');
            }
        }
        
        function guardarTiempo() {
            const campo = SESION_ACTUAL === 1 ? 'tiempo_restante_sesion1' : 'tiempo_restante_sesion2';
            
            fetch('simulacro_ajax.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    action: 'guardar_tiempo',
                    examen_id: EXAMEN_ID,
                    tiempo_restante: TIEMPO_RESTANTE,
                    campo: campo
                })
            });
        }
        
        function autoEnviar() {
            alert('⏰ Se acabó el tiempo. El examen se enviará automáticamente.');
            confirmarFinalizacion();
        }
        
        // ============================================
        // NAVEGACIÓN DE PREGUNTAS
        // ============================================
        function cargarPregunta(index) {
            preguntaActual = index;
            const pregunta = PREGUNTAS[index];
            
            let html = `
                <div class="question-header">
                    <div>
                        <div class="question-number">Pregunta ${index + 1} de 90</div>
                    </div>
                </div>
                
                <div class="question-text">${pregunta.texto_pregunta}</div>
                
                <div class="alternatives">
            `;
            
            pregunta.alternativas.forEach(alt => {
                const isSelected = alt.opcion === pregunta.alternativa_seleccionada;
                html += `
                    <label class="alternative ${isSelected ? 'selected' : ''}" onclick="seleccionarAlternativa('${alt.opcion}')">
                        <input type="radio" name="respuesta" value="${alt.opcion}" ${isSelected ? 'checked' : ''}>
                        <span class="option-letter">${alt.opcion}</span>
                        <span>${alt.texto_alternativa}</span>
                    </label>
                `;
            });
            
            html += '</div>';
            
            document.getElementById('question-card').innerHTML = html;
            
            // Actualizar navegación
            actualizarNavegacion();
            
            // Actualizar botón de marcar
            actualizarBotonMarcar();
        }
        
			function seleccionarAlternativa(opcion) {
				const pregunta = PREGUNTAS[preguntaActual];
				
				// Si ya está seleccionada esta misma opción, desmarcar
				let nuevaAlternativa = opcion;
				if (pregunta.alternativa_seleccionada === opcion) {
					nuevaAlternativa = null; // Desmarcar
				}
				
				// Guardar respuesta (puede ser null para desmarcar)
				fetch('simulacro_ajax.php', {
					method: 'POST',
					headers: {'Content-Type': 'application/json'},
					body: JSON.stringify({
						action: 'guardar_respuesta',
						examen_id: EXAMEN_ID,
						pregunta_id: pregunta.pregunta_id,
						alternativa: nuevaAlternativa
					})
				})
				.then(response => response.json())
				.then(data => {
					if (data.success) {
						// Actualizar estado local
						pregunta.alternativa_seleccionada = nuevaAlternativa;
						
						// Actualizar UI
						actualizarProgreso();
						
						// Recargar la pregunta completa para actualizar radios
						cargarPregunta(preguntaActual);
					}
				})
				.catch(error => {
					console.error('Error al guardar respuesta:', error);
				});
			}
        
        function toggleMarcar() {
            const pregunta = PREGUNTAS[preguntaActual];
            const nuevoEstado = !pregunta.marcada_revision;
            
            fetch('simulacro_ajax.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    action: 'marcar_revision',
                    examen_id: EXAMEN_ID,
                    pregunta_id: pregunta.pregunta_id,
                    marcada: nuevoEstado
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    PREGUNTAS[preguntaActual].marcada_revision = nuevoEstado;
                    actualizarNavegacion();
                    actualizarBotonMarcar();
                    actualizarProgreso();
                }
            });
        }
        
        function preguntaAnterior() {
            if (preguntaActual > 0) {
                cargarPregunta(preguntaActual - 1);
            }
        }
        
        function preguntaSiguiente() {
            if (preguntaActual < PREGUNTAS.length - 1) {
                cargarPregunta(preguntaActual + 1);
            }
        }
        
        function irAPregunta(index) {
            cargarPregunta(index);
        }
        
	function actualizarNavegacion() {
		document.querySelectorAll('.question-nav-btn').forEach((btn, index) => {
			// Limpiar TODAS las clases de estado
			btn.classList.remove('answered', 'marked', 'marked-empty', 'current');
			
			const pregunta = PREGUNTAS[index];
			
			// 1. Pregunta actual (prioridad máxima - siempre visible)
			if (index === preguntaActual) {
				btn.classList.add('current');
			}
			
			// 2. Estados de respuesta (solo si NO es la actual)
			if (index !== preguntaActual) {
				// Marcada para revisión
				if (pregunta.marcada_revision) {
					if (pregunta.alternativa_seleccionada) {
						// Marcada Y respondida = Amarillo
						btn.classList.add('marked');
					} else {
						// Marcada pero SIN responder = Naranja
						btn.classList.add('marked-empty');
					}
				} 
				// Solo respondida (sin marcar)
				else if (pregunta.alternativa_seleccionada) {
					btn.classList.add('answered');
				}
				// Si no cumple nada, queda blanco (sin clase)
			}
		});
		
		// Actualizar botones de navegación
		document.getElementById('btn-previous').disabled = preguntaActual === 0;
		document.getElementById('btn-next').disabled = preguntaActual === PREGUNTAS.length - 1;
	}
        
      function actualizarBotonMarcar() {
			const btn = document.getElementById('btn-mark');
			if (PREGUNTAS[preguntaActual].marcada_revision) {
				btn.textContent = '✓ Marcada para revisión posterior';
				btn.classList.add('marked');
			} else {
				btn.textContent = '🚩 Revisar después';
				btn.classList.remove('marked');
			}
		}
				
		function actualizarProgreso() {
			let respondidas = 0;
			let marcadas = 0;
			
			PREGUNTAS.forEach(p => {
				if (p.alternativa_seleccionada) respondidas++;
				if (p.marcada_revision) marcadas++;
			});
			
			document.getElementById('progress-answered').textContent = `${respondidas}/90`;
			document.getElementById('progress-omitted').textContent = 90 - respondidas;
			document.getElementById('progress-marked').textContent = marcadas;
			
			// Llamar a la función de navegación que ya maneja los colores correctamente
			actualizarNavegacion();
		}
        
        // ============================================
        // FINALIZACIÓN
        // ============================================
        function mostrarModalFinalizacion() {
            let respondidas = PREGUNTAS.filter(p => p.alternativa_seleccionada).length;
            let omitidas = 90 - respondidas;
            let marcadas = PREGUNTAS.filter(p => p.marcada_revision).length;
            
            document.getElementById('modal-respondidas').textContent = respondidas;
            document.getElementById('modal-omitidas').textContent = omitidas;
            document.getElementById('modal-marcadas').textContent = marcadas;
            
            document.getElementById('modal-finalizacion').classList.add('show');
        }
        
        function cerrarModal() {
            document.getElementById('modal-finalizacion').classList.remove('show');
        }
        
        function confirmarFinalizacion() {
            clearInterval(timerInterval);
            
            fetch('simulacro_ajax.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    action: 'finalizar_sesion',
                    examen_id: EXAMEN_ID,
                    sesion: SESION_ACTUAL,
                    tiempo_restante: TIEMPO_RESTANTE
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (SESION_ACTUAL === 1) {
                        // Ir a pantalla intermedia
                        window.location.href = 'simulacro_intermedio.php?examen=' + CODIGO_EXAMEN;
                    } else {
                        // Ir a resultados
                        window.location.href = 'simulacro_resultados.php?examen=' + CODIGO_EXAMEN;
                    }
                }
            });
        }
        
        // ============================================
        // INICIALIZACIÓN
        // ============================================
        document.addEventListener('DOMContentLoaded', () => {
            cargarPregunta(0);
            iniciarTimer();
            
            // Prevenir cierre accidental
            window.addEventListener('beforeunload', (e) => {
                e.preventDefault();
                e.returnValue = '';
            });
        });
		
		// ============================================
        // CANCELACIÓN DE EXAMEN
        // ============================================
        function mostrarModalCancelacion() {
            document.getElementById('modal-cancelacion').classList.add('show');
        }
        
        function cerrarModalCancelacion() {
            document.getElementById('modal-cancelacion').classList.remove('show');
        }
        
        function confirmarCancelacion() {
            // Detener timer
            clearInterval(timerInterval);
            
            // Mostrar mensaje de carga
            cerrarModalCancelacion();
            
            const loadingMsg = document.createElement('div');
            loadingMsg.style.cssText = `
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: rgba(0,0,0,0.9);
                color: white;
                padding: 30px 50px;
                border-radius: 15px;
                text-align: center;
                z-index: 10000;
                font-size: 1.2rem;
            `;
            loadingMsg.innerHTML = '⏳ Cancelando examen...';
            document.body.appendChild(loadingMsg);
            
            // Enviar cancelación
            fetch('simulacro_ajax.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    action: 'cancelar_examen',
                    examen_id: EXAMEN_ID,
                    sesion: SESION_ACTUAL
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadingMsg.innerHTML = '✓ Examen cancelado exitosamente';
                    
                    setTimeout(() => {
                        window.location.href = 'simulacro_inicio.php?cancelado=1';
                    }, 1500);
                } else {
                    loadingMsg.innerHTML = '❌ Error al cancelar: ' + (data.error || 'Desconocido');
                    setTimeout(() => {
                        document.body.removeChild(loadingMsg);
                        alert('Error al cancelar el examen. Por favor, intenta nuevamente.');
                    }, 2000);
                }
            })
            .catch(error => {
                loadingMsg.innerHTML = '❌ Error de conexión';
                setTimeout(() => {
                    document.body.removeChild(loadingMsg);
                    alert('Error de conexión. Por favor, verifica tu internet.');
                }, 2000);
            });
        }
    </script>
</body>
</html>