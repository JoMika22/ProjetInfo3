# Les Saveurs du Soleil

Site web d'un restaurant multiculturel fictif, réalisé dans le cadre du projet
d'Informatique 4 (préING2). Le site gère toute la chaîne d'une commande, du choix
du client jusqu'à la livraison, en passant par l'inscription, le paiement, la
préparation par le restaurateur et la livraison.

## Présentation

L'application distingue quatre types d'utilisateurs, chacun avec son interface :

- le **client**, qui consulte la carte, commande, paie et suit ses commandes ;
- le **restaurateur**, qui prépare les commandes et les assigne à un livreur ;
- le **livreur**, qui consulte les livraisons qui lui sont attribuées et les marque comme effectuées ;
- l'**administrateur**, qui gère les comptes utilisateurs et surveille les incidents.

Le site n'utilise pas de base de données : toutes les données sont stockées dans
des fichiers JSON, ce qui est suffisant pour le périmètre du projet et facile à
déployer.

## Technologies

Le site est écrit en **PHP** pour la partie serveur et en **HTML / CSS** pour les
pages. La partie dynamique (validation des formulaires, filtres, mode sombre,
suivi de commande) repose sur du **JavaScript**, avec des **requêtes asynchrones
(fetch)** pour échanger avec le serveur sans recharger la page. Aucune
bibliothèque externe n'est utilisée.

## Installation

Le site fonctionne avec le serveur web intégré à PHP, ce qui ne nécessite aucune
installation particulière.

1. Ouvrir un terminal dans le dossier du projet (`Projet_info_github`).
2. Lancer le serveur PHP :

   ```
   php -S localhost:8080
   ```

3. Ouvrir `http://localhost:8080` dans un navigateur.

Le serveur doit rester ouvert tant qu'on utilise le site ; pour l'arrêter, faire
`Ctrl + C` dans le terminal. La page d'accueil (`index.php`) s'affiche directement
à l'adresse racine, et les autres pages sont accessibles à leur nom (par exemple
`http://localhost:8080/carte.php`).

## Organisation des fichiers

Les pages visibles (les « vues ») sont à la racine : `index.php` (accueil),
`carte.php`, `inscriptions.php`, `connexion.php`, `profil.php`, `panier.php`,
`paiement.php`, `suivi_commande.php`, `notation.php`, ainsi que les pages
réservées `admin.php`, `restaurateur.php` et `livreur.php`.

Le dossier **`script_php/`** contient le code qui n'est pas une page :
`recup.php` regroupe les fonctions partagées (lecture/écriture des JSON,
recherche d'un utilisateur ou d'un plat, sécurité…), `nav.php` est la barre de
navigation incluse partout, et les fichiers `api_*.php` répondent aux requêtes
asynchrones du JavaScript.

Le dossier **`Data/`** contient les données : `utilisateur.json`, `plat.json`,
`menu.json`, `commandes.json`, et `logs.json` (créé automatiquement pour les
incidents). Le dossier **`js/`** contient les scripts client (thème, validation,
profil, carte, suivi de commande, administration).

## Stockage des données

Les données sont enregistrées au format JSON. Un utilisateur contient son nom,
prénom, email, mot de passe (haché), rôle, adresse, téléphone, date d'inscription
et un indicateur de blocage. Une commande contient la liste de ses articles, son
total, le montant payé, son statut, le type (livraison ou sur place), le client
et le livreur associés, ainsi que les dates et notations.

La lecture et l'écriture passent toujours par les fonctions `lire_donnees_json()`
et `ecrire_donnees_json()` de `recup.php`, ce qui centralise l'accès aux fichiers.

## Fonctionnalités principales

Côté client, on peut s'inscrire, se connecter, parcourir la carte avec recherche,
filtres et tris, ajouter des plats au panier, payer, suivre ses commandes en
temps réel, modifier une commande tant qu'elle n'est pas préparée, et noter une
commande livrée. Le profil permet de modifier ses informations sans recharger la
page.

Côté restaurateur, on suit l'avancement des commandes (en attente → en préparation
→ prête → en livraison) et on assigne un livreur. Côté livreur, on consulte les
livraisons attribuées et on les marque comme effectuées. Côté administrateur, on
gère les utilisateurs (consultation des profils, blocage/déblocage) et on consulte
le journal des incidents.

## Sécurité

La sécurité a été un point d'attention particulier. Les mots de passe ne sont
jamais stockés en clair : ils sont hachés avec `password_hash` (bcrypt) et
vérifiés avec `password_verify`. On ne peut donc pas retrouver un mot de passe à
partir du fichier `utilisateur.json`.

Toutes les données affichées qui proviennent des utilisateurs (noms, adresses,
commentaires…) passent par la fonction `proteger()`, qui applique
`htmlspecialchars`, afin d'éviter les injections de code (XSS). Les données reçues
des formulaires sont également revalidées côté serveur, sans jamais faire
confiance aux seules vérifications JavaScript. Enfin, chaque page réservée vérifie
le rôle de l'utilisateur, et un compte bloqué par un administrateur voit sa
session coupée dès son action suivante.

## Paiement (interface CYBank)

Le paiement passe par l'interface externe CYBank. Au moment de valider, le site
prépare la transaction puis l'envoie à CYBank (`paiement.php`). Après le paiement,
CYBank renvoie le client vers `retour_paiement.php`, qui **vérifie la valeur de
contrôle md5** avant d'enregistrer la commande : une commande n'est créée que si
le paiement est authentique et accepté. Ce contrôle empêche un utilisateur de
simuler un faux paiement en modifiant l'adresse de retour.

La configuration se trouve dans `script_php/config_cybank.php` (code vendeur du
groupe : `MEF-1_J`) et utilise `script_php/getapikey.php` fourni par les
enseignants. Pour tester un paiement, utiliser la carte d'essai : numéro
**5555 1234 5678 9000**, cryptogramme **555**, date au choix.

## Fonctionnalité supplémentaire : le suivi de commande en temps réel

En plus des fonctionnalités demandées, nous avons ajouté un suivi visuel de
commande, à la manière des applications de livraison. Une barre de progression
montre l'avancement (Payée → En préparation → Prête → En livraison → Livrée), et
elle se met à jour **toute seule, sans recharger la page** : le script
`js/suivi.js` interroge régulièrement le serveur (`api_statut_commande.php`) et
redessine la barre dès que le restaurateur ou le livreur fait évoluer le statut.
C'est une mise en pratique concrète des requêtes asynchrones.

## Comptes de test

Quelques comptes sont déjà enregistrés pour tester le site sans avoir à s'inscrire.
Tous les clients utilisent le mot de passe `client123`.

| Email | Mot de passe | Rôle |
|---|---|---|
| jean.dupont@email.com | client123 | client |
| maria.s@email.com | client123 | client |
| admin1@yumland.fr | admin123 | administrateur |
| cuisine@yumland.fr | resto123 | restaurateur |
| livreur@yumland.fr | livreur123 | livreur |
