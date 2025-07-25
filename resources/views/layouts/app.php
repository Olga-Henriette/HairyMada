<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'HairyMada'); ?></title>
    <link rel="stylesheet" href="/assets/css/main.css">
    </head>
<body>
    <div class="container">
        <?php
        // Affichage des messages flash pour les pages de l'application
        if (function_exists('get_flash_message')) {
            $flashMessage = get_flash_message();
            if ($flashMessage) {
                echo '<div class="alert alert-' . htmlspecialchars($flashMessage['type']) . '">';
                echo htmlspecialchars($flashMessage['message']);
                echo '</div>';
            }
        } else {
             if (session_status() == PHP_SESSION_NONE) {
                session_start();
             }
             if (isset($_SESSION['flash_message'])) {
                 $flashMessage = $_SESSION['flash_message'];
                 unset($_SESSION['flash_message']);
                 echo '<div class="alert alert-' . htmlspecialchars($flashMessage['type']) . '">';
                 echo htmlspecialchars($flashMessage['message']);
                 echo '</div>';
             }
        }
        ?>

        <?php if (isset($content)) { echo $content; } ?>
    </div>
</body>
</html>