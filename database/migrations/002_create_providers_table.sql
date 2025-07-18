-- Création de la table providers
CREATE TABLE IF NOT EXISTS providers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    chef_quartier_id INT NOT NULL,
    
    -- ID unique du prestataire
    provider_id VARCHAR(20) NOT NULL UNIQUE, -- Format: QTR-YYYY-XXXX
    
    -- Informations du service
    service_category VARCHAR(100) NOT NULL, -- Ex: Menuiserie, Coiffure, Réparation, etc.
    service_title VARCHAR(200) NOT NULL,
    service_description TEXT NOT NULL,
    service_tags JSON NULL, -- Tags pour la recherche
    
    -- Localisation
    service_address TEXT NOT NULL,
    service_quartier VARCHAR(100) NOT NULL,
    service_region VARCHAR(100) NOT NULL,
    
    -- Médias
    profile_photo VARCHAR(255) NULL,
    cover_photo VARCHAR(255) NULL,
    portfolio_images JSON NULL, -- Array des images du portfolio
    portfolio_videos JSON NULL, -- Array des vidéos du portfolio
    
    -- Informations de contact
    contact_phone VARCHAR(20) NOT NULL,
    contact_email VARCHAR(255) NULL,
    contact_whatsapp VARCHAR(20) NULL,
    
    -- Tarification
    price_range VARCHAR(50) NULL, -- Ex: 10000-50000, Sur devis, etc.
    currency VARCHAR(3) DEFAULT 'MGA',
    
    -- Statut de vérification
    is_verified BOOLEAN DEFAULT FALSE,
    verified_at TIMESTAMP NULL,
    verification_document VARCHAR(255) NULL,
    
    -- Statut du compte
    is_active BOOLEAN DEFAULT TRUE,
    is_featured BOOLEAN DEFAULT FALSE, -- Mis en avant
    is_suspended BOOLEAN DEFAULT FALSE,
    suspension_reason TEXT NULL,
    suspended_at TIMESTAMP NULL,
    
    -- Statistiques
    total_views INT DEFAULT 0,
    total_contacts INT DEFAULT 0,
    total_likes INT DEFAULT 0,
    average_rating DECIMAL(3,2) DEFAULT 0.00,
    total_reviews INT DEFAULT 0,
    
    -- Métadonnées
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Relations et contraintes
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (chef_quartier_id) REFERENCES chef_quartiers(id) ON DELETE RESTRICT,
    
    -- Index pour les performances
    INDEX idx_user_id (user_id),
    INDEX idx_chef_quartier_id (chef_quartier_id),
    INDEX idx_provider_id (provider_id),
    INDEX idx_category (service_category),
    INDEX idx_quartier (service_quartier),
    INDEX idx_region (service_region),
    INDEX idx_verified (is_verified),
    INDEX idx_active (is_active),
    INDEX idx_featured (is_featured),
    INDEX idx_rating (average_rating),
    INDEX idx_created_at (created_at),
    
    -- Index de recherche full-text
    FULLTEXT INDEX idx_search (service_title, service_description, service_tags)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;