-- SceneIQ Database Schema
CREATE DATABASE sceneiq_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE sceneiq_db;

-- Tabla de usuarios
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    avatar VARCHAR(255) DEFAULT NULL,
    theme_preference ENUM('dark', 'light') DEFAULT 'dark',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE
);

-- Tabla de géneros
CREATE TABLE genres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    slug VARCHAR(50) UNIQUE NOT NULL,
    color VARCHAR(7) DEFAULT '#667eea',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de contenido (películas/series)
CREATE TABLE content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    type ENUM('movie', 'series') NOT NULL,
    year INT NOT NULL,
    duration VARCHAR(20),
    synopsis TEXT,
    poster VARCHAR(255),
    backdrop VARCHAR(255),
    imdb_rating DECIMAL(3,1) DEFAULT 0.0,
    tmdb_id VARCHAR(20),
    trailer_url VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de géneros por contenido
CREATE TABLE content_genres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    content_id INT NOT NULL,
    genre_id INT NOT NULL,
    FOREIGN KEY (content_id) REFERENCES content(id) ON DELETE CASCADE,
    FOREIGN KEY (genre_id) REFERENCES genres(id) ON DELETE CASCADE,
    UNIQUE KEY unique_content_genre (content_id, genre_id)
);

-- Tabla de reseñas
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    content_id INT NOT NULL,
    rating DECIMAL(2,1) NOT NULL CHECK (rating >= 0.0 AND rating <= 10.0),
    review_text TEXT,
    spoiler_alert BOOLEAN DEFAULT FALSE,
    is_approved BOOLEAN DEFAULT TRUE,
    helpful_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (content_id) REFERENCES content(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_content_review (user_id, content_id)
);

-- Tabla de preferencias de usuario
CREATE TABLE user_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    genre_id INT NOT NULL,
    preference_weight DECIMAL(3,2) DEFAULT 1.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (genre_id) REFERENCES genres(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_genre_preference (user_id, genre_id)
);

-- Tabla de listas de usuario (watchlist, favorites, etc.)
CREATE TABLE user_lists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    content_id INT NOT NULL,
    list_type ENUM('watchlist', 'favorites', 'watched') NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (content_id) REFERENCES content(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_content_list (user_id, content_id, list_type)
);

-- Tabla de actividad de usuario (para analytics)
CREATE TABLE user_activity (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action_type ENUM('view', 'search', 'review', 'list_add', 'login') NOT NULL,
    content_id INT NULL,
    metadata JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (content_id) REFERENCES content(id) ON DELETE SET NULL
);

-- Insertar géneros por defecto
INSERT INTO genres (name, slug, color) VALUES
('Acción', 'accion', '#ff6b6b'),
('Drama', 'drama', '#4ecdc4'),
('Comedia', 'comedia', '#45b7d1'),
('Thriller', 'thriller', '#96ceb4'),
('Sci-Fi', 'sci-fi', '#ffeaa7'),
('Romance', 'romance', '#fd79a8'),
('Horror', 'horror', '#6c5ce7'),
('Documentales', 'documentales', '#a29bfe'),
('Animación', 'animacion', '#fd63a2'),
('Crimen', 'crimen', '#636e72');

-- Insertar usuario administrador por defecto
INSERT INTO users (username, email, password, full_name, role) VALUES
('admin', 'admin@sceneiq.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador SceneIQ', 'admin');

-- Contenido de ejemplo
INSERT INTO content (title, slug, type, year, duration, synopsis, imdb_rating) VALUES
('The Dark Knight', 'the-dark-knight', 'movie', 2008, '152 min', 'Batman debe enfrentar a su mayor enemigo en esta épica historia de heroísmo y caos.', 9.0),
('Breaking Bad', 'breaking-bad', 'series', 2008, '5 temporadas', 'Un profesor de química se convierte en fabricante de metanfetaminas tras descubrir que tiene cáncer.', 9.5),
('Inception', 'inception', 'movie', 2010, '148 min', 'Un ladrón que roba secretos corporativos mediante tecnología de sueños compartidos.', 8.8),
('Stranger Things', 'stranger-things', 'series', 2016, '4 temporadas', 'Un grupo de niños descubre fuerzas sobrenaturales y experimentos secretos del gobierno.', 8.7),
('The Godfather', 'the-godfather', 'movie', 1972, '175 min', 'La saga de la familia Corleone bajo el patriarca Vito Corleone.', 9.2);