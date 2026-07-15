<?php
session_start();

// Détruire toutes les variables de session
session_unset();

// Détruire la session
session_destroy();

// Rediriger vers la page de chargement (compatible sous-dossier local)
header('Location: ../loader.php');
exit;
