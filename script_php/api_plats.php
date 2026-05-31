<?php

session_start();
require(__DIR__ . "/recup.php");

// On va renvoyer du JSON
header('Content-Type: application/json');

// Recuperer les filtres
$recherche = isset($_GET['recherche']) ? trim($_GET['recherche']) : '';
$categorie = isset($_GET['categorie']) ? $_GET['categorie'] : '';
$allergene = isset($_GET['allergene']) ? $_GET['allergene'] : '';

// Charger tous les plats
$plats = lire_donnees_json('plat');

// Filtrer les plats selon les criteres
$resultat = [];
foreach ($plats as $p) {
    // Filtre categorie
    if ($categorie != '' && $p['categorie'] != $categorie) continue;
    // Filtre allergene : on exclut les plats qui contiennent l'allergene
    if ($allergene != '' && in_array($allergene, $p['allergenes'])) continue;
    // Filtre recherche : on cherche dans le nom (insensible a la casse)
    if ($recherche != '' && stripos($p['nom'], $recherche) === false) continue;

    // Ajouter l'image au plat avant de l'envoyer (catalogue centralise dans recup.php)
    $p['image'] = image_plat($p['id']);
    $resultat[] = $p;
}

// Renvoyer en JSON pour que le JS puisse le traiter
echo json_encode($resultat);
?>
