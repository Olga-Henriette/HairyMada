<h1>Formulaire d'inscription</h1>
<p>Bienvenue sur la page d'inscription de HairyMada !</p>
<form action="/register" method="POST">
    <label for="first_name">Prénom:</label><br>
    <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($old_input['first_name'] ?? ''); ?>"><br><br>

    <label for="last_name">Nom:</label><br>
    <input type="text" id="last_name" name="last_name" required value="<?php echo htmlspecialchars($old_input['last_name'] ?? ''); ?>"><br><br>

    <label for="email">Email:</label><br>
    <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($old_input['email'] ?? ''); ?>"><br><br>

    <label for="phone">Téléphone:</label><br>
    <input type="text" id="phone" name="phone" required value="<?php echo htmlspecialchars($old_input['phone'] ?? ''); ?>"><br><br>

    <label for="password">Mot de passe:</label><br>
    <input type="password" id="password" name="password" required><br><br>

    <label for="password_confirm">Confirmer le mot de passe:</label><br>
    <input type="password" id="password_confirm" name="password_confirm" required><br><br>

    <label for="address">Adresse:</label><br>
    <input type="text" id="address" name="address" required value="<?php echo htmlspecialchars($old_input['address'] ?? ''); ?>"><br><br>

    <label for="quartier">Quartier:</label><br>
    <input type="text" id="quartier" name="quartier" required value="<?php echo htmlspecialchars($old_input['quartier'] ?? ''); ?>"><br><br>

    <button type="submit">S'inscrire</button>
</form>
<p>Déjà un compte ? <a href="/login">Connectez-vous ici</a>.</p>