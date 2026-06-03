<?php

session_start();
require("script_php/recup.php");
require("script_php/suivi.php"); // composant afficher_suivi()

if (!isset($_SESSION['utilisateur_id'])) {
    header("Location: connexion.php");
    exit;
}
verifier_blocage();

$commande_id = isset($_GET['commande_id']) ? intval($_GET['commande_id']) : 0;

// Retrouver la commande du client connecte
$commandes = lire_donnees_json('commandes');
$commande = null;
foreach ($commandes as $cmd) {
    if ($cmd['id'] == $commande_id && $cmd['client_id'] == $_SESSION['utilisateur_id']) {
        $commande = $cmd;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suivi de ma commande</title>
    <link rel="stylesheet" href="style.css" id="theme-css">
</head>
<body>
    <header><h1>Suivi de ma commande</h1></header>
    <?php include("script_php/nav.php"); ?>
    <div class="container">

        <?php if ($commande == null): ?>
            <div class="card texte-centre">
                <p>Cette commande n'existe pas ou ne vous appartient pas.</p>
                <a href="profil.php" class="btn">Retour au profil</a>
            </div>
        <?php else: ?>
            <div class="card">
                <h2 class="titre-centre">Commande #<?php echo (int)$commande['id']; ?></h2>
                <p class="texte-centre">
                    <?php echo proteger($commande['type'] == 'livraison' ? 'Livraison' : 'Sur place / à emporter'); ?>
                    — Total : <?php echo proteger($commande['total']); ?>€
                </p>

                <!-- Conteneur du suivi : rempli par PHP au chargement, puis rafraichi par suivi.js -->
                <div id="suivi-commande" class="suivi-commande" data-commande-id="<?php echo (int)$commande['id']; ?>">
                    <?php afficher_suivi($commande['statut'], $commande['type']); ?>
                </div>

                <p class="texte-centre" id="suivi-info">
                    <em>Cette page se met à jour automatiquement.</em>
                </p>

                <?php if ($commande['statut'] == 'livree' && $commande['note_repas'] == null): ?>
                    <p class="texte-centre">
                        <a href="notation.php?commande_id=<?php echo (int)$commande['id']; ?>" class="btn btn-valider">⭐ Noter cette commande</a>
                    </p>
                <?php endif; ?>
    
                <?php if ($commande['note_repas'] != null): ?>
                    <div class="card texte-centre">
                        <h3>Votre avis</h3>
                        <p>Qualité du repas : <?php echo proteger($commande['note_repas']); ?> / 5</p>
                        <p>Qualité de la livraison : <?php echo proteger($commande['note_livraison']); ?> / 5</p>
                        <?php if (!empty($commande['commentaire'])): ?>
                            <p>« <?php echo proteger($commande['commentaire']); ?> »</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card">
                <h3>Détail</h3>
                <ul>
                    <?php foreach ($commande['articles'] as $item):
                        $plat = trouver_plat($item['plat_id']);
                        if ($plat != null):
                    ?>
                        <li><?php echo proteger($item['quantite']); ?> x <?php echo proteger($plat['nom']); ?></li>
                    <?php endif; endforeach; ?>
                </ul>
            </div>

            <p class="texte-centre"><a href="profil.php" class="btn btn-retour">← Retour au profil</a></p>
        <?php endif; ?>

    </div>
    <script src="js/theme.js"></script>
    <script src="js/suivi.js"></script>
</body>
</html>
