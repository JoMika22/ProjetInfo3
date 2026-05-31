<?php
// Demarrer la session
session_start();
require("script_php/recup.php");
require("script_php/config_cybank.php");

// Verifier si connecté
if (!isset($_SESSION['utilisateur_id'])) {
    header("Location: connexion.php");
    exit;
}
verifier_blocage();

// Recuperer l'utilisateur connecté
$user = trouver_par_id($_SESSION['utilisateur_id']);
$message = "";

// Creer le panier si il existe pas encore
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

// Ajouter un plat au panier
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['plat_id'])) {
    // Securite : ne jamais faire confiance aux donnees recues.
    $plat_id = intval($_POST['plat_id']);
    $quantite = isset($_POST['quantite']) ? intval($_POST['quantite']) : 1;

    // La quantite doit etre au moins 1
    if ($quantite < 1) $quantite = 1;

    // Le plat doit reellement exister dans nos donnees
    $plat_existe = trouver_plat($plat_id);
    if ($plat_existe == null) {
        $message = "Ce plat n'existe pas.";
    } else {
        // Verifier si le plat est deja dans le panier
        $trouve = false;
        foreach ($_SESSION['panier'] as $i => $item) {
            if ($item['plat_id'] == $plat_id) {
                $_SESSION['panier'][$i]['quantite'] = $_SESSION['panier'][$i]['quantite'] + $quantite;
                $trouve = true;
            }
        }
        if ($trouve == false) {
            $_SESSION['panier'][] = ['plat_id' => $plat_id, 'quantite' => $quantite];
        }
        $message = "Article ajouté au panier !";
    }
}

// Supprimer un article du panier
if (isset($_GET['supprimer'])) {
    $index = $_GET['supprimer'];
    $nouveau_panier = [];
    foreach ($_SESSION['panier'] as $i => $item) {
        if ($i != $index) {
            $nouveau_panier[] = $item;
        }
    }
    $_SESSION['panier'] = $nouveau_panier;
    header("Location: panier.php");
    exit;
}

// Vider le panier
if (isset($_GET['vider'])) {
    $_SESSION['panier'] = [];
    header("Location: panier.php");
    exit;
}

// Valider la commande -> on prepare le paiement CYBank (la commande sera
// reellement creee seulement apres un paiement verifie, dans retour_paiement.php)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['valider_commande'])) {
    if (empty($_SESSION['panier'])) {
        $message = "Votre panier est vide !";
    } else {
        $type = $_POST['type_commande'];

        // Calculer le total
        $total = 0;
        foreach ($_SESSION['panier'] as $item) {
            $plat = trouver_plat($item['plat_id']);
            if ($plat != null) {
                $total = $total + ($plat['prix'] * $item['quantite']);
            }
        }

        // Preparer le paiement : on memorise le panier en session, puis on
        // redirige vers la page qui envoie la transaction a CYBank.
        $_SESSION['paiement_en_cours'] = [
            'contexte' => 'panier',
            'transaction' => cybank_transaction_id(),
            'montant' => $total,
            'panier' => $_SESSION['panier'],
            'type' => $type,
            'date_souhaitee' => isset($_POST['date_souhaitee']) ? $_POST['date_souhaitee'] : null
        ];
        header("Location: paiement.php");
        exit;
    }
}

// Calculer le total du panier
$total_panier = 0;
foreach ($_SESSION['panier'] as $item) {
    $plat = trouver_plat($item['plat_id']);
    if ($plat != null) {
        $total_panier = $total_panier + ($plat['prix'] * $item['quantite']);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Panier</title>
    <!-- id pour le changement de theme -->
    <link rel="stylesheet" href="style.css" id="theme-css">
</head>
<body>
    <header><h1>Mon Panier</h1></header>
    <?php include("script_php/nav.php"); ?>

    <div class="container">

        <?php if ($message != ""): ?>
            <div class="card alerte-succes">
                <p><?php echo $message; ?></p>
                <?php if (strpos($message, 'validée') !== false): ?>
                    <a href="profil.php" class="btn">Voir mes commandes</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Zone d'erreur pour la validation JS de la commande -->
        <div id="erreur-commande" class="card zone-erreur"></div>

        <?php if (empty($_SESSION['panier']) && strpos($message, 'validée') === false): ?>
            <div class="card texte-centre">
                <h2>Votre panier est vide</h2>
                <a href="carte.php" class="btn">Voir la carte</a>
            </div>
        
        <?php elseif (!empty($_SESSION['panier'])): ?>
            <div class="card">
                <h2>Récapitulatif</h2>
                <table class="table-admin">
                    <thead>
                        <tr class="en-tete-tableau">
                            <th>Plat</th>
                            <th>Prix</th>
                            <th>Quantité</th>
                            <th>Sous-total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($_SESSION['panier'] as $index => $item):
                        $plat = trouver_plat($item['plat_id']);
                        if ($plat != null):
                    ?>
                        <tr>
                            <td><?php echo proteger($plat['nom']); ?></td>
                            <td><?php echo $plat['prix']; ?>€</td>
                            <td><?php echo $item['quantite']; ?></td>
                            <td><?php echo $plat['prix'] * $item['quantite']; ?>€</td>
                            <td><a href="panier.php?supprimer=<?php echo $index; ?>">Retirer</a></td>
                        </tr>
                    <?php endif; endforeach; ?>
                    </tbody>
                </table>
                
                <p class="total-panier">
                    <strong>Total : <?php echo $total_panier; ?>€</strong>
                </p>
                
                <a href="panier.php?vider=1" class="lien-rouge">Vider le panier</a>
                <a href="carte.php" class="btn marge-gauche">Continuer les achats</a>
            </div>

            <!-- Valider la commande -->
            <div class="card">
                <h2>Valider ma commande</h2>
                <form method="post" id="formulaire-commande">
                    <input type="hidden" name="valider_commande" value="1">
                    
                    <label for="type-commande">Type de commande :</label>
                    <select name="type_commande" id="type-commande">
                        <option value="livraison">Livraison</option>
                        <option value="sur_place">Sur place / A emporter</option>
                    </select>
                    
                    <label>Adresse de livraison :</label>
                    <input type="text" value="<?php echo proteger($user['adresse']); ?>" disabled>
                    
                    <label for="champ-date">Date et heure souhaitée (optionnel) :</label>
                    <input type="datetime-local" name="date_souhaitee" id="champ-date">
                    <p><em>Laissez vide pour une préparation immédiate</em></p>
                    
                    <button type="submit" class="btn btn-valider">
                        Payer et Valider (<?php echo $total_panier; ?>€)
                    </button>
                    <p class="texte-centre"><em>Paiement via API CYBank</em></p>
                </form>
            </div>
        <?php endif; ?>

    </div>

    <!-- script pour le changement de theme + validation -->
    <script src="js/theme.js"></script>
    <script>
        // Validation cote client de la date souhaitee (ne doit pas etre dans le passe)
        window.addEventListener("load", function() {
            var form = document.getElementById("formulaire-commande");
            if (form == null) return;

            form.addEventListener("submit", function(event) {
                var zoneErreur = document.getElementById("erreur-commande");
                zoneErreur.innerHTML = "";
                zoneErreur.style.display = "none";

                var dateChamp = document.getElementById("champ-date");
                var valeurDate = dateChamp.value;

                // Si une date est saisie, verifier qu'elle est dans le futur
                if (valeurDate != "" && valeurDate != null) {
                    var dateChoisie = new Date(valeurDate);
                    var maintenant = new Date();
                    if (dateChoisie < maintenant) {
                        event.preventDefault();
                        zoneErreur.innerHTML = "La date souhaitée ne peut pas être dans le passé.";
                        zoneErreur.style.display = "block";
                        return false;
                    }
                }
                return true;
            });
        });
    </script>
</body>
</html>