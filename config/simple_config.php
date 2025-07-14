<?php
// Configuración básica para SceneIQ
// Usar este archivo si no tienes base de datos configurada aún

// Configuración del sitio
define('SITE_NAME', 'SceneIQ');
define('SITE_URL', 'http://localhost/sceneiq');
define('SITE_DESCRIPTION', 'Descubre tu próxima obsesión cinematográfica');

// Inicializar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Funciones básicas
function escape($string) {
    return htmlspecialchars(trim($string), ENT_QUOTES, 'UTF-8');
}

function getCurrentUser() {
    if (!isset($_SESSION['user_id'])) return null;
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'] ?? 'Usuario',
        'email' => $_SESSION['user_email'] ?? '',
        'role' => $_SESSION['user_role'] ?? 'user',
        'full_name' => $_SESSION['full_name'] ?? 'Usuario',
        'theme' => $_SESSION['theme_preference'] ?? 'dark'
    ];
}

function getAlert() {
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        unset($_SESSION['alert']);
        return $alert;
    }
    return null;
}

function showAlert($message, $type = 'info') {
    $_SESSION['alert'] = ['message' => $message, 'type' => $type];
}

// Clase SceneIQ básica (sin base de datos)
class SceneIQ {
    public function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    public function getGenres() {
        // Géneros de ejemplo
        return [
            ['id' => 1, 'name' => 'Acción', 'slug' => 'accion'],
            ['id' => 2, 'name' => 'Drama', 'slug' => 'drama'],
            ['id' => 3, 'name' => 'Comedia', 'slug' => 'comedia'],
            ['id' => 4, 'name' => 'Thriller', 'slug' => 'thriller'],
            ['id' => 5, 'name' => 'Sci-Fi', 'slug' => 'sci-fi'],
            ['id' => 6, 'name' => 'Romance', 'slug' => 'romance'],
            ['id' => 7, 'name' => 'Horror', 'slug' => 'horror']
        ];
    }
    
    public function getContent($limit = 20, $offset = 0, $type = null, $genre = null) {
        // Contenido de ejemplo
        return [
            [
                'id' => 1,
                'title' => 'The Dark Knight',
                'year' => 2008,
                'type' => 'movie',
                'synopsis' => 'Batman debe enfrentar a su mayor enemigo en esta épica historia de heroísmo y caos.',
                'imdb_rating' => 9.0,
                'avg_rating' => 9.0,
                'review_count' => 156,
                'genres' => 'Acción, Drama, Crimen'
            ],
            [
                'id' => 2,
                'title' => 'Breaking Bad',
                'year' => 2008,
                'type' => 'series',
                'synopsis' => 'Un profesor de química se convierte en fabricante de metanfetaminas tras descubrir que tiene cáncer.',
                'imdb_rating' => 9.5,
                'avg_rating' => 9.5,
                'review_count' => 234,
                'genres' => 'Drama, Crimen, Thriller'
            ],
            [
                'id' => 3,
                'title' => 'Inception',
                'year' => 2010,
                'type' => 'movie',
                'synopsis' => 'Un ladrón que roba secretos corporativos mediante tecnología de sueños compartidos.',
                'imdb_rating' => 8.8,
                'avg_rating' => 8.8,
                'review_count' => 189,
                'genres' => 'Acción, Sci-Fi, Thriller'
            ]
        ];
    }
    
    public function getRecommendations($userId, $limit = 10) {
        return $this->getContent($limit);
    }
    
    public function sanitize($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    public function isAdmin() {
        return $this->isLoggedIn() && $_SESSION['user_role'] === 'admin';
    }
}

// Instancia global
$sceneiq = new SceneIQ();
?>