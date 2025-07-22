<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
// Utile pour les inclusions et les chemins absolus
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

// 2. Charger l'autoloader de Composer
// Cela permet de charger automatiquement les classes de vendors et de l'application (via PSR-4)
require_once ROOT_PATH . '/vendor/autoload.php';

// 3. Charger les variables d'environnement avec vlucas/phpdotenv
// Il est crucial de charger les variables d'environnement avant d'accéder à $_ENV.
if (file_exists(ROOT_PATH . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(ROOT_PATH);
    $dotenv->load();
}

// 4. Démarrer la session PHP si elle n'est pas déjà démarrée
// Placé après le chargement des env vars, au cas où des configs session dépendraient de l'environnement.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 5. Charger le fichier des helpers globaux
// require_once ROOT_PATH . '/app/helpers.php'; // géré par composer.json "files"

// 6. Charger le fichier de configuration des chemins
$paths = require ROOT_PATH . '/config/paths.php';

// 7. Charger la configuration de la base de données
$dbConfig = require ROOT_PATH . '/config/database.php';

// 8. Initialiser la connexion à la base de données (instance Singleton)
use App\Core\Database\Database; 
Database::init($dbConfig); // Initialiser l'instance Singleton avec la configuration

// 9. Initialiser le routeur
use App\Core\Router;
$router = new Router();

// 10. Charger les définitions de routes (ex: web.php)
require_once $paths['routes'] . '/web.php';

// 11. Récupérer l'URI de la requête et la méthode HTTP
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = ''; // vide car le Virtual Host pointe directement vers le dossier 'public'
if (str_starts_with($requestUri, $basePath)) {
    $requestUri = substr($requestUri, strlen($basePath));
}

if (empty($requestUri)) {
    $requestUri = '/';
}


$requestMethod = $_SERVER['REQUEST_METHOD'];

// 12. Dispatcher la requête
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