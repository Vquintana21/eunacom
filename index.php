<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Evaluación Médica - Selector de Temas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --success: #27ae60;
            --danger: #e74c3c;
            --warning: #f39c12;
            --info: #16a085;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 2rem 0;
        }
        
        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        
        .header-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .header-card h1 {
            color: var(--primary);
            font-weight: 700;
            margin-bottom: 0.5rem;
            font-size: 2.5rem;
        }
        
        .header-card p {
            color: #7f8c8d;
            font-size: 1.1rem;
        }
        
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1.5rem;
        }
        
        .stat-card {
            background: linear-gradient(135deg, var(--secondary), var(--info));
            color: white;
            padding: 1.5rem;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .stat-card i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .stat-card h3 {
            font-size: 2rem;
            margin: 0;
            font-weight: bold;
        }
        
        .stat-card p {
            margin: 0;
            opacity: 0.9;
        }
        
        .search-box {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .search-input {
            border: 2px solid #e0e0e0;
            border-radius: 50px;
            padding: 1rem 1.5rem;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .search-input:focus {
            border-color: var(--secondary);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        
        .categoria-card {
            background: white;
            border-radius: 20px;
            padding: 0;
            margin-bottom: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .categoria-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .categoria-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 1.5rem 2rem;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s;
        }
        
        .categoria-header:hover {
            background: linear-gradient(135deg, #34495e, #2980b9);
        }
        
        .categoria-header h3 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .categoria-badge {
            background: rgba(255,255,255,0.2);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .categoria-body {
            padding: 2rem;
            display: none;
        }
        
        .categoria-body.show {
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
        
        .subcategoria-section {
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 2px solid #ecf0f1;
        }
        
        .subcategoria-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .subcategoria-title {
            color: var(--primary);
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .subcategoria-title i {
            color: var(--secondary);
        }
        
        .temas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1rem;
        }
        
        .tema-card {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 1.25rem;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
        }
        
        .tema-card:hover {
            background: white;
            border-color: var(--secondary);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.2);
        }
        
        .tema-codigo {
            position: absolute;
            top: 0.75rem;
            right: 0.75rem;
            background: var(--secondary);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .tema-nombre {
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 0.75rem;
            padding-right: 5rem;
            font-size: 0.95rem;
            line-height: 1.4;
        }
        
        .tema-info {
            display: flex;
            gap: 1rem;
            font-size: 0.85rem;
            color: #7f8c8d;
        }
        
        .tema-info span {
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }
        
        .tipo-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .tipo-cronico { background: #d4edda; color: #155724; }
        .tipo-urgencia { background: #f8d7da; color: #721c24; }
        .tipo-prevencion { background: #d1ecf1; color: #0c5460; }
        .tipo-examenes { background: #fff3cd; color: #856404; }
        .tipo-procedimientos { background: #e2e3e5; color: #383d41; }
        
        .collapse-icon {
            transition: transform 0.3s;
        }
        
        .collapsed .collapse-icon {
            transform: rotate(-90deg);
        }
        
        .no-results {
            text-align: center;
            padding: 3rem;
            color: #7f8c8d;
        }
        
        .no-results i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
    </style>
</head>
<body>
    <?php
    // Cargar el índice JSON
    $index_file = '_json_output/index.json';
    
    if (!file_exists($index_file)) {
        die('<div class="alert alert-danger m-5">Error: No se encontró el archivo index.json. Por favor, ejecuta el script de conversión primero.</div>');
    }
    
    $index_json = file_get_contents($index_file);
    $index_data = json_decode($index_json, true);
    
    if (!$index_data) {
        die('<div class="alert alert-danger m-5">Error: No se pudo cargar el índice de temas.</div>');
    }
    
    $estadisticas = $index_data['estadisticas'];
    $categorias = $index_data['categorias'];
    ?>
    
    <div class="main-container">
        <!-- Header -->
        <div class="header-card">
            <h1><i class="fas fa-graduation-cap"></i> Sistema de Evaluación Médica</h1>
            <p>Selecciona un tema para comenzar tu práctica</p>
            
            <div class="stats-row">
                <div class="stat-card">
                    <i class="fas fa-folder"></i>
                    <h3><?php echo $estadisticas['total_categorias']; ?></h3>
                    <h6>Categorías</h6>
                </div>
                <div class="stat-card">
                    <i class="fas fa-layer-group"></i>
                    <h3><?php echo $estadisticas['total_subcategorias']; ?></h3>
                    <h6>Subcategorías</h6>
                </div>
                <div class="stat-card">
                    <i class="fas fa-book-medical"></i>
                    <h3><?php echo $estadisticas['total_temas']; ?></h3>
                    <h6>Temas</h6>
                </div>
                <div class="stat-card">
                    <i class="fas fa-question-circle"></i>
                    <h3><?php echo $estadisticas['total_preguntas']; ?></h3>
                    <h6>Preguntas</h6>
                </div>
            </div>
        </div>
        
        <!-- Búsqueda -->
        <div class="search-box">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0" style="border-radius: 50px 0 0 50px; border: 2px solid #e0e0e0; border-right: none;">
                    <i class="fas fa-search text-muted"></i>
                </span>
                <input type="text" class="form-control search-input border-start-0 ps-0" id="searchInput" placeholder="Buscar por código o tema... (ej: 1.01.1.013 o fibrilación)">
            </div>
        </div>
        
        <!-- Categorías -->
        <div id="categoriasContainer">
            <?php foreach ($categorias as $categoria): ?>
                <div class="categoria-card" data-categoria="<?php echo strtolower($categoria['nombre']); ?>">
                    <div class="categoria-header collapsed" onclick="toggleCategoria(this)">
                        <div>
                            <h3>
                                <i class="fas fa-chevron-down collapse-icon"></i>
                                <?php echo htmlspecialchars($categoria['nombre']); ?>
                            </h3>
                        </div>
                        <div class="categoria-badge">
                            <?php echo $categoria['total_subcategorias']; ?> subcategorías • 
                            <?php 
                            $total_temas_cat = 0;
                            foreach ($categoria['subcategorias'] as $sub) {
                                $total_temas_cat += $sub['total_temas'];
                            }
                            echo $total_temas_cat;
                            ?> temas
                        </div>
                    </div>
                    
                    <div class="categoria-body">
                        <?php foreach ($categoria['subcategorias'] as $subcategoria): ?>
                            <div class="subcategoria-section" data-subcategoria="<?php echo strtolower($subcategoria['nombre']); ?>">
                                <div class="subcategoria-title">
                                    <i class="fas fa-folder-open"></i>
                                    <?php echo htmlspecialchars($subcategoria['nombre']); ?>
                                    <span class="badge bg-secondary"><?php echo $subcategoria['total_temas']; ?> temas</span>
                                </div>
                                
                                <div class="temas-grid">
                                    <?php foreach ($subcategoria['temas'] as $tema): ?>
                                        <div class="tema-card" 
                                             data-tema="<?php echo strtolower((isset($tema['codigo']) ? $tema['codigo'] : '') . ' ' . (isset($tema['nombre']) ? $tema['nombre'] : '')); ?>"

                                             onclick="seleccionarTema('<?php echo htmlspecialchars($tema['ruta_json'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($tema['codigo'], ENT_QUOTES); ?>')">
                                            <div class="tema-codigo"><?php echo htmlspecialchars($tema['codigo']); ?></div>
                                            <div class="tema-nombre">
                                                <?php 
                                                // Extraer el nombre del tema del código si existe
                                                $nombre_tema = '';
                                                if (isset($tema['nombre'])) {
                                                    $nombre_tema = $tema['nombre'];
                                                } else {
                                                    // Buscar en la ruta del JSON
                                                    $json_path = '_json_output/' . $tema['ruta_json'];
                                                    if (file_exists($json_path)) {
                                                        $tema_data = json_decode(file_get_contents($json_path), true);
                                                        if (isset($tema_data['preguntas'][0]['texto'])) {
                                                            // Usar la primera pregunta como referencia del tema
                                                            $nombre_tema = substr($tema_data['preguntas'][0]['texto'], 0, 80) . '...';
                                                        }
                                                    }
                                                }
                                                
                                                if (empty($nombre_tema)) {
                                                    $nombre_tema = 'Ver tema ' . $tema['codigo'];
                                                }
                                                
                                                echo htmlspecialchars($nombre_tema);
                                                ?>
                                            </div>
                                            <div class="tema-info">
                                                <span>
                                                    <i class="fas fa-question-circle"></i>
                                                    <?php echo $tema['total_preguntas']; ?> preguntas
                                                </span>
                                                <span class="tipo-badge tipo-<?php echo strtolower(str_replace(['á','é','í','ó','ú'], ['a','e','i','o','u'], $tema['tipo'])); ?>">
                                                    <?php echo htmlspecialchars($tema['tipo']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div id="noResults" class="no-results" style="display: none;">
            <i class="fas fa-search"></i>
            <h3>No se encontraron resultados</h3>
            <p>Intenta con otros términos de búsqueda</p>
        </div>
    </div>
    
    <script>
        function toggleCategoria(element) {
            const body = element.nextElementSibling;
            const header = element;
            
            header.classList.toggle('collapsed');
            body.classList.toggle('show');
        }
        
        function seleccionarTema(rutaJson, codigo) {
            // Redirigir a la página del examen con el código del tema
            window.location.href = 'examen.php?tema=' + encodeURIComponent(codigo);
        }
        
        // Búsqueda en tiempo real
        const searchInput = document.getElementById('searchInput');
        const categoriasContainer = document.getElementById('categoriasContainer');
        const noResults = document.getElementById('noResults');
        
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            
            if (searchTerm === '') {
                // Mostrar todo
                document.querySelectorAll('.categoria-card').forEach(card => {
                    card.style.display = 'block';
                    card.querySelector('.categoria-body').classList.remove('show');
                    card.querySelector('.categoria-header').classList.add('collapsed');
                });
                document.querySelectorAll('.subcategoria-section').forEach(section => {
                    section.style.display = 'block';
                });
                document.querySelectorAll('.tema-card').forEach(card => {
                    card.style.display = 'block';
                });
                noResults.style.display = 'none';
                return;
            }
            
            let hasResults = false;
            
            // Buscar en cada categoría
            document.querySelectorAll('.categoria-card').forEach(categoriaCard => {
                let categoriaHasResults = false;
                
                // Buscar en cada subcategoría
                categoriaCard.querySelectorAll('.subcategoria-section').forEach(subcatSection => {
                    let subcatHasResults = false;
                    
                    // Buscar en cada tema
                    subcatSection.querySelectorAll('.tema-card').forEach(temaCard => {
                        const temaText = temaCard.dataset.tema;
                        
                        if (temaText.includes(searchTerm)) {
                            temaCard.style.display = 'block';
                            subcatHasResults = true;
                            hasResults = true;
                        } else {
                            temaCard.style.display = 'none';
                        }
                    });
                    
                    // Mostrar/ocultar subcategoría
                    if (subcatHasResults) {
                        subcatSection.style.display = 'block';
                        categoriaHasResults = true;
                    } else {
                        subcatSection.style.display = 'none';
                    }
                });
                
                // Mostrar/ocultar categoría
                if (categoriaHasResults) {
                    categoriaCard.style.display = 'block';
                    categoriaCard.querySelector('.categoria-body').classList.add('show');
                    categoriaCard.querySelector('.categoria-header').classList.remove('collapsed');
                } else {
                    categoriaCard.style.display = 'none';
                }
            });
            
            // Mostrar mensaje si no hay resultados
            noResults.style.display = hasResults ? 'none' : 'block';
            categoriasContainer.style.display = hasResults ? 'block' : 'none';
        });
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>