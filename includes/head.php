<?php
/**
 * Fragment de head commun à toutes les pages.
 * À inclure au début de chaque page (avant <body>).
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@200;300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>styles/modern.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script>window.NETWORKEE_BASE_URL = <?php echo json_encode($baseUrl); ?>;</script>
    <script src="<?php echo $baseUrl; ?>scripts/theme.js" defer></script>
    <script src="<?php echo $baseUrl; ?>scripts/hover-popover.js" defer></script>
    <script src="<?php echo $baseUrl; ?>scripts/post-menu.js" defer></script>
    <script src="<?php echo $baseUrl; ?>scripts/confirm-modal.js" defer></script>
    <title><?php echo $pageTitle ?? 'Networkee'; ?></title>
</head>
