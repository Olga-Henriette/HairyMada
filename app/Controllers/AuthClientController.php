<?php

namespace App\Controllers;

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
        echo "<h1>Formulaire d'inscription Client</h1>";
        echo "<p>Ceci est la page où les clients s'inscrivent.</p>";
    }


    public function register(): void
    {
        $data = $this->getPostData();


        echo "<h1>Traitement de l'inscription Client</h1>";
        echo "<p>Données reçues pour l'inscription: <pre>" . htmlspecialchars(print_r($data, true)) . "</pre></p>";

    }

    
    public function showLoginForm(): void
    {
        echo "<h1>Formulaire de connexion Client</h1>";
        echo "<p>Ceci est la page de connexion des clients.</p>";
    }


    public function login(): void
    {
        $data = $this->getPostData();

        echo "<h1>Traitement de la connexion Client</h1>";
        echo "<p>Données reçues pour la connexion: <pre>" . htmlspecialchars(print_r($data, true)) . "</pre></p>";
    }
}