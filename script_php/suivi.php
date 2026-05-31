<?php


function afficher_suivi($statut, $type = 'livraison') {
    // Definir les etapes selon le type de commande.
    // Pour une livraison on a une etape "En livraison" ; sinon on l'enleve.
    if ($type == 'livraison') {
        $etapes = [
            'en_attente'     => ['Payée', '💳'],
            'en_preparation' => ['En préparation', '👨‍🍳'],
            'prete'          => ['Prête', '📦'],
            'en_livraison'   => ['En livraison', '🛵'],
            'livree'         => ['Livrée', '✅'],
        ];
    } else {
        // Sur place / a emporter : pas d'etape livraison
        $etapes = [
            'en_attente'     => ['Payée', '💳'],
            'en_preparation' => ['En préparation', '👨‍🍳'],
            'prete'          => ['Prête', '📦'],
            'livree'         => ['Terminée', '✅'],
        ];
    }

    // Trouver l'index de l'etape courante
    $cles = array_keys($etapes);
    $index_courant = array_search($statut, $cles);
    // Si le statut n'est pas reconnu (ex: abandonnee), on n'allume aucune etape
    if ($index_courant === false) $index_courant = -1;

    echo '<div class="suivi-commande" role="list" aria-label="Suivi de la commande">';
    $i = 0;
    foreach ($etapes as $cle => $infos) {
        // Une etape est "faite" si on l'a depassee, "active" si c'est l'etape courante
        $classe = 'suivi-etape';
        if ($i < $index_courant) {
            $classe .= ' suivi-faite';
        } elseif ($i == $index_courant) {
            $classe .= ' suivi-active';
        }

        echo '<div class="' . $classe . '" role="listitem">';
        echo '  <div class="suivi-pastille">' . $infos[1] . '</div>';
        echo '  <div class="suivi-label">' . proteger($infos[0]) . '</div>';
        echo '</div>';

        // Trait de liaison entre les etapes (sauf apres la derniere)
        if ($i < count($etapes) - 1) {
            $classe_trait = ($i < $index_courant) ? 'suivi-trait suivi-trait-fait' : 'suivi-trait';
            echo '<div class="' . $classe_trait . '"></div>';
        }
        $i++;
    }
    echo '</div>';
}
