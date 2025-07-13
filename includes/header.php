<?php
require_once 'includes/functions.php';
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
    <link rel="stylesheet" href="assets/css/main.css">
    <?php if (isset($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
    
    <!-- Meta Open Graph -->
    <meta property="og:title" content="<?php echo SITE_NAME; ?>">
    <meta property="og:description" content="<?php echo SITE_DESCRIPTION; ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo SITE_URL; ?>">
    <meta property="og:site_name" content="<?php echo SITE_NAME; ?>">
    <meta property="og:image" content="<?php echo SITE_URL; ?>/assets/images/og-image.jpg">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo SITE_NAME; ?>">
    <meta name="twitter:description" content="<?php echo SITE_DESCRIPTION; ?>">
    <meta name="twitter:image" content="<?php echo SITE_URL; ?>/assets/images/twitter-card.jpg">
    
    <!-- Preconnect para optimización -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#667eea">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="<?php echo SITE_NAME; ?>">
    
    <!-- Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "<?php echo SITE_NAME; ?>",
        "description": "<?php echo SITE_DESCRIPTION; ?>",
        "url": "<?php echo SITE_URL; ?>",
        "potentialAction": {
            "@type": "SearchAction",
            "target": "<?php echo SITE_URL; ?>/pages/search.php?q={search_term_string}",
            "query-input": "required name=search_term_string"
        }
    }
    </script>
    
    <style>
        /* Variables CSS dinámicas basadas en preferencias del usuario */
        :root {
            <?php if ($user && $user['theme'] === 'light'): ?>
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
        
        /* Loading Screen */
        .loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--dark-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            transition: opacity 0.5s ease;
        }
        
        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: #667eea;
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .loading-text {
            margin-top: 1rem;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        
        /* Mobile Menu Styles */
        .mobile-menu-btn {
            display: none;
            background: var(--glass-bg);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 8px;
            color: var(--text-primary);
            padding: 0.5rem;
            cursor: pointer;
            font-size: 1.2rem;
        }
        
        @media (max-width: 768px) {
            .mobile-menu-btn {
                display: block;
            }
            
            .nav-links {
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: var(--card-bg);
                backdrop-filter: blur(20px);
                border: 1px solid rgba(255, 255, 255, 0.1);
                border-radius: 0 0 15px 15px;
                padding: 1rem;
                flex-direction: column;
                gap: 0.5rem;
                transform: translateY(-100%);
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s ease;
            }
            
            .nav-links.mobile-active {
                transform: translateY(0);
                opacity: 1;
                visibility: visible;
            }
            
            .nav-links li {
                width: 100%;
            }
            
            .nav-links a {
                display: block;
                width: 100%;
                text-align: center;
                padding: 0.75rem;
            }
        }
        
        /* Notification Badge */
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ff4757;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }
        
        /* Search Suggestions */
        .search-suggestions {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 0 0 15px 15px;
            max-height: 300px;
            overflow-y: auto;
            z-index: 1001;
            display: none;
        }
        
        .suggestion-item {
            padding: 0.75rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            cursor: pointer;
            transition: background-color 0.2s ease;
        }
        
        .suggestion-item:hover {
            background: var(--glass-bg);
        }
        
        .suggestion-item:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body data-theme="<?php echo $user ? $user['theme'] : 'dark'; ?>">
    <!-- Loading Screen -->
    <div class="loading-screen" id="loadingScreen">
        <div>
            <div class="loading-spinner"></div>
            <div class="loading-text">Cargando SceneIQ...</div>
        </div>
    </div>
    
    <div class="bg-animation"></div>
    
    <!-- Alert System -->
    <?php if ($alert): ?>
        <div class="alert alert-<?php echo $alert['type']; ?>" id="alertMessage">
            <span><?php echo escape($alert['message']); ?></span>
            <button class="alert-close" onclick="closeAlert()">×</button>
        </div>
    <?php endif; ?>
    
    <!-- Skip to Content (Accessibility) -->
    <a href="#main-content" class="skip-link" style="position: absolute; top: -40px; left: 6px; background: var(--accent); color: white; padding: 8px; text-decoration: none; border-radius: 4px; transition: top 0.3s;">
        Saltar al contenido principal
    </a>
    
    <header class="header" role="banner">
        <div class="nav-container">
            <div class="logo">
                <a href="<?php echo SITE_URL; ?>" aria-label="Ir al inicio de SceneIQ">
                    <span>Scene</span><span style="color: var(--accent);">IQ</span>
                </a>
            </div>
            
            <nav role="navigation" aria-label="Navegación principal">
                <ul class="nav-links" id="mainNavigation">
                    <li>
                        <a href="index.php" 
                           class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>"
                           <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'aria-current="page"' : ''; ?>>
                            <span>🏠</span> Inicio
                        </a>
                    </li>
                    <li>
                        <a href="pages/movies.php" 
                           class="<?php echo strpos($_SERVER['PHP_SELF'], 'movies.php') !== false ? 'active' : ''; ?>"
                           <?php echo strpos($_SERVER['PHP_SELF'], 'movies.php') !== false ? 'aria-current="page"' : ''; ?>>
                            <span>🎬</span> Películas
                        </a>
                    </li>
                    <li>
                        <a href="pages/series.php" 
                           class="<?php echo strpos($_SERVER['PHP_SELF'], 'series.php') !== false ? 'active' : ''; ?>"
                           <?php echo strpos($_SERVER['PHP_SELF'], 'series.php') !== false ? 'aria-current="page"' : ''; ?>>
                            <span>📺</span> Series
                        </a>
                    </li>
                    
                    <?php if ($user): ?>
                        <li>
                            <a href="pages/dashboard.php" 
                               class="<?php echo strpos($_SERVER['PHP_SELF'], 'dashboard.php') !== false ? 'active' : ''; ?>"
                               <?php echo strpos($_SERVER['PHP_SELF'], 'dashboard.php') !== false ? 'aria-current="page"' : ''; ?>>
                                <span>🎯</span> Recomendaciones
                            </a>
                        </li>
                        <li>
                            <a href="pages/profile.php" 
                               class="<?php echo strpos($_SERVER['PHP_SELF'], 'profile.php') !== false ? 'active' : ''; ?>"
                               <?php echo strpos($_SERVER['PHP_SELF'], 'profile.php') !== false ? 'aria-current="page"' : ''; ?>>
                                <span>📋</span> Mi Lista
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php if ($user && $user['role'] === 'admin'): ?>
                        <li>
                            <a href="pages/admin/" 
                               class="<?php echo strpos($_SERVER['PHP_SELF'], 'admin') !== false ? 'active' : ''; ?>"
                               <?php echo strpos($_SERVER['PHP_SELF'], 'admin') !== false ? 'aria-current="page"' : ''; ?>>
                                <span>🛠️</span> Admin
                                <?php
                                // Mostrar badge de notificaciones para admin
                                if ($user['role'] === 'admin') {
                                    $pendingReviews = 0; // Aquí iría la lógica para contar reviews pendientes
                                    if ($pendingReviews > 0): ?>
                                        <span class="notification-badge"><?php echo $pendingReviews; ?></span>
                                    <?php endif;
                                }
                                ?>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            
            <div class="auth-buttons">
                <!-- Theme Toggle -->
                <button class="theme-toggle" 
                        onclick="toggleTheme()" 
                        title="<?php echo $user && $user['theme'] === 'light' ? 'Cambiar a tema oscuro' : 'Cambiar a tema claro'; ?>"
                        aria-label="Cambiar tema de color">
                    <?php echo $user && $user['theme'] === 'light' ? '🌙' : '☀️'; ?>
                </button>
                
                <!-- Search Toggle (Mobile) -->
                <button class="search-toggle mobile-only" 
                        onclick="toggleMobileSearch()" 
                        title="Buscar"
                        aria-label="Abrir búsqueda"
                        style="display: none;">
                    🔍
                </button>
                
                <!-- Mobile Menu Button -->
                <button class="mobile-menu-btn" 
                        onclick="toggleMobileMenu()" 
                        aria-label="Abrir menú de navegación"
                        aria-expanded="false"
                        aria-controls="mainNavigation">
                    ☰
                </button>
                
                <?php if ($user): ?>
                    <!-- User Menu -->
                    <div class="user-menu">
                        <button class="user-avatar" 
                                onclick="toggleUserDropdown()" 
                                aria-label="Menú de usuario"
                                aria-expanded="false"
                                aria-haspopup="true">
                            <img src="<?php echo $user['avatar'] ?? 'assets/images/default-avatar.png'; ?>" 
                                 alt="Avatar de <?php echo escape($user['username']); ?>"
                                 onerror="this.src='assets/images/default-avatar.png'">
                            <span><?php echo escape($user['username']); ?></span>
                            <span style="font-size: 0.7rem; margin-left: 0.5rem;">▼</span>
                        </button>
                        
                        <div class="user-dropdown" id="userDropdown" role="menu" aria-label="Opciones de usuario">
                            <a href="pages/profile.php" role="menuitem">
                                <span>👤</span> Mi Perfil
                            </a>
                            <a href="pages/preferences.php" role="menuitem">
                                <span>⚙️</span> Preferencias
                            </a>
                            <a href="pages/my-reviews.php" role="menuitem">
                                <span>📝</span> Mis Reseñas
                            </a>
                            <a href="pages/watchlist.php" role="menuitem">
                                <span>📋</span> Mi Lista
                            </a>
                            
                            <?php if ($user['role'] === 'admin'): ?>
                                <hr>
                                <a href="pages/admin/analytics.php" role="menuitem">
                                    <span>📊</span> Analytics
                                </a>
                                <a href="pages/admin/users.php" role="menuitem">
                                    <span>👥</span> Usuarios
                                </a>
                            <?php endif; ?>
                            
                            <hr>
                            <a href="pages/help.php" role="menuitem">
                                <span>❓</span> Ayuda
                            </a>
                            <a href="api/logout.php" 
                               onclick="return confirm('¿Estás seguro de que quieres cerrar sesión?')" 
                               role="menuitem">
                                <span>🚪</span> Cerrar Sesión
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Auth Buttons for Non-logged Users -->
                    <a href="pages/login.php" class="btn btn-secondary">
                        <span>🔐</span> Iniciar Sesión
                    </a>
                    <a href="pages/register.php" class="btn btn-primary">
                        <span>✨</span> Registrarse
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Mobile Search Bar -->
        <div class="mobile-search-bar" id="mobileSearchBar" style="display: none; padding: 1rem; border-top: 1px solid rgba(255,255,255,0.1);">
            <div class="search-bar">
                <form action="pages/search.php" method="GET" class="search-form">
                    <input type="text" 
                           name="q" 
                           class="search-input" 
                           placeholder="Buscar películas, series, actores..." 
                           value="<?php echo isset($_GET['q']) ? escape($_GET['q']) : ''; ?>"
                           autocomplete="off"
                           aria-label="Campo de búsqueda">
                    <button type="submit" class="search-btn" aria-label="Buscar">🔍</button>
                </form>
            </div>
        </div>
    </header>

    <main class="main-content" id="main-content" role="main">
        <!-- Search Results Info (if applicable) -->
        <?php if (isset($_GET['q']) && !empty($_GET['q'])): ?>
            <div class="search-info" style="background: var(--glass-bg); padding: 1rem; border-radius: 10px; margin-bottom: 2rem;">
                <h2 style="color: var(--text-primary); margin-bottom: 0.5rem;">
                    Resultados para: "<span style="color: var(--accent);"><?php echo escape($_GET['q']); ?></span>"
                </h2>
                <p style="color: var(--text-secondary); font-size: 0.9rem;">
                    Mostrando contenido relacionado con tu búsqueda
                </p>
            </div>
        <?php endif; ?>
        
        <!-- Breadcrumb Navigation (if applicable) -->
        <?php if (isset($breadcrumbs) && !empty($breadcrumbs)): ?>
            <nav aria-label="Navegación por migas de pan" class="breadcrumb-nav" style="margin-bottom: 2rem;">
                <ol style="display: flex; gap: 0.5rem; list-style: none; color: var(--text-secondary); font-size: 0.9rem;">
                    <?php foreach ($breadcrumbs as $index => $crumb): ?>
                        <li>
                            <?php if ($index < count($breadcrumbs) - 1): ?>
                                <a href="<?php echo $crumb['url']; ?>" style="color: var(--accent); text-decoration: none;">
                                    <?php echo escape($crumb['title']); ?>
                                </a>
                                <span style="margin: 0 0.5rem; color: var(--text-secondary);">›</span>
                            <?php else: ?>
                                <span style="color: var(--text-primary);"><?php echo escape($crumb['title']); ?></span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </nav>
        <?php endif; ?>

    <script>
        // Variables globales para JavaScript
        window.sceneIQConfig = {
            siteUrl: '<?php echo SITE_URL; ?>',
            userId: <?php echo $user ? $user['id'] : 'null'; ?>,
            isLoggedIn: <?php echo $user ? 'true' : 'false'; ?>,
            isAdmin: <?php echo $user && $user['role'] === 'admin' ? 'true' : 'false'; ?>,
            theme: '<?php echo $user ? $user['theme'] : 'dark'; ?>',
            csrfToken: '<?php echo $sceneiq->generateCSRFToken(); ?>'
        };
        
        // Ocultar loading screen cuando la página esté lista
        window.addEventListener('load', function() {
            const loadingScreen = document.getElementById('loadingScreen');
            if (loadingScreen) {
                loadingScreen.style.opacity = '0';
                setTimeout(() => {
                    loadingScreen.style.display = 'none';
                }, 500);
            }
        });
        
        // Funciones del header
        function toggleMobileMenu() {
            const navLinks = document.getElementById('mainNavigation');
            const menuBtn = document.querySelector('.mobile-menu-btn');
            
            navLinks.classList.toggle('mobile-active');
            const isExpanded = navLinks.classList.contains('mobile-active');
            menuBtn.setAttribute('aria-expanded', isExpanded);
            menuBtn.innerHTML = isExpanded ? '✕' : '☰';
        }
        
        function toggleMobileSearch() {
            const searchBar = document.getElementById('mobileSearchBar');
            const isVisible = searchBar.style.display !== 'none';
            searchBar.style.display = isVisible ? 'none' : 'block';
            
            if (!isVisible) {
                searchBar.querySelector('input').focus();
            }
        }
        
        function toggleUserDropdown() {
            const dropdown = document.getElementById('userDropdown');
            const button = document.querySelector('.user-avatar');
            
            dropdown.classList.toggle('active');
            const isExpanded = dropdown.classList.contains('active');
            button.setAttribute('aria-expanded', isExpanded);
        }
        
        // Cerrar dropdown al hacer clic fuera
        document.addEventListener('click', function(event) {
            const userMenu = document.querySelector('.user-menu');
            const dropdown = document.getElementById('userDropdown');
            
            if (userMenu && !userMenu.contains(event.target)) {
                dropdown.classList.remove('active');
                document.querySelector('.user-avatar').setAttribute('aria-expanded', 'false');
            }
        });
        
        // Accesibilidad con teclado
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                // Cerrar dropdown de usuario
                const dropdown = document.getElementById('userDropdown');
                if (dropdown && dropdown.classList.contains('active')) {
                    dropdown.classList.remove('active');
                    document.querySelector('.user-avatar').setAttribute('aria-expanded', 'false');
                    document.querySelector('.user-avatar').focus();
                }
                
                // Cerrar menú móvil
                const navLinks = document.getElementById('mainNavigation');
                if (navLinks && navLinks.classList.contains('mobile-active')) {
                    toggleMobileMenu();
                }
            }
        });
        
        // Focus management para accesibilidad
        document.querySelector('.skip-link').addEventListener('focus', function() {
            this.style.top = '6px';
        });
        
        document.querySelector('.skip-link').addEventListener('blur', function() {
            this.style.top = '-40px';
        });
    </script>