<?php
// views/home.php - Vista principal del sistema

function renderHomePage($data = []) {
    $resultados_calles = $data['resultados_calles'] ?? [];
    $resultados_servicios = $data['resultados_servicios'] ?? [];
    $resultados_codigos_postales = $data['resultados_codigos_postales'] ?? [];
    $busqueda_calle = $data['busqueda_calle'] ?? '';
    $busqueda_codigo_postal = $data['busqueda_codigo_postal'] ?? '';
    $tipo_servicio = $data['tipo_servicio'] ?? '';
    $colonia_servicio = $data['colonia_servicio'] ?? '';
    $tipos_servicios = $data['tipos_servicios'] ?? [];
    $estadisticas = $data['estadisticas'] ?? [];
    $error_message = $data['error_message'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo Config::get('APP_NAME', 'Sistema de Servicios Privados'); ?> - Chihuahua</title>
    <link rel="stylesheet" href="css/autocomplete.css">
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
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 1.2em;
            opacity: 0.9;
        }
        
        .content {
            padding: 30px;
        }
        
        .search-section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .search-section h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 1.5em;
        }
        
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #34495e;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .results {
            margin-top: 30px;
        }
        
        .results h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 1.3em;
        }
        
        .result-item {
            background: white;
            border: 1px solid #e1e8ed;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .result-item h4 {
            color: #2c3e50;
            margin-bottom: 8px;
        }
        
        .result-item p {
            color: #7f8c8d;
            margin-bottom: 5px;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        
        .stat-card h3 {
            font-size: 2em;
            margin-bottom: 10px;
        }
        
        .stat-card p {
            opacity: 0.9;
        }
        
        .no-results {
            text-align: center;
            color: #7f8c8d;
            font-style: italic;
            padding: 40px;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🗺️ <?php echo Config::get('APP_NAME', 'Sistema de Servicios Privados'); ?></h1>
            <p>Chihuahua - Búsqueda de Calles y Servicios</p>
        </div>
        
        <div class="content">
            <?php if ($error_message): ?>
                <div class="error-message">
                    <strong>Error:</strong> <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <!-- Búsqueda de Calles -->
            <div class="search-section">
                <h2>🔍 Buscar Calles</h2>
                <form method="GET">
                    <div class="form-group">
                        <label for="buscar_calle">Nombre de la calle:</label>
                        <input type="text" id="buscar-calles" name="buscar_calle" 
                               value="<?php echo htmlspecialchars($busqueda_calle); ?>" 
                               placeholder="Ej: AVENIDA, CENTRO, REFORMA..." autocomplete="off">
                    </div>
                    <button type="submit" class="btn">Buscar Calles</button>
                </form>
            </div>
            
            <!-- Búsqueda de Códigos Postales -->
            <div class="search-section">
                <h2>📮 Buscar Códigos Postales</h2>
                <form method="GET">
                    <div class="form-group">
                        <label for="buscar-codigo-postal">Código postal o asentamiento:</label>
                        <input type="text" id="buscar-codigo-postal" name="buscar_codigo_postal" 
                               value="<?php echo htmlspecialchars($data['busqueda_codigo_postal'] ?? ''); ?>" 
                               placeholder="Ej: 31000, CENTRO, Chihuahua..." autocomplete="off">
                    </div>
                    <button type="submit" name="buscar_cp" class="btn">Buscar Código Postal</button>
                </form>
            </div>
            
            <!-- Búsqueda de Servicios -->
            <div class="search-section">
                <h2>🏥 Buscar Servicios</h2>
                <form method="GET">
                    <div class="form-group">
                        <label for="tipo_servicio">Tipo de servicio:</label>
                        <select id="tipo_servicio" name="tipo_servicio">
                            <option value="">Selecciona un servicio</option>
                            <?php foreach ($tipos_servicios as $tipo): ?>
                                <option value="<?php echo $tipo; ?>" 
                                        <?php echo ($tipo_servicio == $tipo) ? 'selected' : ''; ?>>
                                    <?php echo ucfirst(str_replace('_', ' ', $tipo)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="colonia_servicio">Colonia (opcional):</label>
                        <input type="text" id="buscar-servicios" name="colonia_servicio" 
                               value="<?php echo htmlspecialchars($colonia_servicio); ?>" 
                               placeholder="Ej: CENTRO, NORTE..." autocomplete="off">
                    </div>
                    <button type="submit" name="buscar_servicio" class="btn">Buscar Servicios</button>
                </form>
            </div>
            
            <!-- Resultados de Calles -->
            <?php if (!empty($resultados_calles)): ?>
                <div class="results">
                    <h3>📍 Calles Encontradas (<?php echo count($resultados_calles); ?>)</h3>
                    <?php foreach ($resultados_calles as $calle): ?>
                        <div class="result-item">
                            <h4><?php echo htmlspecialchars($calle['nombre']); ?></h4>
                            <p><strong>Tipo:</strong> <?php echo htmlspecialchars($calle['t_vial2']); ?></p>
                            <p><strong>Clasificación:</strong> <?php echo htmlspecialchars($calle['cla_calle']); ?></p>
                            <?php if ($calle['v_ppal'] == 'SI'): ?>
                                <p><strong>Vía Principal:</strong> ✅ Sí</p>
                            <?php endif; ?>
                            <?php if ($calle['calle1'] || $calle['calle2']): ?>
                                <p><strong>Intersecciones:</strong> 
                                   <?php echo htmlspecialchars($calle['calle1'] . ' - ' . $calle['calle2']); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php elseif ($busqueda_calle): ?>
                <div class="no-results">
                    <p>No se encontraron calles con el término "<?php echo htmlspecialchars($busqueda_calle); ?>"</p>
                </div>
            <?php endif; ?>
            
            <!-- Resultados de Servicios -->
            <?php if (!empty($resultados_servicios)): ?>
                <div class="results">
                    <h3>🏥 Servicios Encontrados (<?php echo count($resultados_servicios); ?>)</h3>
                    <?php foreach ($resultados_servicios as $servicio): ?>
                        <div class="result-item">
                            <h4><?php echo htmlspecialchars($servicio['nombre'] ?? 'Sin nombre'); ?></h4>
                            <?php if (isset($servicio['direccion'])): ?>
                                <p><strong>Dirección:</strong> <?php echo htmlspecialchars($servicio['direccion']); ?></p>
                            <?php endif; ?>
                            <?php if (isset($servicio['colonia'])): ?>
                                <p><strong>Colonia:</strong> <?php echo htmlspecialchars($servicio['colonia']); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php elseif ($tipo_servicio): ?>
                <div class="no-results">
                    <p>No se encontraron servicios de tipo "<?php echo htmlspecialchars($tipo_servicio); ?>"</p>
                </div>
            <?php endif; ?>
            
            <!-- Resultados de Códigos Postales -->
            <?php if (!empty($resultados_codigos_postales)): ?>
                <div class="results">
                    <h3>📮 Códigos Postales Encontrados (<?php echo count($resultados_codigos_postales); ?>)</h3>
                    <?php foreach ($resultados_codigos_postales as $cp): ?>
                        <div class="result-item">
                            <h4><?php echo htmlspecialchars($cp['d_codigo'] . ' - ' . $cp['d_asenta']); ?></h4>
                            <p><strong>Municipio:</strong> <?php echo htmlspecialchars($cp['D_mnpio']); ?></p>
                            <?php if (!empty($cp['d_tipo_asenta'])): ?>
                                <p><strong>Tipo:</strong> <?php echo htmlspecialchars($cp['d_tipo_asenta']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($cp['d_ciudad'])): ?>
                                <p><strong>Ciudad:</strong> <?php echo htmlspecialchars($cp['d_ciudad']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($cp['d_zona'])): ?>
                                <p><strong>Zona:</strong> <?php echo htmlspecialchars($cp['d_zona']); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php elseif ($busqueda_codigo_postal): ?>
                <div class="no-results">
                    <p>No se encontraron códigos postales con el término "<?php echo htmlspecialchars($busqueda_codigo_postal); ?>"</p>
                </div>
            <?php endif; ?>
            
            <!-- Estadísticas -->
            <?php if (!empty($estadisticas)): ?>
                <div class="stats">
                    <div class="stat-card">
                        <h3><?php echo $estadisticas['total_calles']; ?></h3>
                        <p>Total de Calles</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $estadisticas['calles_principales']; ?></h3>
                        <p>Calles Principales</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $estadisticas['tipos_servicios']; ?></h3>
                        <p>Tipos de Servicios</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $estadisticas['farmacias']; ?></h3>
                        <p>Farmacias</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="js/autocomplete.js"></script>
</body>
</html>
<?php
}
?>
