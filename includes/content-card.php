<!-- Componente reutilizable para mostrar tarjetas de contenido -->
<div class="content-card" data-content-id="<?php echo $content['id']; ?>">
    <div class="card-image" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <?php if ($content['poster']): ?>
            <img src="<?php echo $content['poster']; ?>" alt="<?php echo escape($content['title']); ?>" loading="lazy">
        <?php endif; ?>
        <div class="card-overlay">
            <button class="quick-add-btn" onclick="addToWatchlist(<?php echo $content['id']; ?>)" title="Agregar a lista">+</button>
        </div>
    </div>
    
    <div class="card-content">
        <h3 class="card-title">
            <a href="pages/content.php?id=<?php echo $content['id']; ?>">
                <?php echo escape($content['title']); ?>
            </a>
        </h3>
        
        <div class="card-meta">
            <span class="card-year"><?php echo $content['year']; ?></span>
            <div class="card-rating">
                <span class="star">⭐</span>
                <span class="rating-value">
                    <?php echo number_format($content['avg_rating'] ?? $content['imdb_rating'], 1); ?>
                </span>
                <?php if (isset($content['review_count']) && $content['review_count'] > 0): ?>
                    <span class="review-count">(<?php echo $content['review_count']; ?>)</span>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card-type">
            <span class="type-badge type-<?php echo $content['type']; ?>">
                <?php echo $content['type'] === 'movie' ? '🎬 Película' : '📺 Serie'; ?>
            </span>
        </div>
        
        <?php if ($content['synopsis']): ?>
            <p class="card-description">
                <?php echo escape($sceneiq->truncateText($content['synopsis'], 120)); ?>
            </p>
        <?php endif; ?>
        
        <?php if (isset($content['genres']) && $content['genres']): ?>
            <div class="card-genres">
                <?php 
                $genreList = explode(',', $content['genres']);
                foreach (array_slice($genreList, 0, 3) as $genre): ?>
                    <span class="genre-pill"><?php echo escape(trim($genre)); ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div class="card-actions">
            <?php if ($user): ?>
                <button class="btn-small btn-review" onclick="openReviewModal(<?php echo $content['id']; ?>)">
                    Reseñar
                </button>
                <button class="btn-small btn-wishlist" onclick="addToWatchlist(<?php echo $content['id']; ?>)">
                    + Lista
                </button>
            <?php else: ?>
                <a href="pages/content.php?id=<?php echo $content['id']; ?>" class="btn-small btn-review">
                    Ver Detalles
                </a>
                <a href="pages/login.php" class="btn-small btn-wishlist">
                    Iniciar Sesión
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>