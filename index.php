<?php
// Demarrer la session
session_start();
require("script_php/recup.php");

// Charger les plats et les menus depuis les fichiers JSON
$plats = lire_donnees_json('plat');
$menus = lire_donnees_json('menu');

// Calcul des plats les plus populaires (a partir des commandes reelles)
$commandes = lire_donnees_json('commandes');
$compteur = []; // plat_id => quantite totale commandee
foreach ($commandes as $cmd) {
    if (!isset($cmd['articles'])) continue;
    foreach ($cmd['articles'] as $art) {
        $pid = $art['plat_id'];
        if (!isset($compteur[$pid])) $compteur[$pid] = 0;
        $compteur[$pid] += $art['quantite'];
    }
}
// Trier par quantite decroissante
arsort($compteur);
// Garder les 3 plus populaires
$populaires = array_slice(array_keys($compteur), 0, 3);

// Si aucune commande, on met en avant les 3 premiers plats par defaut
if (empty($populaires)) {
    foreach (array_slice($plats, 0, 3) as $p) {
        $populaires[] = $p['id'];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Les Saveurs du Soleil</title>
    <link rel="stylesheet" href="style.css" id="theme-css">
</head>
<body>

<header>
    <h1>Les Saveurs du Soleil</h1>
    <p class="sous-titre">Restaurant multiculturel</p>
</header>

<!-- Barre de navigation -->
<?php include("script_php/nav.php"); ?>

<section class="container">

    <div class="card recherche-container">
        <p class="recherche-label">Rechercher un plat</p>
        <form action="carte.php" method="get">
            <label for="recherche-accueil" class="sr-only">Rechercher un plat</label>
            <input type="text" id="recherche-accueil" name="recherche" class="recherche-input" placeholder="Ex : Couscous, Tajine, Bacalhau..." aria-label="Rechercher un plat">
            <button type="submit" class="btn">Rechercher</button>
        </form>
    </div>

    <h2 class="titre-centre">⭐ Nos plats les plus populaires</h2>

    <div class="products-grid">
        <?php foreach ($populaires as $pid):
            $p = trouver_plat($pid);
            if ($p == null) continue;
            $img = image_plat($p['id']);
        ?>
            <div class="product-card">
                <?php if ($img != ''): ?>
                    <img src="<?php echo proteger($img); ?>" alt="Photo du plat : <?php echo proteger($p['nom']); ?>">
                <?php endif; ?>
                <div class="product-info texte-centre">
                    <h3><?php echo proteger($p['nom']); ?> : <?php echo proteger($p['prix']); ?>€</h3>
                    <p>Catégorie : <?php echo proteger($p['categorie']); ?></p>
                    <a href="carte.php" class="btn">Commander</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Menus chargés depuis menu.json -->
    <h2 class="titre-centre titre-categorie">🍽️ Nos Menus</h2>
    <div class="products-grid">
        <?php foreach ($menus as $menu): ?>
            <div class="product-card">
                <div class="product-info texte-centre padding-haut">
                    <h3><?php echo proteger($menu['nom']); ?> : <?php echo proteger($menu['prix']); ?>€</h3>
                    <p><?php echo proteger($menu['description']); ?></p>
                    <ul class="liste-menu">
                        <?php foreach ($menu['plats_inclus'] as $pid):
                            $p = trouver_plat($pid);
                            if ($p):
                        ?>
                            <li><?php echo proteger($p['nom']); ?></li>
                        <?php endif; endforeach; ?>
                    </ul>
                    <a href="carte.php" class="btn">Commander</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</section>

<footer class="pied-de-page">
    © 2026 Les Saveurs du Soleil – Restaurant multiculturel
</footer>

<script src="js/theme.js"></script>

</body>
</html>
