# ProjetInfo3

## Phase 4 - Sécurité (identifiants de test)

Les mots de passe sont désormais hachés (`password_hash` / `password_verify`).
Le fichier `utilisateur.json` ne contient plus aucun mot de passe en clair.

Comptes de test (à utiliser pour la démo / soutenance) :

| Email | Mot de passe | Rôle |
|---|---|---|
| jean.dupont@email.com | client123 | client |
| maria.s@email.com | client123 | client |
| admin1@yumland.fr | admin123 | admin |
| cuisine@yumland.fr | resto123 | restaurateur |
| livreur@yumland.fr | livreur123 | livreur |

### Corrections Phase 4 (à reporter dans le rapport)
- Mots de passe hachés (bcrypt via password_hash).
- Échappement des sorties utilisateur avec htmlspecialchars (fonction `proteger()` dans recup.php) → protection contre les injections XSS.
- Correction du bug de la carte (api_plats.php).
- Correction du bug d'édition de profil (apostrophes dans l'adresse).

### Fonctionnalités Phase 4 ajoutées
- **Blocage / déblocage d'utilisateur par l'admin en asynchrone** (fetch → script_php/api_admin.php, sans rechargement). Un compte bloqué voit sa session coupée dès sa prochaine action (fonction `verifier_blocage()` appelée sur chaque page protégée) et ne peut plus se connecter.
- **Journal des incidents** (logs) : mauvais mot de passe, tentative de connexion sur compte bloqué, blocage/déblocage. Consultable par l'admin (Data/logs.json, créé automatiquement).
- **Modification d'une commande payée non préparée** (modifier_commande.php) : ajout/retrait d'articles, recalcul du total, paiement de la différence si le total augmente, ticket de réduction si le total baisse.
- **Flux restaurateur complet** : en attente → en préparation → prête → en livraison (assignation d'un livreur).
- **Validation serveur renforcée** : plat_id et quantité contrôlés côté serveur (panier), jamais de confiance aveugle aux données POST.
- **Accessibilité** : balises viewport sur toutes les pages, labels reliés aux champs (for/id), aria-label sur les filtres, attributs alt sur les images, classe .sr-only.
- **Plats les plus populaires** sur la page d'accueil, calculés à partir des commandes réelles.
- **Refactorisation** : catalogue d'images centralisé dans une fonction `image_plat()` (recup.php), réutilisée par l'accueil et l'API de la carte.

### Note technique pour la soutenance
Avec un stockage par fichiers JSON et les sessions PHP par fichier, on ne peut pas détruire à distance la session d'un autre utilisateur. Le blocage est donc appliqué « à la prochaine action » du compte bloqué via `verifier_blocage()`, ce qui répond au besoin : l'utilisateur bloqué ne peut plus continuer à utiliser le site.

### Paiement CYBank (Phase 4)
Le paiement utilise désormais l'interface externe CYBank.

Flux : `panier.php` (ou `modifier_commande.php` pour un complément) prépare la
transaction en session → `paiement.php` envoie un formulaire POST à CYBank
(transaction, montant, vendeur, retour, control md5) → l'utilisateur paie sur
CYBank → CYBank redirige vers `retour_paiement.php`, qui **vérifie la valeur de
contrôle md5** avant d'enregistrer la commande. Une commande n'est créée
qu'après un paiement authentique et accepté.


Carte de test CYBank : 5555 1234 5678 9000, cryptogramme 555, date au choix.
Tant que ces deux réglages ne sont pas faits, `paiement.php` affiche un avertissement
et CYBank refusera la transaction (clé d'API invalide).

### Fonctionnalité innovante (soutenance) : suivi visuel de commande
Une barre de progression visuelle (stepper) montre l'avancement d'une commande :
Payée → En préparation → Prête → En livraison → Livrée (l'étape "En livraison"
est masquée pour les commandes sur place / à emporter).

- Composant réutilisable : `script_php/suivi.php` (fonction `afficher_suivi()`).
- Page dédiée : `suivi_commande.php` (lien "📍 Suivre" depuis le profil pour
  toute commande non livrée).
- **Mise à jour en temps réel sans rechargement** : `js/suivi.js` interroge
  `script_php/api_statut_commande.php` toutes les 5 secondes (requête asynchrone)
  et redessine la barre quand le restaurateur/livreur fait évoluer le statut.
- Sécurité : l'API ne renvoie le statut que si la commande appartient au client connecté.


Démo suggérée : ouvrir suivi_commande.php d'une commande côté client, puis faire
évoluer son statut depuis le compte restaurateur dans un autre onglet — la barre
avance toute seule côté client.
