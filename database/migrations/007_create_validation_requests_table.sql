-- Création de la table validation_requests
CREATE TABLE IF NOT EXISTS validation_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    provider_id INT NOT NULL,
    chef_quartier_id INT NOT NULL,
    
    -- Type de demande
    request_type ENUM('profile_creation', 'portfolio_addition', 'profile_update', 'document_verification') NOT NULL,
    
    -- Données de la demande (JSON)
    request_data JSON NOT NULL,
    
    -- Fichiers associés
    attached_files JSON NULL, -- Array des fichiers joints
    
    -- Statut de la demande
    status ENUM('pending', 'approved', 'rejected', 'cancelled') DEFAULT 'pending',
    
    -- Commentaires et raisons
    provider_message TEXT NULL,
    chef_response TEXT NULL,
    rejection_reason TEXT NULL,
    
    -- Métadonnées de traitement
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at TIMESTAMP NULL,
    reviewed_by INT NULL, -- ID du chef qui a traité
    
    -- Priority (urgent, normal, low)
    priority ENUM('urgent', 'normal', 'low') DEFAULT 'normal',
    
    -- Métadonnées
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Relations et contraintes
    FOREIGN KEY (provider_id) REFERENCES providers(id) ON DELETE CASCADE,
    FOREIGN KEY (chef_quartier_id) REFERENCES chef_quartiers(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES chef_quartiers(id) ON DELETE SET NULL,
    
    -- Index pour les performances
    INDEX idx_provider_id (provider_id),
    INDEX idx_chef_quartier_id (chef_quartier_id),
    INDEX idx_request_type (request_type),
    INDEX idx_status (status),
    INDEX idx_priority (priority),
    INDEX idx_submitted_at (submitted_at),
    INDEX idx_reviewed_at (reviewed_at),
    INDEX idx_status_priority (status, priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;