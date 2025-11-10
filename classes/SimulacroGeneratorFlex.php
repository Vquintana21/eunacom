<?php
/**
 * Generador de Simulacros EUNACOM - VERSIÓN DINÁMICA
 * Se adapta automáticamente a las preguntas disponibles
 */

class SimulacroGenerator {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Genera un nuevo simulacro completo
     */
    public function generarSimulacro($usuario_id) {
        try {
            $this->pdo->beginTransaction();
            
            echo "<p>1️⃣ Iniciando transacción...</p>";
            
            // 1. Crear registro de examen
            $codigo_examen = $this->generarCodigoUnico();
            echo "<p>2️⃣ Código generado: <strong>$codigo_examen</strong></p>";
            
            $sql = "
                INSERT INTO examenes (usuario_id, codigo_examen, estado, sesion_actual)
                VALUES (?, ?, 'en_curso', 1)
            ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$usuario_id, $codigo_examen]);
            $examen_id = $this->pdo->lastInsertId();
            
            echo "<p>3️⃣ Examen creado con ID: <strong>$examen_id</strong></p>";
            
            // 2. Obtener preguntas disponibles y seleccionar 180
            echo "<p>4️⃣ Analizando preguntas disponibles...</p>";
            $preguntas_seleccionadas = $this->seleccionarPreguntasDinamicas();
            echo "<p>✅ Se seleccionaron <strong>" . count($preguntas_seleccionadas) . " preguntas</strong></p>";
            
            // 3. Mezclar aleatoriamente
            echo "<p>5️⃣ Mezclando preguntas aleatoriamente...</p>";
            shuffle($preguntas_seleccionadas);
            
            // 4. Dividir en 2 sesiones (90 + 90)
            echo "<p>6️⃣ Dividiendo en 2 sesiones...</p>";
            $sesion1 = array_slice($preguntas_seleccionadas, 0, 90);
            $sesion2 = array_slice($preguntas_seleccionadas, 90, 90);
            echo "<p>✅ Sesión 1: " . count($sesion1) . " preguntas | Sesión 2: " . count($sesion2) . " preguntas</p>";
            
            // 5. Insertar preguntas del examen
            echo "<p>7️⃣ Insertando preguntas en examen_preguntas...</p>";
            $this->insertarPreguntasExamen($examen_id, $sesion1, 1);
            echo "<p>✅ Sesión 1 insertada</p>";
            $this->insertarPreguntasExamen($examen_id, $sesion2, 2);
            echo "<p>✅ Sesión 2 insertada</p>";
            
            // 6. Crear registros de respuestas vacías
            echo "<p>8️⃣ Creando registros de respuestas vacías...</p>";
            $this->crearRespuestasVacias($examen_id, $preguntas_seleccionadas);
            echo "<p>✅ Respuestas inicializadas</p>";
            
            echo "<p>9️⃣ Confirmando transacción...</p>";
            $this->pdo->commit();
            echo "<p>✅ ¡Transacción completada!</p>";
            
            return [
                'success' => true,
                'examen_id' => $examen_id,
                'codigo_examen' => $codigo_examen
            ];
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];
        }
    }
    
    /**
     * Selecciona 180 preguntas de forma dinámica según disponibilidad
     */
    private function seleccionarPreguntasDinamicas() {
        // 1. Obtener total de preguntas disponibles por área
        $sql = "
            SELECT 
                a.id as area_id,
                a.nombre as area_nombre,
                COUNT(p.id) as total_disponibles
            FROM areas a
            INNER JOIN especialidades e ON a.id = e.area_id
            INNER JOIN temas t ON e.id = t.especialidad_id
            INNER JOIN preguntas p ON t.id = p.tema_id
            GROUP BY a.id, a.nombre
            HAVING total_disponibles > 0
            ORDER BY a.id
        ";
        
        $areas_disponibles = $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($areas_disponibles)) {
            throw new Exception("No hay preguntas disponibles en la base de datos");
        }
        
        // 2. Calcular total disponible
        $total_disponible = array_sum(array_column($areas_disponibles, 'total_disponibles'));
        
        if ($total_disponible < 180) {
            throw new Exception(
                "No hay suficientes preguntas para generar un simulacro. " .
                "Se necesitan 180, solo hay {$total_disponible} disponibles."
            );
        }
        
        // 3. Calcular distribución proporcional
        $distribucion = [];
        foreach ($areas_disponibles as $area) {
            $proporcion = $area['total_disponibles'] / $total_disponible;
            $cantidad = round(180 * $proporcion);
            
            // Asegurar que no pedimos más de las disponibles
            $cantidad = min($cantidad, $area['total_disponibles']);
            
            $distribucion[$area['area_id']] = [
                'nombre' => $area['area_nombre'],
                'disponibles' => $area['total_disponibles'],
                'cantidad' => $cantidad
            ];
        }
        
        // 4. Ajustar para que sumen exactamente 180
        $suma_actual = array_sum(array_column($distribucion, 'cantidad'));
        
        if ($suma_actual < 180) {
            // Agregar preguntas extras a las áreas con más disponibles
            $diferencia = 180 - $suma_actual;
            foreach ($distribucion as $area_id => &$config) {
                if ($diferencia <= 0) break;
                
                $puede_agregar = $config['disponibles'] - $config['cantidad'];
                if ($puede_agregar > 0) {
                    $agregar = min($diferencia, $puede_agregar);
                    $config['cantidad'] += $agregar;
                    $diferencia -= $agregar;
                }
            }
        } elseif ($suma_actual > 180) {
            // Quitar preguntas extras
            $diferencia = $suma_actual - 180;
            foreach ($distribucion as $area_id => &$config) {
                if ($diferencia <= 0) break;
                
                $puede_quitar = max(0, $config['cantidad'] - 1);
                if ($puede_quitar > 0) {
                    $quitar = min($diferencia, $puede_quitar);
                    $config['cantidad'] -= $quitar;
                    $diferencia -= $quitar;
                }
            }
        }
        
        // 5. Mostrar tabla de distribución
        echo "<table border='1' style='margin:10px 0;border-collapse:collapse;width:100%;'>";
        echo "<tr style='background:#3498db;color:white;'>
                <th style='padding:8px;'>Área</th>
                <th style='padding:8px;'>Disponibles</th>
                <th style='padding:8px;'>Seleccionadas</th>
                <th style='padding:8px;'>%</th>
              </tr>";
        
        $preguntas_totales = [];
        
        foreach ($distribucion as $area_id => $config) {
            $porcentaje = round(($config['cantidad'] / 180) * 100, 1);
            
            echo "<tr style='background:#d4edda;'>
                    <td style='padding:8px;'><strong>{$config['nombre']}</strong></td>
                    <td style='padding:8px;text-align:center;'>{$config['disponibles']}</td>
                    <td style='padding:8px;text-align:center;'>{$config['cantidad']}</td>
                    <td style='padding:8px;text-align:center;'>{$porcentaje}%</td>
                  </tr>";
            
            // 6. Seleccionar preguntas del área
            $sql = "
                SELECT p.id 
                FROM preguntas p
                INNER JOIN temas t ON p.tema_id = t.id
                INNER JOIN especialidades e ON t.especialidad_id = e.id
                WHERE e.area_id = ?
                ORDER BY RAND()
                LIMIT {$config['cantidad']}
            ";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$area_id]);
            $preguntas = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $preguntas_totales = array_merge($preguntas_totales, $preguntas);
        }
        
        // Total
        $total_seleccionado = count($preguntas_totales);
        echo "<tr style='background:#3498db;color:white;font-weight:bold;'>
                <td style='padding:8px;'>TOTAL</td>
                <td style='padding:8px;text-align:center;'>{$total_disponible}</td>
                <td style='padding:8px;text-align:center;'>{$total_seleccionado}</td>
                <td style='padding:8px;text-align:center;'>100%</td>
              </tr>";
        echo "</table>";
        
        // Validar que tengamos exactamente 180
        if (count($preguntas_totales) !== 180) {
            throw new Exception(
                "Error: Se seleccionaron " . count($preguntas_totales) . " preguntas en lugar de 180"
            );
        }
        
        return $preguntas_totales;
    }
    
    /**
     * Inserta las preguntas en la tabla examen_preguntas
     */
    private function insertarPreguntasExamen($examen_id, $preguntas, $sesion) {
        $sql = "
            INSERT INTO examen_preguntas (examen_id, pregunta_id, sesion, orden)
            VALUES (?, ?, ?, ?)
        ";
        $stmt = $this->pdo->prepare($sql);
        
        foreach ($preguntas as $orden => $pregunta_id) {
            $stmt->execute([$examen_id, $pregunta_id, $sesion, $orden + 1]);
        }
    }
    
    /**
     * Crea registros vacíos de respuestas
     */
    private function crearRespuestasVacias($examen_id, $preguntas) {
        $sql = "
            INSERT INTO respuestas_usuario (examen_id, pregunta_id)
            VALUES (?, ?)
        ";
        $stmt = $this->pdo->prepare($sql);
        
        foreach ($preguntas as $pregunta_id) {
            $stmt->execute([$examen_id, $pregunta_id]);
        }
    }
    
    /**
     * Genera código único para el examen
     */
    private function generarCodigoUnico() {
        return 'SIM-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }
}