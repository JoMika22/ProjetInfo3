<?php
// Demarrer la session
session_start();
require("script_php/recup.php");

$erreur = "";

// Redirection si deja connecté
if (isset($_SESSION['utilisateur_id'])) {
    header("Location: profil.php");
    exit;
}

// Traitement du formulaire quand on clique sur "Se connecter"
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $mdp = $_POST['mot_de_passe'];
    
    // On cherche l'utilisateur dans le fichier JSON
    $user = trouver_par_email($email);
    
    // Si l'utilisateur existe et que le mot de passe est bon
    // password_verify compare le mot de passe saisi avec le hash stocke
    if ($user != null && password_verify($mdp, $user['mot_de_passe'])) {
        // Refuser l'acces si le compte est bloque
        if (isset($user['bloque']) && $user['bloque'] == true) {
            log_incident('connexion_bloquee', "Tentative de connexion sur le compte bloque : " . $email);
            $erreur = "Ce compte a été bloqué. Contactez un administrateur.";
        } else {
            // On stocke les infos dans la session
            $_SESSION['utilisateur_id'] = $user['id'];
            $_SESSION['utilisateur_nom'] = $user['nom'];
            $_SESSION['utilisateur_prenom'] = $user['prenom'];
            $_SESSION['utilisateur_role'] = $user['role'];
            
            // Redirection selon le role
            if ($user['role'] == 'admin') {
                header("Location: admin.php");
            } elseif ($user['role'] == 'restaurateur') {
                header("Location: restaurateur.php");
            } elseif ($user['role'] == 'livreur') {
                header("Location: livreur.php");
            } else {
                header("Location: profil.php");
            }
            exit;
        }
    } else {
        log_incident('mauvais_mdp', "Echec de connexion pour l'email : " . $email);
        $erreur = "Email ou mot de passe incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <!-- le lien CSS a un id pour pouvoir changer la charte graphique en JS -->
    <link rel="stylesheet" href="style.css" id="theme-css">
</head>
<body>
    <header><h1>Connexion</h1></header>
    <?php include("script_php/nav.php"); ?>
    <div class="container">

        <?php if (isset($_GET['inscription'])): ?>
            <div class="card alerte-succes">
                <p>Inscription réussie ! Connectez-vous.</p>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['bloque'])): ?>
            <div class="card alerte-erreur">
                <p>Votre session a été fermée car votre compte a été bloqué.</p>
            </div>
        <?php endif; ?>

        <?php if ($erreur != ""): ?>
            <div class="card alerte-erreur">
                <p><?php echo $erreur; ?></p>
            </div>
        <?php endif; ?>

        <!-- zone d'erreur affichee par le JS -->
        <div id="erreur-connexion" class="card zone-erreur"></div>

        <form class="card form-centrer" method="post" id="formulaire-connexion">
            <label for="champ-email">Email :</label>
            <input type="email" name="email" id="champ-email" maxlength="50">
            <!-- compteur de caracteres pour l'email -->
            <div class="compteur-caracteres" id="compteur-email">0 / 50</div>

            <label for="champ-mdp">Mot de passe :</label>
            <!-- Conteneur pour positionner l'icone oeil -->
            <div class="champ-mdp-conteneur">
                <input type="password" name="mot_de_passe" id="champ-mdp" maxlength="30">
                <span class="icone-oeil" id="oeil-mdp" onclick="basculerMotDePasse('champ-mdp','oeil-mdp')">👁️</span>
            </div>
            <div class="compteur-caracteres" id="compteur-mdp">0 / 30</div>

            <button type="submit" class="btn">Se connecter</button>
        </form>
    </div>

    <!-- Scripts JS -->
    <script src="js/theme.js"></script>
    <script src="js/validation.js"></script>
    <script>
        // Activer les compteurs de caracteres et brancher la validation
        // (on attend que le DOM soit pret)
        window.addEventListener("load", function() {
            activerCompteur("champ-email", "compteur-email", 50);
            activerCompteur("champ-mdp", "compteur-mdp", 30);

            // Brancher la validation sur la soumission du formulaire
            var form = document.getElementById("formulaire-connexion");
            form.addEventListener("submit", validerConnexion);
        });
    </script>
</body>
</html>
