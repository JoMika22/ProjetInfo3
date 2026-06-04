// Appele depuis le bouton de la page admin (profil detaille d'un utilisateur)

async function basculerBlocage(userId, action) {
    var zone = document.getElementById("zone-message-admin");
    if (zone != null) {
        zone.innerHTML = "";
        zone.style.display = "none";
    }

    // Construire les donnees du POST
    var donnees = new URLSearchParams();
    donnees.append("user_id", userId);
    donnees.append("action", action);

    try {
        var reponse = await fetch("script_php/api_admin.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: donnees
        });

        if (!reponse.ok) {
            afficherMessageAdmin("Erreur de communication avec le serveur.", true);
            return;
        }

        // Le serveur renvoie du JSON
        var data = await reponse.json();

        if (data.ok == true) {
            // Mettre a jour le bouton et l'etat affiche sans recharger
            majInterfaceBlocage(data.bloque);
            afficherMessageAdmin(data.message, false);
        } else {
            afficherMessageAdmin(data.message, true);
        }
    } catch (erreur) {
        afficherMessageAdmin("Impossible de joindre le serveur.", true);
    }
}

// Met a jour le bouton + le texte d'etat selon le nouvel etat (bloque ou non)
function majInterfaceBlocage(estBloque) {
    var bouton = document.getElementById("btn-blocage");
    var etat = document.getElementById("etat-compte");

    if (bouton != null) {
        if (estBloque) {
            bouton.innerHTML = "Débloquer le compte";
            bouton.setAttribute("onclick", "basculerBlocage(" + bouton.dataset.userid + ",'debloquer')");
            bouton.className = "btn btn-succes";
        } else {
            bouton.innerHTML = "Bloquer le compte";
            bouton.setAttribute("onclick", "basculerBlocage(" + bouton.dataset.userid + ",'bloquer')");
            bouton.className = "btn btn-danger";
        }
    }
    if (etat != null) {
        etat.innerHTML = estBloque ? "🔴 Compte bloqué" : "🟢 Compte actif";
    }
}

function afficherMessageAdmin(message, estErreur) {
    var zone = document.getElementById("zone-message-admin");
    if (zone == null) return;
    zone.innerHTML = message;
    zone.className = estErreur ? "card zone-erreur" : "card zone-succes";
    zone.style.display = "block";
}
