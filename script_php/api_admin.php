<?php

session_start();
require(__DIR__ . "/recup.php");

header('Content-Type: application/json');

// Securite : seul un admin connecte peut utiliser ce point d'entree
if (!isset($_SESSION['utilisateur_id']) || $_SESSION['utilisateur_role'] != 'admin') {
    echo json_encode(['ok' => false, 'message' => "Accès refusé."]);
    exit;
}

// On n'accepte que le POST (une action qui modifie des donnees ne doit pas passer en GET)
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['ok' => false, 'message' => "Méthode incorrecte."]);
    exit;
}

$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($action != 'bloquer' && $action != 'debloquer') {
    echo json_encode(['ok' => false, 'message' => "Action inconnue."]);
    exit;
}

// Empecher l'admin de se bloquer lui-meme
if ($user_id == $_SESSION['utilisateur_id']) {
    echo json_encode(['ok' => false, 'message' => "Vous ne pouvez pas bloquer votre propre compte."]);
    exit;
}

$utilisateurs = lire_donnees_json('utilisateur');
$trouve = false;
$nouvel_etat = false;
$cible_email = '';

foreach ($utilisateurs as $i => $u) {
    if ($u['id'] == $user_id) {
        $nouvel_etat = ($action == 'bloquer');
        $utilisateurs[$i]['bloque'] = $nouvel_etat;
        $cible_email = $u['email'];
        $trouve = true;
    }
}

if (!$trouve) {
    echo json_encode(['ok' => false, 'message' => "Utilisateur introuvable."]);
    exit;
}

ecrire_donnees_json('utilisateur', $utilisateurs);

// Journaliser l'action d'administration
$qui = $_SESSION['utilisateur_prenom'] . ' ' . $_SESSION['utilisateur_nom'];
log_incident('admin_blocage', $qui . " a " . ($nouvel_etat ? "bloqué" : "débloqué") . " le compte " . $cible_email);

echo json_encode([
    'ok' => true,
    'bloque' => $nouvel_etat,
    'message' => $nouvel_etat ? "Compte bloqué." : "Compte débloqué."
]);
