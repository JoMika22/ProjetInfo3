<?php

// Proteger une chaine avant de l'afficher dans du HTML (anti-XSS)
// A utiliser sur TOUTE donnee qui vient de l'utilisateur : echo proteger($u['nom']);
function proteger($texte) {
    return htmlspecialchars($texte, ENT_QUOTES, 'UTF-8');
}

// Journaliser un incident (mauvais mot de passe, compte bloque, blocage, ...)
// Les logs sont stockes dans Data/logs.json
function log_incident($type, $message) {
    $chemin = __DIR__ . '/../Data/logs.json';
    $logs = [];
    if (file_exists($chemin)) {
        $logs = json_decode(file_get_contents($chemin), true);
        if (!is_array($logs)) $logs = [];
    }
    $logs[] = [
        'date' => date('Y-m-d H:i:s'),
        'type' => $type,
        'message' => $message
    ];
    file_put_contents($chemin, json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Verifier si l'utilisateur connecte est bloque.
// Si oui, on detruit sa session immediatement et on le renvoie a la connexion.
// A appeler en haut de chaque page protegee, apres session_start().
function verifier_blocage() {
    if (!isset($_SESSION['utilisateur_id'])) return;
    $u = trouver_par_id($_SESSION['utilisateur_id']);
    // Si le compte a disparu ou est bloque, on coupe la session
    if ($u == null || (isset($u['bloque']) && $u['bloque'] == true)) {
        session_destroy();
        header("Location: connexion.php?bloque=1");
        exit;
    }
}

// Lire un fichier JSON
function lire_donnees_json($nom) {
    $chemin = __DIR__ . '/../Data/' . $nom . '.json';
    if (!file_exists($chemin)) {
        return [];
    }
    $contenu = file_get_contents($chemin);
    return json_decode($contenu, true);
}

// Ecrire dans un fichier JSON
function ecrire_donnees_json($nom, $donnees) {
    $chemin = __DIR__ . '/../Data/' . $nom . '.json';
    file_put_contents($chemin, json_encode($donnees, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Trouver le prochain ID
function generer_id($donnees) {
    $max = 0;
    foreach ($donnees as $d) {
        if ($d['id'] > $max) {
            $max = $d['id'];
        }
    }
    return $max + 1;
}

// Chercher un utilisateur par email
function trouver_par_email($email) {
    $users = lire_donnees_json('utilisateur');
    foreach ($users as $u) {
        if ($u['email'] == $email) {
            return $u;
        }
    }
    return null;
}

// Chercher un utilisateur par ID
function trouver_par_id($id) {
    $users = lire_donnees_json('utilisateur');
    foreach ($users as $u) {
        if ($u['id'] == $id) {
            return $u;
        }
    }
    return null;
}

// Chercher un plat par ID
function trouver_plat($id) {
    $plats = lire_donnees_json('plat');
    foreach ($plats as $p) {
        if ($p['id'] == $id) {
            return $p;
        }
    }
    return null;
}

// Renvoyer l'URL de l'image d'un plat selon son ID (catalogue centralise)
function image_plat($id) {
    $images = [
        101 => "https://cdn.chefclub.tools/uploads/recipes/cover-thumbnail/0a1c1e2d-b279-4c89-87da-46965aba7d96.jpg",
        102 => "https://images.eatself.com/1597/Products_73729_materiasrepositorio-1563547891.jpeg",
        103 => "https://www.kilometre-0.fr/wp-content/uploads/2019/01/images20180617Cuisine_mart546.jpg",
        104 => "https://www.la-cuisine-marocaine.com/photos-recettes/salade-tomate-concombre-marocaine.jpg",
        201 => "https://sebplatform.api.groupe-seb.com/statics/82159e7f-4ddf-4bc9-b792-8f9ee58c88b8.png?w=1920&fit=scale",
        202 => "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSJHs0B7g8nv0QHIinCRIvamyTKTMIbQw9IDw&s",
        203 => "https://www.aide-en-cuisine.fr/wp-content/uploads/2025/03/recette-couscous-marocain-1024x768.webp",
        204 => "https://soursweetbitter.com/wp-content/uploads/al_opt_content/IMAGE/soursweetbitter.com/wp-content/uploads/2024/05/francesinha1-1024x819.webp.bv_resized_ipad.webp.bv.webp?bv_host=soursweetbitter.com",
        205 => "https://thumbs.dreamstime.com/b/mamaliga-nourriture-traditionnelle-roumaine-de-salaj-fait-par-le-lard-cormeal-et-frit-155046616.jpg",
        206 => "https://assets.afcdn.com/recipe/20130130/59691_w1024h768c1cx256cy192.webp",
        301 => "https://images.ctfassets.net/1p5r6txvlxu4/2AuCQgVaK08nA3Wgm7TJbr/7981b99d4a52359fe5dd1090dd2ec96a/Tiramisu_original.jpg?w=768&h=541&fm=webp&q=100&fit=fill&f=center",
        302 => "https://www.abelandcole.co.uk/media/11695_37039_m.jpg",
        303 => "https://creationhloua.com/wp-content/uploads/2024/10/chantilly-stracciatella-cremeuse-et-gourmande-en-quelques-minutes.jpg",
        401 => "https://i.pinimg.com/736x/5e/e4/8e/5ee48e1cb3022b3c1f7ea2c4952ae765.jpg",
        402 => "https://boutique.lapaiou-pizzeria.fr/images/produits/2045/1732117882-dsc08318.webp",
        403 => "https://www.sommelleriedefrance.com/6009-large_default/marquis-des-leves-.jpg"
    ];
    return isset($images[$id]) ? $images[$id] : '';
}

// Compter les commandes d'un utilisateur
function compter_commandes($user_id) {
    $commandes = lire_donnees_json('commandes');
    $nb = 0;
    foreach ($commandes as $c) {
        if ($c['client_id'] == $user_id) {
            $nb++;
        }
    }
    return $nb;
}