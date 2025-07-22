<?php

/**
 * Fichier de configuration des chemins pour l'application HairyMada.
 *
 * Définit les chemins absolus vers les répertoires clés de l'application.
 *
 * @package HairyMada
 * @author Olga Henriette VOLANIAINA
 * @version 1.1
 */

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

return [
    'root'        => ROOT_PATH,
    'app'         => ROOT_PATH . '/app',
    'config'      => ROOT_PATH . '/config',
    'database'    => ROOT_PATH . '/database',
    'public'      => ROOT_PATH . '/public',
    'resources'   => ROOT_PATH . '/resources',
    'routes'      => ROOT_PATH . '/routes',
    'storage'     => ROOT_PATH . '/storage',
    'views'       => ROOT_PATH . '/resources/views',
    'logs'        => ROOT_PATH . '/storage/logs',
    'uploads'     => ROOT_PATH . '/public/uploads',
    'cache_views' => ROOT_PATH . '/storage/cache/views', 
];