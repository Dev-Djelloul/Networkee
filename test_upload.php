<?php
// Outil de diagnostic pour les uploads
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo '<h2>Informations $_FILES</h2>';
    echo '<pre>';
    print_r($_FILES);
    echo '</pre>';

    if (isset($_FILES['test'])) {
        $f = $_FILES['test'];
        if ($f['error'] === UPLOAD_ERR_OK) {
            $target = __DIR__ . '/uploads/' . basename($f['name']);
            if (move_uploaded_file($f['tmp_name'], $target)) {
                echo '<p style="color:green">✅ Upload réussi : ' . htmlspecialchars($target) . '</p>';
            } else {
                echo '<p style="color:red">❌ move_uploaded_file a échoué.</p>';
            }
        } else {
            $errors = [
                1 => 'UPLOAD_ERR_INI_SIZE : le fichier dépasse upload_max_filesize',
                2 => 'UPLOAD_ERR_FORM_SIZE : le fichier dépasse MAX_FILE_SIZE',
                3 => 'UPLOAD_ERR_PARTIAL : upload incomplet',
                4 => 'UPLOAD_ERR_NO_FILE : aucun fichier envoyé',
                6 => 'UPLOAD_ERR_NO_TMP_DIR : dossier temporaire manquant',
                7 => 'UPLOAD_ERR_CANT_WRITE : échec écriture disque',
                8 => 'UPLOAD_ERR_EXTENSION : extension PHP bloquée',
            ];
            echo '<p style="color:red">❌ Erreur upload : ' . ($errors[$f['error']] ?? 'Code ' . $f['error']) . '</p>';
        }
    }
    echo '<hr><a href="">Retour</a>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Test upload</title></head>
<body style="font-family: Inter, sans-serif; padding: 2rem;">
    <h1>Test d'upload Networkee</h1>
    <p>upload_max_filesize : <?php echo ini_get('upload_max_filesize'); ?></p>
    <p>post_max_size : <?php echo ini_get('post_max_size'); ?></p>
    <p>upload_tmp_dir : <?php echo ini_get('upload_tmp_dir') ?: 'défaut système'; ?></p>
    <p>Permissions dossier uploads : <?php echo substr(sprintf('%o', fileperms(__DIR__ . '/uploads')), -4); ?></p>
    <form method="post" enctype="multipart/form-data">
        <input type="file" name="test" accept="image/*"><br><br>
        <button type="submit">Uploader le fichier</button>
    </form>
</body>
</html>
