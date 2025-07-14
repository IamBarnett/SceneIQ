<?php
// Detectar si estamos en subdirectorio
$is_subdir = strpos($_SERVER['REQUEST_URI'], '/pages/') !== false;
$base_path = $is_subdir ? '../' : '';

// Incluir configuración
if (file_exists($base_path . 'includes/functions.php')) {
    require_once $base_path . 'includes/functions.php';
} else {
    require_once $base_path . 'config/simple_config.php';
}

$user = getCurrentUser();
$alert = getAlert();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . SITE_NAME : SITE_NAME . ' - ' . SITE_DESCRIPTION; ?></title>
    <meta name="description" content="<?php echo isset($pageDescription) ? $pageDescription : SITE_DESCRIPTION; ?>">
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/main.css">
    
    <style>
        :root {
            <?php if ($user && isset($user['theme']) && $user['theme'] === 'light'): ?>
                --dark-bg: #f8f9fa;
                --text-primary: #333333;
                --text-secondary: #666666;
                --card-bg: rgba(255, 255, 255, 0.8);
                --glass-bg: rgba(0, 0, 0, 0.05);
            <?php else: ?>
                --dark-bg: #0a0e27;
                --text-primary: #ffffff;
                --text-secondary: #b8c6db;
                --card-bg: rgba(255, 255, 255, 0.03);
                --glass-bg: rgba(255, 255, 255, 0.1);
            <?php endif; ?>
        }
    </style>
</head>
<body data-theme="<?php echo $user && isset($user['theme']) ? $user['theme'] : 'dark'; ?>">
    <div class="bg-animation"></div>
    
    <?php if ($alert): ?>
        <div class="alert alert-<?php echo $alert['type']; ?>" id="alertMessage">
            <span><?php echo escape($alert['message']); ?></span>
            <button class="alert-close" onclick="closeAlert()">×</button>
        </div>
    <?php endif; ?>
    
    <header class="header">
        <div class="nav-container">
            <div class="logo">
                <a href="<?php echo $base_path; ?>index.php">
                    Scene<span style="color: #ff6b6b;">IQ</span>
                </a>
            </div>
            
            <nav>
                <ul class="nav-links" id="mainNavigation">
                    <li><a href="<?php echo $base_path; ?>pages/movies.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'movies.php') !== false ? 'active' : ''; ?>">🎬 Películas</a></li>
                    <li><a href="<?php echo $base_path; ?>pages/series.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'series.php') !== false ? 'active' : ''; ?>">📺 Series</a></li>
                    
                    <?php if ($user): ?>
                        <li><a href="<?php echo $base_path; ?>pages/dashboard.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'dashboard.php') !== false ? 'active' : ''; ?>">🎯 Recomendaciones</a></li>
                        <li><a href="<?php echo $base_path; ?>pages/profile.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'profile.php') !== false ? 'active' : ''; ?>">📋 Mi Lista</a></li>
                    <?php endif; ?>
                    
                    <?php if ($user && isset($user['role']) && $user['role'] === 'admin'): ?>
                        <li><a href="<?php echo $base_path; ?>pages/admin/" class="<?php echo strpos($_SERVER['PHP_SELF'], 'admin') !== false ? 'active' : ''; ?>">🛠️ Admin</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            
            <div class="auth-buttons">
                <button class="theme-toggle" onclick="toggleTheme()" title="Cambiar tema">
                    <?php echo $user && isset($user['theme']) && $user['theme'] === 'light' ? '🌙' : '☀️'; ?>
                </button>
                
                <button class="mobile-menu-btn" onclick="toggleMobileMenu()" style="display: none;">☰</button>
                
                <?php if ($user): ?>
                    <div class="user-menu">
                        <button class="user-avatar" onclick="toggleUserDropdown()">
                            <img src="<?php echo $base_path; ?>assets/images/default-avatar.png" alt="Avatar">
                            <span><?php echo escape($user['username']); ?></span>
                        </button>
                        
                        <div class="user-dropdown" id="userDropdown">
                            <a href="<?php echo $base_path; ?>pages/profile.php">👤 Mi Perfil</a>
                            <a href="<?php echo $base_path; ?>pages/preferences.php">⚙️ Preferencias</a>
                            <a href="<?php echo $base_path; ?>pages/my-reviews.php">📝 Mis Reseñas</a>
                            <hr>
                            <a href="<?php echo $base_path; ?>api/logout.php" onclick="return confirm('¿Cerrar sesión?')">🚪 Cerrar Sesión</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?php echo $base_path; ?>pages/login.php" class="btn btn-secondary">🔐 Iniciar Sesión</a>
                    <a href="<?php echo $base_path; ?>pages/register.php" class="btn btn-primary">✨ Registrarse</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="main-content">

    <script>
        window.sceneIQConfig = {
            siteUrl: '<?php echo SITE_URL; ?>',
            userId: <?php echo $user && isset($user['id']) ? $user['id'] : 'null'; ?>,
            isLoggedIn: <?php echo $user ? 'true' : 'false'; ?>,
            theme: '<?php echo $user && isset($user['theme']) ? $user['theme'] : 'dark'; ?>',
            csrfToken: '<?php echo $sceneiq->generateCSRFToken(); ?>'
        };
        
        function toggleTheme() {
            const body = document.body;
            const currentTheme = body.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            body.setAttribute('data-theme', newTheme);
            
            const toggle = document.querySelector('.theme-toggle');
            if (toggle) toggle.textContent = newTheme === 'dark' ? '☀️' : '🌙';
        }
        
        function toggleMobileMenu() {
            const nav = document.getElementById('mainNavigation');
            const btn = document.querySelector('.mobile-menu-btn');
            if (nav) {
                nav.classList.toggle('mobile-active');
                btn.innerHTML = nav.classList.contains('mobile-active') ? '✕' : '☰';
            }
        }
        
        function toggleUserDropdown() {
            const dropdown = document.getElementById('userDropdown');
            if (dropdown) dropdown.classList.toggle('active');
        }
        
        function closeAlert() {
            const alert = document.getElementById('alertMessage');
            if (alert) alert.remove();
        }
        
        // Cerrar dropdown al clickear fuera
        document.addEventListener('click', function(e) {
            const userMenu = document.querySelector('.user-menu');
            const dropdown = document.getElementById('userDropdown');
            
            if (userMenu && !userMenu.contains(e.target) && dropdown) {
                dropdown.classList.remove('active');
            }
        });
        
        // Responsive menu
        window.addEventListener('resize', function() {
            const nav = document.getElementById('mainNavigation');
            const btn = document.querySelector('.mobile-menu-btn');
            
            if (window.innerWidth <= 768) {
                btn.style.display = 'block';
            } else {
                btn.style.display = 'none';
                nav.classList.remove('mobile-active');
                btn.innerHTML = '☰';
            }
        });
        
        // Inicializar responsive
        if (window.innerWidth <= 768) {
            document.querySelector('.mobile-menu-btn').style.display = 'block';
        }
    </script>