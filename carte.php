<?php
// Demarrer la session
session_start();
require("script_php/recup.php");
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carte - Les Saveurs du Soleil</title>
    <!-- id pour le changement de theme -->
    <link rel="stylesheet" href="style.css" id="theme-css">
</head>

<body>
    <header>
        <h1>La Carte</h1>
        <p class="sous-titre">Découvrez nos spécialités</p>
    </header>
    
    <?php include("script_php/nav.php"); ?>

    <section class="container">
        
        <div class="card recherche-container recherche-large">
            <p class="recherche-label">Rechercher et filtrer</p>

            <!-- ce formulaire ne soumet plus au serveur, il declenche du JS -->
            <label for="filtre-recherche" class="sr-only">Rechercher un produit</label>
            <input type="text" id="filtre-recherche" placeholder="Chercher un produit..." class="recherche-input input-reduit" aria-label="Rechercher un produit">

            <select id="filtre-categorie" class="select-filtre" aria-label="Filtrer par type de plat">
                <option value="">Type de plat</option>
                <option value="entree">Entrées</option>
                <option value="plat">Plats de résistance</option>
                <option value="dessert">Desserts</option>
                <option value="boisson">Boissons</option>
            </select>

            <select id="filtre-allergene" class="select-filtre" aria-label="Filtrer par allergène">
                <option value="">Allergènes</option>
                <option value="gluten">Sans Gluten</option>
                <option value="lactose">Sans Lactose</option>
                <option value="oeuf">Sans Œuf</option>
                <option value="poisson">Sans Poisson</option>
            </select>

            <!-- tri cote client (sur les donnees deja recuperees) -->
            <select id="filtre-tri" class="select-filtre" aria-label="Trier les plats">
                <option value="">Trier par...</option>
                <option value="prix_asc">Prix croissant</option>
                <option value="prix_desc">Prix décroissant</option>
                <option value="nom_asc">Nom (A-Z)</option>
            </select>
        </div>

        <!-- zone qui sera remplie dynamiquement par le JS -->
        <div id="zone-plats">
            <!-- Affichage de secours si JS desactivé -->
            <noscript>
                <p class="texte-centre">Veuillez activer JavaScript pour utiliser les filtres et tris.</p>
            </noscript>
        </div>

    </section>

    <footer class="pied-de-page">
        © 2026 Les Saveurs du Soleil – Restaurant multiculturel
    </footer>

    <script src="js/theme.js"></script>
    <script src="js/carte.js"></script>
    <script>
        // Initialiser la page (charger les plats au demarrage)
        window.addEventListener("load", initCarte);
    </script>
</body>
</html>
