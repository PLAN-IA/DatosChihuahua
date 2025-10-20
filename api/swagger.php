<?php
// api/swagger.php - Swagger UI completo con todos los endpoints

require_once __DIR__ . '/../config/Config.php';
Config::load();

// Configurar headers para HTML
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Documentation - Sistema de Códigos Postales</title>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/swagger-ui-dist@5.9.0/swagger-ui.css" />
    <style>
        html {
            box-sizing: border-box;
            overflow: -moz-scrollbars-vertical;
            overflow-y: scroll;
        }
        *, *:before, *:after {
            box-sizing: inherit;
        }
        body {
            margin:0;
            background: #fafafa;
        }
    </style>
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="https://unpkg.com/swagger-ui-dist@5.9.0/swagger-ui-bundle.js"></script>
    <script src="https://unpkg.com/swagger-ui-dist@5.9.0/swagger-ui-standalone-preset.js"></script>
    <script>
        window.onload = function() {
            // Especificación OpenAPI completa
            const spec = {
                "openapi": "3.0.0",
                "info": {
                    "title": "Sistema de Códigos Postales - Chihuahua",
                    "description": "API completa para búsqueda, consulta, análisis y monitoreo de códigos postales del estado de Chihuahua, México",
                    "version": "2.0.0"
                },
                "servers": [
                    {
                        "url": "http://localhost:8000",
                        "description": "Servidor de desarrollo local"
                    }
                ],
                "paths": {
                    "/api/autocomplete.php": {
                        "get": {
                            "summary": "Autocompletado de códigos postales",
                            "description": "Proporciona sugerencias de autocompletado para búsqueda de códigos postales",
                            "tags": ["Autocompletado"],
                            "parameters": [
                                {
                                    "name": "q",
                                    "in": "query",
                                    "required": true,
                                    "description": "Término de búsqueda (mínimo 2 caracteres)",
                                    "schema": {"type": "string", "minLength": 2, "example": "31000"}
                                },
                                {
                                    "name": "type",
                                    "in": "query",
                                    "required": false,
                                    "description": "Tipo de búsqueda",
                                    "schema": {"type": "string", "enum": ["codigos_postales"], "default": "codigos_postales"}
                                },
                                {
                                    "name": "limit",
                                    "in": "query",
                                    "required": false,
                                    "description": "Número máximo de resultados",
                                    "schema": {"type": "integer", "minimum": 1, "maximum": 1000, "default": 50}
                                }
                            ],
                            "responses": {
                                "200": {
                                    "description": "Sugerencias de autocompletado",
                                    "content": {
                                        "application/json": {
                                            "schema": {
                                                "type": "object",
                                                "properties": {
                                                    "suggestions": {"type": "array"},
                                                    "query": {"type": "string"},
                                                    "total": {"type": "integer"}
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    },
                    "/api/search/cp.php": {
                        "get": {
                            "summary": "Buscar códigos postales",
                            "description": "Buscar códigos postales exactos o parciales",
                            "tags": ["Búsqueda"],
                            "parameters": [
                                {
                                    "name": "query",
                                    "in": "query",
                                    "required": true,
                                    "description": "Término de búsqueda",
                                    "schema": {"type": "string", "example": "31000"}
                                },
                                {
                                    "name": "limit",
                                    "in": "query",
                                    "required": false,
                                    "description": "Límite de resultados",
                                    "schema": {"type": "integer", "default": 100}
                                }
                            ],
                            "responses": {
                                "200": {
                                    "description": "Resultados de búsqueda",
                                    "content": {
                                        "application/json": {
                                            "schema": {
                                                "type": "object",
                                                "properties": {
                                                    "query": {"type": "string"},
                                                    "resultados": {"type": "array"},
                                                    "total": {"type": "integer"},
                                                    "timestamp": {"type": "string"}
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    },
                    "/api/search/municipio.php": {
                        "get": {
                            "summary": "Buscar por municipio",
                            "description": "Listar todos los códigos postales de un municipio",
                            "tags": ["Búsqueda"],
                            "parameters": [
                                {
                                    "name": "name",
                                    "in": "query",
                                    "required": true,
                                    "description": "Nombre del municipio",
                                    "schema": {"type": "string", "example": "Chihuahua"}
                                }
                            ],
                            "responses": {
                                "200": {
                                    "description": "Códigos postales del municipio",
                                    "content": {
                                        "application/json": {
                                            "schema": {
                                                "type": "object",
                                                "properties": {
                                                    "municipio": {"type": "string"},
                                                    "resultados": {"type": "array"},
                                                    "total": {"type": "integer"},
                                                    "timestamp": {"type": "string"}
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    },
                    "/api/search/colonia.php": {
                        "get": {
                            "summary": "Buscar por colonia",
                            "description": "Listar códigos postales por colonia o fraccionamiento",
                            "tags": ["Búsqueda"],
                            "parameters": [
                                {
                                    "name": "name",
                                    "in": "query",
                                    "required": true,
                                    "description": "Nombre de la colonia",
                                    "schema": {"type": "string", "example": "Centro"}
                                }
                            ],
                            "responses": {
                                "200": {
                                    "description": "Códigos postales de la colonia",
                                    "content": {
                                        "application/json": {
                                            "schema": {
                                                "type": "object",
                                                "properties": {
                                                    "colonia": {"type": "string"},
                                                    "resultados": {"type": "array"},
                                                    "total": {"type": "integer"},
                                                    "timestamp": {"type": "string"}
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    },
                    "/api/suggestions.php": {
                        "get": {
                            "summary": "Autocompletado inteligente",
                            "description": "Autocompletado inteligente para formularios",
                            "tags": ["Búsqueda"],
                            "parameters": [
                                {
                                    "name": "text",
                                    "in": "query",
                                    "required": true,
                                    "description": "Texto para autocompletar",
                                    "schema": {"type": "string", "example": "chi"}
                                }
                            ],
                            "responses": {
                                "200": {
                                    "description": "Sugerencias de autocompletado",
                                    "content": {
                                        "application/json": {
                                            "schema": {
                                                "type": "object",
                                                "properties": {
                                                    "texto": {"type": "string"},
                                                    "sugerencias": {"type": "array"},
                                                    "total": {"type": "integer"},
                                                    "timestamp": {"type": "string"}
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    },
                    "/api/cp/details.php": {
                        "get": {
                            "summary": "Detalles de código postal",
                            "description": "Obtiene información detallada de un código postal específico",
                            "tags": ["Información Detallada"],
                            "parameters": [
                                {
                                    "name": "codigo",
                                    "in": "query",
                                    "required": true,
                                    "description": "Código postal a consultar",
                                    "schema": {"type": "string", "pattern": "^[0-9]{5}$", "example": "31000"}
                                }
                            ],
                            "responses": {
                                "200": {
                                    "description": "Detalles del código postal",
                                    "content": {
                                        "application/json": {
                                            "schema": {
                                                "type": "object",
                                                "properties": {
                                                    "d_codigo": {"type": "string"},
                                                    "d_asenta": {"type": "string"},
                                                    "d_tipo_asenta": {"type": "string"},
                                                    "D_mnpio": {"type": "string"},
                                                    "d_estado": {"type": "string"},
                                                    "d_zona": {"type": "string"}
                                                }
                                            }
                                        }
                                    }
                                },
                                "404": {
                                    "description": "Código postal no encontrado"
                                }
                            }
                        }
                    },
                    "/api/cp/colonias.php": {
                        "get": {
                            "summary": "Colonias por código postal",
                            "description": "Listar colonias asociadas a un código postal",
                            "tags": ["Información Detallada"],
                            "parameters": [
                                {
                                    "name": "codigo",
                                    "in": "query",
                                    "required": true,
                                    "description": "Código postal",
                                    "schema": {"type": "string", "example": "31000"}
                                }
                            ],
                            "responses": {
                                "200": {
                                    "description": "Colonias del código postal",
                                    "content": {
                                        "application/json": {
                                            "schema": {
                                                "type": "object",
                                                "properties": {
                                                    "codigo_postal": {"type": "string"},
                                                    "colonias": {"type": "array"},
                                                    "total": {"type": "integer"},
                                                    "timestamp": {"type": "string"}
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    },
                    "/api/cp/municipio.php": {
                        "get": {
                            "summary": "Información del municipio",
                            "description": "Información administrativa del municipio y estado",
                            "tags": ["Información Detallada"],
                            "parameters": [
                                {
                                    "name": "codigo",
                                    "in": "query",
                                    "required": true,
                                    "description": "Código postal",
                                    "schema": {"type": "string", "example": "31000"}
                                }
                            ],
                            "responses": {
                                "200": {
                                    "description": "Información del municipio",
                                    "content": {
                                        "application/json": {
                                            "schema": {
                                                "type": "object",
                                                "properties": {
                                                    "codigo_postal": {"type": "string"},
                                                    "informacion_municipio": {"type": "object"},
                                                    "timestamp": {"type": "string"}
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    },
                    "/api/stats/overview.php": {
                        "get": {
                            "summary": "Resumen general del sistema",
                            "description": "Obtiene estadísticas generales y estado del sistema",
                            "tags": ["Estadísticas"],
                            "responses": {
                                "200": {
                                    "description": "Resumen del sistema",
                                    "content": {
                                        "application/json": {
                                            "schema": {
                                                "type": "object",
                                                "properties": {
                                                    "sistema": {"type": "object"},
                                                    "codigos_postales": {"type": "object"},
                                                    "base_datos": {"type": "object"}
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    },
                    "/api/stats/codigos-postales.php": {
                        "get": {
                            "summary": "Estadísticas detalladas de códigos postales",
                            "description": "Obtiene estadísticas específicas de códigos postales por zona, municipio y estado",
                            "tags": ["Estadísticas"],
                            "responses": {
                                "200": {
                                    "description": "Estadísticas de códigos postales",
                                    "content": {
                                        "application/json": {
                                            "schema": {
                                                "type": "object",
                                                "properties": {
                                                    "total_codigos_postales": {"type": "integer"},
                                                    "por_zona": {"type": "object"},
                                                    "por_municipio": {"type": "object"},
                                                    "por_estado": {"type": "object"},
                                                    "timestamp": {"type": "string"}
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    },
                    "/api/stats/municipios.php": {
                        "get": {
                            "summary": "Estadísticas por municipio",
                            "description": "Cantidad de códigos postales por municipio",
                            "tags": ["Estadísticas"],
                            "responses": {
                                "200": {
                                    "description": "Estadísticas de municipios",
                                    "content": {
                                        "application/json": {
                                            "schema": {
                                                "type": "object",
                                                "properties": {
                                                    "estadisticas_municipios": {"type": "array"},
                                                    "total_municipios": {"type": "integer"},
                                                    "timestamp": {"type": "string"}
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    },
                    "/api/stats/colonias.php": {
                        "get": {
                            "summary": "Estadísticas por colonia",
                            "description": "Cantidad de códigos postales por colonia",
                            "tags": ["Estadísticas"],
                            "responses": {
                                "200": {
                                    "description": "Estadísticas de colonias",
                                    "content": {
                                        "application/json": {
                                            "schema": {
                                                "type": "object",
                                                "properties": {
                                                    "estadisticas_colonias": {"type": "array"},
                                                    "total_colonias": {"type": "integer"},
                                                    "timestamp": {"type": "string"}
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    },
                    "/api/stats/health.php": {
                        "get": {
                            "summary": "Estado de salud del sistema",
                            "description": "Verifica el estado de salud del sistema y la conexión a la base de datos",
                            "tags": ["Estadísticas"],
                            "responses": {
                                "200": {
                                    "description": "Estado de salud del sistema",
                                    "content": {
                                        "application/json": {
                                            "schema": {
                                                "type": "object",
                                                "properties": {
                                                    "status": {"type": "string", "enum": ["healthy", "unhealthy"]},
                                                    "database": {"type": "string", "enum": ["connected", "disconnected"]},
                                                    "timestamp": {"type": "string"},
                                                    "memory_usage": {"type": "integer"},
                                                    "memory_peak": {"type": "integer"}
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    },
                    "/api/analysis/codigos-postales-por-zona.php": {
                        "get": {
                            "summary": "Análisis por zona",
                            "description": "Proporciona análisis detallado de la distribución de códigos postales por zona",
                            "tags": ["Análisis"],
                            "responses": {
                                "200": {
                                    "description": "Análisis por zona",
                                    "content": {
                                        "application/json": {
                                            "schema": {
                                                "type": "object",
                                                "properties": {
                                                    "zonas": {"type": "array"},
                                                    "timestamp": {"type": "string"}
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    },
                    "/api/analysis/zonas-populosas.php": {
                        "get": {
                            "summary": "Zonas populosas",
                            "description": "Zonas con mayor densidad de códigos postales",
                            "tags": ["Análisis"],
                            "responses": {
                                "200": {
                                    "description": "Análisis de zonas populosas",
                                    "content": {
                                        "application/json": {
                                            "schema": {
                                                "type": "object",
                                                "properties": {
                                                    "zonas_populosas": {"type": "array"},
                                                    "total_zonas": {"type": "integer"},
                                                    "timestamp": {"type": "string"}
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    },
                    "/api/analysis/rangos.php": {
                        "get": {
                            "summary": "Análisis por rangos",
                            "description": "Agrupación de códigos postales en rangos para reportes",
                            "tags": ["Análisis"],
                            "responses": {
                                "200": {
                                    "description": "Análisis por rangos",
                                    "content": {
                                        "application/json": {
                                            "schema": {
                                                "type": "object",
                                                "properties": {
                                                    "rangos_codigos_postales": {"type": "array"},
                                                    "total_rangos": {"type": "integer"},
                                                    "timestamp": {"type": "string"}
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    },
                    "/api/analysis/municipios.php": {
                        "get": {
                            "summary": "Análisis de municipios",
                            "description": "Mapas y análisis por municipio",
                            "tags": ["Análisis"],
                            "responses": {
                                "200": {
                                    "description": "Análisis de municipios",
                                    "content": {
                                        "application/json": {
                                            "schema": {
                                                "type": "object",
                                                "properties": {
                                                    "analisis_municipios": {"type": "array"},
                                                    "total_municipios": {"type": "integer"},
                                                    "timestamp": {"type": "string"}
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    },
                    "/api/monitor/status.php": {
                        "get": {
                            "summary": "Estado del sistema en tiempo real",
                            "description": "Obtiene el estado actual del sistema, memoria y rendimiento",
                            "tags": ["Monitoreo"],
                            "responses": {
                                "200": {
                                    "description": "Estado del sistema",
                                    "content": {
                                        "application/json": {
                                            "schema": {
                                                "type": "object",
                                                "properties": {
                                                    "sistema": {"type": "string"},
                                                    "database": {"type": "string"},
                                                    "timestamp": {"type": "string"},
                                                    "php_version": {"type": "string"},
                                                    "memory_usage": {"type": "integer"},
                                                    "memory_peak": {"type": "integer"},
                                                    "load_time": {"type": "number"}
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    },
                    "/api/monitor/performance.php": {
                        "get": {
                            "summary": "Métricas de rendimiento",
                            "description": "Obtiene métricas detalladas de rendimiento del sistema",
                            "tags": ["Monitoreo"],
                            "responses": {
                                "200": {
                                    "description": "Métricas de rendimiento",
                                    "content": {
                                        "application/json": {
                                            "schema": {
                                                "type": "object",
                                                "properties": {
                                                    "memory": {"type": "object"},
                                                    "execution_time": {"type": "number"},
                                                    "timestamp": {"type": "string"}
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    },
                    "/api/monitor/database.php": {
                        "get": {
                            "summary": "Estado de la base de datos",
                            "description": "Obtiene información detallada sobre el estado de la base de datos",
                            "tags": ["Monitoreo"],
                            "responses": {
                                "200": {
                                    "description": "Estado de la base de datos",
                                    "content": {
                                        "application/json": {
                                            "schema": {
                                                "type": "object",
                                                "properties": {
                                                    "status": {"type": "string"},
                                                    "version": {"type": "string"},
                                                    "uptime": {"type": "string"},
                                                    "connections": {"type": "string"},
                                                    "tables": {"type": "object"},
                                                    "timestamp": {"type": "string"}
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    },
                    "/api/monitor/logs.php": {
                        "get": {
                            "summary": "Logs del sistema",
                            "description": "Últimos logs de consultas y errores",
                            "tags": ["Monitoreo"],
                            "parameters": [
                                {
                                    "name": "limit",
                                    "in": "query",
                                    "required": false,
                                    "description": "Límite de logs",
                                    "schema": {"type": "integer", "default": 50}
                                }
                            ],
                            "responses": {
                                "200": {
                                    "description": "Logs del sistema",
                                    "content": {
                                        "application/json": {
                                            "schema": {
                                                "type": "object",
                                                "properties": {
                                                    "logs": {"type": "array"},
                                                    "total_logs": {"type": "integer"},
                                                    "timestamp": {"type": "string"}
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    },
                    "/api/monitor/usage.php": {
                        "get": {
                            "summary": "Métricas de uso",
                            "description": "Número de consultas por endpoint, para métricas internas",
                            "tags": ["Monitoreo"],
                            "responses": {
                                "200": {
                                    "description": "Métricas de uso",
                                    "content": {
                                        "application/json": {
                                            "schema": {
                                                "type": "object",
                                                "properties": {
                                                    "endpoints": {"type": "object"},
                                                    "total_requests": {"type": "integer"},
                                                    "periodo": {"type": "string"},
                                                    "timestamp": {"type": "string"}
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    },
                    "/api/cp/bulk.php": {
                        "get": {
                            "summary": "Exportar datos",
                            "description": "Descargar listado completo en CSV/JSON para integración con Plania",
                            "tags": ["Integración"],
                            "parameters": [
                                {
                                    "name": "format",
                                    "in": "query",
                                    "required": false,
                                    "description": "Formato de exportación",
                                    "schema": {"type": "string", "enum": ["json", "csv"], "default": "json"}
                                },
                                {
                                    "name": "limit",
                                    "in": "query",
                                    "required": false,
                                    "description": "Límite de registros",
                                    "schema": {"type": "integer", "default": 10000}
                                }
                            ],
                            "responses": {
                                "200": {
                                    "description": "Datos exportados",
                                    "content": {
                                        "application/json": {
                                            "schema": {
                                                "type": "object",
                                                "properties": {
                                                    "datos": {"type": "array"},
                                                    "total_registros": {"type": "integer"},
                                                    "formato": {"type": "string"},
                                                    "timestamp": {"type": "string"}
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                },
                "tags": [
                    {
                        "name": "Autocompletado",
                        "description": "Endpoints para autocompletado y búsqueda básica"
                    },
                    {
                        "name": "Búsqueda",
                        "description": "Endpoints para búsqueda avanzada de códigos postales"
                    },
                    {
                        "name": "Información Detallada",
                        "description": "Endpoints para obtener información detallada específica"
                    },
                    {
                        "name": "Estadísticas",
                        "description": "Endpoints para obtener estadísticas del sistema"
                    },
                    {
                        "name": "Análisis",
                        "description": "Endpoints para análisis de datos e insights"
                    },
                    {
                        "name": "Monitoreo",
                        "description": "Endpoints para monitoreo del sistema"
                    },
                    {
                        "name": "Integración",
                        "description": "Endpoints para integración con sistemas externos"
                    }
                ]
            };

            const ui = SwaggerUIBundle({
                spec: spec,
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl
                ],
                layout: "StandaloneLayout",
                validatorUrl: null,
                tryItOutEnabled: true,
                supportedSubmitMethods: ['get', 'post', 'put', 'delete', 'patch'],
                onComplete: function() {
                    console.log('Swagger UI loaded successfully');
                }
            });
        };
    </script>
</body>
</html>