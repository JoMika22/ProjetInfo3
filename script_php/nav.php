<!-- Barre de navigation qui change selon le role de l'utilisateur -->
<nav>
    <a href="index.php">Accueil</a>
    <a href="carte.php">Carte</a>

    <?php if (!isset($_SESSION['utilisateur_id'])): ?>
        <!-- Visiteur non connecté -->
        <a href="inscriptions.php">Inscription</a>
        <a href="connexion.php">Connexion</a>

    <?php elseif ($_SESSION['utilisateur_role'] == 'client'): ?>
        <!-- Menu client -->
        <a href="profil.php">Profil</a>
        <a href="panier.php">Panier</a>
        <a href="notation.php">Notation</a>
        <a href="script_php/deconnexion.php">Déconnexion</a>

    <?php elseif ($_SESSION['utilisateur_role'] == 'restaurateur'): ?>
        <!-- Menu restaurateur -->
        <a href="restaurateur.php">Commandes</a>
        <a href="script_php/deconnexion.php">Déconnexion</a>

    <?php elseif ($_SESSION['utilisateur_role'] == 'livreur'): ?>
        <!-- Menu livreur -->
        <a href="livreur.php">Mes livraisons</a>
        <a href="script_php/deconnexion.php">Déconnexion</a>

    <?php elseif ($_SESSION['utilisateur_role'] == 'admin'): ?>
        <!-- Menu admin (acces a tout) -->
        <a href="profil.php">Profil</a>
        <a href="admin.php">Admin</a>
        <a href="restaurateur.php">Restaurateur</a>
        <a href="livreur.php">Livreur</a>
        <a href="notation.php">Notation</a>
        <a href="script_php/deconnexion.php">Déconnexion</a>
    <?php endif; ?>

    <!-- bouton de changement de charte graphique -->
    <button type="button" id="btn-theme" class="btn-theme">🌙 Mode sombre</button>
</nav>