<?php

namespace App\Services;

use Database;
use PDO;
use Exception;

/**
 * Service d'authentification pour HairyMada
 * 
 * Gère l'inscription, la connexion, les sessions et les tokens JWT
 * 
 * @package HairyMada\Services
 * @author Olga Henriette VOLANIAINA
 * @version 1.0
 */
class AuthService
{
    private $db;
    
    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * ============================
     * GESTION DES UTILISATEURS
     * ============================
     */
    
    /**
     * Inscrire un nouvel utilisateur
     * 
     * @param array $data Données de l'utilisateur
     * @return array Résultat de l'inscription
     */
}