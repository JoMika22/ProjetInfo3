<?php
// Demarrer la session
session_start();
require("script_php/recup.php");

// Verifier si restaurateur ou admin
if (!isset($_SESSION['utilisateur_id']) || ($_SESSION['utilisateur_role'] != 'restaurateur' && $_SESSION['utilisateur_role'] != 'admin')) {
    header("Location: connexion.php");
    exit;
}
verifier_blocage();

// Charger les commandes et les utilisateurs
$commandes = lire_donnees_json('commandes');
$utilisateurs = lire_donnees_json('utilisateur');

// Recuperer la liste des livreurs
$livreurs = [];
foreach ($utilisateurs as $u) {
    if ($u['role'] == 'livreur') {
        $livreurs[] = $u;
    }
}

// Traitement quand le restaurateur clique sur un bouton
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cmd_id = $_POST['commande_id'];
    $action = $_POST['action'];
    
    // Parcourir les commandes pour trouver celle a modifier
    foreach ($commandes as $i => $cmd) {
        if ($cmd['id'] == $cmd_id) {
            if ($action == 'preparer') {
                $commandes[$i]['statut'] = 'en_preparation';
            }
            if ($action == 'prete') {
                $commandes[$i]['statut'] = 'prete';
            }
            if ($action == 'envoyer') {
                $commandes[$i]['statut'] = 'en_livraison';
                $commandes[$i]['livreur_id'] = intval($_POST['livreur_id']);
            }
        }
    }
    // Sauvegarder les modifications
    ecrire_donnees_json('commandes', $commandes);
    header("Location: restaurateur.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuisine</title>
    <!-- id pour le changement de theme -->
    <link rel="stylesheet" href="style.css" id="theme-css">
</head>
<body>
    <header><h1>Interface Cuisine (Tablette)</h1></header>
    <?php include("script_php/nav.php"); ?>
    
    <div class="container">
        <h2 class="titre-attente">🔥 En Attente de Préparation</h2>
        <?php foreach ($commandes as $cmd):
            if ($cmd['statut'] != 'en_attente' && $cmd['statut'] != 'en_preparation' && $cmd['statut'] != 'prete') continue;
            $client = trouver_par_id($cmd['client_id']);
        ?>
        <div class="card">
            <h3>Commande #<?php echo $cmd['id']; ?> – <?php echo proteger($client['prenom'] . ' ' . $client['nom']); ?></h3>
            <p><strong>Statut :</strong> <?php echo $cmd['statut']; ?></p>
            <ul>
                <?php foreach ($cmd['articles'] as $a):
                    $plat = trouver_plat($a['plat_id']);
                    if ($plat != null):
                ?>
                    <li><?php echo proteger($a['quantite']); ?>x <?php echo proteger($plat['nom']); ?></li>
                <?php endif; endforeach; ?>
            </ul>
            <?php if ($cmd['statut'] == 'en_attente'): ?>
                <form method="post">
                    <input type="hidden" name="commande_id" value="<?php echo (int)$cmd['id']; ?>">
                    <input type="hidden" name="action" value="preparer">
                    <button type="submit" class="btn">Commencer la préparation</button>
                </form>
            <?php endif; ?>
            <?php if ($cmd['statut'] == 'en_preparation'): ?>
                <form method="post">
                    <input type="hidden" name="commande_id" value="<?php echo (int)$cmd['id']; ?>">
                    <input type="hidden" name="action" value="prete">
                    <button type="submit" class="btn">Marquer comme prête ✅</button>
                </form>
            <?php endif; ?>
            <?php if ($cmd['statut'] == 'prete'): ?>
                <form method="post">
                    <input type="hidden" name="commande_id" value="<?php echo (int)$cmd['id']; ?>">
                    <input type="hidden" name="action" value="envoyer">
                    <label for="livreur-<?php echo (int)$cmd['id']; ?>" class="sr-only">Choisir un livreur</label>
                    <select name="livreur_id" id="livreur-<?php echo (int)$cmd['id']; ?>" class="select-inline">
                        <?php foreach ($livreurs as $liv): ?>
                            <option value="<?php echo proteger($liv['id']); ?>"><?php echo proteger($liv['prenom']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn">Valider & Envoyer au Livreur ➡️</button>
                </form>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>

        <hr>

        <h2 class="titre-livraison">🚚 En Cours de Livraison</h2>
        <?php foreach ($commandes as $cmd):
            if ($cmd['statut'] != 'en_livraison') continue;
            $client = trouver_par_id($cmd['client_id']);
            $livreur = ($cmd['livreur_id'] != null) ? trouver_par_id($cmd['livreur_id']) : null;
        ?>
        <div class="card carte-grise">
            <h3>Commande #<?php echo $cmd['id']; ?></h3>
            <p>Livreur : <?php echo ($livreur != null) ? proteger($livreur['prenom']) : 'Non attribué'; ?></p>
            <p>Statut : En route</p>
        </div>
        <?php endforeach; ?>
    </div>

    <script src="js/theme.js"></script>
</body>
</html>
