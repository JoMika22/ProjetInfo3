<?php

session_start();
require("script_php/recup.php");
require("script_php/config_cybank.php");

// Acces reserve a un utilisateur connecte
if (!isset($_SESSION['utilisateur_id'])) {
    header("Location: connexion.php");
    exit;
}
verifier_blocage();

$user = trouver_par_id($_SESSION['utilisateur_id']);
$message = "";

// Recuperer l'id de la commande (GET au chargement, POST lors d'une action)
$commande_id = 0;
if (isset($_GET['commande_id'])) $commande_id = intval($_GET['commande_id']);
if (isset($_POST['commande_id'])) $commande_id = intval($_POST['commande_id']);

// Petite fonction interne : recalculer le total d'une commande
function recalculer_total($articles) {
    $total = 0;
    foreach ($articles as $item) {
        $plat = trouver_plat($item['plat_id']);
        if ($plat != null) {
            $total = $total + ($plat['prix'] * $item['quantite']);
        }
    }
    return $total;
}

// Charger les commandes et localiser celle qui nous interesse
$commandes = lire_donnees_json('commandes');
$indexCommande = -1;
foreach ($commandes as $i => $cmd) {
    // Securite : la commande doit appartenir au client connecte
    // et ne pas encore etre en preparation (statut "en_attente")
    if ($cmd['id'] == $commande_id && $cmd['client_id'] == $user['id'] && $cmd['statut'] == 'en_attente') {
        $indexCommande = $i;
    }
}

// Traitement des actions (uniquement si la commande est valide et modifiable)
if ($indexCommande != -1 && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action == 'retirer' && isset($_POST['index_article'])) {
        $idx = intval($_POST['index_article']);
        $nouveaux = [];
        foreach ($commandes[$indexCommande]['articles'] as $k => $art) {
            if ($k != $idx) $nouveaux[] = $art;
        }
        $commandes[$indexCommande]['articles'] = $nouveaux;
        $commandes[$indexCommande]['total'] = recalculer_total($nouveaux);
        ecrire_donnees_json('commandes', $commandes);
        $message = "Article retiré.";
    }

    if ($action == 'ajouter' && isset($_POST['plat_id'])) {
        $plat_id = intval($_POST['plat_id']);
        $quantite = isset($_POST['quantite']) ? intval($_POST['quantite']) : 1;
        if ($quantite < 1) $quantite = 1;

        $plat = trouver_plat($plat_id);
        if ($plat == null) {
            $message = "Ce plat n'existe pas.";
        } else {
            $articles = $commandes[$indexCommande]['articles'];
            $trouve = false;
            foreach ($articles as $k => $art) {
                if ($art['plat_id'] == $plat_id) {
                    $articles[$k]['quantite'] += $quantite;
                    $trouve = true;
                }
            }
            if (!$trouve) {
                $articles[] = ['plat_id' => $plat_id, 'quantite' => $quantite];
            }
            $commandes[$indexCommande]['articles'] = $articles;
            $commandes[$indexCommande]['total'] = recalculer_total($articles);
            ecrire_donnees_json('commandes', $commandes);
            $message = "Article ajouté.";
        }
    }

    if ($action == 'payer') {
        $total = $commandes[$indexCommande]['total'];
        $paye = isset($commandes[$indexCommande]['montant_paye']) ? $commandes[$indexCommande]['montant_paye'] : 0;
        if ($total > $paye) {
            $difference = $total - $paye;
            // On passe par CYBank pour encaisser la difference (paiement verifie au retour)
            $_SESSION['paiement_en_cours'] = [
                'contexte' => 'diff',
                'transaction' => cybank_transaction_id(),
                'montant' => $difference,
                'commande_id' => $commandes[$indexCommande]['id']
            ];
            header("Location: paiement.php");
            exit;
        }
    }
}

// Recharger la commande a jour pour l'affichage
$commande = ($indexCommande != -1) ? $commandes[$indexCommande] : null;

// Tous les plats pour le menu d'ajout
$tous_plats = lire_donnees_json('plat');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier ma commande</title>
    <link rel="stylesheet" href="style.css" id="theme-css">
</head>
<body>
    <header><h1>Modifier ma commande</h1></header>
    <?php include("script_php/nav.php"); ?>
    <div class="container">

        <?php if ($message != ""): ?>
            <div class="card alerte-succes"><p><?php echo proteger($message); ?></p></div>
        <?php endif; ?>

        <?php if ($commande == null): ?>
            <div class="card texte-centre">
                <p>Cette commande n'existe pas, ne vous appartient pas, ou est déjà en préparation (non modifiable).</p>
                <a href="profil.php" class="btn">Retour au profil</a>
            </div>
        <?php else:
            $total = $commande['total'];
            $paye = isset($commande['montant_paye']) ? $commande['montant_paye'] : 0;
            $difference = $total - $paye;
        ?>
            <div class="card">
                <h2>Commande #<?php echo (int)$commande['id']; ?></h2>
                <table class="table-admin">
                    <thead>
                        <tr class="en-tete-tableau">
                            <th>Plat</th><th>Prix</th><th>Quantité</th><th>Sous-total</th><th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($commande['articles'] as $index => $item):
                        $plat = trouver_plat($item['plat_id']);
                        if ($plat != null):
                    ?>
                        <tr>
                            <td><?php echo proteger($plat['nom']); ?></td>
                            <td><?php echo proteger($plat['prix']); ?>€</td>
                            <td><?php echo proteger($item['quantite']); ?></td>
                            <td><?php echo $plat['prix'] * $item['quantite']; ?>€</td>
                            <td>
                                <form method="post">
                                    <input type="hidden" name="commande_id" value="<?php echo (int)$commande['id']; ?>">
                                    <input type="hidden" name="action" value="retirer">
                                    <input type="hidden" name="index_article" value="<?php echo (int)$index; ?>">
                                    <button type="submit" class="btn btn-petit btn-danger">Retirer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endif; endforeach; ?>
                    </tbody>
                </table>
                <p class="total-panier"><strong>Total : <?php echo $total; ?>€</strong></p>
                <p>Déjà payé : <?php echo $paye; ?>€</p>

                <?php if ($difference > 0): ?>
                    <div class="card alerte-erreur">
                        <p>Votre commande a augmenté : il reste <strong><?php echo $difference; ?>€</strong> à payer.</p>
                        <form method="post">
                            <input type="hidden" name="commande_id" value="<?php echo (int)$commande['id']; ?>">
                            <input type="hidden" name="action" value="payer">
                            <button type="submit" class="btn btn-valider">Payer la différence (<?php echo $difference; ?>€) via CYBank</button>
                        </form>
                    </div>
                <?php elseif ($difference < 0): ?>
                    <div class="card alerte-succes">
                        <p>Votre commande a diminué. Un ticket de réduction de <strong><?php echo abs($difference); ?>€</strong> vous sera offert sur une prochaine commande.</p>
                    </div>
                <?php else: ?>
                    <p><em>Votre commande est entièrement payée.</em></p>
                <?php endif; ?>
            </div>

            <!-- Ajouter un article -->
            <div class="card">
                <h2>Ajouter un plat</h2>
                <form method="post">
                    <input type="hidden" name="commande_id" value="<?php echo (int)$commande['id']; ?>">
                    <input type="hidden" name="action" value="ajouter">

                    <label for="select-plat">Plat :</label>
                    <select name="plat_id" id="select-plat" class="select-filtre">
                        <?php foreach ($tous_plats as $p): ?>
                            <option value="<?php echo (int)$p['id']; ?>"><?php echo proteger($p['nom'] . ' (' . $p['prix'] . '€)'); ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label for="champ-quantite">Quantité :</label>
                    <input type="number" name="quantite" id="champ-quantite" value="1" min="1">

                    <button type="submit" class="btn">Ajouter à la commande</button>
                </form>
            </div>

            <a href="profil.php" class="btn btn-retour">← Retour au profil</a>
        <?php endif; ?>

    </div>
    <script src="js/theme.js"></script>
</body>
</html>
