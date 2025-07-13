<?php
require_once 'config/database.php';
require_once 'config/config.php';

// Inicializar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class SceneIQ {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    // Funciones de seguridad
    public function sanitize($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    public function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public function validateCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    // Funciones de autenticación
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    public function isAdmin() {
        return $this->isLoggedIn() && $_SESSION['user_role'] === 'admin';
    }

    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: pages/login.php');
            exit();
        }
    }

    public function requireAdmin() {
        if (!$this->isAdmin()) {
            header('Location: index.php');
            exit();
        }
    }

    // Funciones de usuario
    public function registerUser($username, $email, $password, $fullName) {
        try {
            $hashedPassword = $this->hashPassword($password);
            
            $stmt = $this->conn->prepare("
                INSERT INTO users (username, email, password, full_name) 
                VALUES (:username, :email, :password, :full_name)
            ");
            
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':full_name', $fullName);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            return false;
        }
    }

    public function loginUser($email, $password) {
        try {
            $stmt = $this->conn->prepare("
                SELECT id, username, email, password, full_name, role, theme_preference 
                FROM users 
                WHERE email = :email AND is_active = 1
            ");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            $user = $stmt->fetch();
            
            if ($user && $this->verifyPassword($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['theme_preference'] = $user['theme_preference'];
                
                // Actualizar último login
                $this->updateLastLogin($user['id']);
                
                // Registrar actividad
                $this->logActivity($user['id'], 'login');
                
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return false;
        }
    }

    public function logoutUser() {
        session_destroy();
        setcookie('remember_token', '', time() - 3600, '/');
    }

    // Funciones de contenido
    public function getContent($limit = 20, $offset = 0, $type = null, $genre = null) {
        try {
            $sql = "
                SELECT c.*, 
                       GROUP_CONCAT(g.name) as genres,
                       AVG(r.rating) as avg_rating,
                       COUNT(r.id) as review_count
                FROM content c
                LEFT JOIN content_genres cg ON c.id = cg.content_id
                LEFT JOIN genres g ON cg.genre_id = g.id
                LEFT JOIN reviews r ON c.id = r.content_id
                WHERE c.status = 'active'
            ";

            $params = [];
            
            if ($type) {
                $sql .= " AND c.type = :type";
                $params[':type'] = $type;
            }
            
            if ($genre) {
                $sql .= " AND g.slug = :genre";
                $params[':genre'] = $genre;
            }
            
            $sql .= " GROUP BY c.id ORDER BY c.created_at DESC LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get content error: " . $e->getMessage());
            return [];
        }
    }

    public function getRecommendations($userId, $limit = 10) {
        try {
            // Algoritmo básico de recomendaciones basado en géneros preferidos
            $stmt = $this->conn->prepare("
                SELECT c.*, AVG(r.rating) as avg_rating, COUNT(r.id) as review_count
                FROM content c
                JOIN content_genres cg ON c.id = cg.content_id
                JOIN user_preferences up ON cg.genre_id = up.genre_id
                LEFT JOIN reviews r ON c.id = r.content_id
                LEFT JOIN user_lists ul ON c.id = ul.content_id AND ul.user_id = :user_id
                WHERE up.user_id = :user_id 
                AND ul.id IS NULL
                AND c.status = 'active'
                GROUP BY c.id
                ORDER BY (AVG(r.rating) * up.preference_weight) DESC, c.imdb_rating DESC
                LIMIT :limit
            ");
            
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get recommendations error: " . $e->getMessage());
            return [];
        }
    }

    public function searchContent($query, $limit = 20) {
        try {
            $searchTerm = '%' . $query . '%';
            $stmt = $this->conn->prepare("
                SELECT c.*, AVG(r.rating) as avg_rating, COUNT(r.id) as review_count
                FROM content c
                LEFT JOIN reviews r ON c.id = r.content_id
                WHERE (c.title LIKE :query OR c.synopsis LIKE :query)
                AND c.status = 'active'
                GROUP BY c.id
                ORDER BY c.title ASC
                LIMIT :limit
            ");
            
            $stmt->bindValue(':query', $searchTerm);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Search error: " . $e->getMessage());
            return [];
        }
    }

    // Funciones de reseñas
    public function addReview($userId, $contentId, $rating, $reviewText = null, $spoilerAlert = false) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO reviews (user_id, content_id, rating, review_text, spoiler_alert)
                VALUES (:user_id, :content_id, :rating, :review_text, :spoiler_alert)
                ON DUPLICATE KEY UPDATE 
                    rating = VALUES(rating),
                    review_text = VALUES(review_text),
                    spoiler_alert = VALUES(spoiler_alert),
                    updated_at = CURRENT_TIMESTAMP
            ");
            
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':content_id', $contentId);
            $stmt->bindParam(':rating', $rating);
            $stmt->bindParam(':review_text', $reviewText);
            $stmt->bindParam(':spoiler_alert', $spoilerAlert);
            
            $result = $stmt->execute();
            
            if ($result) {
                $this->logActivity($userId, 'review', $contentId);
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Add review error: " . $e->getMessage());
            return false;
        }
    }

    public function getReviews($contentId, $limit = 10) {
        try {
            $stmt = $this->conn->prepare("
                SELECT r.*, u.username, u.avatar
                FROM reviews r
                JOIN users u ON r.user_id = u.id
                WHERE r.content_id = :content_id AND r.is_approved = 1
                ORDER BY r.created_at DESC
                LIMIT :limit
            ");
            
            $stmt->bindValue(':content_id', $contentId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get reviews error: " . $e->getMessage());
            return [];
        }
    }

    // Funciones de utilidad
    public function logActivity($userId, $action, $contentId = null, $metadata = null) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO user_activity (user_id, action_type, content_id, metadata, ip_address, user_agent)
                VALUES (:user_id, :action_type, :content_id, :metadata, :ip_address, :user_agent)
            ");
            
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':action_type', $action);
            $stmt->bindParam(':content_id', $contentId);
            $stmt->bindParam(':metadata', $metadata ? json_encode($metadata) : null);
            $stmt->bindParam(':ip_address', $_SERVER['REMOTE_ADDR']);
            $stmt->bindParam(':user_agent', $_SERVER['HTTP_USER_AGENT']);
            
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Log activity error: " . $e->getMessage());
        }
    }

    public function updateLastLogin($userId) {
        try {
            $stmt = $this->conn->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = :user_id");
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Update last login error: " . $e->getMessage());
        }
    }

    public function getGenres() {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM genres ORDER BY name ASC");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get genres error: " . $e->getMessage());
            return [];
        }
    }

    public function addToUserList($userId, $contentId, $listType) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO user_lists (user_id, content_id, list_type)
                VALUES (:user_id, :content_id, :list_type)
                ON DUPLICATE KEY UPDATE added_at = CURRENT_TIMESTAMP
            ");
            
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':content_id', $contentId);
            $stmt->bindParam(':list_type', $listType);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Add to list error: " . $e->getMessage());
            return false;
        }
    }

    public function formatDate($date, $format = 'd/m/Y H:i') {
        return date($format, strtotime($date));
    }

    public function truncateText($text, $length = 150) {
        return strlen($text) > $length ? substr($text, 0, $length) . '...' : $text;
    }

    public function generateSlug($text) {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9-]/', '-', $text);
        $text = preg_replace('/-+/', '-', $text);
        return trim($text, '-');
    }
}

// Instancia global
$sceneiq = new SceneIQ();

// Funciones helper globales
function escape($string) {
    global $sceneiq;
    return $sceneiq->sanitize($string);
}

function getCurrentUser() {
    if (!isset($_SESSION['user_id'])) return null;
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'email' => $_SESSION['user_email'],
        'role' => $_SESSION['user_role'],
        'full_name' => $_SESSION['full_name'],
        'theme' => $_SESSION['theme_preference'] ?? 'dark'
    ];
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function showAlert($message, $type = 'info') {
    $_SESSION['alert'] = ['message' => $message, 'type' => $type];
}

function getAlert() {
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        unset($_SESSION['alert']);
        return $alert;
    }
    return null;
}
?>