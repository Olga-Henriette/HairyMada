-- Création de la table reports
CREATE TABLE IF NOT EXISTS reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reporter_id INT NOT NULL,
    
    -- Entité signalée (prestataire OU chef de quartier)
    reported_provider_id INT NULL,
    reported_chef_id INT NULL,
    
    -- Type de signalement
    report_type ENUM('fraud', 'inappropriate_content', 'poor_service', 'harassment', 'spam', 'fake_profile', 'other') NOT NULL,
    
    -- Détails du signalement
    report_reason VARCHAR(255) NOT NULL,
    report_description TEXT NOT NULL,
    
    -- Preuves jointes
    evidence_files JSON NULL, -- Array des fichiers de preuve
    
    -- Statut du signalement
    status ENUM('pending', 'under_review', 'resolved', 'dismissed', 'escalated') DEFAULT 'pending',
    
    -- Résolution
    resolution_notes TEXT NULL,
    resolution_action ENUM('none', 'warning', 'suspension', 'permanent_ban', 'profile_correction') NULL,
    resolved_by INT NULL, -- ID du chef ou admin qui a résolu
    resolved_at TIMESTAMP NULL,
    
    -- Escalade vers l'administration
    escalated_to_admin BOOLEAN DEFAULT FALSE,
    escalation_reason TEXT NULL,
    escalated_at TIMESTAMP NULL,
    
    -- Gravité du signalement
    severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    
    -- Métadonnées
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Relations et contraintes
    FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reported_provider_id) REFERENCES providers(id) ON DELETE CASCADE,
    FOREIGN KEY (reported_chef_id) REFERENCES chef_quartiers(id) ON DELETE CASCADE,
    
    -- Contrainte : il faut signaler soit un prestataire soit un chef
    CHECK (
        (reported_provider_id IS NOT NULL AND reported_chef_id IS NULL) OR
        (reported_provider_id IS NULL AND reported_chef_id IS NOT NULL)
    ),
    
    -- Index pour les performances
    INDEX idx_reporter_id (reporter_id),
    INDEX idx_reported_provider_id (reported_provider_id),
    INDEX idx_reported_chef_id (reported_chef_id),
    INDEX idx_report_type (report_type),
    INDEX idx_status (status),
    INDEX idx_severity (severity),
    INDEX idx_escalated (escalated_to_admin),
    INDEX idx_created_at (created_at),
    INDEX idx_resolved_at (resolved_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;