<?php

namespace App\Controllers;

/**
 * Classe de base pour tous les contrôleurs HairyMada.
 *
 * Fournit des méthodes utilitaires communes aux contrôleurs, telles que
 * le rendu des vues, les redirections et la gestion des messages flash.
 *
 * @package HairyMada\Controllers
 * @author Olga Henriette VOLANIAINA
 * @version 1.0
 */
abstract class BaseController
{

    protected function render(string $viewPath, array $data = []): void
    {
        extract($data);

        $paths = require ROOT_PATH . '/config/paths.php';
        $viewFile = $paths['views'] . '/' . str_replace('.', '/', $viewPath) . '.php';

        if (!file_exists($viewFile)) {
            if (function_exists('log_error')) {
                log_error("Vue introuvable: " . $viewFile);
            }
            throw new \Exception("La vue '{$viewPath}' est introuvable.");
        }

        require $viewFile;
    }


    protected function redirect(string $url, int $statusCode = 302): void
    {
        if (function_exists('redirect')) {
            redirect($url, $statusCode);
        } else {
            header("Location: " . $url, true, $statusCode);
            exit();
        }
    }

    
    protected function redirectWithMessage(string $url, string $message, string $type = 'info'): void
    {
        if (function_exists('redirect_with_message')) {
            redirect_with_message($url, $message, $type);
        } else {
            if (!isset($_SESSION)) {
                session_start(); 
            }
            $_SESSION['flash_message'] = ['message' => $message, 'type' => $type];
            $this->redirect($url);
        }
    }

    
    protected function getFlashMessage(): ?array
    {
        if (function_exists('get_flash_message')) {
            return get_flash_message();
        } else {
            if (!isset($_SESSION)) {
                session_start(); 
            }
            if (isset($_SESSION['flash_message'])) {
                $message = $_SESSION['flash_message'];
                unset($_SESSION['flash_message']);
                return $message;
            }
            return null;
        }
    }


    protected function getPostData(): array
    {
        return $_POST;
    }


    protected function getGetData(): array
    {
        return $_GET;
    }

  
    protected function input(string $key, $default = null)
    {
        return $_REQUEST[$key] ?? $default;
    }
}