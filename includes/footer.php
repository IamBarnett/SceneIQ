</main>
    
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>SceneIQ</h3>
                    <p>La plataforma definitiva para descubrir tu próxima obsesión cinematográfica.</p>
                    <div class="social-links">
                        <a href="#" aria-label="Twitter">🐦</a>
                        <a href="#" aria-label="Instagram">📷</a>
                        <a href="#" aria-label="YouTube">📺</a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h4>Explorar</h4>
                    <ul>
                        <li><a href="pages/movies.php">Películas</a></li>
                        <li><a href="pages/series.php">Series</a></li>
                        <li><a href="pages/trending.php">Tendencias</a></li>
                        <li><a href="pages/top-rated.php">Mejor Valoradas</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Comunidad</h4>
                    <ul>
                        <li><a href="pages/reviews.php">Reseñas</a></li>
                        <li><a href="pages/users.php">Usuarios</a></li>
                        <li><a href="pages/forums.php">Foros</a></li>
                        <li><a href="pages/help.php">Ayuda</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Legal</h4>
                    <ul>
                        <li><a href="pages/privacy.php">Privacidad</a></li>
                        <li><a href="pages/terms.php">Términos</a></li>
                        <li><a href="pages/contact.php">Contacto</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> SceneIQ. Todos los derechos reservados.</p>
                <p>Hecho con ❤️ para los amantes del cine y las series.</p>
            </div>
        </div>
    </footer>

    <!-- Floating Action Button -->
    <?php if ($user): ?>
        <button class="fab" onclick="openReviewModal()" title="Escribir Reseña">✏️</button>
    <?php endif; ?>
    
    <!-- Scripts -->
    <script src="assets/js/main.js"></script>
    <?php if (isset($additionalJS)): ?>
        <?php foreach ($additionalJS as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- CSRF Token para AJAX -->
    <script>
        window.csrfToken = '<?php echo $sceneiq->generateCSRFToken(); ?>';
        window.userId = <?php echo $user ? $user['id'] : 'null'; ?>;
        window.siteUrl = '<?php echo SITE_URL; ?>';
    </script>
</body>
</html>