<?php

define('CYBANK_VENDEUR', 'MEF-1_J');

// URL de l'interface de paiement CYBank
define('CYBANK_URL', 'https://www.plateforme-smc.fr/cybank/index.php');

$__chemin_getapikey = __DIR__ . '/getapikey.php';
if (file_exists($__chemin_getapikey)) {
    require_once($__chemin_getapikey);
}

// Recuperer la cle d'API du vendeur
function cybank_api_key() {
    if (function_exists('getAPIKey')) {
        return getAPIKey(CYBANK_VENDEUR);
    }
    return 'zzzz';
}

// Indique si CYBank est correctement configure
function cybank_est_configure() {
    return (bool) preg_match('/^[0-9a-zA-Z]{15}$/', cybank_api_key());
}

// Generer un identifiant de transaction au format demande : [0-9a-zA-Z]{10,24}
function cybank_transaction_id() {
    $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $id = '';
    for ($i = 0; $i < 16; $i++) {
        $id .= $caracteres[random_int(0, strlen($caracteres) - 1)];
    }
    return $id;
}

// Formater un montant: 2 decimales, separateur "."
function cybank_format_montant($montant) {
    return number_format((float)$montant, 2, '.', '');
}

// Valeur de controle pour l'envoi vers CYBank
function cybank_control_envoi($transaction, $montant, $vendeur, $retour) {
    $api_key = cybank_api_key();
    return md5($api_key . "#" . $transaction . "#" . $montant . "#" . $vendeur . "#" . $retour . "#");
}

// Valeur de controle du retour
function cybank_control_retour($transaction, $montant, $vendeur, $statut) {
    $api_key = cybank_api_key();
    return md5($api_key . "#" . $transaction . "#" . $montant . "#" . $vendeur . "#" . $statut . "#");
}
