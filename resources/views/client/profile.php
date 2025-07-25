<h1>Mon Profil</h1>
<p>Bienvenue sur votre page de profil, '<?php echo htmlspecialchars($user->getFullName() ?? 'Utilisateur'); ?>'' !</p>
<p>Ceci est un exemple de votre tableau de bord personnel.</p>
<p>Email: <?php echo htmlspecialchars($user->email ?? 'N/A'); ?></p>
<p>Téléphone: <?php echo htmlspecialchars($user->phone ?? 'N/A'); ?></p>
<p>Quartier: <?php echo htmlspecialchars($user->quartier ?? 'N/A'); ?></p>
<p>Ce n'est encore loin d'être la version final, be patient please !!!.</p>
<p><a href="/">Retour à l'accueil</a></p>
<p><a href="/logout">Se déconnecter</a></p>