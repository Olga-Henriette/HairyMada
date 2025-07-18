-- Création de la table users (clients)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    address TEXT NOT NULL,
    quartier VARCHAR(100) NOT NULL,
    
    -- Statut de vérification
    email_verified_at TIMESTAMP NULL,
    phone_verified_at TIMESTAMP NULL,
    email_verification_token VARCHAR(255) NULL,
    phone_verification_token VARCHAR(6) NULL,
    
    -- Statut du compte
    is_active BOOLEAN DEFAULT TRUE,
    is_blocked BOOLEAN DEFAULT FALSE,
    blocked_reason TEXT NULL,
    blocked_at TIMESTAMP NULL,
    
    -- Métadonnées
    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Index pour les performances
    INDEX idx_email (email),
    INDEX idx_phone (phone),
    INDEX idx_quartier (quartier),
    INDEX idx_active (is_active),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;