<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Evaluación Médica</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .main-container {
            max-width: 900px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .header-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .header-card h1 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .topic-badge {
            display: inline-block;
            background: var(--secondary-color);
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
        
        .progress-section {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .progress-bar {
            height: 10px;
            border-radius: 10px;
            transition: width 0.3s ease;
        }
        
        .question-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        
        .question-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        
        .question-number {
            display: inline-block;
            background: var(--secondary-color);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            text-align: center;
            line-height: 40px;
            font-weight: bold;
            margin-right: 1rem;
        }
        
        .question-text {
            font-size: 1.1rem;
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 1.5rem;
        }
        
        .alternative-option {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 0.75rem;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
        }
        
        .alternative-option:hover {
            background: #e9ecef;
            border-color: var(--secondary-color);
        }
        
        .alternative-option input[type="radio"] {
            margin-right: 1rem;
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        
        .alternative-option.selected {
            background: #e3f2fd;
            border-color: var(--secondary-color);
            border-width: 3px;
        }
        
        .alternative-option.correct {
            background: #d4edda;
            border-color: var(--success-color);
            border-width: 3px;
        }
        
        .alternative-option.incorrect {
            background: #f8d7da;
            border-color: var(--danger-color);
            border-width: 3px;
        }
        
        .option-letter {
            display: inline-block;
            width: 35px;
            height: 35px;
            background: var(--secondary-color);
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 35px;
            font-weight: bold;
            margin-right: 1rem;
            flex-shrink: 0;
        }
        
        .option-letter.correct-letter {
            background: var(--success-color);
        }
        
        .option-letter.incorrect-letter {
            background: var(--danger-color);
        }
        
        .explanation-box {
            background: #fff3cd;
            border-left: 4px solid var(--warning-color);
            padding: 1rem;
            margin-top: 1rem;
            border-radius: 5px;
            display: none;
        }
        
        .explanation-box.show {
            display: block;
            animation: slideDown 0.3s ease;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .explanation-icon {
            color: var(--warning-color);
            margin-right: 0.5rem;
        }
        
        .submit-btn {
            background: var(--secondary-color);
            border: none;
            padding: 1rem 3rem;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
            transition: all 0.3s;
        }
        
        .submit-btn:hover {
            background: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(52, 152, 219, 0.4);
        }
        
        .results-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 2rem;
        }
        
        .score-circle {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2.5rem;
            font-weight: bold;
            color: white;
        }
        
        .score-excellent {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .score-good {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .score-average {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .score-poor {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-top: 1.5rem;
        }
        
        .stat-box {
            padding: 1rem;
            border-radius: 10px;
            text-align: center;
        }
        
        .stat-box i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .stat-correct {
            background: #d4edda;
            color: var(--success-color);
        }
        
        .stat-incorrect {
            background: #f8d7da;
            color: var(--danger-color);
        }
        
        .stat-unanswered {
            background: #fff3cd;
            color: var(--warning-color);
        }
        
        .reset-btn {
            background: #95a5a6;
            border: none;
            padding: 0.75rem 2rem;
            font-weight: 600;
            border-radius: 50px;
            margin-top: 1rem;
        }
        
        .reset-btn:hover {
            background: #7f8c8d;
        }
    </style>
</head>
<body>
    <?php
    // Leer archivos JSON
    $preguntas_file = 'preguntas.json';
    $respuestas_file = 'respuestas.json';
    
    if (!file_exists($preguntas_file) || !file_exists($respuestas_file)) {
        die('<div class="alert alert-danger m-5">Error: No se encontraron los archivos JSON necesarios.</div>');
    }
    
    $preguntas_json = file_get_contents($preguntas_file);
    $respuestas_json = file_get_contents($respuestas_file);
    
    $datos_preguntas = json_decode($preguntas_json, true);
    $datos_respuestas = json_decode($respuestas_json, true);
    
    $mostrar_resultados = false;
    $respuestas_usuario = array();
    $puntaje = 0;
    $total_preguntas = count($datos_preguntas['questions']);
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $mostrar_resultados = true;
        
        foreach ($datos_preguntas['questions'] as $index => $pregunta) {
            $respuesta_usuario = isset($_POST['question_' . $index]) ? $_POST['question_' . $index] : null;
            $respuestas_usuario[$index] = $respuesta_usuario;
            
            $respuesta_correcta = $datos_respuestas['answers'][$index]['answer'];
            
            if ($respuesta_usuario === $respuesta_correcta) {
                $puntaje++;
            }
        }
        
        $porcentaje = round(($puntaje / $total_preguntas) * 100);
    }
    ?>
    
    <div class="main-container">
        <!-- Header -->
        <div class="header-card">
            <h1><i class="fas fa-stethoscope"></i> Sistema de Evaluación Médica</h1>
            <div class="topic-badge">
                <i class="fas fa-book-medical"></i> 
                <?php echo htmlspecialchars($datos_preguntas['topic']); ?>
            </div>
            <small class="d-block text-muted mt-2">Código: <?php echo htmlspecialchars($datos_preguntas['code']); ?></small>
        </div>
        
        <?php if ($mostrar_resultados): ?>
            <!-- Resultados -->
            <div class="results-card">
                <?php
                $clase_score = 'score-poor';
                $mensaje = 'Sigue practicando';
                $icono = 'fa-sad-tear';
                
                if ($porcentaje >= 90) {
                    $clase_score = 'score-excellent';
                    $mensaje = '¡Excelente trabajo!';
                    $icono = 'fa-trophy';
                } elseif ($porcentaje >= 70) {
                    $clase_score = 'score-good';
                    $mensaje = '¡Buen trabajo!';
                    $icono = 'fa-smile';
                } elseif ($porcentaje >= 50) {
                    $clase_score = 'score-average';
                    $mensaje = 'Puedes mejorar';
                    $icono = 'fa-meh';
                }
                ?>
                
                <div class="score-circle <?php echo $clase_score; ?>">
                    <?php echo $porcentaje; ?>%
                </div>
                
                <h2><i class="fas <?php echo $icono; ?>"></i> <?php echo $mensaje; ?></h2>
                
                <div class="stats-grid">
                    <div class="stat-box stat-correct">
                        <i class="fas fa-check-circle"></i>
                        <div><strong><?php echo $puntaje; ?></strong></div>
                        <small>Correctas</small>
                    </div>
                    <div class="stat-box stat-incorrect">
                        <i class="fas fa-times-circle"></i>
                        <div><strong><?php echo ($total_preguntas - $puntaje); ?></strong></div>
                        <small>Incorrectas</small>
                    </div>
                    <div class="stat-box stat-unanswered">
                        <i class="fas fa-list-ol"></i>
                        <div><strong><?php echo $total_preguntas; ?></strong></div>
                        <small>Total</small>
                    </div>
                </div>
                
                <button class="btn btn-primary reset-btn" onclick="location.reload()">
                    <i class="fas fa-redo"></i> Intentar de nuevo
                </button>
            </div>
        <?php else: ?>
            <!-- Barra de progreso -->
            <div class="progress-section">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span><i class="fas fa-tasks"></i> Progreso del examen</span>
                    <span id="progress-text">0 de <?php echo $total_preguntas; ?> respondidas</span>
                </div>
                <div class="progress">
                    <div id="progress-bar" class="progress-bar bg-success" role="progressbar" style="width: 0%"></div>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Formulario de preguntas -->
        <form method="post" id="exam-form">
            <?php foreach ($datos_preguntas['questions'] as $index => $pregunta): ?>
                <div class="question-card">
                    <div class="d-flex align-items-start mb-3">
                        <span class="question-number"><?php echo $pregunta['question']; ?></span>
                        <div class="question-text flex-grow-1">
                            <?php echo isset($pregunta['question_text']) ? htmlspecialchars($pregunta['question_text']) : 'Pregunta ' . $pregunta['question']; ?>
                        </div>
                    </div>
                    
                    <div class="alternatives">
                        <?php foreach ($pregunta['alternatives'] as $alternativa): ?>
                            <?php foreach ($alternativa as $opcion => $texto): ?>
                                <?php
                                $es_seleccionada = $mostrar_resultados && isset($respuestas_usuario[$index]) && $respuestas_usuario[$index] === $opcion;
                                $respuesta_correcta = $datos_respuestas['answers'][$index]['answer'];
                                $es_correcta = $mostrar_resultados && $opcion === $respuesta_correcta;
                                $es_incorrecta = $mostrar_resultados && $es_seleccionada && $opcion !== $respuesta_correcta;
                                
                                $clase_alternativa = '';
                                $clase_letra = '';
                                
                                if ($es_correcta) {
                                    $clase_alternativa = 'correct';
                                    $clase_letra = 'correct-letter';
                                } elseif ($es_incorrecta) {
                                    $clase_alternativa = 'incorrect';
                                    $clase_letra = 'incorrect-letter';
                                } elseif ($es_seleccionada) {
                                    $clase_alternativa = 'selected';
                                }
                                ?>
                                
                                <label class="alternative-option <?php echo $clase_alternativa; ?>">
                                    <input type="radio" 
                                           name="question_<?php echo $index; ?>" 
                                           value="<?php echo $opcion; ?>"
                                           <?php echo $es_seleccionada ? 'checked' : ''; ?>
                                           <?php echo $mostrar_resultados ? 'disabled' : ''; ?>
                                           onchange="updateProgress()">
                                    <span class="option-letter <?php echo $clase_letra; ?>"><?php echo $opcion; ?></span>
                                    <span class="flex-grow-1"><?php echo htmlspecialchars($texto); ?></span>
                                    
                                    <?php if ($es_correcta): ?>
                                        <i class="fas fa-check-circle text-success ms-2"></i>
                                    <?php elseif ($es_incorrecta): ?>
                                        <i class="fas fa-times-circle text-danger ms-2"></i>
                                    <?php endif; ?>
                                </label>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if ($mostrar_resultados): ?>
                        <div class="explanation-box show">
                            <strong><i class="fas fa-lightbulb explanation-icon"></i> Explicación:</strong>
                            <p class="mb-0 mt-2"><?php echo htmlspecialchars($datos_respuestas['answers'][$index]['explanation']); ?></p>
                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="fas fa-check"></i> Respuesta correcta: 
                                    <strong><?php echo $respuesta_correcta; ?></strong> - 
                                    <?php echo htmlspecialchars($datos_respuestas['answers'][$index]['detail']); ?>
                                </small>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            
            <?php if (!$mostrar_resultados): ?>
                <div class="text-center mb-5">
                    <button type="submit" class="btn btn-primary submit-btn">
                        <i class="fas fa-paper-plane"></i> Enviar Respuestas
                    </button>
                </div>
            <?php endif; ?>
        </form>
    </div>
    
    <script>
        function updateProgress() {
            const total = <?php echo $total_preguntas; ?>;
            const answered = document.querySelectorAll('input[type="radio"]:checked').length;
            const percentage = (answered / total) * 100;
            
            document.getElementById('progress-bar').style.width = percentage + '%';
            document.getElementById('progress-text').textContent = answered + ' de ' + total + ' respondidas';
        }
        
        // Scroll suave al hacer clic en submit
        document.getElementById('exam-form')?.addEventListener('submit', function(e) {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>