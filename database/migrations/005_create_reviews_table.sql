-- Création de la table reviews
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    provider_id INT NOT NULL,
    reviewer_id INT NOT NULL,
    
    -- Évaluation
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    
    -- Commentaire
    comment TEXT NOT NULL,
    
    -- Détails de l'évaluation
    service_quality_rating INT NULL CHECK (service_quality_rating >= 1 AND service_quality_rating <= 5),
    communication_rating INT NULL CHECK (communication_rating >= 1 AND communication_rating <= 5),
    punctuality_rating INT NULL CHECK (punctuality_rating >= 1 AND punctuality_rating <= 5),
    price_rating INT NULL CHECK (price_rating >= 1 AND price_rating <= 5),
    
    -- Recommandation
    would_recommend BOOLEAN DEFAULT TRUE,
    
    -- Photos de l'évaluation
    review_photos JSON NULL, -- Array des photos du travail réalisé
    
    -- Statut de l'avis
    is_visible BOOLEAN DEFAULT TRUE,
    is_verified BOOLEAN DEFAULT FALSE, -- Vérifié par le chef de quartier
    is_featured BOOLEAN DEFAULT FALSE, -- Mis en avant
    
    -- Modération
    is_reported BOOLEAN DEFAULT FALSE,
    report_count INT DEFAULT 0,
    moderation_notes TEXT NULL,
    
    -- Réponse du prestataire
    provider_response TEXT NULL,
    provider_responded_at TIMESTAMP NULL,
    
    -- Métadonnées
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Relations et contraintes
    FOREIGN KEY (provider_id) REFERENCES providers(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE,
    
    -- Un utilisateur ne peut évaluer qu'une seule fois le même prestataire
    UNIQUE KEY unique_review (provider_id, reviewer_id),
    
    -- Index pour les performances
    INDEX idx_provider_id (provider_id),
    INDEX idx_reviewer_id (reviewer_id),
    INDEX idx_rating (rating),
    INDEX idx_is_visible (is_visible),
    INDEX idx_is_verified (is_verified),
    INDEX idx_is_featured (is_featured),
    INDEX idx_created_at (created_at),
    INDEX idx_provider_rating (provider_id, rating),
    INDEX idx_provider_visible (provider_id, is_visible)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;