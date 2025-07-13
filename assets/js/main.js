// SceneIQ Main JavaScript
class SceneIQ {
    constructor() {
        this.init();
        this.bindEvents();
        this.setupTheme();
    }

    init() {
        console.log('🎬 SceneIQ Initialized');
        this.currentTheme = document.body.getAttribute('data-theme') || 'dark';
        this.setupComponents();
        this.loadUserPreferences();
    }

    bindEvents() {
        // Navigation events
        this.setupNavigation();
        
        // Search functionality
        this.setupSearch();
        
        // Modal system
        this.setupModals();
        
        // User interactions
        this.setupUserInteractions();
        
        // Responsive handlers
        this.setupResponsive();
    }

    setupComponents() {
        // Inicializar componentes dinámicos
        this.initializeCards();
        this.initializeInfiniteScroll();
        this.initializeTooltips();
    }

    setupNavigation() {
        // User dropdown
        const userAvatar = document.querySelector('.user-avatar');
        const userDropdown = document.getElementById('userDropdown');

        if (userAvatar && userDropdown) {
            userAvatar.addEventListener('click', (e) => {
                e.stopPropagation();
                userDropdown.classList.toggle('active');
            });

            document.addEventListener('click', () => {
                userDropdown.classList.remove('active');
            });
        }

        // Mobile menu toggle
        this.setupMobileMenu();
    }

    setupMobileMenu() {
        const navLinks = document.querySelector('.nav-links');
        if (window.innerWidth <= 768 && navLinks) {
            // Crear botón de menú móvil si no existe
            let mobileMenuBtn = document.querySelector('.mobile-menu-btn');
            if (!mobileMenuBtn) {
                mobileMenuBtn = document.createElement('button');
                mobileMenuBtn.className = 'mobile-menu-btn';
                mobileMenuBtn.innerHTML = '☰';
                mobileMenuBtn.style.cssText = `
                    background: var(--glass-bg);
                    border: 1px solid rgba(255,255,255,0.2);
                    border-radius: 8px;
                    color: var(--text-primary);
                    padding: 0.5rem;
                    cursor: pointer;
                `;
                
                const authButtons = document.querySelector('.auth-buttons');
                authButtons.insertBefore(mobileMenuBtn, authButtons.firstChild);
                
                mobileMenuBtn.addEventListener('click', () => {
                    navLinks.classList.toggle('mobile-active');
                });
            }
        }
    }

    setupSearch() {
        const searchInput = document.querySelector('.search-input');
        const searchForm = document.querySelector('.search-form');
        
        if (searchForm) {
            searchForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const query = searchInput.value.trim();
                if (query) {
                    this.performSearch(query);
                }
            });
        }

        // Búsqueda en tiempo real
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                const query = e.target.value.trim();
                
                if (query.length >= 3) {
                    searchTimeout = setTimeout(() => {
                        this.performLiveSearch(query);
                    }, 300);
                } else {
                    this.hideLiveSearch();
                }
            });
        }
    }

    async performSearch(query) {
        try {
            const response = await fetch('api/search.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': window.csrfToken
                },
                body: JSON.stringify({ query: query })
            });

            const data = await response.json();
            
            if (data.success) {
                // Redirigir a página de resultados o mostrar resultados
                window.location.href = `pages/search.php?q=${encodeURIComponent(query)}`;
            }
        } catch (error) {
            console.error('Error en búsqueda:', error);
            this.showNotification('Error al realizar la búsqueda', 'error');
        }
    }

    async performLiveSearch(query) {
        try {
            const response = await fetch('api/live-search.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': window.csrfToken
                },
                body: JSON.stringify({ query: query, limit: 5 })
            });

            const data = await response.json();
            
            if (data.success && data.results.length > 0) {
                this.showLiveSearchResults(data.results);
            } else {
                this.hideLiveSearch();
            }
        } catch (error) {
            console.error('Error en búsqueda en vivo:', error);
        }
    }

    showLiveSearchResults(results) {
        let dropdown = document.querySelector('.live-search-dropdown');
        
        if (!dropdown) {
            dropdown = document.createElement('div');
            dropdown.className = 'live-search-dropdown';
            dropdown.style.cssText = `
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: var(--card-bg);
                border: 1px solid rgba(255,255,255,0.2);
                border-radius: var(--border-radius-small);
                backdrop-filter: blur(20px);
                max-height: 300px;
                overflow-y: auto;
                z-index: 1001;
                margin-top: 0.5rem;
            `;
            document.querySelector('.search-bar').appendChild(dropdown);
        }

        dropdown.innerHTML = results.map(item => `
            <div class="live-search-item" onclick="window.location.href='pages/content.php?id=${item.id}'" 
                 style="padding: 0.75rem; cursor: pointer; border-bottom: 1px solid rgba(255,255,255,0.05); transition: var(--transition);">
                <div style="display: flex; gap: 0.75rem; align-items: center;">
                    <img src="${item.poster || 'assets/images/placeholder.jpg'}" 
                         style="width: 40px; height: 60px; object-fit: cover; border-radius: 4px;">
                    <div>
                        <div style="color: var(--text-primary); font-weight: 600; font-size: 0.9rem;">${item.title}</div>
                        <div style="color: var(--text-secondary); font-size: 0.8rem;">${item.year} • ${item.type === 'movie' ? 'Película' : 'Serie'}</div>
                    </div>
                </div>
            </div>
        `).join('');

        dropdown.style.display = 'block';
    }

    hideLiveSearch() {
        const dropdown = document.querySelector('.live-search-dropdown');
        if (dropdown) {
            dropdown.style.display = 'none';
        }
    }

    setupModals() {
        // Review modal
        window.openReviewModal = (contentId = null) => {
            this.openReviewModal(contentId);
        };

        window.closeReviewModal = () => {
            this.closeModal('reviewModal');
        };

        // Cerrar modales con ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeAllModals();
            }
        });

        // Cerrar modales clickeando fuera
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal')) {
                this.closeModal(e.target.id);
            }
        });
    }

    openReviewModal(contentId = null) {
        const modal = document.getElementById('reviewModal');
        if (!modal) return;

        if (contentId) {
            // Pre-llenar con contenido específico
            this.loadContentForReview(contentId);
        }

        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        
        // Focus en el primer input
        setTimeout(() => {
            const firstInput = modal.querySelector('input, textarea');
            if (firstInput) firstInput.focus();
        }, 100);
    }

    closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    closeAllModals() {
        const modals = document.querySelectorAll('.modal.active');
        modals.forEach(modal => {
            modal.classList.remove('active');
        });
        document.body.style.overflow = '';
    }

    async loadContentForReview(contentId) {
        try {
            const response = await fetch('api/content.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': window.csrfToken
                },
                body: JSON.stringify({ id: contentId })
            });

            const data = await response.json();
            
            if (data.success) {
                this.populateReviewForm(data.content);
            }
        } catch (error) {
            console.error('Error cargando contenido:', error);
        }
    }

    populateReviewForm(content) {
        const selectedContent = document.getElementById('selectedContent');
        const contentPreview = selectedContent.querySelector('.content-preview');
        
        contentPreview.innerHTML = `
            <img src="${content.poster || 'assets/images/placeholder.jpg'}" 
                 style="width: 80px; height: 120px; object-fit: cover; border-radius: 8px;">
            <div>
                <h4 style="color: var(--text-primary); margin-bottom: 0.5rem;">${content.title}</h4>
                <p style="color: var(--text-secondary); font-size: 0.9rem;">${content.year} • ${content.type === 'movie' ? 'Película' : 'Serie'}</p>
            </div>
        `;
        
        selectedContent.style.display = 'block';
        selectedContent.dataset.contentId = content.id;
        
        // Ocultar búsqueda
        document.getElementById('contentSearch').style.display = 'none';
    }

    setupUserInteractions() {
        // Sistema de rating con estrellas
        this.setupRatingSystem();
        
        // Wishlist functionality
        window.addToWatchlist = (contentId) => {
            this.addToWatchlist(contentId);
        };

        // Like reviews
        window.likeReview = (reviewId) => {
            this.likeReview(reviewId);
        };

        // Random recommendation
        window.getRandomRecommendation = () => {
            this.getRandomRecommendation();
        };
    }

    setupRatingSystem() {
        const stars = document.querySelectorAll('#ratingStars .star');
        const ratingValue = document.getElementById('ratingValue');
        let currentRating = 0;

        stars.forEach((star, index) => {
            star.addEventListener('mouseenter', () => {
                this.highlightStars(index + 1);
            });

            star.addEventListener('mouseleave', () => {
                this.highlightStars(currentRating);
            });

            star.addEventListener('click', () => {
                currentRating = index + 1;
                this.highlightStars(currentRating);
                if (ratingValue) {
                    ratingValue.textContent = `${currentRating}/10`;
                }
            });
        });
    }

    highlightStars(rating) {
        const stars = document.querySelectorAll('#ratingStars .star');
        stars.forEach((star, index) => {
            if (index < rating) {
                star.classList.add('active');
            } else {
                star.classList.remove('active');
            }
        });
    }

    async addToWatchlist(contentId) {
        if (!window.userId) {
            window.location.href = 'pages/login.php';
            return;
        }

        try {
            const response = await fetch('api/user-lists.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': window.csrfToken
                },
                body: JSON.stringify({ 
                    content_id: contentId, 
                    list_type: 'watchlist',
                    action: 'add'
                })
            });

            const data = await response.json();
            
            if (data.success) {
                this.showNotification('Agregado a tu lista de seguimiento', 'success');
                this.updateWatchlistButton(contentId, true);
            } else {
                this.showNotification(data.message || 'Error al agregar a la lista', 'error');
            }
        } catch (error) {
            console.error('Error adding to watchlist:', error);
            this.showNotification('Error al agregar a la lista', 'error');
        }
    }

    async likeReview(reviewId) {
        if (!window.userId) {
            window.location.href = 'pages/login.php';
            return;
        }

        try {
            const response = await fetch('api/like-review.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': window.csrfToken
                },
                body: JSON.stringify({ review_id: reviewId })
            });

            const data = await response.json();
            
            if (data.success) {
                const likeButton = document.querySelector(`[onclick="likeReview(${reviewId})"]`);
                const countSpan = likeButton.querySelector('.like-count');
                countSpan.textContent = data.new_count;
                likeButton.classList.add('liked');
                likeButton.onclick = null; // Prevent multiple likes
            }
        } catch (error) {
            console.error('Error liking review:', error);
        }
    }

    async getRandomRecommendation() {
        try {
            const response = await fetch('api/random-recommendation.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': window.csrfToken
                }
            });

            const data = await response.json();
            
            if (data.success && data.content) {
                this.showRandomRecommendationModal(data.content);
            } else {
                this.showNotification('No se pudo obtener una recomendación', 'warning');
            }
        } catch (error) {
            console.error('Error getting random recommendation:', error);
            this.showNotification('Error al obtener recomendación', 'error');
        }
    }

    showRandomRecommendationModal(content) {
        const modal = document.createElement('div');
        modal.className = 'modal active';
        modal.innerHTML = `
            <div class="modal-content" style="max-width: 600px;">
                <div class="modal-header">
                    <h3>🎲 Tu Recomendación Sorpresa</h3>
                    <button class="modal-close" onclick="this.closest('.modal').remove()">&times;</button>
                </div>
                <div class="modal-body">
                    <div style="display: flex; gap: 1.5rem; align-items: flex-start;">
                        <img src="${content.poster || 'assets/images/placeholder.jpg'}" 
                             style="width: 150px; height: 225px; object-fit: cover; border-radius: 12px; flex-shrink: 0;">
                        <div style="flex: 1;">
                            <h4 style="color: var(--text-primary); font-size: 1.5rem; margin-bottom: 0.5rem;">${content.title}</h4>
                            <p style="color: var(--text-secondary); margin-bottom: 1rem;">
                                ${content.year} • ${content.type === 'movie' ? 'Película' : 'Serie'}
                                ${content.duration ? ' • ' + content.duration : ''}
                            </p>
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                                <span style="color: #ffd700;">⭐</span>
                                <span style="color: var(--text-primary); font-weight: 600;">${content.imdb_rating}</span>
                            </div>
                            <p style="color: var(--text-secondary); line-height: 1.6; margin-bottom: 1.5rem;">
                                ${content.synopsis || 'Sin descripción disponible.'}
                            </p>
                            <div style="display: flex; gap: 1rem;">
                                <a href="pages/content.php?id=${content.id}" class="btn btn-primary">Ver Detalles</a>
                                <button class="btn btn-secondary" onclick="addToWatchlist(${content.id}); this.closest('.modal').remove();">+ Mi Lista</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        document.body.style.overflow = 'hidden';
    }

    setupTheme() {
        window.toggleTheme = () => {
            this.toggleTheme();
        };
    }

    toggleTheme() {
        const newTheme = this.currentTheme === 'dark' ? 'light' : 'dark';
        this.setTheme(newTheme);
        this.saveThemePreference(newTheme);
    }

    setTheme(theme) {
        document.body.setAttribute('data-theme', theme);
        this.currentTheme = theme;
        
        // Actualizar icono del toggle
        const themeToggle = document.querySelector('.theme-toggle');
        if (themeToggle) {
            themeToggle.textContent = theme === 'dark' ? '☀️' : '🌙';
        }
    }

    async saveThemePreference(theme) {
        if (!window.userId) return;

        try {
            await fetch('api/user-preferences.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': window.csrfToken
                },
                body: JSON.stringify({ theme: theme })
            });
        } catch (error) {
            console.error('Error saving theme preference:', error);
        }
    }

    loadUserPreferences() {
        // Cargar preferencias del usuario desde localStorage como fallback
        const savedTheme = localStorage.getItem('sceneiq_theme');
        if (savedTheme && savedTheme !== this.currentTheme) {
            this.setTheme(savedTheme);
        }
    }

    setupResponsive() {
        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                this.handleResize();
            }, 250);
        });
    }

    handleResize() {
        // Reconfigurar menú móvil
        this.setupMobileMenu();
        
        // Ajustar grids
        this.adjustGridLayouts();
    }

    adjustGridLayouts() {
        const grids = document.querySelectorAll('.content-grid');
        grids.forEach(grid => {
            const width = grid.offsetWidth;
            const minCardWidth = 250;
            const columns = Math.floor(width / minCardWidth);
            grid.style.gridTemplateColumns = `repeat(${Math.max(1, columns)}, 1fr)`;
        });
    }

    initializeCards() {
        // Lazy loading para imágenes
        this.setupLazyLoading();
        
        // Animaciones de hover mejoradas
        this.setupCardAnimations();
    }

    setupLazyLoading() {
        const images = document.querySelectorAll('img[data-src]');
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.add('fade-in');
                    observer.unobserve(img);
                }
            });
        });

        images.forEach(img => imageObserver.observe(img));
    }

    setupCardAnimations() {
        const cards = document.querySelectorAll('.content-card');
        cards.forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.style.transform = 'translateY(-10px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', () => {
                card.style.transform = 'translateY(0) scale(1)';
            });
        });
    }

    initializeInfiniteScroll() {
        const loadMoreButtons = document.querySelectorAll('.load-more-btn');
        loadMoreButtons.forEach(button => {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        button.click();
                        observer.unobserve(button);
                    }
                });
            }, { threshold: 0.1 });

            observer.observe(button);
        });
    }

    initializeTooltips() {
        const tooltipTriggers = document.querySelectorAll('[data-tooltip]');
        tooltipTriggers.forEach(trigger => {
            trigger.addEventListener('mouseenter', (e) => {
                this.showTooltip(e.target, e.target.dataset.tooltip);
            });
            
            trigger.addEventListener('mouseleave', () => {
                this.hideTooltip();
            });
        });
    }

    showTooltip(element, text) {
        let tooltip = document.querySelector('.tooltip');
        if (!tooltip) {
            tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.style.cssText = `
                position: absolute;
                background: var(--card-bg);
                backdrop-filter: blur(20px);
                border: 1px solid rgba(255,255,255,0.2);
                border-radius: 6px;
                padding: 0.5rem;
                color: var(--text-primary);
                font-size: 0.8rem;
                z-index: 2000;
                pointer-events: none;
                white-space: nowrap;
            `;
            document.body.appendChild(tooltip);
        }

        tooltip.textContent = text;
        const rect = element.getBoundingClientRect();
        tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
        tooltip.style.top = rect.top - tooltip.offsetHeight - 8 + 'px';
        tooltip.style.opacity = '1';
    }

    hideTooltip() {
        const tooltip = document.querySelector('.tooltip');
        if (tooltip) {
            tooltip.style.opacity = '0';
        }
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type}`;
        notification.innerHTML = `
            <span>${message}</span>
            <button class="alert-close" onclick="this.parentElement.remove()">×</button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }

    updateWatchlistButton(contentId, isInWatchlist) {
        const buttons = document.querySelectorAll(`[onclick*="addToWatchlist(${contentId})"]`);
        buttons.forEach(button => {
            if (isInWatchlist) {
                button.textContent = '✓ En Lista';
                button.classList.add('in-watchlist');
                button.onclick = () => this.removeFromWatchlist(contentId);
            }
        });
    }

    async removeFromWatchlist(contentId) {
        try {
            const response = await fetch('api/user-lists.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': window.csrfToken
                },
                body: JSON.stringify({ 
                    content_id: contentId, 
                    list_type: 'watchlist',
                    action: 'remove'
                })
            });

            const data = await response.json();
            
            if (data.success) {
                this.showNotification('Removido de tu lista', 'success');
                this.updateWatchlistButton(contentId, false);
            }
        } catch (error) {
            console.error('Error removing from watchlist:', error);
        }
    }
}

// Funciones globales para compatibilidad
window.closeAlert = function() {
    const alert = document.getElementById('alertMessage');
    if (alert) alert.remove();
};

window.toggleUserDropdown = function() {
    const dropdown = document.getElementById('userDropdown');
    if (dropdown) dropdown.classList.toggle('active');
};

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    window.sceneIQApp = new SceneIQ();
});

// Exportar para uso en otros scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SceneIQ;
}