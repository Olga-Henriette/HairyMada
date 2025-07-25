<?php
/**
 * Fichier d'aide principal pour HairyMada
 *
 * Ce fichier contient toutes les fonctions utilitaires
 * utilisées dans l'application
 *
 * @package HairyMada
 * @author Olga Henriette VOLANIAINA
 * @version 1.0
 */

/**
 * ============================
 * FONCTIONS DE SÉCURITÉ
 * ============================
 */

/**
 * Nettoyer et sécuriser une chaîne de caractères
 *
 * @param string $string Chaîne à nettoyer
 * @return string Chaîne nettoyée
 */
function sanitize_string(string $string): string
{
    return htmlspecialchars(strip_tags(trim($string)), ENT_QUOTES, 'UTF-8');
}

/**
 * Valider une adresse email
 *
 * @param string $email Email à valider
 * @return bool True si valide
 */
function is_valid_email(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Valider un numéro de téléphone malgache
 *
 * @param string $phone Numéro à valider
 * @return bool True si valide
 */
function is_valid_madagascar_phone(string $phone): bool
{
    // Formats acceptés: +261XXXXXXXXX, 0XXXXXXXXX, 261XXXXXXXXX
    $pattern = '/^(\+261|261|0)[23-9]\d{8}$/';
    return preg_match($pattern, $phone) === 1;
}

/**
 * Générer un token sécurisé
 *
 * @param int $length Longueur du token
 * @return string Token généré
 */
function generate_secure_token(int $length = 32): string
{
    return bin2hex(random_bytes($length / 2));
}

/**
 * Hasher un mot de passe
 *
 * @param string $password Mot de passe à hasher
 * @return string Hash du mot de passe
 */
function hash_password(string $password): string
{
    return password_hash($password, PASSWORD_ARGON2ID);
}

/**
 * Vérifier un mot de passe
 *
 * @param string $password Mot de passe en clair
 * @param string $hash Hash à vérifier
 * @return bool True si correct
 */
function verify_password(string $password, string $hash): bool
{
    return password_verify($password, $hash);
}

/**
 * Générer un token CSRF
 *
 * @return string Token CSRF
 */
function generate_csrf_token(): string
{
    // S'assurer que la session est démarrée avant de l'utiliser
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = generate_secure_token(32);
    }
    return $_SESSION['csrf_token'];
}

/**
 * Vérifier un token CSRF
 *
 * @param string $token Token à vérifier
 * @return bool True si valide
 */
function verify_csrf_token(string $token): bool
{
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * ============================
 * FONCTIONS DE VALIDATION
 * ============================
 */

/**
 * Valider les données d'inscription
 *
 * @param array $data Données à valider
 * @return array Erreurs trouvées
 */
function validate_registration_data(array $data): array
{
    $errors = [];
    
    // Validation du prénom
    if (empty($data['first_name'])) {
        $errors['first_name'] = 'Le prénom est requis';
    } elseif (strlen($data['first_name']) < 2) {
        $errors['first_name'] = 'Le prénom doit contenir au moins 2 caractères';
    }
    
    // Validation du nom
    if (empty($data['last_name'])) {
        $errors['last_name'] = 'Le nom est requis';
    } elseif (strlen($data['last_name']) < 2) {
        $errors['last_name'] = 'Le nom doit contenir au moins 2 caractères';
    }
    
    // Validation de l'email
    if (empty($data['email'])) {
        $errors['email'] = 'L\'email est requis';
    } elseif (!is_valid_email($data['email'])) {
        $errors['email'] = 'L\'email n\'est pas valide';
    }
    
    // Validation du téléphone
    if (empty($data['phone'])) {
        $errors['phone'] = 'Le numéro de téléphone est requis';
    } elseif (!is_valid_madagascar_phone($data['phone'])) {
        $errors['phone'] = 'Le numéro de téléphone n\'est pas valide pour Madagascar';
    }
    
    // Validation du mot de passe
    if (empty($data['password'])) {
        $errors['password'] = 'Le mot de passe est requis';
    } elseif (strlen($data['password']) < 8) {
        $errors['password'] = 'Le mot de passe doit contenir au moins 8 caractères';
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $data['password'])) {
        $errors['password'] = 'Le mot de passe doit contenir au moins une majuscule, une minuscule et un chiffre';
    }
    
    // Validation de la confirmation du mot de passe
    if (empty($data['password_confirm'])) {
        $errors['password_confirm'] = 'La confirmation du mot de passe est requise';
    } elseif ($data['password'] !== $data['password_confirm']) {
        $errors['password_confirm'] = 'Les mots de passe ne correspondent pas';
    }
    
    // Validation de l'adresse
    if (empty($data['address'])) {
        $errors['address'] = 'L\'adresse est requise';
    } elseif (strlen($data['address']) < 10) {
        $errors['address'] = 'L\'adresse doit être plus détaillée';
    }
    
    // Validation du quartier
    if (empty($data['quartier'])) {
        $errors['quartier'] = 'Le quartier est requis';
    }
    
    return $errors;
}

/**
 * ============================
 * FONCTIONS D'UPLOAD
 * ============================
 */

/**
 * Valider un fichier uploadé
 *
 * @param array $file Fichier $_FILES
 * @param array $allowed_types Types autorisés
 * @param int $max_size Taille maximale en octets
 * @return array Erreurs trouvées
 */
function validate_upload_file(array $file, array $allowed_types = [], int $max_size = 10485760): array
{
    $errors = [];
    
    // Vérifier si le fichier a été uploadé
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Erreur lors de l\'upload du fichier';
        return $errors;
    }
    
    // Vérifier la taille
    if ($file['size'] > $max_size) {
        $errors[] = 'Le fichier est trop volumineux (max: ' . formatBytes($max_size) . ')';
    }
    
    // Vérifier le type MIME
    if (!empty($allowed_types)) {
        $file_type = mime_content_type($file['tmp_name']);
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = 'Type de fichier non autorisé';
        }
    }
    
    return $errors;
}

/**
 * Générer un nom de fichier sécurisé
 *
 * @param string $original_name Nom original
 * @param string $prefix Préfixe optionnel
 * @return string Nom sécurisé
 */
function generate_secure_filename(string $original_name, string $prefix = ''): string
{
    $extension = pathinfo($original_name, PATHINFO_EXTENSION);
    $timestamp = time();
    $random = bin2hex(random_bytes(8));
    
    return $prefix . $timestamp . '_' . $random . '.' . $extension;
}

/**
 * ============================
 * FONCTIONS D'AFFICHAGE
 * ============================
 */

/**
 * Formater les octets en format lisible
 *
 * @param int $bytes Nombre d'octets
 * @param int $precision Précision décimale
 * @return string Taille formatée
 */
function formatBytes(int $bytes, int $precision = 2): string
{
    $units = ['B', 'KB', 'MB', 'GB'];
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

/**
 * Formater une date en français
 *
 * @param string $date Date à formater
 * @param string $format Format de sortie
 * @return string Date formatée
 */
function format_date_french(string $date, string $format = 'd/m/Y à H:i'): string
{
    $timestamp = strtotime($date);
    return date($format, $timestamp);
}

/**
 * Calculer le temps écoulé depuis une date
 *
 * @param string $date Date de référence
 * @return string Temps écoulé
 */
function time_ago(string $date): string
{
    $timestamp = strtotime($date);
    $difference = time() - $timestamp;
    
    if ($difference < 60) {
        return 'Il y a quelques secondes';
    } elseif ($difference < 3600) {
        $minutes = floor($difference / 60);
        return "Il y a $minutes minute" . ($minutes > 1 ? 's' : '');
    } elseif ($difference < 86400) {
        $hours = floor($difference / 3600);
        return "Il y a $hours heure" . ($hours > 1 ? 's' : '');
    } elseif ($difference < 2592000) {
        $days = floor($difference / 86400);
        return "Il y a $days jour" . ($days > 1 ? 's' : '');
    } else {
        return format_date_french($date);
    }
}

/**
 * Tronquer un texte
 *
 * @param string $text Texte à tronquer
 * @param int $length Longueur maximale
 * @param string $suffix Suffixe à ajouter
 * @return string Texte tronqué
 */
function truncate_text(string $text, int $length = 100, string $suffix = '...'): string
{
    if (strlen($text) <= $length) {
        return $text;
    }
    
    return substr($text, 0, $length) . $suffix;
}

/**
 * ============================
 * FONCTIONS SPÉCIFIQUES HAIRYMADA
 * ============================
 */

/**
 * Générer un ID unique pour un prestataire
 *
 * @param int $quartier_id ID du quartier
 * @return string ID unique
 */
function generate_provider_id(int $quartier_id): string
{
    $year = date('Y');
    $quarter_code = str_pad($quartier_id, 3, '0', STR_PAD_LEFT);
    $random = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    return "QTR-{$year}-{$quarter_code}-{$random}";
}

/**
 * Générer un code pour un chef de quartier
 *
 * @param string $quartier_name Nom du quartier
 * @return string Code unique
 */
function generate_chef_code(string $quartier_name): string
{
    $year = date('Y');
    $quarter_code = strtoupper(substr($quartier_name, 0, 3));
    $random = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
    
    return "CHF-{$year}-{$quarter_code}-{$random}";
}

/**
 * Obtenir les catégories de services disponibles
 *
 * @return array Catégories de services
 */
function get_service_categories(): array
{
    return [
        'menuiserie' => 'Menuiserie',
        'coiffure' => 'Coiffure',
        'couture' => 'Couture',
        'reparation' => 'Réparation',
        'nettoyage' => 'Nettoyage',
        'jardinage' => 'Jardinage',
        'peinture' => 'Peinture',
        'plomberie' => 'Plomberie',
        'electricite' => 'Électricité',
        'cuisine' => 'Cuisine',
        'livraison' => 'Livraison',
        'education' => 'Éducation',
        'sante' => 'Santé',
        'autre' => 'Autre'
    ];
}

/**
 * Obtenir les régions de Madagascar
 *
 * @return array Régions
 */
function get_madagascar_regions(): array
{
    return [
        'Analamanga',
        'Vakinankaratra',
        'Itasy',
        'Bongolava',
        'Haute Matsiatra',
        'Amoron\'i Mania',
        'Vatovavy Fitovinany',
        'Ihorombe',
        'Atsimo Atsinanana',
        'Atsinanana',
        'Analanjirofo',
        'Alaotra Mangoro',
        'Boeny',
        'Sofia',
        'Betsiboka',
        'Melaky',
        'Atsimo Andrefana',
        'Androy',
        'Anosy',
        'Menabe',
        'Diana',
        'Sava'
    ];
}

/**
 * ============================
 * FONCTIONS DE REDIRECTION
 * ============================
 */

/**
 * Rediriger vers une URL
 *
 * @param string $url URL de destination
 * @param int $status_code Code de statut HTTP
 */
function redirect(string $url, int $status_code = 302): void
{
    //Sauvegarder la session avant la redirection
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }

    header("Location: $url", true, $status_code);
    exit();
}

/**
 * Définit un message flash dans la session.
 *
 * @param string $message Message à afficher.
 * @param string $type Type de message (success, error, warning, info).
 */
function set_flash_message(string $message, string $type = 'info'): void
{
    // S'assurer que la session est démarrée avant de l'utiliser
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['flash_message'] = ['message' => $message, 'type' => $type];
}


/**
 * Rediriger avec un message flash
 *
 * @param string $url URL de destination
 * @param string $message Message à afficher
 * @param string $type Type de message (success, error, warning, info)
 */
function redirect_with_message(string $url, string $message, string $type = 'info'): void
{
    set_flash_message($message, $type); // Utilise la nouvelle fonction set_flash_message
    redirect($url);
}

/**
 * Obtenir et supprimer un message flash
 *
 * @return array|null Message flash
 */
function get_flash_message(): ?array
{
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Stocke les anciennes données de saisie dans la session.
 * Utile pour pré-remplir les formulaires après une redirection due à une erreur.
 *
 * @param array $input Les données de saisie à stocker.
 */
function set_old_input(array $input): void
{
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['old_input'] = $input;
}

/**
 * Récupère les anciennes données de saisie de la session et les supprime.
 *
 * @return array Les anciennes données de saisie.
 */
function get_old_input(): array
{
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    $oldInput = $_SESSION['old_input'] ?? [];
    unset($_SESSION['old_input']);
    return $oldInput;
}

/**
 * ============================
 * FONCTIONS DE DEBUGGING
 * ============================
 */

/**
 * Fonction de debug améliorée
 *
 * @param mixed $data Données à afficher
 * @param bool $die Arrêter l'exécution
 */
function dd($data, bool $die = true): void
{
    echo '<pre style="background: #000; color: #fff; padding: 10px; border-radius: 5px; margin: 10px 0;">';
    var_dump($data);
    echo '</pre>';
    
    if ($die) {
        die();
    }
}

/**
 * Logger une erreur
 *
 * @param string $message Message d'erreur
 * @param array $context Contexte supplémentaire
 */
function log_error(string $message, array $context = []): void
{
    $log_message = date('Y-m-d H:i:s') . ' - ERROR: ' . $message;
    
    if (!empty($context)) {
        $log_message .= ' - Context: ' . json_encode($context);
    }
    
    $log_message .= "\n";
    
    error_log($log_message, 3, __DIR__ . '/../storage/logs/app.log');
}

/**
 * ============================
 * FONCTIONS DE CACHE
 * ============================
 */

/**
 * Obtenir l'URL de base de l'application
 *
 * @return string URL de base
 */
function base_url(): string
{
    return $_ENV['APP_URL'] ?? 'http://localhost/hairymada';
}

/**
 * Obtenir l'URL d'un asset
 *
 * @param string $path Chemin vers l'asset
 * @return string URL complète
 */
function asset_url(string $path): string
{
    return base_url() . '/public/assets/' . ltrim($path, '/');
}

/**
 * Obtenir l'URL d'un upload
 *
 * @param string $path Chemin vers le fichier
 * @return string URL complète
 */
function upload_url(string $path): string
{
    return base_url() . '/public/uploads/' . ltrim($path, '/');
}