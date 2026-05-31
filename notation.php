<?php
// Demarrer la session
session_start();
require("script_php/recup.php");

// Verifier si connecté
if (!isset($_SESSION['utilisateur_id'])) {
    header("Location: connexion.php");
    exit;
}
verifier_blocage();

$mon_id = $_SESSION['utilisateur_id'];
// Recuperer l'id de la commande a noter (depuis l'URL)
$commande_id = isset($_GET['commande_id']) ? $_GET['commande_id'] : 0;
$message = "";

// Traitement quand le client envoie sa notation
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cmd_id = $_POST['commande_id'];
    $commandes = lire_donnees_json('commandes');
    
    // Chercher la commande et enregistrer les notes
    foreach ($commandes as $i => $cmd) {
        if ($cmd['id'] == $cmd_id && $cmd['client_id'] == $mon_id) {
            $commandes[$i]['note_repas'] = $_POST['note_repas'];
            $commandes[$i]['note_livraison'] = $_POST['note_livraison'];
            $commandes[$i]['commentaire'] = $_POST['commentaire'];
        }
    }
    // Sauvegarder
    ecrire_donnees_json('commandes', $commandes);
    $message = "Merci pour votre avis !";
}

// Trouver la commande a noter
$commande = null;
if ($commande_id > 0) {
    $commandes = lire_donnees_json('commandes');
    foreach ($commandes as $c) {
        if ($c['id'] == $commande_id && $c['client_id'] == $mon_id && $c['statut'] == 'livree') {
            $commande = $c;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avis</title>
    <!-- id pour le changement de theme -->
    <link rel="stylesheet" href="style.css" id="theme-css">
</head>
<body>
    <header><h1>Votre Avis</h1></header>
    <?php include("script_php/nav.php"); ?>
    <div class="container">

        <?php if ($message != ""): ?>
            <div class="card alerte-succes">
                <p><?php echo $message; ?></p>
                <a href="profil.php" class="btn">Retour au profil</a>
            </div>

        <?php elseif ($commande != null): ?>

        <!-- Zone d'erreur pour la validation JS -->
        <div id="erreur-notation" class="card zone-erreur"></div>

        <div class="card">
            <h2>Noter la commande #<?php echo $commande['id']; ?></h2>
            <form method="post" id="formulaire-notation">
                <input type="hidden" name="commande_id" value="<?php echo $commande['id']; ?>">
            
                <label for="note-repas">Qualité du repas :</label>
                <select name="note_repas" id="note-repas">
                    <option value="5">5 étoiles - Excellent</option>
                    <option value="4">4 étoiles - Très bon</option>
                    <option value="3">3 étoiles - Moyen</option>
                    <option value="2">2 étoiles - Passable</option>
                    <option value="1">1 étoile - Mauvais</option>
                </select>

                <label for="note-livraison">Qualité de la livraison :</label>
                <select name="note_livraison" id="note-livraison">
                    <option value="5">5 étoiles - Rapide et aimable</option>
                    <option value="4">4 étoiles - Rapide</option>
                    <option value="3">3 étoiles - Moyen</option>
                    <option value="2">2 étoiles - Lent / Non aimable</option>
                    <option value="1">1 étoile - Retard / Froid</option>
                </select>

                <label for="champ-commentaire">Commentaire :</label>
                <textarea name="commentaire" id="champ-commentaire" maxlength="300" placeholder="Dites-nous en plus..."></textarea>
                <!-- Phase 3 : compteur de caracteres pour le commentaire -->
                <div class="compteur-caracteres" id="compteur-commentaire">0 / 300</div>

                <button type="submit" class="btn">Envoyer mon avis</button>
            </form>
        </div>

        <?php else: ?>
        <div class="card texte-centre">
            <p>Sélectionnez une commande à noter depuis votre <a href="profil.php">profil</a>.</p>
        </div>
        <?php endif; ?>

    </div>

    <!-- scripts JS -->
    <script src="js/theme.js"></script>
    <script src="js/validation.js"></script>
    <script>
        window.addEventListener("load", function() {
            // Le compteur et la validation ne servent que si le formulaire existe
            var form = document.getElementById("formulaire-notation");
            if (form != null) {
                activerCompteur("champ-commentaire", "compteur-commentaire", 300);
                form.addEventListener("submit", validerNotation);
            }
        });
    </script>
</body>
</html>
