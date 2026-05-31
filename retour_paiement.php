<?php

session_start();
require("script_php/recup.php");
require("script_php/config_cybank.php");

if (!isset($_SESSION['utilisateur_id'])) {
    header("Location: connexion.php");
    exit;
}
verifier_blocage();

$user = trouver_par_id($_SESSION['utilisateur_id']);

// Recuperer les parametres renvoyes par CYBank
$transaction = isset($_GET['transaction']) ? $_GET['transaction'] : '';
$montant     = isset($_GET['montant']) ? $_GET['montant'] : '';
$vendeur     = isset($_GET['vendeur']) ? $_GET['vendeur'] : '';
// CYBank renvoie le statut sous le nom "status" (valeurs accepted / declined)
$statut      = isset($_GET['status']) ? $_GET['status'] : (isset($_GET['statut']) ? $_GET['statut'] : '');
$control     = isset($_GET['control']) ? $_GET['control'] : '';

$succes = false;
$message = "";

// 1) Il doit y avoir un paiement en cours dans la session
if (!isset($_SESSION['paiement_en_cours'])) {
    $message = "Aucun paiement en cours. Le paiement a peut-être déjà été traité.";
} else {
    $p = $_SESSION['paiement_en_cours'];

    // 2) La transaction et le montant renvoyes doivent correspondre a ce qu'on a initie
    $montant_attendu = cybank_format_montant($p['montant']);
    $coherent = ($transaction === $p['transaction'] && $montant === $montant_attendu);

    // 3) La valeur de controle doit etre valide (integrite + authenticite)
    $control_calcule = cybank_control_retour($transaction, $montant, $vendeur, $statut);
    $controle_ok = ($control !== '' && hash_equals($control_calcule, $control));

    if (!$coherent || !$controle_ok) {
        // Donnees incoherentes ou falsifiees -> on refuse
        log_incident('paiement_invalide', "Retour CYBank invalide pour la transaction " . $transaction . " (utilisateur " . $user['email'] . ")");
        $message = "Le paiement n'a pas pu être vérifié (données non conformes). Aucune commande n'a été enregistrée.";
    } elseif ($statut === 'accepted') {
        //  Paiement authentique et accepte
        if ($p['contexte'] === 'panier') {
            // Creer la commande maintenant (et seulement maintenant)
            $commandes = lire_donnees_json('commandes');
            $total = (float) $p['montant'];
            $nouvelle = [
                'id' => generer_id($commandes),
                'client_id' => $user['id'],
                'articles' => $p['panier'],
                'total' => $total,
                'montant_paye' => $total,
                'statut' => 'en_attente',
                'type' => $p['type'],
                'adresse_livraison' => $user['adresse'],
                'infos_complementaires' => isset($user['infos_complementaires']) ? $user['infos_complementaires'] : '',
                'date_commande' => date('Y-m-d H:i:s'),
                'date_souhaitee' => isset($p['date_souhaitee']) ? $p['date_souhaitee'] : null,
                'date_livraison' => null,
                'livreur_id' => null,
                'note_repas' => null,
                'note_livraison' => null,
                'commentaire' => null,
                'transaction' => $transaction
            ];
            $commandes[] = $nouvelle;
            ecrire_donnees_json('commandes', $commandes);

            // Vider le panier et le paiement en cours
            $_SESSION['panier'] = [];
            unset($_SESSION['paiement_en_cours']);
            $succes = true;
            $message = "Paiement accepté ! Votre commande a été enregistrée.";
        } elseif ($p['contexte'] === 'diff') {
            // Paiement du complement sur une commande existante
            $commandes = lire_donnees_json('commandes');
            foreach ($commandes as $i => $cmd) {
                if ($cmd['id'] == $p['commande_id'] && $cmd['client_id'] == $user['id']) {
                    $deja = isset($cmd['montant_paye']) ? $cmd['montant_paye'] : 0;
                    $commandes[$i]['montant_paye'] = $deja + (float) $p['montant'];
                }
            }
            ecrire_donnees_json('commandes', $commandes);
            unset($_SESSION['paiement_en_cours']);
            $succes = true;
            $message = "Paiement complémentaire accepté ! Votre commande est à jour.";
        }
    } else {
        // Statut "declined"  mais authentique
        unset($_SESSION['paiement_en_cours']);
        $message = "Le paiement a été refusé. Aucune commande n'a été enregistrée.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultat du paiement</title>
    <link rel="stylesheet" href="style.css" id="theme-css">
</head>
<body>
    <header><h1>Résultat du paiement</h1></header>
    <?php include("script_php/nav.php"); ?>
    <div class="container">
        <div class="card <?php echo $succes ? 'alerte-succes' : 'alerte-erreur'; ?>">
            <p><?php echo proteger($message); ?></p>
            <a href="profil.php" class="btn">Voir mes commandes</a>
            <a href="carte.php" class="btn">Retour à la carte</a>
        </div>
    </div>
    <script src="js/theme.js"></script>
</body>
</html>
