// Verifier si une chaine est vide
function estVide(chaine) {
    if (chaine == null) return true;
    // On enleve les espaces avant/apres
    var s = chaine.replace(/^\s+|\s+$/g, "");
    return s.length == 0;
}

// Verifier le format d'un email (presence d'un @ et d'un .)
function estEmailValide(email) {
    if (estVide(email)) return false;
    var posArobase = email.indexOf("@");
    var posPoint = email.lastIndexOf(".");
    // L'arobase doit etre present et avant le dernier point
    if (posArobase < 1) return false;
    if (posPoint < posArobase + 2) return false;
    if (posPoint == email.length - 1) return false;
    return true;
}

// Verifier qu'un numero de telephone fait 10 chiffres
function estTelephoneValide(tel) {
    if (estVide(tel)) return false;
    // Enlever les espaces
    var t = tel.replace(/\s/g, "");
    if (t.length != 10) return false;
    // Verifier que tous les caracteres sont des chiffres
    for (var i = 0; i < t.length; i++) {
        if (isNaN(parseInt(t.charAt(i)))) {
            return false;
        }
    }
    return true;
}

// Verifier la longueur minimale d'un mot de passe
function estMotDePasseValide(mdp) {
    if (estVide(mdp)) return false;
    if (mdp.length < 6) return false;
    return true;
}

// Afficher un message d'erreur dans une zone dediee
function afficherErreur(idZone, message) {
    var zone = document.getElementById(idZone);
    if (zone != null) {
        zone.innerHTML = message;
        zone.style.display = "block";
    }
}

// Cacher la zone d'erreur
function cacherErreur(idZone) {
    var zone = document.getElementById(idZone);
    if (zone != null) {
        zone.innerHTML = "";
        zone.style.display = "none";
    }
}

function basculerMotDePasse(idChamp, idIcone) {
    var champ = document.getElementById(idChamp);
    var icone = document.getElementById(idIcone);
    if (champ == null) return;

    if (champ.type == "password") {
        champ.type = "text";
        if (icone != null) icone.innerHTML = "🙈";
    } else {
        champ.type = "password";
        if (icone != null) icone.innerHTML = "👁️";
    }
}

function activerCompteur(idChamp, idCompteur, max) {
    var champ = document.getElementById(idChamp);
    var compteur = document.getElementById(idCompteur);
    if (champ == null || compteur == null) return;

    // Fonction qui met a jour le compteur
    function maj() {
        var n = champ.value.length;
        compteur.innerHTML = n + " / " + max;
        // Si on depasse la limite, on affiche en rouge
        if (n > max) {
            compteur.style.color = "red";
        } else {
            compteur.style.color = "#555";
        }
    }
    // Au chargement et a chaque saisie
    maj();
    champ.addEventListener("input", maj);
}

function validerConnexion(event) {
    cacherErreur("erreur-connexion");

    var email = document.getElementById("champ-email").value;
    var mdp = document.getElementById("champ-mdp").value;

    var messages = [];

    if (!estEmailValide(email)) {
        messages.push("L'adresse email n'est pas valide.");
    }
    if (estVide(mdp)) {
        messages.push("Le mot de passe est obligatoire.");
    }

    if (messages.length > 0) {
        // On empeche l'envoi du formulaire
        event.preventDefault();
        afficherErreur("erreur-connexion", messages.join("<br>"));
        return false;
    }
    // Sinon le formulaire part normalement vers le serveur
    return true;
}

function validerInscription(event) {
    cacherErreur("erreur-inscription");

    var nom = document.getElementById("champ-nom").value;
    var prenom = document.getElementById("champ-prenom").value;
    var email = document.getElementById("champ-email").value;
    var mdp = document.getElementById("champ-mdp").value;
    var mdp2 = document.getElementById("champ-mdp2").value;
    var adresse = document.getElementById("champ-adresse").value;
    var tel = document.getElementById("champ-tel").value;

    var messages = [];

    if (estVide(nom)) {
        messages.push("Le nom est obligatoire.");
    }
    if (estVide(prenom)) {
        messages.push("Le prénom est obligatoire.");
    }
    if (!estEmailValide(email)) {
        messages.push("L'adresse email n'est pas valide.");
    }
    if (!estMotDePasseValide(mdp)) {
        messages.push("Le mot de passe doit faire au moins 6 caractères.");
    }
    if (mdp != mdp2) {
        messages.push("Les deux mots de passe ne correspondent pas.");
    }
    if (estVide(adresse)) {
        messages.push("L'adresse est obligatoire.");
    }
    if (!estTelephoneValide(tel)) {
        messages.push("Le numéro de téléphone doit contenir 10 chiffres.");
    }

    if (messages.length > 0) {
        event.preventDefault();
        afficherErreur("erreur-inscription", messages.join("<br>"));
        return false;
    }
    return true;
}

function validerNotation(event) {
    cacherErreur("erreur-notation");

    var noteRepas = document.getElementById("note-repas").value;
    var noteLivraison = document.getElementById("note-livraison").value;
    var commentaire = document.getElementById("champ-commentaire").value;

    var messages = [];
    // Verifier que les notes sont des nombres entre 1 et 5
    var nr = parseInt(noteRepas);
    var nl = parseInt(noteLivraison);
    if (isNaN(nr) || nr < 1 || nr > 5) {
        messages.push("La note du repas est invalide.");
    }
    if (isNaN(nl) || nl < 1 || nl > 5) {
        messages.push("La note de la livraison est invalide.");
    }
    // Le commentaire est limite a 300 caracteres
    if (commentaire.length > 300) {
        messages.push("Le commentaire ne peut pas dépasser 300 caractères.");
    }

    if (messages.length > 0) {
        event.preventDefault();
        afficherErreur("erreur-notation", messages.join("<br>"));
        return false;
    }
    return true;
}
