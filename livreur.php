<?php
// Demarrer la session
session_start();
require("script_php/recup.php");

// Verifier si livreur ou admin
if (!isset($_SESSION['utilisateur_id']) || ($_SESSION['utilisateur_role'] != 'livreur' && $_SESSION['utilisateur_role'] != 'admin')) {
    header("Location: connexion.php");
    exit;
}
verifier_blocage();

// Charger les commandes
$commandes = lire_donnees_json('commandes');
$mon_id = $_SESSION['utilisateur_id'];

// Traitement quand le livreur clique sur un bouton
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cmd_id = $_POST['commande_id'];
    $action = $_POST['action'];
    
    foreach ($commandes as $i => $cmd) {
        if ($cmd['id'] == $cmd_id) {
            // Marquer la commande comme livree
            if ($action == 'livree') {
                $commandes[$i]['statut'] = 'livree';
                $commandes[$i]['date_livraison'] = date('Y-m-d H:i:s');
            }
            // Ou comme annulee si adresse introuvable
            if ($action == 'abandonner') {
                $commandes[$i]['statut'] = 'annulee';
            }
        }
    }
    // Sauvegarder
    ecrire_donnees_json('commandes', $commandes);
    header("Location: livreur.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Livreur</title>
    <!-- id pour le changement de theme -->
    <link rel="stylesheet" href="style.css" id="theme-css">
</head>
<body class="livreur-mode">
    <header><h1>Espace Livreur</h1></header>
    <?php include("script_php/nav.php"); ?>
    
    <div class="container">
        <?php
        $a_livraison = false;
        foreach ($commandes as $cmd):
            if ($cmd['livreur_id'] != $mon_id || $cmd['statut'] != 'en_livraison') continue;
            $a_livraison = true;
            $client = trouver_par_id($cmd['client_id']);
        ?>
        <div class="card">
            <h2>Commande #<?php echo $cmd['id']; ?></h2>
            
            <div class="address-box">
                <p><strong>Client :</strong> <?php echo proteger($client['prenom'] . ' ' . $client['nom']); ?></p>
                <p><strong>Adresse :</strong> <?php echo proteger($cmd['adresse_livraison']); ?></p>
                <p><strong>Code :</strong> <?php echo proteger($cmd['infos_complementaires']); ?></p>
                <p><strong>Tel :</strong> <?php echo proteger($client['telephone']); ?></p>
            </div>

            <a href="https://waze.com/ul?q=<?php echo urlencode($cmd['adresse_livraison']); ?>" target="_blank">
                <button class="btn btn-big btn-secondaire" type="button">
                    🗺️ GPS (WAZE/MAPS)
                </button>
            </a>

            <form method="post">
                <input type="hidden" name="commande_id" value="<?php echo $cmd['id']; ?>">
                <input type="hidden" name="action" value="livree">
                <button type="submit" class="btn btn-big btn-succes">
                    ✅ LIVRAISON TERMINÉE
                </button>
            </form>

            <form method="post" class="marge-haut">
                <input type="hidden" name="commande_id" value="<?php echo $cmd['id']; ?>">
                <input type="hidden" name="action" value="abandonner">
                <button type="submit" class="btn btn-big btn-danger">
                    ❌ ADRESSE INTROUVABLE
                </button>
            </form>
        </div>
        <?php endforeach; ?>

        <?php if (!$a_livraison): ?>
            <div class="card texte-centre">
                <h2>Pas de livraison en cours</h2>
                <p>En attente d'une nouvelle commande...</p>
            </div>
        <?php endif; ?>
    </div>

    <script src="js/theme.js"></script>
</body>
</html>
