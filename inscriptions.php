<?php
// Demarrer la session
session_start();
require("script_php/recup.php");

$erreur = "";

// Traitement du formulaire quand on clique sur "S'inscrire"
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recuperer les données du formulaire
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $mdp = $_POST['mot_de_passe'];
    $mdp2 = $_POST['confirmation'];
    $adresse = $_POST['adresse'];
    $telephone = $_POST['telephone'];
    $infos = $_POST['infos_sup'];
    
    // Verifications
    if ($mdp != $mdp2) {
        $erreur = "Les mots de passe ne correspondent pas.";
    } elseif (trouver_par_email($email) != null) {
        $erreur = "Cet email est déjà utilisé.";
    } else {
        // Lire les utilisateurs existants
        $utilisateurs = lire_donnees_json('utilisateur');
        
        // Creer le nouvel utilisateur
        $nouveau = [
            'id' => generer_id($utilisateurs),
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'mot_de_passe' => password_hash($mdp, PASSWORD_DEFAULT),
            'role' => 'client',
            'adresse' => $adresse,
            'telephone' => $telephone,
            'infos_complementaires' => $infos,
            'date_inscription' => date('Y-m-d')
        ];
        
        // Ajouter au tableau et sauvegarder dans le fichier JSON
        $utilisateurs[] = $nouveau;
        ecrire_donnees_json('utilisateur', $utilisateurs);
        
        // Rediriger vers la page de connexion
        header("Location: connexion.php?inscription=ok");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <!-- id pour le changement de theme -->
    <link rel="stylesheet" href="style.css" id="theme-css">
</head>
<body>
    <header><h1>Nous rejoindre</h1></header>
    <?php include("script_php/nav.php"); ?>
    <div class="container">

        <?php if ($erreur != ""): ?>
            <div class="card alerte-erreur">
                <p><?php echo $erreur; ?></p>
            </div>
        <?php endif; ?>

        <!-- zone d'erreur pour la validation JS -->
        <div id="erreur-inscription" class="card zone-erreur"></div>

        <form class="card" method="post" id="formulaire-inscription">
            <h2>Créer un compte</h2>
            <label for="champ-nom">Nom :</label>
            <input type="text" name="nom" id="champ-nom" maxlength="30">

            <label for="champ-prenom">Prénom :</label>
            <input type="text" name="prenom" id="champ-prenom" maxlength="30">

            <label for="champ-email">Email :</label>
            <input type="email" name="email" id="champ-email" maxlength="50">
            <div class="compteur-caracteres" id="compteur-email">0 / 50</div>

            <label for="champ-mdp">Mot de passe :</label>
            <div class="champ-mdp-conteneur">
                <input type="password" name="mot_de_passe" id="champ-mdp" maxlength="30">
                <span class="icone-oeil" id="oeil-mdp" onclick="basculerMotDePasse('champ-mdp','oeil-mdp')">👁️</span>
            </div>
            <div class="compteur-caracteres" id="compteur-mdp">0 / 30</div>

            <label for="champ-mdp2">Confirmer le mot de passe :</label>
            <div class="champ-mdp-conteneur">
                <input type="password" name="confirmation" id="champ-mdp2" maxlength="30">
                <span class="icone-oeil" id="oeil-mdp2" onclick="basculerMotDePasse('champ-mdp2','oeil-mdp2')">👁️</span>
            </div>

            <label for="champ-adresse">Adresse complète :</label>
            <input type="text" name="adresse" id="champ-adresse">

            <label for="champ-tel">Téléphone :</label>
            <input type="tel" name="telephone" id="champ-tel" maxlength="14">

            <label for="champ-infos">Informations complémentaires (Code, Étage...) :</label>
            <textarea name="infos_sup" id="champ-infos"></textarea>

            <button type="submit" class="btn">S'inscrire</button>
        </form>
    </div>

    <script src="js/theme.js"></script>
    <script src="js/validation.js"></script>
    <script>
        window.addEventListener("load", function() {
            activerCompteur("champ-email", "compteur-email", 50);
            activerCompteur("champ-mdp", "compteur-mdp", 30);

            var form = document.getElementById("formulaire-inscription");
            form.addEventListener("submit", validerInscription);
        });
    </script>
</body>
</html>
