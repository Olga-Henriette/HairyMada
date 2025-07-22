<?php

namespace App\Core\Database; 

use PDO;
use PDOException;
use Exception;

/**
 * Classe de gestion de la base de données (Singleton).
 *
 * Gère la connexion unique à la base de données et fournit des méthodes
 * pour l'exécution des requêtes SQL et la gestion des transactions.
 *
 * @package HairyMada\Core\Database
 * @author Olga Henriette VOLANIAINA
 * @version 1.1
 */
class Database
{
    private static ?Database $instance = null; 
    private ?PDO $connection = null; 
    private array $config;

    
    private function __construct()
    {
        
    }

    
    public static function init(array $config): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
            self::$instance->config = $config;
            self::$instance->connect(); 
        }
        return self::$instance;
    }

 
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            if (function_exists('log_error')) {
                log_error("Tentative d'obtenir l'instance de Database avant initialisation. Appelez Database::init() d'abord.");
            }
            throw new Exception("L'instance de la base de données n'a pas été initialisée. Appelez Database::init() en premier.");
        }
        return self::$instance;
    }

 
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

            $this->connection->exec("SET time_zone = '+03:00'"); 

        } catch (PDOException $e) {
            if (function_exists('log_error')) {
                log_error("Erreur de connexion à la base de données: " . $e->getMessage());
            }
            throw new PDOException("Impossible de se connecter à la base de données: " . $e->getMessage());
        }
    }

    public function getConnection(): PDO
    {
        if ($this->connection === null) {

            if (function_exists('log_error')) {
                log_error("Tentative d'obtenir une connexion PDO non initialisée.");
            }
            throw new Exception("La connexion PDO n'est pas établie.");
        }
        return $this->connection;
    }

    
    public function query(string $query, array $params = []): \PDOStatement
    {
        try {
            $statement = $this->getConnection()->prepare($query);
            $statement->execute($params);

            return $statement;

        } catch (PDOException $e) {
            if (function_exists('log_error')) {
                log_error("Erreur de requête SQL: " . $e->getMessage() . " - Query: " . $query . " - Params: " . json_encode($params));
            }
            throw $e;
        }
    }

    
    public function getLastInsertId(): string
    {
        return $this->getConnection()->lastInsertId();
    }

  
    public function beginTransaction(): void
    {
        $this->getConnection()->beginTransaction();
    }

    
    public function commit(): void
    {
        $this->getConnection()->commit();
    }

 
    public function rollBack(): void
    {
        $this->getConnection()->rollBack();
    }

  
    public function inTransaction(): bool
    {
        return $this->getConnection()->inTransaction();
    }

 
    public function close(): void
    {
        $this->connection = null;
    }

    
    private function __clone() {}

  
    public function __wakeup()
    {
        throw new Exception("Impossible de désérialiser un singleton");
    }
}