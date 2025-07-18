-- Création de la table chef_quartiers
CREATE TABLE IF NOT EXISTS chef_quartiers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    
    -- Informations du quartier
    quartier_name VARCHAR(100) NOT NULL,
    region VARCHAR(100) NOT NULL,
    commune VARCHAR(100) NOT NULL,
    fokontany VARCHAR(100) NOT NULL,
    
    -- Code unique du chef de quartier
    chef_code VARCHAR(20) NOT NULL UNIQUE, -- Format: CHF-YYYY-XXXX
    
    -- Informations administratives
    appointment_date DATE NOT NULL,
    appointment_document VARCHAR(255) NULL, -- Scan du document de nomination
    
    -- Statistiques
    total_providers INT DEFAULT 0,
    total_validations INT DEFAULT 0,
    total_reports_handled INT DEFAULT 0,
    
    -- Statut
    is_active BOOLEAN DEFAULT TRUE,
    is_suspended BOOLEAN DEFAULT FALSE,
    suspended_reason TEXT NULL,
    suspended_at TIMESTAMP NULL,
    
    -- Métadonnées
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Relations et contraintes
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    -- Index pour les performances
    INDEX idx_user_id (user_id),
    INDEX idx_quartier (quartier_name),
    INDEX idx_region (region),
    INDEX idx_chef_code (chef_code),
    INDEX idx_active (is_active),
    UNIQUE KEY unique_quartier_chef (quartier_name, region, commune, fokontany)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;