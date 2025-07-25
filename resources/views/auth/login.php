<h1>Formulaire de connexion</h1>
<p>Connectez-vous à votre compte HairyMada.</p>
<form action="/login" method="POST">
    <label for="email">Email / Téléphone:</label><br>
    <input type="text" id="email" name="email" required value="<?php echo htmlspecialchars($old_input['email'] ?? ''); ?>"><br><br>

    <label for="password">Mot de passe:</label><br>
    <input type="password" id="password" name="password" required><br><br>

    <button type="submit">Se connecter</button>
</form>
<p>Pas encore de compte ? <a href="/register">Inscrivez-vous ici</a>.</p>
<p><a href="/forgot-password">Mot de passe oublié ?</a></p>