// Toutes les 5 secondes, on demande au serveur le statut de la commande.
// Si le statut a change, on redessine la barre sans recharger la page.

// Etapes selon le type
function etapesPour(type) {
    if (type === "livraison") {
        return [
            { cle: "en_attente",     label: "Payée",          emoji: "💳" },
            { cle: "en_preparation", label: "En préparation", emoji: "👨‍🍳" },
            { cle: "prete",          label: "Prête",          emoji: "📦" },
            { cle: "en_livraison",   label: "En livraison",   emoji: "🛵" },
            { cle: "livree",         label: "Livrée",         emoji: "✅" }
        ];
    }
    return [
        { cle: "en_attente",     label: "Payée",          emoji: "💳" },
        { cle: "en_preparation", label: "En préparation", emoji: "👨‍🍳" },
        { cle: "prete",          label: "Prête",          emoji: "📦" },
        { cle: "livree",         label: "Terminée",       emoji: "✅" }
    ];
}

// Redessiner la barre de suivi dans le conteneur, selon le statut/type recus
function dessinerSuivi(statut, type) {
    var conteneur = document.getElementById("suivi-commande");
    if (conteneur == null) return;

    var etapes = etapesPour(type);
    var indexCourant = -1;
    for (var i = 0; i < etapes.length; i++) {
        if (etapes[i].cle === statut) indexCourant = i;
    }

    conteneur.innerHTML = "";
    for (var j = 0; j < etapes.length; j++) {
        var classe = "suivi-etape";
        if (j < indexCourant) classe += " suivi-faite";
        else if (j === indexCourant) classe += " suivi-active";

        var etape = document.createElement("div");
        etape.className = classe;

        var pastille = document.createElement("div");
        pastille.className = "suivi-pastille";
        pastille.textContent = etapes[j].emoji;

        var label = document.createElement("div");
        label.className = "suivi-label";
        label.textContent = etapes[j].label;

        etape.appendChild(pastille);
        etape.appendChild(label);
        conteneur.appendChild(etape);

        // Trait de liaison
        if (j < etapes.length - 1) {
            var trait = document.createElement("div");
            trait.className = (j < indexCourant) ? "suivi-trait suivi-trait-fait" : "suivi-trait";
            conteneur.appendChild(trait);
        }
    }
}

// Interroger le serveur pour connaitre le statut courant
async function rafraichirStatut(commandeId) {
    try {
        var reponse = await fetch("script_php/api_statut_commande.php?commande_id=" + commandeId);
        if (!reponse.ok) return;
        var data = await reponse.json();
        if (data.ok === true) {
            dessinerSuivi(data.statut, data.type);
            // Si la commande est arrivee a son etat final, inutile de continuer a interroger
            if (data.statut === "livree") {
                clearInterval(window.intervalleSuivi);
            }
        }
    } catch (e) {
        // En cas d'erreur reseau, on reessaiera au prochain intervalle
    }
}

// Au chargement : demarrer le rafraichissement automatique si un suivi est present
document.addEventListener("DOMContentLoaded", function () {
    var conteneur = document.getElementById("suivi-commande");
    if (conteneur == null) return;
    var commandeId = conteneur.dataset.commandeId;
    if (!commandeId) return;

    // Premier rafraichissement immediat, puis toutes les 5 secondes
    rafraichirStatut(commandeId);
    window.intervalleSuivi = setInterval(function () {
        rafraichirStatut(commandeId);
    }, 5000);
});
