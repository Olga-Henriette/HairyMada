<?php
/**
 * Script de migration automatique pour HairyMada
 * 
 * Ce script exécute toutes les migrations SQL dans l'ordre
 * et maintient un historique des migrations exécutées
 * 
 * Usage: php database/migrate.php
 * 
 * @package HairyMada
 * @author Olga Henriette VOLANIAINA
 * @version 1.0
 */

// Configuration
require_once __DIR__ . '/../config/database.php';

/**
 * Classe de gestion des migrations
 */
class MigrationManager
{
    private $db;
    private $migrationsPath;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->migrationsPath = __DIR__ . '/migrations';
        $this->createMigrationsTable();
    }
    
    /**
     * Créer la table des migrations si elle n'existe pas
     */
    private function createMigrationsTable(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL UNIQUE,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                
                INDEX idx_migration (migration),
                INDEX idx_executed_at (executed_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        $this->db->query($sql);
        echo "✅ Table des migrations créée\n";
    }
    
    /**
     * Exécuter toutes les migrations en attente
     */
    public function migrate(): void
    {
        echo "🚀 Démarrage des migrations HairyMada...\n\n";
        
        // Obtenir la liste des migrations
        $migrations = $this->getMigrationFiles();
        $executedMigrations = $this->getExecutedMigrations();
        
        $newMigrations = array_diff($migrations, $executedMigrations);
        
        if (empty($newMigrations)) {
            echo "✅ Aucune nouvelle migration à exécuter\n";
            return;
        }
        
        echo "📋 " . count($newMigrations) . " migration(s) à exécuter:\n";
        
        foreach ($newMigrations as $migration) {
            echo "⏳ Exécution de: $migration\n";
            
            try {
                $this->executeMigration($migration);
                $this->markMigrationAsExecuted($migration);
                echo "✅ $migration exécutée avec succès\n";
            } catch (Exception $e) {
                echo "❌ Erreur lors de l'exécution de $migration:\n";
                echo "   " . $e->getMessage() . "\n";
                break;
            }
        }
        
        echo "\n🎉 Migrations terminées !\n";
    }
    
    /**
     * Obtenir la liste des fichiers de migration
     */
    private function getMigrationFiles(): array
    {
        $files = scandir($this->migrationsPath);
        $migrations = [];
        
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
                $migrations[] = $file;
            }
        }
        
        sort($migrations);
        return $migrations;
    }
    
    /**
     * Obtenir la liste des migrations déjà exécutées
     */
    private function getExecutedMigrations(): array
    {
        $result = $this->db->query("SELECT migration FROM migrations ORDER BY migration");
        return $result->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Exécuter une migration
     */
    private function executeMigration(string $migration): void
    {
        $filePath = $this->migrationsPath . '/' . $migration;
        
        if (!file_exists($filePath)) {
            throw new Exception("Fichier de migration introuvable: $filePath");
        }
        
        $sql = file_get_contents($filePath);
        
        // Diviser le SQL en requêtes séparées
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            function($statement) {
                return !empty($statement) && !preg_match('/^\s*--/', $statement);
            }
        );
        
        $this->db->beginTransaction();
        
        try {
            foreach ($statements as $statement) {
                if (!empty($statement)) {
                    $this->db->query($statement);
                }
            }
            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    /**
     * Marquer une migration comme exécutée
     */
    private function markMigrationAsExecuted(string $migration): void
    {
        $this->db->query(
            "INSERT INTO migrations (migration) VALUES (?)",
            [$migration]
        );
    }
    
    /**
     * Afficher le statut des migrations
     */
    public function status(): void
    {
        echo "📊 Statut des migrations HairyMada:\n\n";
        
        $allMigrations = $this->getMigrationFiles();
        $executedMigrations = $this->getExecutedMigrations();
        
        foreach ($allMigrations as $migration) {
            $status = in_array($migration, $executedMigrations) ? '✅ Exécutée' : '⏳ En attente';
            echo "- $migration: $status\n";
        }
        
        echo "\nTotal: " . count($allMigrations) . " migrations\n";
        echo "Exécutées: " . count($executedMigrations) . "\n";
        echo "En attente: " . (count($allMigrations) - count($executedMigrations)) . "\n";
    }
}

// Exécution du script
try {
    $migrationManager = new MigrationManager();
    
    // Vérifier l'argument de commande
    $command = $argv[1] ?? 'migrate';
    
    switch ($command) {
        case 'status':
            $migrationManager->status();
            break;
            
        case 'migrate':
        default:
            $migrationManager->migrate();
            break;
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    exit(1);
}