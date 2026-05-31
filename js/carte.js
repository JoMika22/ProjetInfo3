// Variable globale qui stocke les plats actuellement affiches pour pouvoir les trier sans recontacter le serveur
var platsActuels = [];

async function appliquerFiltres() {
    // Recuperer les valeurs des filtres
    var recherche = document.getElementById("filtre-recherche").value;
    var categorie = document.getElementById("filtre-categorie").value;
    var allergene = document.getElementById("filtre-allergene").value;

    // Construire l'URL avec les parametres en GET
    var url = "script_php/api_plats.php?recherche=" + encodeURIComponent(recherche);
    url = url + "&categorie=" + encodeURIComponent(categorie);
    url = url + "&allergene=" + encodeURIComponent(allergene);

    try {
        var reponse = await fetch(url);

        if (!reponse.ok) {
            document.getElementById("zone-plats").innerHTML = "<p>Erreur de chargement.</p>";
            return;
        }

        // json() convertit directement le corps de la reponse en tableau
        var donnees = await reponse.json();
        platsActuels = donnees;

        // Re-appliquer le tri actuel
        trierEtAfficher();
    } catch (erreur) {
        // En cas d'echec du fetch
        document.getElementById("zone-plats").innerHTML = "<p>Impossible de contacter le serveur.</p>";
    }
}

function trierEtAfficher() {
    var tri = document.getElementById("filtre-tri").value;

    // Faire une copie du tableau pour ne pas modifier l'original
    var liste = platsActuels.slice();

    if (tri == "prix_asc") {
        // Tri par prix croissant (vu en cours JS page 50 : sort avec fonction)
        liste.sort(function(a, b) {
            return a.prix - b.prix;
        });
    } else if (tri == "prix_desc") {
        // Tri par prix decroissant
        liste.sort(function(a, b) {
            return b.prix - a.prix;
        });
    } else if (tri == "nom_asc") {
        // Tri alphabetique
        liste.sort(function(a, b) {
            if (a.nom < b.nom) return -1;
            if (a.nom > b.nom) return 1;
            return 0;
        });
    }
    // Si tri == "" (aucun tri), on garde l'ordre original

    afficherPlats(liste);
}

function afficherPlats(liste) {
    var zone = document.getElementById("zone-plats");
    zone.innerHTML = "";

    if (liste.length == 0) {
        zone.innerHTML = "<p class='texte-centre'>Aucun plat ne correspond à votre recherche.</p>";
        return;
    }

    var categories = ["entree", "plat", "dessert", "boisson"];
    var titres = {
        "entree": "🥗 Nos Entrées",
        "plat": "🍲 Nos Plats de Résistance",
        "dessert": "🍰 Nos Desserts",
        "boisson": "🍹 Nos Boissons"
    };

    for (var c = 0; c < categories.length; c++) {
        var cat = categories[c];
        // Filtrer les plats de cette categorie
        var platsCat = [];
        for (var i = 0; i < liste.length; i++) {
            if (liste[i].categorie == cat) {
                platsCat.push(liste[i]);
            }
        }
        if (platsCat.length == 0) continue;

        // Ajouter le titre de la categorie
        var titre = document.createElement("h2");
        titre.className = "titre-centre titre-categorie";
        titre.innerHTML = titres[cat];
        zone.appendChild(titre);

        // Creer la grille de plats
        var grille = document.createElement("div");
        grille.className = "products-grid";

        for (var j = 0; j < platsCat.length; j++) {
            var p = platsCat[j];
            grille.appendChild(creerCartePlat(p));
        }
        zone.appendChild(grille);
    }
}

// Creer une carte plat (element DOM)
function creerCartePlat(plat) {
    var carte = document.createElement("div");
    carte.className = "product-card";

    if (plat.image != null && plat.image != "") {
        var img = document.createElement("img");
        img.src = plat.image;
        img.alt = plat.nom;
        carte.appendChild(img);
    }

    var info = document.createElement("div");
    info.className = "product-info texte-centre";

    var h3 = document.createElement("h3");
    h3.innerHTML = plat.nom + " : " + plat.prix + "€";
    info.appendChild(h3);

    var pAller = document.createElement("p");
    var listeAller = "Aucun";
    if (plat.allergenes != null && plat.allergenes.length > 0) {
        listeAller = plat.allergenes.join(", ");
    }
    pAller.innerHTML = "<em>Allergènes : " + listeAller + "</em>";
    info.appendChild(pAller);

    // Bouton "Ajouter" qui poste vers panier.php
    var formulaire = document.createElement("form");
    formulaire.method = "post";
    formulaire.action = "panier.php";

    var hidId = document.createElement("input");
    hidId.type = "hidden";
    hidId.name = "plat_id";
    hidId.value = plat.id;
    formulaire.appendChild(hidId);

    var hidQte = document.createElement("input");
    hidQte.type = "hidden";
    hidQte.name = "quantite";
    hidQte.value = "1";
    formulaire.appendChild(hidQte);

    var bouton = document.createElement("button");
    bouton.type = "submit";
    bouton.className = "btn";
    bouton.innerHTML = "Ajouter";
    formulaire.appendChild(bouton);

    info.appendChild(formulaire);
    carte.appendChild(info);
    return carte;
}

function initCarte() {
    // Brancher les filtres : quand ils changent, on relance la requete asynchrone
    document.getElementById("filtre-categorie").addEventListener("change", appliquerFiltres);
    document.getElementById("filtre-allergene").addEventListener("change", appliquerFiltres);

    // La recherche se declenche quand on tape (input) avec un petit delai
    var timerRecherche = null;
    document.getElementById("filtre-recherche").addEventListener("input", function() {
        // Si on tape vite, on annule la requete precedente (anti-spam serveur)
        if (timerRecherche != null) {
            clearTimeout(timerRecherche);
        }
        timerRecherche = setTimeout(appliquerFiltres, 300);
    });

    // Le tri se fait cote client (pas de requete au serveur)
    document.getElementById("filtre-tri").addEventListener("change", trierEtAfficher);

    // Charger les plats au depart (sans filtre)
    appliquerFiltres();
}
