// Quand on clique sur "Modifier", on transforme les <span> en <input>
function activerModification() {
    // Petite fonction qui remplace le texte d'un span par un champ de saisie.
    // On utilise .value (et non innerHTML) pour que les apostrophes/guillemets
    // dans l'adresse ne cassent rien (ex: "Avenue de l'Atlas").
    function transformer(idSpan, idInput) {
        var span = document.getElementById(idSpan);
        var valeur = span.textContent;
        span.innerHTML = "";
        var input = document.createElement("input");
        input.type = "text";
        input.id = idInput;
        input.value = valeur;
        span.appendChild(input);
    }

    transformer("affichage-nom", "edit-nom");
    transformer("affichage-prenom", "edit-prenom");
    transformer("affichage-adresse", "edit-adresse");
    transformer("affichage-tel", "edit-tel");

    // Cacher le bouton modifier et afficher les boutons valider/annuler
    document.getElementById("btn-modifier").style.display = "none";
    document.getElementById("btn-valider-profil").style.display = "inline-block";
    document.getElementById("btn-annuler-profil").style.display = "inline-block";
}

// Annuler les modifications => on recharge la page
function annulerModification() {
    location.reload();
}

// Envoie les modifications au serveur en asynchrone via fetch + async/await
async function envoyerModifications() {
    // Cacher les erreurs precedentes
    var zoneErreur = document.getElementById("erreur-profil");
    zoneErreur.innerHTML = "";
    zoneErreur.style.display = "none";

    // Recuperer les nouvelles valeurs
    var nom = document.getElementById("edit-nom").value;
    var prenom = document.getElementById("edit-prenom").value;
    var adresse = document.getElementById("edit-adresse").value;
    var tel = document.getElementById("edit-tel").value;

    // Validation cote client avant l'envoi
    var erreurs = [];
    if (nom == "" || nom == null) erreurs.push("Le nom est obligatoire.");
    if (prenom == "" || prenom == null) erreurs.push("Le prénom est obligatoire.");
    if (adresse == "" || adresse == null) erreurs.push("L'adresse est obligatoire.");
    var telNet = tel.replace(/\s/g, "");
    if (telNet.length != 10) erreurs.push("Le téléphone doit faire 10 chiffres.");
    for (var i = 0; i < telNet.length; i++) {
        if (isNaN(parseInt(telNet.charAt(i)))) {
            erreurs.push("Le téléphone ne doit contenir que des chiffres.");
            break;
        }
    }

    if (erreurs.length > 0) {
        zoneErreur.innerHTML = erreurs.join("<br>");
        zoneErreur.style.display = "block";
        return;
    }

    var donnees = new URLSearchParams();
    donnees.append("nom", nom);
    donnees.append("prenom", prenom);
    donnees.append("adresse", adresse);
    donnees.append("telephone", tel);

    try {
        // Appel asynchrone
        var reponse = await fetch("script_php/api_profil.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: donnees
        });

        if (!reponse.ok) {
            zoneErreur.innerHTML = "Erreur de communication avec le serveur.";
            zoneErreur.style.display = "block";
            return;
        }

        var texteReponse = await reponse.text();

        if (texteReponse == "OK") {
            // Le serveur a accepte la modification
            document.getElementById("affichage-nom").textContent = nom;
            document.getElementById("affichage-prenom").textContent = prenom;
            document.getElementById("affichage-adresse").textContent = adresse;
            document.getElementById("affichage-tel").textContent = tel;

            // Remettre les boutons dans leur etat initial
            document.getElementById("btn-modifier").style.display = "inline-block";
            document.getElementById("btn-valider-profil").style.display = "none";
            document.getElementById("btn-annuler-profil").style.display = "none";

            // Afficher le message de succes
            var zoneSucces = document.getElementById("succes-profil");
            zoneSucces.innerHTML = "Vos informations ont été mises à jour !";
            zoneSucces.style.display = "block";
            // Le message disparait apres 3 secondes
            setTimeout(function() {
                zoneSucces.style.display = "none";
            }, 3000);
        } else {
            zoneErreur.innerHTML = "Erreur : " + texteReponse;
            zoneErreur.style.display = "block";
        }
    } catch (erreur) {
        // On entre ici si la requete fetch a echoue (serveur injoignable, etc.)
        zoneErreur.innerHTML = "Impossible de joindre le serveur.";
        zoneErreur.style.display = "block";
    }
}
