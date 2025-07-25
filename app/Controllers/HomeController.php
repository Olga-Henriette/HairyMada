<?php

namespace App\Controllers;

class HomeController extends BaseController
{
    /**
     * Affiche la page d'accueil de l'application.
     *
     * @return void
     */
    public function index(): void
    {
        $this->render('home'); // Rendre la vue 'resources/views/home.php'
    }
}