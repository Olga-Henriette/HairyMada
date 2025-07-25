<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Point d'entrée principal de l'application HairyMada.
 *
 * Ce fichier initialise l'environnement de l'application, charge les configurations,
 * les helpers, et dispatche la requête via le routeur.
 *
 * @package HairyMada
 * @author Olga Henriette VOLANIAINA
 * @version 1.1
 */


// 1. Définir le chemin racine du projet
// pour les inclusions et les chemins absolus
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

// 2. Charger l'autoloader de Composer
// permet de charger automatiquement les classes de vendors et de l'application (via PSR-4)
require_once ROOT_PATH . '/vendor/autoload.php';


// 3. Charger les variables d'environnement avec vlucas/phpdotenv
// le charger avant d'accéder à $_ENV.
if (file_exists(ROOT_PATH . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(ROOT_PATH);
    $dotenv->load();
}

// 4. Charger le fichier de configuration des chemins
$paths = require ROOT_PATH . '/config/paths.php';

// 5. Charger la configuration de la base de données
$dbConfig = require ROOT_PATH . '/config/database.php';

// 6. Initialiser la connexion à la base de données (instance Singleton)
use App\Core\Database\Database;
Database::init($dbConfig);

// 7. Initialiser le routeur
use App\Core\Router;
$router = new Router();

// 8. Charger les définitions de routes (ex: web.php)
require_once $paths['routes'] . '/web.php';


// 9. Récupérer l'URI de la requête et la méthode HTTP
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = '';
if (str_starts_with($requestUri, $basePath)) {
    $requestUri = substr($requestUri, strlen($basePath));
}
if (empty($requestUri)) {
    $requestUri = '/';
}
$requestMethod = $_SERVER['REQUEST_METHOD'];

// 10. Dispatcher la requête
try {
    $router->dispatch($requestUri, $requestMethod);
} catch (Exception $e) {
    // Gestion des erreurs plus robuste
    if (function_exists('log_error')) {
        log_error("Erreur de routage ou d'application: " . $e->getMessage(), [
            'uri' => $requestUri,
            'method' => $requestMethod,
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
    }

    // Définir le code de statut HTTP
    $statusCode = 500; // Erreur interne par défaut
    if ($e->getMessage() === "Aucune route trouvée pour l'URI: {$requestUri} avec la méthode: {$requestMethod}") {
        $statusCode = 404; // Page non trouvée
    }
    http_response_code($statusCode);

    // Afficher une page d'erreur conviviale
    if ($statusCode === 404) {
        echo "<h1>Erreur 404 - Page non trouvée</h1>";
        echo "<p>L'URL demandée <strong>" . htmlspecialchars($requestUri) . "</strong> n'existe pas.</p>";
    } else {
        echo "<h1>Erreur Interne du Serveur</h1>";
        echo "<p>Une erreur est survenue lors du traitement de votre requête. Veuillez réessayer plus tard.</p>";
    }

    // Afficher le message d'erreur détaillé uniquement en mode debug
    if (($_ENV['APP_DEBUG'] ?? 'false') === 'true') {
        echo "<hr>";
        echo "<h2>Détails de l'erreur:</h2>";
        echo "<p>Message: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p>Fichier: " . htmlspecialchars($e->getFile()) . " (Ligne: " . $e->getLine() . ")</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    }
}
