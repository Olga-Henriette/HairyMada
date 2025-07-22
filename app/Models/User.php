<?php

namespace App\Models;

use Exception;
use PDOException;

/**
 * Modèle User (Clients)
 *
 * Gère tous les utilisateurs clients de la plateforme HairyMada
 *
 * @package HairyMada\Models
 * @author Olga Henriette VOLANIAINA
 * @version 1.0
 */
class User extends BaseModel
{
    /**
     * Nom de la table
     */
    protected string $table = 'users';

    /**
     * Colonnes fillables
     */
    protected array $fillable = [
        'email',
        'phone',
        'password_hash',
        'first_name',
        'last_name',
        'address',
        'quartier',
        'email_verification_token',
        'phone_verification_token',
        'is_active',
        'is_blocked',
        'blocked_reason'
    ];

    /**
     * Colonnes protégées
     */
    protected array $guarded = [
        'id',
        'email_verified_at',
        'phone_verified_at',
        'blocked_at',
        'last_login_at',
        'created_at',
        'updated_at'
    ];

    /**
     * Créer un nouvel utilisateur avec hash du mot de passe
     *
     * @param array $userData Données de l'utilisateur
     * @return static|null
     */
    public static function create(array $userData): ?self
    {
        try {
            // Validation des données requises
            $required = ['email', 'phone', 'password', 'first_name', 'last_name', 'address', 'quartier'];
            foreach ($required as $field) {
                if (empty($userData[$field])) {
                    throw new Exception("Le champ '$field' est requis");
                }
            }
            
            // Vérifier l'unicité de l'email
            if (static::findByEmail($userData['email'])) {
                throw new Exception("Un utilisateur avec cet email existe déjà");
            }
            
            // Vérifier l'unicité du téléphone
            if (static::findByPhone($userData['phone'])) {
                throw new Exception("Un utilisateur avec ce numéro existe déjà");
            }
            
            // Hasher le mot de passe
            $userData['password_hash'] = password_hash($userData['password'], PASSWORD_DEFAULT);
            unset($userData['password']); // Supprimer le mot de passe en clair
            
            // Générer les tokens de vérification
            $userData['email_verification_token'] = bin2hex(random_bytes(32));
            $userData['phone_verification_token'] = sprintf('%06d', mt_rand(100000, 999999));
            
            // Créer l'utilisateur
            $user = new static($userData);
            
            if ($user->save()) {
                return $user;
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log("Erreur User::create: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Trouver un utilisateur par email
     *
     * @param string $email Email de l'utilisateur
     * @return static|null
     */
    public static function findByEmail(string $email): ?self
    {
        return static::findBy(['email' => $email]);
    }

    /**
     * Trouver un utilisateur par téléphone
     *
     * @param string $phone Numéro de téléphone
     * @return static|null
     */
    public static function findByPhone(string $phone): ?self
    {
        return static::findBy(['phone' => $phone]);
    }

    /**
     * Authentifier un utilisateur
     *
     * @param string $login Email ou téléphone
     * @param string $password Mot de passe
     * @return static|null
     */
    public static function authenticate(string $login, string $password): ?self
    {
        // Chercher par email ou téléphone
        $user = static::findByEmail($login) ?? static::findByPhone($login);
        
        if (!$user) {
            return null;
        }
        
        // Vérifier le mot de passe
        if (!password_verify($password, $user->password_hash)) {
            return null;
        }
        
        // Vérifier que le compte est actif
        if (!$user->is_active || $user->is_blocked) {
            return null;
        }
        
        // Mettre à jour la dernière connexion
        $user->updateLastLogin();
        
        return $user;
    }

    /**
     * Vérifier l'email avec le token
     *
     * @param string $token Token de vérification
     * @return bool
     */
    public function verifyEmail(string $token): bool
    {
        if ($this->email_verification_token !== $token) {
            return false;
        }
        
        $this->setAttribute('email_verified_at', date('Y-m-d H:i:s'));
        $this->setAttribute('email_verification_token', null);
        
        return $this->save();
    }

    /**
     * Vérifier le téléphone avec le code SMS
     *
     * @param string $code Code SMS
     * @return bool
     */
    public function verifyPhone(string $code): bool
    {
        if ($this->phone_verification_token !== $code) {
            return false;
        }
        
        $this->setAttribute('phone_verified_at', date('Y-m-d H:i:s'));
        $this->setAttribute('phone_verification_token', null);
        
        return $this->save();
    }

    /**
     * Vérifier si l'email est vérifié
     *
     * @return bool
     */
    public function isEmailVerified(): bool
    {
        return !empty($this->email_verified_at);
    }

    /**
     * Vérifier si le téléphone est vérifié
     *
     * @return bool
     */
    public function isPhoneVerified(): bool
    {
        return !empty($this->phone_verified_at);
    }

    /**
     * Vérifier si l'utilisateur est complètement vérifié
     *
     * @return bool
     */
    public function isFullyVerified(): bool
    {
        return $this->isEmailVerified() && $this->isPhoneVerified();
    }

    /**
     * Bloquer l'utilisateur
     *
     * @param string $reason Raison du blocage
     * @return bool
     */
    public function block(string $reason): bool
    {
        $this->setAttribute('is_blocked', true);
        $this->setAttribute('blocked_reason', $reason);
        $this->setAttribute('blocked_at', date('Y-m-d H:i:s'));
        
        return $this->save();
    }

    /**
     * Débloquer l'utilisateur
     *
     * @return bool
     */
    public function unblock(): bool
    {
        $this->setAttribute('is_blocked', false);
        $this->setAttribute('blocked_reason', null);
        $this->setAttribute('blocked_at', null);
        
        return $this->save();
    }

    /**
     * Désactiver le compte
     *
     * @return bool
     */
    public function deactivate(): bool
    {
        $this->setAttribute('is_active', false);
        return $this->save();
    }

    /**
     * Activer le compte
     *
     * @return bool
     */
    public function activate(): bool
    {
        $this->setAttribute('is_active', true);
        return $this->save();
    }

    /**
     * Changer le mot de passe
     *
     * @param string $newPassword Nouveau mot de passe
     * @return bool
     */
    public function changePassword(string $newPassword): bool
    {
        $this->setAttribute('password_hash', password_hash($newPassword, PASSWORD_DEFAULT));
        return $this->save();
    }

    /**
     * Mettre à jour la dernière connexion
     *
     * @return bool
     */
    public function updateLastLogin(): bool
    {
        $this->setAttribute('last_login_at', date('Y-m-d H:i:s'));
        return $this->save();
    }

    /**
     * Générer un nouveau token de vérification d'email
     *
     * @return string
     */
    public function generateEmailVerificationToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $this->setAttribute('email_verification_token', $token);
        $this->save();
        
        return $token;
    }

    /**
     * Générer un nouveau code de vérification SMS
     *
     * @return string
     */
    public function generatePhoneVerificationToken(): string
    {
        $code = sprintf('%06d', mt_rand(100000, 999999));
        $this->setAttribute('phone_verification_token', $code);
        $this->save();
        
        return $code;
    }

    /**
     * Obtenir le nom complet de l'utilisateur
     *
     * @return string
     */
    public function getFullName(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Obtenir l'initiale du nom
     *
     * @return string
     */
    public function getInitials(): string
    {
        $firstName = substr($this->first_name, 0, 1);
        $lastName = substr($this->last_name, 0, 1);
        
        return strtoupper($firstName . $lastName);
    }

    /**
     * Obtenir les utilisateurs actifs
     *
     * @param array $orderBy Ordre de tri
     * @param int|null $limit Limite
     * @param int $offset Offset
     * @return array
     */
    public static function getActive(array $orderBy = ['created_at' => 'DESC'], ?int $limit = null, int $offset = 0): array
    {
        $instance = new static();
        
        $sql = "SELECT * FROM {$instance->table} WHERE is_active = 1 AND is_blocked = 0";
        
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
            $users = [];
            
            while ($row = $result->fetch()) {
                $users[] = new static($row);
            }
            
            return $users;
            
        } catch (PDOException $e) {
            error_log("Erreur User::getActive: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Rechercher des utilisateurs par quartier
     *
     * @param string $quartier Nom du quartier
     * @return array
     */
    public static function findByQuartier(string $quartier): array
    {
        $instance = new static();
        
        try {
            $result = $instance->db->query(
                "SELECT * FROM {$instance->table} WHERE quartier LIKE ? AND is_active = 1",
                ["%$quartier%"]
            );
            
            $users = [];
            while ($row = $result->fetch()) {
                $users[] = new static($row);
            }
            
            return $users;
            
        } catch (PDOException $e) {
            error_log("Erreur User::findByQuartier: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtenir les statistiques des utilisateurs
     *
     * @return array
     */
    public static function getStats(): array
    {
        $instance = new static();
        
        try {
            // Total d'utilisateurs
            $totalResult = $instance->db->query("SELECT COUNT(*) as total FROM {$instance->table}");
            $total = $totalResult->fetch()['total'];
            
            // Utilisateurs actifs
            $activeResult = $instance->db->query("SELECT COUNT(*) as active FROM {$instance->table} WHERE is_active = 1 AND is_blocked = 0");
            $active = $activeResult->fetch()['active'];
            
            // Utilisateurs vérifiés
            $verifiedResult = $instance->db->query("SELECT COUNT(*) as verified FROM {$instance->table} WHERE email_verified_at IS NOT NULL AND phone_verified_at IS NOT NULL");
            $verified = $verifiedResult->fetch()['verified'];
            
            // Nouveaux utilisateurs cette semaine
            $weekResult = $instance->db->query("SELECT COUNT(*) as week FROM {$instance->table} WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
            $thisWeek = $weekResult->fetch()['week'];
            
            return [
                'total' => (int)$total,
                'active' => (int)$active,
                'verified' => (int)$verified,
                'this_week' => (int)$thisWeek,
                'blocked' => (int)($total - $active)
            ];
            
        } catch (PDOException $e) {
            error_log("Erreur User::getStats: " . $e->getMessage());
            return [
                'total' => 0,
                'active' => 0,
                'verified' => 0,
                'this_week' => 0,
                'blocked' => 0
            ];
        }
    }

    /**
     * Convertir en tableau (exclure les données sensibles)
     *
     * @return array
     */
    public function toArray(): array
    {
        $data = parent::toArray();
        
        // Supprimer les données sensibles
        unset($data['password_hash']);
        unset($data['email_verification_token']);
        unset($data['phone_verification_token']);
        
        return $data;
    }
}
