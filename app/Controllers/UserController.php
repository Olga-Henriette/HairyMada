<?php

namespace App\Controllers;

use App\Models\User;

class UserController extends BaseController
{
    public function showProfile(): void
    {
        // Point de débogage 2: Vérifier la session au début de la requête /profile
        // dd(['Message' => 'Session au début de UserController::showProfile', 'Session' => $_SESSION]); 

        if (!isset($_SESSION['user_id'])) {
            $this->redirectWithMessage('/login', 'Vous devez être connecté pour accéder à cette page.', 'error');
            return;
        }

        $userId = $_SESSION['user_id'];
        $user = User::find($userId);

        if (!$user) {
            unset($_SESSION['user_id']);
            unset($_SESSION['user_email']);
            unset($_SESSION['user_full_name']);
            $this->redirectWithMessage('/login', 'Votre session n\'est plus valide. Veuillez vous reconnecter.', 'error');
            return;
        }

        $this->render('client.profile', [
            'user' => $user,
            'title' => 'Mon Profil - HairyMada'
        ]);
    }
}
