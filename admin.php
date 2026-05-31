<?php
// Demarrer la session
session_start();
require("script_php/recup.php");

// Verifier si l'utilisateur est admin, sinon rediriger
if (!isset($_SESSION['utilisateur_id']) || $_SESSION['utilisateur_role'] != 'admin') {
    header("Location: connexion.php");
    exit;
}
// Securite : si l'admin lui-meme a ete bloque entre temps, on coupe sa session
verifier_blocage();

// Charger les donnees necessaires
$utilisateurs = lire_donnees_json('utilisateur');

// Quelle vue afficher ?
$voir = isset($_GET['voir_profil']) ? $_GET['voir_profil'] : null;
$vue_logs = isset($_GET['logs']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
    <link rel="stylesheet" href="style.css" id="theme-css">
</head>
<body>
    <header><h1>Administration</h1></header>
    <?php include("script_php/nav.php"); ?>
    <div class="container">

        <?php if ($vue_logs): ?>
            <!-- ===== Vue : journal des incidents ===== -->
            <a href="admin.php" class="btn btn-retour">← Retour</a>
            <h2>Journal des incidents</h2>
            <div class="card">
                <?php
                $logs = lire_donnees_json('logs');
                if (empty($logs)):
                ?>
                    <p>Aucun incident enregistré pour le moment.</p>
                <?php else: ?>
                    <table class="table-admin">
                        <thead>
                            <tr class="en-tete-tableau">
                                <th>Date</th>
                                <th>Type</th>
                                <th>Message</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        // Afficher les plus recents en premier
                        foreach (array_reverse($logs) as $log):
                        ?>
                            <tr>
                                <td><?php echo proteger($log['date']); ?></td>
                                <td><?php echo proteger($log['type']); ?></td>
                                <td><?php echo proteger($log['message']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

        <?php elseif ($voir != null):
            $profil = trouver_par_id($voir);
            if ($profil != null):
                $est_bloque = (isset($profil['bloque']) && $profil['bloque'] == true);
        ?>
            <!-- ===== Vue : profil detaille ===== -->
            <a href="admin.php" class="btn btn-retour">← Retour</a>

            <!-- zone de message pour les actions asynchrones -->
            <div id="zone-message-admin" class="card zone-succes" style="display:none;"></div>

            <div class="card">
                <h2>Profil de <?php echo proteger($profil['prenom'] . ' ' . $profil['nom']); ?></h2>
                <p><strong>Email :</strong> <?php echo proteger($profil['email']); ?></p>
                <p><strong>Rôle :</strong> <?php echo proteger($profil['role']); ?></p>
                <p><strong>Adresse :</strong> <?php echo proteger($profil['adresse']); ?></p>
                <p><strong>Téléphone :</strong> <?php echo proteger($profil['telephone']); ?></p>
                <p><strong>Inscrit le :</strong> <?php echo proteger($profil['date_inscription']); ?></p>
                <hr class="separateur">

                <p><strong>État du compte :</strong>
                    <span id="etat-compte"><?php echo $est_bloque ? "🔴 Compte bloqué" : "🟢 Compte actif"; ?></span>
                </p>

                <?php if ($profil['id'] != $_SESSION['utilisateur_id']): ?>
                    <!-- Bouton de blocage/deblocage en asynchrone -->
                    <button id="btn-blocage"
                            class="btn <?php echo $est_bloque ? 'btn-succes' : 'btn-danger'; ?>"
                            data-userid="<?php echo (int)$profil['id']; ?>"
                            onclick="basculerBlocage(<?php echo (int)$profil['id']; ?>,'<?php echo $est_bloque ? 'debloquer' : 'bloquer'; ?>')">
                        <?php echo $est_bloque ? "Débloquer le compte" : "Bloquer le compte"; ?>
                    </button>
                <?php else: ?>
                    <p><em>(Vous ne pouvez pas bloquer votre propre compte.)</em></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php else: ?>
            <!-- ===== Vue : liste des utilisateurs ===== -->
            <h2>Gestion des Utilisateurs</h2>
            <p><a href="admin.php?logs=1" class="btn btn-petit">📋 Voir le journal des incidents</a></p>
            <div class="card">
                <table class="table-admin">
                    <thead>
                        <tr class="en-tete-tableau">
                            <th>Nom</th>
                            <th>Rôle</th>
                            <th>État</th>
                            <th>Total Commandes</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($utilisateurs as $u):
                        $bloque = (isset($u['bloque']) && $u['bloque'] == true);
                    ?>
                        <tr>
                            <td><?php echo proteger($u['prenom'] . ' ' . $u['nom']); ?></td>
                            <td><?php echo proteger($u['role']); ?></td>
                            <td><?php echo $bloque ? "🔴 Bloqué" : "🟢 Actif"; ?></td>
                            <td><?php echo compter_commandes($u['id']); ?></td>
                            <td><a href="admin.php?voir_profil=<?php echo (int)$u['id']; ?>" class="btn btn-petit">Voir Profil</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    </div>

    <script src="js/theme.js"></script>
    <script src="js/admin.js"></script>
</body>
</html>
