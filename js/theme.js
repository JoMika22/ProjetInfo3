// Fonction pour ecrire un cookie (avec expiration 1 an)
function ecrireCookie(nom, valeur) {
    var date = new Date();
    // Cookie qui dure 1 an (365 jours)
    date.setTime(date.getTime() + (365 * 24 * 60 * 60 * 1000));
    document.cookie = nom + "=" + valeur + "; expires=" + date.toUTCString() + "; path=/";
}

// Fonction pour lire un cookie par son nom
function lireCookie(nom) {
    // On decoupe la chaine des cookies par "; "
    var cookies = document.cookie.split("; ");
    for (var i = 0; i < cookies.length; i++) {
        var c = cookies[i].split("=");
        if (c[0] == nom) {
            return c[1];
        }
    }
    return null;
}

// Appliquer le theme en modifiant la balise <link> du CSS
function appliquerTheme(theme) {
    var lienCss = document.getElementById("theme-css");
    if (lienCss == null) return;

    // Verification de la valeur
    if (theme != "sombre" && theme != "clair") {
        theme = "clair";
    }

    if (theme == "sombre") {
        lienCss.setAttribute("href", "style-dark.css");
    } else {
        lienCss.setAttribute("href", "style.css");
    }

    // Mettre a jour le texte du bouton
    var bouton = document.getElementById("btn-theme");
    if (bouton != null) {
        if (theme == "sombre") {
            bouton.innerHTML = "☀️ Mode clair";
        } else {
            bouton.innerHTML = "🌙 Mode sombre";
        }
    }
}

// Au chargement de la page, lire le cookie et appliquer le theme
window.onload = function() {
    var themeCookie = lireCookie("theme_yumland");

    // Si pas de cookie ou valeur incoherente => mode par defaut (clair)
    if (themeCookie != "sombre" && themeCookie != "clair") {
        themeCookie = "clair";
    }
    appliquerTheme(themeCookie);

    // Quand on clique sur le bouton, on change le theme
    var bouton = document.getElementById("btn-theme");
    if (bouton != null) {
        bouton.addEventListener("click", function() {
            var themeActuel = lireCookie("theme_yumland");
            var nouveau;
            if (themeActuel == "sombre") {
                nouveau = "clair";
            } else {
                nouveau = "sombre";
            }
            ecrireCookie("theme_yumland", nouveau);
            appliquerTheme(nouveau);
        });
    }
};
