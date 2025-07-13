// Si el usuario está logueado, obtener recomendaciones
$recommendations = [];
if ($user) {
    $recommendations = $sceneiq->getRecommendations($user['id'], 8);
}

$genres = $sceneiq->getGenres();
?>

<section class="hero">
    <h1>Descubre tu próxima obsesión</h1>
    <p>Explora miles de reseñas y encuentra las mejores películas y series según tus gustos</p>
    <div class="search-bar">
        <form action="pages/search.php" method="GET" class="search-form">
            <input type="text" name="q" class="search-input" placeholder="Buscar películas, series, actores..." 
                   value="<?php echo isset($_GET['q']) ? escape($_GET['q']) : ''; ?>" required>
            <button type="submit" class="search-btn">🔍</button>
        </form>
    </div>
</section>

<section class="genre-filter">
    <a href="index.php" class="genre-tag <?php echo !isset($_GET['genre']) ? 'active' : ''; ?>">Todos</a>
    <?php foreach ($genres as $genre): ?>
        <a href="?genre=<?php echo $genre['slug']; ?>" 
           class="genre-tag <?php echo isset($_GET['genre']) && $_GET['genre'] === $genre['slug'] ? 'active' : ''; ?>">
            <?php echo escape($genre['name']); ?>
        </a>
    <?php endforeach; ?>
</section>

<!-- Trending Section -->
<?php if (!empty($trendingContent)): ?>
<section class="trending-bar">
    <div class="section-header">
        <h2 class="section-title">📈 Trending Ahora</h2>
    </div>
    <div class="trending-content">
        <?php foreach ($trendingContent as $index => $content): ?>
            <div class="trending-item" onclick="location.href='pages/content.php?id=<?php echo $content['id']; ?>'">
                <div class="trending-number">#<?php echo $index + 1; ?></div>
                <div class="trending-title"><?php echo escape($content['title']); ?></div>
                <div class="trending-rating">⭐ <?php echo number_format($content['avg_rating'] ?? $content['imdb_rating'], 1); ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- Recomendaciones personalizadas -->
<?php if ($user && !empty($recommendations)): ?>
<section class="content-section">
    <div class="section-header">
        <h2 class="section-title">🎯 Recomendado para ti</h2>
        <a href="pages/dashboard.php" class="view-all">Ver más</a>
    </div>
    <div class="content-grid">
        <?php foreach ($recommendations as $content): ?>
            <?php include 'includes/content-card.php'; ?>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- Películas Destacadas -->
<?php if (!empty($recentMovies)): ?>
<section class="content-section">
    <div class="section-header">
        <h2 class="section-title">🎬 Películas Destacadas</h2>
        <a href="pages/movies.php" class="view-all">Ver todas</a>
    </div>
    <div class="content-grid">
        <?php foreach ($recentMovies as $content): ?>
            <?php include 'includes/content-card.php'; ?>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- Series Populares -->
<?php if (!empty($recentSeries)): ?>
<section class="content-section">
    <div class="section-header">
        <h2 class="section-title">📺 Series Populares</h2>
        <a href="pages/series.php" class="view-all">Ver todas</a>
    </div>
    <div class="content-grid">
        <?php foreach ($recentSeries as $content): ?>
            <?php include 'includes/content-card.php'; ?>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- Call to Action para usuarios no registrados -->
<?php if (!$user): ?>
<section class="cta-section">
    <div class="cta-content">
        <h2>¡Únete a SceneIQ hoy!</h2>
        <p>Recibe recomendaciones personalizadas, escribe reseñas y conecta con otros cinéfilos.</p>
        <div class="cta-buttons">
            <a href="pages/register.php" class="btn btn-primary btn-large">Registrarse Gratis</a>
            <a href="pages/login.php" class="btn btn-secondary btn-large">Iniciar Sesión</a>
        </div>
    </div>
</section>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>