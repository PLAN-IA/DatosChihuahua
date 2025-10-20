# 🗺️ Sistema de Servicios Privados - Chihuahua

Sistema web desarrollado en PHP para búsqueda de calles y servicios en Chihuahua, utilizando MariaDB como base de datos.

## 📁 Estructura del Proyecto

```
proyecto/
├── config/
│   ├── Config.php          # Clase de configuración
│   └── database.php        # Conexión a base de datos
├── controllers/
│   └── HomeController.php  # Controlador principal
├── models/
│   ├── Calles.php          # Modelo para calles
│   └── Servicios.php       # Modelo para servicios
├── views/
│   └── home.php           # Vista principal
├── logs/                  # Directorio de logs (se crea automáticamente)
├── index.php              # Punto de entrada
├── env.example            # Archivo de configuración de ejemplo
└── README.md              # Este archivo
```

## 🚀 Instalación

### 1. Requisitos Previos
- **PHP 7.4+** con extensiones PDO y PDO_MySQL
- **MariaDB/MySQL** con la base de datos importada
- **Servidor web** (Apache/Nginx) o PHP built-in server

### 2. Configuración

1. **Clonar/descargar el proyecto**
2. **Copiar archivo de configuración:**
   ```bash
   copy env.example .env
   ```

3. **Editar configuración en `.env`:**
   ```env
   # Base de datos
   DB_HOST=localhost
   DB_NAME=mapas_automatizacion
   DB_USER=tu_usuario
   DB_PASSWORD=tu_password
   
   # Aplicación
   APP_NAME="Sistema de Servicios Privados"
   APP_ENV=development
   APP_DEBUG=true
   ```

4. **Importar base de datos:**
   - Abrir HeidiSQL
   - Conectarse a MariaDB
   - Ejecutar el archivo `migracion_mapas.sql`

### 3. Ejecutar el Sistema

**Opción A - Servidor PHP integrado:**
```bash
php -S localhost:8000
```

**Opción B - Servidor web:**
- Configurar Apache/Nginx para apuntar al directorio del proyecto
- Acceder a `http://localhost/proyecto`

## 🎯 Funcionalidades

### ✅ Búsqueda de Calles
- Búsqueda por nombre con resultados ordenados
- Información de tipo de vialidad y clasificación
- Indicador de vías principales
- Intersecciones disponibles

### ✅ Búsqueda de Servicios
- 15 tipos de servicios disponibles:
  - Farmacias, Hospitales, Bibliotecas
  - Escuelas (Primarias, Secundarias, Superiores)
  - Parques, Bomberos, Ambulancias
  - Gaseras, Cines, Estadios, Monumentos
  - Templos (Católicos y Cristianos)
- Filtro opcional por colonia

### ✅ Estadísticas en Tiempo Real
- Contador total de calles
- Número de servicios por tipo
- Estadísticas generales del sistema

## 🔧 Características Técnicas

### ✅ Buenas Prácticas Implementadas
- **Patrón MVC** - Separación de responsabilidades
- **Configuración externa** - Variables de entorno
- **Manejo de errores** - Logs y excepciones controladas
- **Validación de entrada** - Sanitización de datos
- **Prepared statements** - Prevención de SQL injection
- **Logging** - Registro de errores en archivos
- **Documentación** - Código documentado con PHPDoc

### ✅ Seguridad
- Validación de parámetros de entrada
- Escape de salida HTML
- Prepared statements para consultas SQL
- Manejo seguro de errores (no exposición de información sensible)

### ✅ Rendimiento
- Conexión persistente a base de datos
- Límites configurables de resultados
- Paginación para grandes conjuntos de datos

## 📊 Base de Datos

### Tablas Principales:
- `vialidadwgs84` - Calles y vialidad (83,989 registros)
- `coloniaswgs84` - Colonias (1,152 registros)
- `farmaciaswgs84` - Farmacias (402 registros)
- `unidad_medica_wgs84` - Unidades médicas (275 registros)
- `bibliotecaswgs84` - Bibliotecas (86 registros)
- `pparqueswgs84` - Parques y áreas verdes (6,005 registros)
- Y 22 tablas más con diferentes servicios

## 🔍 Ejemplos de Uso

### Búsqueda de Calles
```
GET /?buscar_calle=AVENIDA
```

### Búsqueda de Servicios
```
GET /?buscar_servicio=1&tipo_servicio=farmacias&colonia_servicio=centro
```

## 🛠️ Desarrollo

### Estructura MVC:
- **Modelos** (`models/`) - Lógica de datos y validación
- **Controladores** (`controllers/`) - Lógica de negocio
- **Vistas** (`views/`) - Presentación y HTML
- **Configuración** (`config/`) - Configuración del sistema

### Agregar Nuevas Funcionalidades:
1. Crear método en el modelo correspondiente
2. Agregar método en el controlador
3. Actualizar la vista si es necesario
4. Documentar cambios

## 📝 Logs

Los logs se guardan en `logs/app.log` con formato:
```
[2024-01-01 12:00:00] [CALLES] Error buscando calles: mensaje
```

## 🔧 Configuración Avanzada

### Variables de Entorno Disponibles:
```env
# Base de datos
DB_HOST=localhost
DB_NAME=mapas_automatizacion
DB_USER=root
DB_PASSWORD=
DB_CHARSET=utf8mb4
DB_PORT=3306

# Aplicación
APP_NAME="Sistema de Servicios Privados"
APP_VERSION=1.0.0
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost

# Límites
MAX_SEARCH_RESULTS=50
DEFAULT_SEARCH_LIMIT=20

# Logs
LOG_LEVEL=info
LOG_FILE=logs/app.log
```

## 🚨 Solución de Problemas

### Error de Conexión a Base de Datos:
1. Verificar que MariaDB esté ejecutándose
2. Revisar credenciales en `.env`
3. Verificar que la base de datos existe
4. Comprobar permisos del usuario

### Error de Archivo .env:
```bash
copy env.example .env
```

### Error de Permisos:
```bash
chmod 755 logs/
chmod 644 .env
```

## 📞 Soporte

Para reportar problemas o solicitar funcionalidades:
1. Verificar logs en `logs/app.log`
2. Revisar configuración en `.env`
3. Comprobar que todos los archivos estén presentes

¡El sistema está listo para usar! 🎉
