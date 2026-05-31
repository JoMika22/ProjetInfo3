<?php

session_start();
require("script_php/recup.php");
require("script_php/config_cybank.php");

if (!isset($_SESSION['utilisateur_id'])) {
    header("Location: connexion.php");
    exit;
}
verifier_blocage();

// S'il n'y a aucun paiement prepare, on retourne au panier
if (!isset($_SESSION['paiement_en_cours'])) {
    header("Location: panier.php");
    exit;
}

$p = $_SESSION['paiement_en_cours'];
$transaction = $p['transaction'];
$montant = cybank_format_montant($p['montant']);
$vendeur = CYBANK_VENDEUR;

// Construire l'URL de retour absolue vers retour_paiement.php (meme dossier que ce fichier)
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$dossier = rtrim(str_replace('\\', '/', dirname($_SERVER['PHP_SELF'])), '/');
$retour = $scheme . '://' . $host . $dossier . '/retour_paiement.php';

// Valeur de controle (le montant et l'URL de retour utilises ici doivent etre
// exactement ceux envoyes dans le formulaire ci-dessous)
$control = cybank_control_envoi($transaction, $montant, $vendeur, $retour);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement sécurisé</title>
    <link rel="stylesheet" href="style.css" id="theme-css">
</head>
<body>
    <header><h1>Paiement sécurisé</h1></header>
    <?php include("script_php/nav.php"); ?>
    <div class="container">

        <?php if (!cybank_est_configure()): ?>
            <div class="card alerte-erreur">
                <p><strong>CYBank n'est pas encore configuré.</strong></p>
                <p>Pour activer le paiement réel, il faut :</p>
                <ul>
                    <li>mettre votre code vendeur (identifiant de groupe) dans <code>script_php/config_cybank.php</code> ;</li>
                    <li>déposer le fichier <code>getapikey.php</code> dans le dossier <code>script_php/</code>.</li>
                </ul>
            </div>
        <?php endif; ?>

        <div class="card texte-centre">
            <h2>Récapitulatif</h2>
            <p>Montant à payer : <strong><?php echo proteger($montant); ?> €</strong></p>
            <p>Transaction : <?php echo proteger($transaction); ?></p>
            <p><em>Carte de test CYBank : 5555 1234 5678 9000 — cryptogramme 555 — date au choix.</em></p>

            <!-- Ce formulaire envoie les donnees de la transaction a CYBank -->
            <form action="<?php echo proteger(CYBANK_URL); ?>" method="POST">
                <input type="hidden" name="transaction" value="<?php echo proteger($transaction); ?>">
                <input type="hidden" name="montant" value="<?php echo proteger($montant); ?>">
                <input type="hidden" name="vendeur" value="<?php echo proteger($vendeur); ?>">
                <input type="hidden" name="retour" value="<?php echo proteger($retour); ?>">
                <input type="hidden" name="control" value="<?php echo proteger($control); ?>">
                <button type="submit" class="btn btn-valider">Payer <?php echo proteger($montant); ?> € via CYBank</button>
            </form>

            <p><a href="panier.php" class="lien-rouge">Annuler</a></p>
        </div>

    </div>
    <script src="js/theme.js"></script>
</body>
</html>
