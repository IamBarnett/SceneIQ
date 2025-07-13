<?php
// Configuración general de la aplicación
define('SITE_NAME', 'SceneIQ');
define('SITE_URL', 'http://localhost/sceneiq');
define('SITE_DESCRIPTION', 'Descubre tu próxima obsesión cinematográfica');

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'sceneiq_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Configuración de sesiones
define('SESSION_LIFETIME', 3600 * 24 * 7); // 7 días
define('COOKIE_LIFETIME', 3600 * 24 * 30); // 30 días

// Configuración de archivos
define('UPLOAD_PATH', 'uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Configuración de seguridad
define('SALT', 'SceneIQ_2025_Salt_Key');
define('CSRF_TOKEN_NAME', 'sceneiq_csrf_token');

// Configuración de API
define('TMDB_API_KEY', 'your_tmdb_api_key_here');
define('API_RATE_LIMIT', 100); // requests per hour

// Configuración de email (para notificaciones)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your_email@gmail.com');
define('SMTP_PASSWORD', 'your_password');

// Zonas horarias
date_default_timezone_set('America/Panama');

// Configuración de errores
if ($_SERVER['SERVER_NAME'] === 'localhost') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}
?>