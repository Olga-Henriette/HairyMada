<?php

/**
 * Configuration de la base de données HairyMada
 * 
 * Ce fichier gère toutes les connexions à la base de données
 * et les paramètres de configuration MySQL
 * 
 * @package HairyMada
 * @author Olga Henriette VOLANIAINA
 * @version 1.0
 */

/**
 * Chargement des variables d'environnement
 * une version simple sans Dotenv
 */
function loadEnv($path) {
    if (!file_exists($path)) {
        throw new Exception("Fichier .env introuvable : $path");
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) continue; // Ignorer les commentaires
        if (strpos($line, '=') === false) continue; // Ignorer les lignes sans =
        
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        
        if (!array_key_exists($key, $_ENV)) {
            $_ENV[$key] = $value;
        }
    }
}

// Charger les variables d'environnement
loadEnv(__DIR__ . '/../.env');

/**
 * Configuration principale de la base de données
 */
$config = [
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

/**
 * Classe de gestion de la base de données
 */
class Database
{
    private static $instance = null;
    private $connection;
    private $config;
    
    /**
     * Constructeur privé pour le pattern Singleton
     */
    private function __construct()
    {
        global $config;
        $this->config = $config;
        $this->connect();
    }
    
    /**
     * Obtenir l'instance unique de la base de données
     * 
     * @return Database Instance unique
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Établir la connexion à la base de données
     * 
     * @throws PDOException Si la connexion échoue
     */
    private function connect(): void
    {
        $config = $this->config['connections'][$this->config['default']];
        
        try {
            $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
            
            $this->connection = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                $config['options']
            );
            
            // Configuration additionnelle
            $this->connection->exec("SET time_zone = '+03:00'"); // Fuseau horaire Madagascar
            
        } catch (PDOException $e) {
            // Log de l'erreur
            error_log("Erreur de connexion à la base de données: " . $e->getMessage());
            throw new PDOException("Impossible de se connecter à la base de données: " . $e->getMessage());
        }
    }
    
    /**
     * Obtenir la connexion PDO
     * 
     * @return PDO Instance de connexion
     */
    public function getConnection(): PDO
    {
        return $this->connection;
    }
    
    /**
     * Exécuter une requête préparée
     * 
     * @param string $query Requête SQL
     * @param array $params Paramètres de la requête
     * @return PDOStatement Résultat de la requête
     */
    public function query(string $query, array $params = []): PDOStatement
    {
        try {
            $statement = $this->connection->prepare($query);
            $statement->execute($params);
            
            return $statement;
            
        } catch (PDOException $e) {
            error_log("Erreur de requête SQL: " . $e->getMessage() . " - Query: " . $query);
            throw $e;
        }
    }
    
    /**
     * Obtenir le dernier ID inséré
     * 
     * @return string Dernier ID
     */
    public function getLastInsertId(): string
    {
        return $this->connection->lastInsertId();
    }
    
    /**
     * Commencer une transaction
     */
    public function beginTransaction(): void
    {
        $this->connection->beginTransaction();
    }
    
    /**
     * Valider une transaction
     */
    public function commit(): void
    {
        $this->connection->commit();
    }
    
    /**
     * Annuler une transaction
     */
    public function rollBack(): void
    {
        $this->connection->rollBack();
    }
    
    /**
     * Vérifier si une transaction est active
     * 
     * @return bool Statut de la transaction
     */
    public function inTransaction(): bool
    {
        return $this->connection->inTransaction();
    }
    
    /**
     * Fermer la connexion
     */
    public function close(): void
    {
        $this->connection = null;
    }
    
    /**
     * Empêcher le clonage
     */
    private function __clone() {}
    
    /**
     * Empêcher la désérialisation
     */
    public function __wakeup()
    {
        throw new Exception("Impossible de désérialiser un singleton");
    }
}

// Retourner la configuration pour compatibilité
return $config;