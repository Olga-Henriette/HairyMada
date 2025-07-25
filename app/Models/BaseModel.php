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
  
    protected Database $db;

    /**
     * Nom de la table (à définir dans chaque modèle enfant)
     */
    protected string $table;
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
     * Indique si le modèle existe déjà en base de données.
     */
    protected bool $existsInDatabase = false;

    /**
     * Constructeur
     */
    public function __construct(array $attributes = [])
    {
        $this->db = Database::getInstance();
        $this->fill($attributes);

        // Si l'ID est présent dans les attributs, cela signifie que l'instance est chargée depuis la BDD.
        // Alors, marque-la comme existante et réinitialise les attributs "sales".
        if (!empty($attributes[$this->primaryKey])) {
            $this->existsInDatabase = true;
            $this->dirty = []; // Réinitialise les attributs "sales" après l'hydratation
        }
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
            // Ne pas marquer comme dirty si c'est la première fois qu'on remplit (lors de la construction)
            // L'attribut 'id' peut être rempli mais ne doit pas rendre l'objet "sale".
            if ($this->isFillable($key) || $key === $this->primaryKey) { 
                $this->attributes[$key] = $value;
                // Ne marquer comme dirty que si la valeur change ET que l'objet existe déjà et que ce n'est pas la construction initiale.
                // OU si l'objet n'existe pas encore (nouvelle instance) et que la valeur est définie.
                if ($this->existsInDatabase && $this->getAttribute($key) !== $value) {
                    $this->dirty[$key] = true;
                } elseif (!$this->existsInDatabase && isset($this->attributes[$key])) {
                     // Pour les nouvelles instances, tous les attributs "fillables" sont initialement sales.
                     // Le "dirty" sera réinitialisé après le premier save().
                     $this->dirty[$key] = true;
                }
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
        // Ne marquer comme dirty que si la valeur change
        if (!array_key_exists($key, $this->attributes) || $this->attributes[$key] !== $value) {
            $this->attributes[$key] = $value;
            $this->dirty[$key] = true;
        }
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
        $instance = new static(); // Crée une instance temporaire pour accéder à $table
        
        try {
            $result = $instance->db->query(
                "SELECT * FROM {$instance->table} WHERE {$instance->primaryKey} = ? LIMIT 1",
                [$id]
            );
            
            $data = $result->fetch(PDO::FETCH_ASSOC); // Utiliser FETCH_ASSOC pour s'assurer que les clés sont des noms de colonnes
            
            if ($data) {
                $foundInstance = new static($data);
                $foundInstance->existsInDatabase = true; // Marquer explicitement comme existant
                $foundInstance->dirty = []; // Réinitialiser dirty après le chargement initial
                return $foundInstance;
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
            
            $data = $result->fetch(PDO::FETCH_ASSOC); 
            
            if ($data) {
                $foundInstance = new static($data);
                $foundInstance->existsInDatabase = true; 
                $foundInstance->dirty = []; 
                return $foundInstance;
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
            if ($this->existsInDatabase) { 
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
            $now = date('Y-m-d H:i:s');
            $this->setAttribute('created_at', $now);
            $this->setAttribute('updated_at', $now);
        }
        
        $columns = array_keys($this->attributes);
        $placeholders = array_fill(0, count($columns), '?');
        $params = array_values($this->attributes); 

        $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ")
                VALUES (" . implode(', ', $placeholders) . ")";
        
        try {
            $this->db->query($sql, $params); 
            
            // Récupérer l'ID généré
            $lastId = $this->db->getLastInsertId();
            $this->setAttribute($this->primaryKey, $lastId);
            
            // Marquer comme existant après une insertion réussie
            $this->existsInDatabase = true; 
            
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