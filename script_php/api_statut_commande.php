<?php

session_start();
require(__DIR__ . "/recup.php");
header('Content-Type: application/json');

// Il faut etre connecte
if (!isset($_SESSION['utilisateur_id'])) {
    echo json_encode(['ok' => false, 'message' => "Non connecté."]);
    exit;
}

$commande_id = isset($_GET['commande_id']) ? intval($_GET['commande_id']) : 0;

$commandes = lire_donnees_json('commandes');
foreach ($commandes as $cmd) {
    // Securite : on ne renvoie le statut que si la commande appartient au client connecte
    if ($cmd['id'] == $commande_id && $cmd['client_id'] == $_SESSION['utilisateur_id']) {
        echo json_encode([
            'ok' => true,
            'statut' => $cmd['statut'],
            'type' => isset($cmd['type']) ? $cmd['type'] : 'livraison'
        ]);
        exit;
    }
}

echo json_encode(['ok' => false, 'message' => "Commande introuvable."]);
