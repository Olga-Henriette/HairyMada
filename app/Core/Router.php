<?php

namespace App\Core;

/**
 * Classe Router pour HairyMada.
 *
 * Gère l'enregistrement et le dispatching des routes HTTP.
 *
 * @package HairyMada\Core
 * @author Olga Henriette VOLANIAINA
 * @version 1.0
 */
class Router
{
    
    protected array $routes = [];

    
    public function get(string $uri, $action): void
    {
        $this->addRoute('GET', $uri, $action);
    }

    
    public function post(string $uri, $action): void
    {
        $this->addRoute('POST', $uri, $action);
    }

    protected function addRoute(string $method, string $uri, $action): void
    {
        $uri = '/' . trim($uri, '/');
        $this->routes[] = compact('method', 'uri', 'action');
    }

    
    public function dispatch(string $uri, string $method): void
    {
        $uri = '/' . trim($uri, '/');

        foreach ($this->routes as $route) {
            if ($route['uri'] === $uri && $route['method'] === $method) {
                $action = $route['action'];

                if (is_callable($action)) {
                    call_user_func($action);
                    return;
                }

                if (is_string($action) && str_contains($action, '@')) {
                    list($controllerName, $methodName) = explode('@', $action);
                    $controllerClass = "App\\Controllers\\" . $controllerName;

                    if (!class_exists($controllerClass)) {
                        $errorMessage = "Contrôleur introuvable: " . $controllerClass;
                        if (function_exists('log_error')) {
                            log_error($errorMessage);
                        }
                        throw new \Exception($errorMessage);
                    }

                    $controller = new $controllerClass();

                    if (!method_exists($controller, $methodName)) {
                        $errorMessage = "Méthode d'action introuvable: " . $controllerClass . "::" . $methodName;
                        if (function_exists('log_error')) {
                            log_error($errorMessage);
                        }
                        throw new \Exception($errorMessage);
                    }

                    call_user_func([$controller, $methodName]);
                    return;
                }

                if (function_exists('log_error')) {
                    log_error("Type d'action de route invalide pour URI: " . $uri . ", Méthode: " . $method);
                }
                throw new \Exception("Type d'action de route invalide.");
            }
        }

        $errorMessage = "Aucune route trouvée pour l'URI: {$uri} avec la méthode: {$method}";
        if (function_exists('log_error')) {
            log_error($errorMessage);
        }
        throw new \Exception($errorMessage);
    }
}