<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Códigos Postales - Chihuahua - API Documentation</title>
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
            const spec = {
                "openapi": "3.0.0",
                "info": {
                    "title": "Sistema de Códigos Postales - Chihuahua",
                    "description": "API para búsqueda y consulta de códigos postales del estado de Chihuahua, México",
                    "version": "2.0.0",
                    "contact": {
                        "name": "Sistema de Mapas",
                        "email": "admin@sistemamapas.com"
                    },
                    "license": {
                        "name": "MIT",
                        "url": "https://opensource.org/licenses/MIT"
                    }
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
                                                    "suggestions": {
                                                        "type": "array",
                                                        "items": {"$ref": "#/components/schemas/CodigoPostalSuggestion"}
                                                    },
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
                                },
                                {
                                    "name": "limit",
                                    "in": "query",
                                    "required": false,
                                    "description": "Límite de sugerencias",
                                    "schema": {"type": "integer", "default": 20}
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
                    "/api/cp/bulk.php": {
                        "get": {
                            "summary": "Exportar datos",
                            "description": "Descargar listado completo en CSV/JSON para integración",
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
                "components": {
                    "schemas": {
                        "CodigoPostalSuggestion": {
                            "type": "object",
                            "properties": {
                                "value": {"type": "string", "description": "Valor completo para mostrar", "example": "31000 - Centro"},
                                "label": {"type": "string", "description": "Etiqueta para mostrar", "example": "31000 - Centro"},
                                "codigo_postal": {"type": "string", "description": "Código postal", "example": "31000"},
                                "asentamiento": {"type": "string", "description": "Nombre del asentamiento", "example": "Centro"},
                                "municipio": {"type": "string", "description": "Nombre del municipio", "example": "Chihuahua"},
                                "zona": {"type": "string", "description": "Tipo de zona", "example": "Urbana"}
                            }
                        },
                        "Error": {
                            "type": "object",
                            "properties": {
                                "error": {"type": "string", "description": "Mensaje de error", "example": "Código postal no encontrado"},
                                "timestamp": {"type": "string", "format": "date-time"}
                            }
                        }
                    }
                },
                "tags": [
                    {"name": "Autocompletado", "description": "Endpoints para autocompletado y búsqueda básica"},
                    {"name": "Búsqueda", "description": "Endpoints para búsqueda avanzada de códigos postales"},
                    {"name": "Integración", "description": "Endpoints para integración con sistemas externos"}
                ]
            };

            SwaggerUIBundle({
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
                layout: "StandaloneLayout"
            });
        };
    </script>
</body>
</html>
