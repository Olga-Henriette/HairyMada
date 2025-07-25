<?php

use App\Core\Router; 
use App\Controllers\AuthClientController;
use App\Controllers\UserController;
use App\Controllers\HomeController;


/**
 * Fichier de définition des routes web pour l'application HairyMada.
 *
 * Ce fichier est inclus par le routeur principal de l'application
 * pour enregistrer toutes les routes accessibles via HTTP GET/POST.
 *
 * @package HairyMada\Routes
 * @author Olga Henriette VOLANIAINA
 * @version 1.0
 */

// Instancier le routeur (sera passé ou instancié une fois dans le fichier d'entrée)
if (!isset($router) || !$router instanceof Router) {
    $router = new Router();
}

/**
 * Routes d'accueil et statiques
 */

$router->get('/', 'HomeController@index');

$router->get('/about', function() {
    echo "<h1>À propos de nous</h1>";
    echo "<p>Ceci est la page 'À propos' de HairyMada.</p>";
});

/**
 * Routes d'authentification (les contrôleurs réels seront créés plus tard)
 */
$router->get('/register', 'AuthClientController@showRegistrationForm');
$router->post('/register', 'AuthClientController@register');

$router->get('/login', 'AuthClientController@showLoginForm');
$router->post('/login', 'AuthClientController@login');

// route pour le profil utilisateur (nécessitera une authentification)
$router->get('/profile', 'UserController@showProfile');


