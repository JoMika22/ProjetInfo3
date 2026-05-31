<?php
// ============================================================
// Configuration de l'interface de paiement CYBank
// ============================================================

// >>> A FAIRE 1 : mettre ICI votre code vendeur = identifiant de groupe de projet
//     (exemple : MI-3_A). Tant que ce n'est pas fait, CYBank refusera le paiement.
define('CYBANK_VENDEUR', 'MEF-1_J');

// URL de l'interface de paiement CYBank
define('CYBANK_URL', 'https://www.plateforme-smc.fr/cybank/index.php');

// >>> A FAIRE 2 : telecharger getapikey.zip sur
//     https://www.plateforme-smc.fr/cybank/getapikey.zip
//     puis placer le fichier getapikey.php dans ce dossier (script_php/).
//     Il fournit la fonction getAPIKey($vendeur) qui renvoie votre cle d'API.
$__chemin_getapikey = __DIR__ . '/getapikey.php';
if (file_exists($__chemin_getapikey)) {
    require_once($__chemin_getapikey);
}

// Recuperer la cle d'API du vendeur (repli sur "zzzz" si getapikey.php est absent)
function cybank_api_key() {
    if (function_exists('getAPIKey')) {
        return getAPIKey(CYBANK_VENDEUR);
    }
    return 'zzzz';
}

// Indique si CYBank est correctement configure (cle valide : 15 caracteres hexa/alnum)
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

// Formater un montant comme l'exige CYBank : 2 decimales, separateur "."
function cybank_format_montant($montant) {
    return number_format((float)$montant, 2, '.', '');
}

// Valeur de controle pour l'ENVOI vers CYBank (basee sur l'URL de retour)
function cybank_control_envoi($transaction, $montant, $vendeur, $retour) {
    $api_key = cybank_api_key();
    return md5($api_key . "#" . $transaction . "#" . $montant . "#" . $vendeur . "#" . $retour . "#");
}

// Valeur de controle du RETOUR (basee sur le statut) : sert a verifier l'integrite
function cybank_control_retour($transaction, $montant, $vendeur, $statut) {
    $api_key = cybank_api_key();
    return md5($api_key . "#" . $transaction . "#" . $montant . "#" . $vendeur . "#" . $statut . "#");
}
