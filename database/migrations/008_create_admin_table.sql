-- Création de la table admins
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    
    -- Rôle administratif
    admin_role ENUM('super_admin', 'admin', 'moderator') NOT NULL DEFAULT 'admin',
    
    -- Permissions
    permissions JSON NOT NULL, -- Array des permissions spécifiques
    
    -- Informations d'accès
    last_login_at TIMESTAMP NULL,
    login_attempts INT DEFAULT 0,
    locked_until TIMESTAMP NULL,
    
    -- Audit trail
    actions_count INT DEFAULT 0,
    last_action_at TIMESTAMP NULL,
    
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
    INDEX idx_admin_role (admin_role),
    INDEX idx_is_active (is_active),
    INDEX idx_is_suspended (is_suspended),
    INDEX idx_last_login (last_login_at),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Création de la table admin_actions pour l'audit
CREATE TABLE IF NOT EXISTS admin_actions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    
    -- Action effectuée
    action_type VARCHAR(100) NOT NULL,
    action_description TEXT NOT NULL,
    
    -- Entité affectée
    target_type VARCHAR(100) NULL, -- user, provider, chef_quartier, etc.
    target_id INT NULL,
    
    -- Données de l'action
    action_data JSON NULL,
    
    -- Adresse IP et user agent
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    
    -- Métadonnées
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Relations et contraintes
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE,
    
    -- Index pour les performances
    INDEX idx_admin_id (admin_id),
    INDEX idx_action_type (action_type),
    INDEX idx_target (target_type, target_id),
    INDEX idx_created_at (created_at),
    INDEX idx_admin_action (admin_id, action_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;