<?php
// ============================================
// ACTIVAR ERRORES PARA DEBUG
// ============================================
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

session_start();

// Conexión a BD
$db_host = 'localhost';
$db_user = 'dpimeduchile_vquintana';     
$db_pass = 'Vq_09875213';              
$db_name = 'dpimeduchile_eunacom';

try {
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("<h1>Error de conexión</h1><pre>" . $e->getMessage() . "</pre>");
}

// Usuario de prueba (mientras no hay login)
$usuario_id = 1;

// Verificar si hay examen en curso
$sql = "
    SELECT * FROM examenes 
    WHERE usuario_id = ? 
    AND estado IN ('en_curso', 'sesion1_completa')
    ORDER BY id DESC 
    LIMIT 1
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$usuario_id]);
$examen_en_curso = $stmt->fetch(PDO::FETCH_ASSOC);

// Procesar inicio de nuevo simulacro
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['iniciar_simulacro'])) {
    
    echo "<h2>?? DEBUG: Iniciando simulacro...</h2>";
    
    // Verificar que el archivo existe
    if (!file_exists('classes/SimulacroGenerator.php')) {
        die("<h3 style='color:red;'>? ERROR: No se encuentra el archivo classes/SimulacroGenerator.php</h3>");
    }
    
    require_once 'classes/SimulacroGeneratorFlex.php';
    
    echo "<p>? Archivo SimulacroGenerator.php cargado</p>";
    
    try {
        $generator = new SimulacroGenerator($pdo);
        echo "<p>? Clase SimulacroGenerator instanciada</p>";
        
        echo "<p>?? Generando simulacro para usuario_id: $usuario_id...</p>";
        
        $resultado = $generator->generarSimulacro($usuario_id);
        
        echo "<h3>?? Resultado:</h3>";
        echo "<pre>";
        print_r($resultado);
        echo "</pre>";
        
        if ($resultado['success']) {
            echo "<p style='color:green;'>? ?Simulacro generado exitosamente!</p>";
            echo "<p>Redirigiendo en 3 segundos...</p>";
            echo "<script>setTimeout(() => { window.location.href = 'simulacro_examen.php?examen=" . $resultado['codigo_examen'] . "'; }, 3000);</script>";
            exit;
        } else {
            echo "<h3 style='color:red;'>? ERROR al generar simulacro:</h3>";
            echo "<pre style='background:#ffebee;padding:15px;border-radius:5px;'>";
            echo htmlspecialchars($resultado['error']);
            echo "</pre>";
            
            // Mostrar trace si existe
            if (isset($resultado['trace'])) {
                echo "<h4>Stack Trace:</h4>";
                echo "<pre style='background:#fff3cd;padding:15px;border-radius:5px;font-size:12px;'>";
                echo htmlspecialchars($resultado['trace']);
                echo "</pre>";
            }
        }
        
    } catch (Exception $e) {
        echo "<h3 style='color:red;'>? EXCEPCIóN CAPTURADA:</h3>";
        echo "<pre style='background:#ffebee;padding:15px;border-radius:5px;'>";
        echo "Mensaje: " . htmlspecialchars($e->getMessage()) . "\n\n";
        echo "Archivo: " . $e->getFile() . "\n";
        echo "Línea: " . $e->getLine() . "\n\n";
        echo "Trace:\n" . $e->getTraceAsString();
        echo "</pre>";
    }
    
    echo "<hr><p><a href='simulacro_inicio.php'>← Volver a intentar</a></p>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulacro EUNACOM</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .container {
            max-width: 800px;
            width: 100%;
        }
        
        .card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        
        h1 {
            color: #2c3e50;
            font-size: 2.5rem;
            margin-bottom: 10px;
            text-align: center;
        }
        
        .subtitle {
            color: #7f8c8d;
            text-align: center;
            margin-bottom: 30px;
            font-size: 1.1rem;
        }
        
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .info-box h3 {
            color: #1976d2;
            margin-bottom: 15px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .info-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        
        .info-number {
            font-size: 2rem;
            font-weight: bold;
            color: #2196f3;
        }
        
        .info-label {
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        
        .warning-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .warning-box h3 {
            color: #856404;
            margin-bottom: 15px;
        }
        
        .warning-box ul {
            margin-left: 20px;
            color: #856404;
        }
        
        .warning-box li {
            margin-bottom: 8px;
        }
        
        .btn {
            background: #2196f3;
            color: white;
            border: none;
            padding: 18px 40px;
            font-size: 1.2rem;
            font-weight: 600;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
            margin-top: 20px;
        }
        
        .btn:hover {
            background: #1976d2;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(33, 150, 243, 0.4);
        }
        
        .btn-continuar {
            background: #ff9800;
        }
        
        .btn-continuar:hover {
            background: #f57c00;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>馃彞 Simulacro EUNACOM</h1>
            <p class="subtitle">Examen de Habilitaci贸n M茅dica</p>
            
            <?php if (isset($error)): ?>
                <div class="error">
                    <strong>Error:</strong> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($examen_en_curso): ?>
                <!-- Examen en curso -->
                <div class="warning-box">
                    <h3>鈿狅笍 Tienes un examen en curso</h3>
                    <p><strong>C贸digo:</strong> <?= $examen_en_curso['codigo_examen'] ?></p>
                    <p><strong>Estado:</strong> <?= $examen_en_curso['estado'] === 'en_curso' ? 'Sesi贸n 1' : 'Sesi贸n 2' ?></p>
                </div>
                
                <form method="GET" action="simulacro_examen.php">
                    <input type="hidden" name="examen" value="<?= $examen_en_curso['codigo_examen']?>">
                    <button type="submit" class="btn btn-continuar">
                        鈻讹笍 Continuar Examen
                    </button>
                </form>
            <?php else: ?>
                <!-- Informaci贸n del simulacro -->
                <div class="info-box">
                    <h3>馃搵 Caracter铆sticas del Simulacro</h3>
                    
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-number">180</div>
                            <div class="info-label">Preguntas</div>
                        </div>
                        <div class="info-item">
                            <div class="info-number">2</div>
                            <div class="info-label">Sesiones</div>
                        </div>
                        <div class="info-item">
                            <div class="info-number">90</div>
                            <div class="info-label">Min/Sesi贸n</div>
                        </div>
                        <div class="info-item">
                            <div class="info-number">3h</div>
                            <div class="info-label">Tiempo Total</div>
                        </div>
                    </div>
                </div>
                
                <div class="warning-box">
                    <h3>鈿狅笍 Instrucciones Importantes</h3>
                    <ul>
                        <li>El simulacro consta de <strong>2 sesiones de 90 minutos</strong> cada una</li>
                        <li>Cada sesi贸n tiene <strong>90 preguntas</strong></li>
                        <li>Una vez iniciado, el <strong>timer no se puede detener</strong></li>
                        <li>Puedes cerrar el navegador, <strong>tu progreso se guarda</strong></li>
                        <li>Al finalizar el tiempo, <strong>se env铆a autom谩ticamente</strong></li>
                        <li>No podr谩s retroceder a la sesi贸n 1 una vez iniciada la sesi贸n 2</li>
                    </ul>
                </div>
                
                <form method="POST">
                    <button type="submit" name="iniciar_simulacro" class="btn">
                        馃殌 Iniciar Simulacro
                    </button>
                </form>
            <?php endif; ?>
            
            <p style="text-align: center; margin-top: 20px;">
                <a href="entrenamiento.php" style="color: #2196f3; text-decoration: none;">
                    鈫?Volver a Entrenamiento
                </a>
            </p>
        </div>
    </div>
</body>
</html>