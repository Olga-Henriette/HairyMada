<?php

/**
 * Configuration de la base de données HairyMada
 * * Ce fichier gère toutes les connexions à la base de données
 * et les paramètres de configuration MySQL.
 * Il ne contient que le tableau de configuration, les variables d'environnement
 * sont chargées globalement, et la classe Database est dans son propre fichier.
 * * @package HairyMada
 * @author Olga Henriette VOLANIAINA
 * @version 1.1
 */

// Utilise $_ENV directement car Dotenv est chargé dans public/index.php

return [
    // Connexion par défaut
    'default' => $_ENV['DB_CONNECTION'] ?? 'mysql',
    
    // Configurations des connexions
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'port' => $_ENV['DB_PORT'] ?? '3306',
            'database' => $_ENV['DB_DATABASE'] ?? 'hairymada_db',
            'username' => $_ENV['DB_USERNAME'] ?? 'root',
            'password' => $_ENV['DB_PASSWORD'] ?? 'password',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci'
            ]
        ]
    ],
    
    // Configuration des migrations
    'migrations' => [
        'table' => 'migrations',
        'path' => __DIR__ . '/../database/migrations'
    ],
    
    // Configuration du cache de requêtes
    'cache' => [
        'enabled' => true,
        'duration' => 3600, // 1 heure
        'prefix' => 'hairymada_query_'
    ],
    
    // Configuration des logs de requêtes
    'logging' => [
        'enabled' => ($_ENV['APP_DEBUG'] ?? 'false') === 'true',
        'file' => __DIR__ . '/../storage/logs/database.log',
        'slow_query_threshold' => 1000 // ms
    ]
];