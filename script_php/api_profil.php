<?php

session_start();
require(__DIR__ . "/recup.php");

// Verifier que l'utilisateur est connecté
if (!isset($_SESSION['utilisateur_id'])) {
    echo "Vous devez être connecté.";
    exit;
}

// Verifier que c'est bien une requête POST
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo "Méthode incorrecte.";
    exit;
}

// Recuperer les données envoyées par l'AJAX
$nom = isset($_POST['nom']) ? $_POST['nom'] : '';
$prenom = isset($_POST['prenom']) ? $_POST['prenom'] : '';
$adresse = isset($_POST['adresse']) ? $_POST['adresse'] : '';
$telephone = isset($_POST['telephone']) ? $_POST['telephone'] : '';

// Validation cote serveur aussi (au cas où quelqu'un contourne la validation JS)
if ($nom == '' || $prenom == '' || $adresse == '') {
    echo "Tous les champs sont obligatoires.";
    exit;
}
if (strlen($telephone) < 10) {
    echo "Le téléphone est invalide.";
    exit;
}

// Charger les utilisateurs et trouver celui qui est connecté
$utilisateurs = lire_donnees_json('utilisateur');
$mon_id = $_SESSION['utilisateur_id'];
$trouve = false;

foreach ($utilisateurs as $i => $u) {
    if ($u['id'] == $mon_id) {
        // Mise à jour des champs
        $utilisateurs[$i]['nom'] = $nom;
        $utilisateurs[$i]['prenom'] = $prenom;
        $utilisateurs[$i]['adresse'] = $adresse;
        $utilisateurs[$i]['telephone'] = $telephone;
        $trouve = true;
    }
}

if (!$trouve) {
    echo "Utilisateur introuvable.";
    exit;
}

// Sauvegarder dans le fichier JSON
ecrire_donnees_json('utilisateur', $utilisateurs);

// Mettre à jour la session
$_SESSION['utilisateur_nom'] = $nom;
$_SESSION['utilisateur_prenom'] = $prenom;

// On renvoie "OK" pour signaler le succès
echo "OK";
?>