<?php

namespace App\Controllers;
use App\Models\User;
use Exception;

/**
 * Contrôleur d'authentification pour les clients HairyMada.
 *
 * Gère l'inscription, la connexion et les actions liées à l'authentification des utilisateurs.
 * Ce contrôleur étend BaseController pour bénéficier des méthodes utilitaires communes.
 *
 * @package HairyMada\Controllers
 * @author Olga Henriette VOLANIAINA
 * @version 1.0
 */
class AuthClientController extends BaseController
{

    public function showRegistrationForm(): void
    {
        $this->render('auth.register', ['title' => 'Inscription - HairyMada']);
    }


    public function register(): void
    {
        // 1. Récupérer et nettoyer les données du formulaire
        $data = $this->getPostData();

        // S'assurer que les clés nécessaires existent avec une valeur par défaut vide si non présentes
        // pour éviter les 'Undefined array key' si un champ est manquant.
        $userData = [
            'first_name' => trim($data['first_name'] ?? ''),
            'last_name' => trim($data['last_name'] ?? ''),
            'email' => trim(strtolower($data['email'] ?? '')),
            'phone' => trim($data['phone'] ?? ''),
            'password' => $data['password'] ?? '',
            'password_confirm' => $data['password_confirm'] ?? '', 
            'address' => trim($data['address'] ?? ''),
            'quartier' => trim($data['quartier'] ?? ''),
        ];

        // 2. Validation côté contrôleur 
        if (empty($userData['last_name']) || empty($userData['email']) ||
            empty($userData['phone']) || empty($userData['password']) || empty($userData['address']) ||
            empty($userData['quartier'])) {
            $this->redirectWithMessage('/register', 'Veuillez remplir tous les champs obligatoires.', 'error', $userData);
            return;
        }

        if (!filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
            $this->redirectWithMessage('/register', 'L\'adresse email n\'est pas valide.', 'error', $userData);
            return;
        }


        // Validation pour le numéro de téléphone (Madagascar)
        if (!empty($userData['phone'])) {
            // Regex pour les numéros de téléphone malagasy (ex: 032, 033, 038 suivis de 7 chiffres)
            if (!preg_match('/^03[2-8]\d{7}$/', $userData['phone'])) {
                $this->redirectWithMessage('/register', 'Le numéro de téléphone n\'est pas au format valide (ex: 03x yyyyyyy).', 'error', $userData);
                return;
            }
        }
        
        if ($userData['password'] !== $userData['password_confirm']) {
            $this->redirectWithMessage('/register', 'Les mots de passe ne correspondent pas.', 'error', $userData);
            return;
        }

        // validations pour la longueur mot de passe minimum
        if (strlen($userData['password']) < 6) {
            $this->redirectWithMessage('/register', 'Le mot de passe doit contenir au moins 6 caractères.', 'error', $userData);
            return;
        }

        try {
            // 3. Tenter de créer l'utilisateur via le modèle User
            $user = User::create($userData);

            if ($user) {
                // Inscription réussie
                // redirection vers la page de connexion avec un message.
                $this->redirectWithMessage('/login', 'Inscription réussie ! Veuillez vérifier votre email et votre téléphone pour activer votre compte.', 'success');
            } else {
                // L'inscription a échoué (ex: email/téléphone déjà utilisé, erreur BD).
                $this->redirectWithMessage('/register', 'Échec de l\'inscription. Un compte avec cet email ou téléphone existe peut-être déjà ou une erreur interne est survenue..', 'error', $userData);
            }
        } catch (Exception $e) {
            // Capturer les exceptions lancées par User::create (ex: "Le champ '...' est requis")
            error_log("Erreur dans AuthClientController::register: " . $e->getMessage());
            // Afficher le message de l'exception si elle est lancée par le modèle (ex: "Le champ '...' est requis")
            $this->redirectWithMessage('/register', 'Erreur lors de l\'inscription : ' . $e->getMessage(), 'error', $userData);
        }
    }

    
    public function showLoginForm(): void
    {
        $this->render('auth.login', ['title' => 'Connexion - HairyMada']);
    }


    public function login(): void
    {
        // 1. Récupérer et nettoyer les données du formulaire
        $data = $this->getPostData();

        $loginInput = trim($data['email'] ?? ''); // Utilise 'email' comme champ de connexion principal
        $password = $data['password'] ?? '';


        // 2. Validation côté contrôleur
        if (empty($loginInput) || empty($password)) {
            $this->redirectWithMessage('/login', 'Veuillez saisir votre email/téléphone et votre mot de passe.', 'error', ['email' => $loginInput]);
            return;
        }

        try {
 
            // 3. Tenter d'authentifier l'utilisateur via le modèle User
            $user = User::authenticate($loginInput, $password);

            if ($user) {
                // Authentification réussie
                // Démarrer la session utilisateur
                $_SESSION['user_id'] = $user->id;
                $_SESSION['user_email'] = $user->email;
                $_SESSION['user_full_name'] = $user->getFullName();

                $this->redirectWithMessage('/profile', 'Connexion réussie ! Bienvenue ' . $user->getFullName(), 'success');
            } else {
                // Échec de l'authentification
                $this->redirectWithMessage('/login', 'Email ou mot de passe incorrect, ou compte inactif/bloqué.', 'error', ['email' => $loginInput]);
            }
        } catch (Exception $e) {
            // Capturer les erreurs inattendues
            $this->redirectWithMessage('/login', 'Une erreur inattendue est survenue lors de la connexion.', 'error', ['email' => $loginInput]);
        }
    }
}