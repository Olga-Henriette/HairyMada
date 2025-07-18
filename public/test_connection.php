<?php
/**
 * Test de connexion à la base de données
 * URL: http://localhost/hairymada/public/test_connection.php
 */

// Affichage des erreurs pour le développement
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🧪 Test de connexion HairyMada</h1>";

try {
    // Test 1: Chargement du fichier de configuration
    echo "<h2>Test 1: Chargement de la configuration</h2>";
    
    if (file_exists('../config/database.php')) {
        echo "✅ Fichier config/database.php trouvé<br>";
        require_once '../config/database.php';
        echo "✅ Configuration chargée avec succès<br>";
    } else {
        throw new Exception("❌ Fichier config/database.php introuvable");
    }
    
    // Test 2: Vérification du fichier .env
    echo "<h2>Test 2: Vérification du fichier .env</h2>";
    
    if (file_exists('../.env')) {
        echo "✅ Fichier .env trouvé<br>";
        echo "📋 Configuration BDD:<br>";
        echo "- Host: " . ($_ENV['DB_HOST'] ?? 'non défini') . "<br>";
        echo "- Database: " . ($_ENV['DB_DATABASE'] ?? 'non défini') . "<br>";
        echo "- Username: " . ($_ENV['DB_USERNAME'] ?? 'non défini') . "<br>";
    } else {
        throw new Exception("❌ Fichier .env introuvable");
    }
    
    // Test 3: Connexion à la base de données
    echo "<h2>Test 3: Connexion à la base de données</h2>";
    
    $db = Database::getInstance();
    $connection = $db->getConnection();
    
    echo "✅ Connexion à la base de données réussie !<br>";
    
    // Test 4: Test d'une requête simple
    echo "<h2>Test 4: Test d'une requête</h2>";
    
    $result = $db->query("SELECT 1 as test");
    $row = $result->fetch();
    
    if ($row['test'] == 1) {
        echo "✅ Requête de test exécutée avec succès<br>";
    }
    
    // Test 5: Vérification de l'existence de la table users
    echo "<h2>Test 5: Vérification de la table users</h2>";
    
    $result = $db->query("SHOW TABLES LIKE 'users'");
    if ($result->rowCount() > 0) {
        echo "✅ Table 'users' trouvée<br>";
        
        // Compter les utilisateurs
        $result = $db->query("SELECT COUNT(*) as count FROM users");
        $count = $result->fetch()['count'];
        echo "📊 Nombre d'utilisateurs: $count<br>";
    } else {
        echo "⚠️ Table 'users' non trouvée - Vous devez exécuter la migration<br>";
    }
    
    echo "<h2>✅ Tous les tests sont passés !</h2>";
    echo "<p style='color: green; font-weight: bold;'>La connexion à la base de données fonctionne correctement.</p>";
    echo "<p><a href='../'>← Retour à l'accueil</a></p>";
    
} catch (Exception $e) {
    echo "<h2>❌ Erreur détectée</h2>";
    echo "<p style='color: red; font-weight: bold;'>Erreur: " . $e->getMessage() . "</p>";
    echo "<p>Vérifiez:</p>";
    echo "<ul>";
    echo "<li>Que Laragon est démarré</li>";
    echo "<li>Que la base de données 'hairymada_db' existe</li>";
    echo "<li>Que le fichier .env est correctement configuré</li>";
    echo "<li>Que les fichiers de configuration sont présents</li>";
    echo "</ul>";
}
?>