-- Création de la table notifications
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    
    -- Type de notification
    notification_type ENUM(
        'validation_request',
        'validation_approved',
        'validation_rejected',
        'new_review',
        'new_report',
        'report_resolved',
        'account_suspended',
        'account_reactivated',
        'new_message',
        'system_announcement',
        'welcome',
        'reminder'
    ) NOT NULL,
    
    -- Contenu de la notification
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    
    -- Données additionnelles (JSON)
    data JSON NULL,
    
    -- Liens et actions
    action_url VARCHAR(500) NULL,
    action_text VARCHAR(100) NULL,
    
    -- Statut de la notification
    is_read BOOLEAN DEFAULT FALSE,
    is_seen BOOLEAN DEFAULT FALSE,
    
    -- Priorité
    priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    
    -- Canaux de notification
    sent_via_email BOOLEAN DEFAULT FALSE,
    sent_via_sms BOOLEAN DEFAULT FALSE,
    sent_via_push BOOLEAN DEFAULT FALSE,
    
    -- Métadonnées d'envoi
    email_sent_at TIMESTAMP NULL,
    sms_sent_at TIMESTAMP NULL,
    push_sent_at TIMESTAMP NULL,
    
    -- Expiration
    expires_at TIMESTAMP NULL,
    
    -- Métadonnées
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL,
    
    -- Relations et contraintes
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    -- Index pour les performances
    INDEX idx_user_id (user_id),
    INDEX idx_notification_type (notification_type),
    INDEX idx_is_read (is_read),
    INDEX idx_is_seen (is_seen),
    INDEX idx_priority (priority),
    INDEX idx_created_at (created_at),
    INDEX idx_expires_at (expires_at),
    INDEX idx_user_unread (user_id, is_read),
    INDEX idx_user_type (user_id, notification_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;