<?php
// Demarrer la session
session_start();
require("script_php/recup.php");

// Verifier si l'utilisateur est connecté
if (!isset($_SESSION['utilisateur_id'])) {
    header("Location: connexion.php");
    exit;
}
// Si le compte a ete bloque, on coupe la session immediatement
verifier_blocage();

// Recuperer les infos de l'utilisateur connecté
$user = trouver_par_id($_SESSION['utilisateur_id']);

// Recuperer les commandes du client
$commandes = lire_donnees_json('commandes');
$mes_commandes = [];
foreach ($commandes as $c) {
    if ($c['client_id'] == $user['id']) {
        $mes_commandes[] = $c;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil</title>
    <!-- id pour le changement de theme -->
    <link rel="stylesheet" href="style.css" id="theme-css">
</head>
<body>
    <header><h1>Mon Espace</h1></header>
    <?php include("script_php/nav.php"); ?>

    <div class="container">

        <!-- zones de message dynamiques -->
        <div id="erreur-profil" class="card zone-erreur"></div>
        <div id="succes-profil" class="card zone-succes"></div>

        <div class="card">
            <h2>Mes Informations</h2>
            <p><strong>Nom :</strong> <span id="affichage-nom"><?php echo proteger($user['nom']); ?></span></p>
            <p><strong>Prénom :</strong> <span id="affichage-prenom"><?php echo proteger($user['prenom']); ?></span></p>
            <p><strong>Adresse :</strong> <span id="affichage-adresse"><?php echo proteger($user['adresse']); ?></span></p>
            <p><strong>Téléphone :</strong> <span id="affichage-tel"><?php echo proteger($user['telephone']); ?></span></p>

            <!-- boutons modifier / valider / annuler -->
            <button type="button" id="btn-modifier" class="btn" onclick="activerModification()">✎ Modifier mes informations</button>
            <button type="button" id="btn-valider-profil" class="btn btn-succes" onclick="envoyerModifications()" style="display:none;">✓ Valider</button>
            <button type="button" id="btn-annuler-profil" class="btn btn-danger" onclick="annulerModification()" style="display:none;">✗ Annuler</button>
        </div>
        
        <div class="card">
            <h3>📜 Anciennes Commandes</h3>
            <?php if (empty($mes_commandes)): ?>
                <p>Aucune commande pour le moment.</p>
            <?php else: ?>
                <ul>
                <?php foreach ($mes_commandes as $cmd): ?>
                    <li>
                        <?php echo proteger($cmd['date_commande']); ?> - <?php echo proteger($cmd['total']); ?>€ 
                        (<?php echo proteger($cmd['statut']); ?>)
                            - <a href="suivi_commande.php?commande_id=<?php echo (int)$cmd['id']; ?>">🧾 Détails</a>
                        <?php if ($cmd['statut'] != 'livree'): ?>
                            - <a href="suivi_commande.php?commande_id=<?php echo (int)$cmd['id']; ?>">📍 Suivre</a>
                        <?php endif; ?>
                        <?php if ($cmd['statut'] == 'livree' && $cmd['note_repas'] == null): ?>
                            - <a href="notation.php?commande_id=<?php echo (int)$cmd['id']; ?>">⭐ Noter</a>
                        <?php endif; ?>
                        <?php if ($cmd['statut'] == 'en_attente'): ?>
                            - <a href="modifier_commande.php?commande_id=<?php echo (int)$cmd['id']; ?>">✎ Modifier</a>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <!-- scripts JS -->
    <script src="js/theme.js"></script>
    <script src="js/profil.js"></script>
</body>
</html>
