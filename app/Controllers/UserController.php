<?php

namespace App\Controllers;

/**
 * Contrôleur pour la gestion des profils utilisateurs HairyMada.
 * Ce contrôleur étend BaseController pour bénéficier des méthodes utilitaires communes.
 *
 * @package HairyMada\Controllers
 * @author Olga Henriette VOLANIAINA
 * @version 1.0
 */
class UserController extends BaseController
{
  
    public function showProfile(): void
    {
        echo "<h1>Profil Utilisateur</h1>";
        echo "<p>Ceci est la page de profil de l'utilisateur. (Nécessitera une authentification)</p>";
    }
}