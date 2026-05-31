<?php
// Deconnexion : on detruit la session et on redirige vers l'accueil
session_start();
session_destroy();
header("Location: ../index.php");
exit;