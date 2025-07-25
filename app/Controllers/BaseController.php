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

    // pour passer l'ancienne entrée à la vue
    protected function render(string $viewPath, array $data = []): void
    {
        // Récupère les anciennes entrées en utilisant le helper global
        $oldInput = get_old_input(); // Utilisation du helper
        $data['old_input'] = $oldInput; // Passe old_input à la vue

        extract($data); // Extrait maintenant $data et $old_input

        $paths = require ROOT_PATH . '/config/paths.php';
        $viewFile = $paths['views'] . '/' . str_replace('.', '/', $viewPath) . '.php';

        if (!file_exists($viewFile)) {
            if (function_exists('log_error')) {
                log_error("Vue introuvable: " . $viewFile);
            }
            throw new \Exception("La vue '{$viewPath}' est introuvable.");
        }

    // Démarrer la mise en tampon de la sortie
        ob_start();
        require $viewFile; // Inclut la vue spécifique (login.php, register.php)
        $content = ob_get_clean(); // Récupère le contenu de la vue

        $layoutPath = $paths['views'] . '/layouts/';
        if (str_starts_with($viewPath, 'auth.')) {
            $layoutPath .= 'auth.php';
            // Le titre est déjà défini dans les contrôleurs qui appellent render,
            // ou il peut être passé via $data['title'].
            // Si non défini, il utilisera le défaut de auth.php
        } elseif (str_starts_with($viewPath, 'client.')) {
            $layoutPath .= 'app.php'; 
        } else {
            $layoutPath .= 'app.php'; 
        }

        // Le titre pour le layout est déjà passé via $data['title'] et extrait
        // Si $title n'est pas défini, il prendra la valeur par défaut du layout

        // Inclure le layout, qui aura accès à $content et $title
        if (!file_exists($layoutPath)) {
             if (function_exists('log_error')) {
                 log_error("Layout introuvable: " . $layoutPath);
             }
            throw new \Exception("Le layout '{$layoutPath}' est introuvable.");
        }
        require $layoutPath;
    }


    protected function redirect(string $url, int $statusCode = 302): void
    {
        redirect($url, $statusCode);
    }

    
    protected function redirectWithMessage(string $url, string $message, string $type = 'info', array $oldInput = []): void
    {
        // Délégué la gestion du message flash au helper
        set_flash_message($message, $type); // Utilisation du helper
        
        if (!empty($oldInput)) {
            // Délégué la gestion de l'ancien input au helper
            set_old_input($oldInput); // Utilisation du helper
        }
        $this->redirect($url);
    }

    // une méthode pour récupérer l'ancienne entrée
    protected function getOldInput(): array
    {
        return get_old_input(); 
    }

    protected function getFlashMessage(): ?array
    {
        return get_flash_message(); 
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