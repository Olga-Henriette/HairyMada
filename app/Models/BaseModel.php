<?php

namespace App\Models;

use App\Core\Database\Database;
use PDO;
use PDOException;
use Exception;

/**
 * Classe de base pour tous les modèles HairyMada
 *
 * Cette classe fournit les fonctionnalités CRUD de base
 * et les méthodes communes à tous les modèles
 *
 * @package HairyMada\Models
 * @author Olga Henriette VOLANIAINA
 * @version 1.0
 */
abstract class BaseModel
{
    /**
     * Instance de la base de données
     */
    protected Database $db;

    /**
     * Nom de la table (à définir dans chaque modèle enfant)
     */
    protected string $table;

    /**
     * Clé primaire de la table
     */
    protected string $primaryKey = 'id';

    /**
     * Colonnes fillables (peuvent être assignées en masse)
     */
    protected array $fillable = [];

    /**
     * Colonnes protégées (ne peuvent pas être assignées en masse)
     */
    protected array $guarded = ['id', 'created_at', 'updated_at'];

    /**
     * Timestamps automatiques
     */
    protected bool $timestamps = true;

    /**
     * Données de l'instance actuelle
     */
    protected array $attributes = [];

    /**
     * Attributs modifiés
     */
    protected array $dirty = [];

    /**
     * Constructeur
     */
    public function __construct(array $attributes = [])
    {
        $this->db = Database::getInstance();
        $this->fill($attributes);
    }

    /**
     * Remplir les attributs du modèle
     *
     * @param array $attributes Attributs à assigner
     * @return self
     */
    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            if ($this->isFillable($key)) {
                $this->setAttribute($key, $value);
            }
        }
        
        return $this;
    }

    /**
     * Vérifier si un attribut est fillable
     *
     * @param string $key Nom de l'attribut
     * @return bool
     */
    protected function isFillable(string $key): bool
    {
        // Si fillable est défini, vérifier qu'il est dans la liste
        if (!empty($this->fillable)) {
            return in_array($key, $this->fillable);
        }
        
        // Sinon, vérifier qu'il n'est pas dans guarded
        return !in_array($key, $this->guarded);
    }

    /**
     * Définir un attribut
     *
     * @param string $key Nom de l'attribut
     * @param mixed $value Valeur
     */
    public function setAttribute(string $key, $value): void
    {
        $this->attributes[$key] = $value;
        $this->dirty[$key] = true;
    }

    /**
     * Obtenir un attribut
     *
     * @param string $key Nom de l'attribut
     * @param mixed $default Valeur par défaut
     * @return mixed
     */
    public function getAttribute(string $key, $default = null)
    {
        return $this->attributes[$key] ?? $default;
    }

    /**
     * Magic getter
     */
    public function __get(string $key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Magic setter
     */
    public function __set(string $key, $value): void
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Trouver un enregistrement par ID
     *
     * @param mixed $id ID de l'enregistrement
     * @return static|null
     */
    public static function find($id): ?self
    {
        $instance = new static();
        
        try {
            $result = $instance->db->query(
                "SELECT * FROM {$instance->table} WHERE {$instance->primaryKey} = ? LIMIT 1",
                [$id]
            );
            
            $data = $result->fetch();
            
            if ($data) {
                return new static($data);
            }
            
            return null;
            
        } catch (PDOException $e) {
            error_log("Erreur BaseModel::find: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Trouver un enregistrement ou lancer une exception
     *
     * @param mixed $id ID de l'enregistrement
     * @return static
     * @throws Exception Si l'enregistrement n'est pas trouvé
     */
    public static function findOrFail($id): self
    {
        $record = static::find($id);
        
        if (!$record) {
            throw new Exception("Enregistrement introuvable avec l'ID: $id");
        }
        
        return $record;
    }

    /**
     * Trouver un enregistrement par critères
     *
     * @param array $criteria Critères de recherche
     * @return static|null
     */
    public static function findBy(array $criteria): ?self
    {
        $instance = new static();
        
        $conditions = [];
        $params = [];
        
        foreach ($criteria as $key => $value) {
            $conditions[] = "$key = ?";
            $params[] = $value;
        }
        
        $whereClause = implode(' AND ', $conditions);
        
        try {
            $result = $instance->db->query(
                "SELECT * FROM {$instance->table} WHERE $whereClause LIMIT 1",
                $params
            );
            
            $data = $result->fetch();
            
            if ($data) {
                return new static($data);
            }
            
            return null;
            
        } catch (PDOException $e) {
            error_log("Erreur BaseModel::findBy: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtenir tous les enregistrements
     *
     * @param array $orderBy Ordre de tri
     * @param int|null $limit Limite
     * @param int $offset Offset
     * @return array
     */
    public static function all(array $orderBy = [], ?int $limit = null, int $offset = 0): array
    {
        $instance = new static();
        
        $sql = "SELECT * FROM {$instance->table}";
        
        // Ordre de tri
        if (!empty($orderBy)) {
            $orderClauses = [];
            foreach ($orderBy as $column => $direction) {
                $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
                $orderClauses[] = "$column $direction";
            }
            $sql .= " ORDER BY " . implode(', ', $orderClauses);
        }
        
        // Limite et offset
        if ($limit !== null) {
            $sql .= " LIMIT $limit";
            if ($offset > 0) {
                $sql .= " OFFSET $offset";
            }
        }
        
        try {
            $result = $instance->db->query($sql);
            $records = [];
            
            while ($row = $result->fetch()) {
                $records[] = new static($row);
            }
            
            return $records;
            
        } catch (PDOException $e) {
            error_log("Erreur BaseModel::all: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Compter les enregistrements
     *
     * @param array $criteria Critères de filtrage
     * @return int
     */
    public static function count(array $criteria = []): int
    {
        $instance = new static();
        
        $sql = "SELECT COUNT(*) as total FROM {$instance->table}";
        $params = [];
        
        if (!empty($criteria)) {
            $conditions = [];
            foreach ($criteria as $key => $value) {
                $conditions[] = "$key = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        
        try {
            $result = $instance->db->query($sql, $params);
            return (int) $result->fetch()['total'];
            
        } catch (PDOException $e) {
            error_log("Erreur BaseModel::count: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Sauvegarder l'enregistrement
     *
     * @return bool
     */
    public function save(): bool
    {
        try {
            if ($this->exists()) {
                return $this->update();
            } else {
                return $this->insert();
            }
        } catch (Exception $e) {
            error_log("Erreur BaseModel::save: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Insérer un nouvel enregistrement
     *
     * @return bool
     */
    protected function insert(): bool
    {
        if ($this->timestamps) {
            $this->setAttribute('created_at', date('Y-m-d H:i:s'));
            $this->setAttribute('updated_at', date('Y-m-d H:i:s'));
        }
        
        $columns = array_keys($this->attributes);
        $placeholders = array_fill(0, count($columns), '?');
        
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ")
                VALUES (" . implode(', ', $placeholders) . ")";
        
        try {
            $this->db->query($sql, array_values($this->attributes));
            
            // Récupérer l'ID généré
            $lastId = $this->db->getLastInsertId();
            $this->setAttribute($this->primaryKey, $lastId);
            
            // Marquer comme propre
            $this->dirty = [];
            
            return true;
            
        } catch (PDOException $e) {
            error_log("Erreur BaseModel::insert: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mettre à jour l'enregistrement
     *
     * @return bool
     */
    protected function update(): bool
    {
        if (empty($this->dirty)) {
            return true; // Rien à mettre à jour
        }
        
        if ($this->timestamps) {
            $this->setAttribute('updated_at', date('Y-m-d H:i:s'));
        }
        
        $updates = [];
        $params = [];
        
        foreach ($this->dirty as $key => $isDirty) {
            if ($isDirty && $key !== $this->primaryKey) {
                $updates[] = "$key = ?";
                $params[] = $this->attributes[$key];
            }
        }
        
        if (empty($updates)) {
            return true;
        }
        
        // Ajouter l'ID pour la clause WHERE
        $params[] = $this->getAttribute($this->primaryKey);
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $updates) .
               " WHERE {$this->primaryKey} = ?";
        
        try {
            $this->db->query($sql, $params);
            
            // Marquer comme propre
            $this->dirty = [];
            
            return true;
            
        } catch (PDOException $e) {
            error_log("Erreur BaseModel::update: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprimer l'enregistrement
     *
     * @return bool
     */
    public function delete(): bool
    {
        if (!$this->exists()) {
            return false;
        }
        
        try {
            $this->db->query(
                "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?",
                [$this->getAttribute($this->primaryKey)]
            );
            
            return true;
            
        } catch (PDOException $e) {
            error_log("Erreur BaseModel::delete: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifier si l'enregistrement existe en base
     *
     * @return bool
     */
    public function exists(): bool
    {
        return !empty($this->getAttribute($this->primaryKey));
    }

    /**
     * Convertir en tableau
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->attributes;
    }

    /**
     * Convertir en JSON
     *
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->attributes, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}