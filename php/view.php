<?php
$timestamp = isset($_GET['archive']) ? $_GET['archive'] : '';
$domain = isset($_GET['domain']) ? $_GET['domain'] : '';

if ($timestamp && $domain) {
    //път
    $archive_dir = "../archives/$timestamp/$domain";

    $iframe_src = "$archive_dir/index.html";

    // Ако index.html не съществува търсим първия .html файл (reccursive)
    if (!file_exists(__DIR__ . "/$iframe_src")) {
        // Търсим първия .html файл в цялата папка (reccursive)
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . "/../archives/$timestamp/$domain"));
        $html_file_found = false;

        foreach ($iterator as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'html') {
                // Относителния път
                $relative_path = str_replace(realpath(__DIR__ . "/..") . DIRECTORY_SEPARATOR, "../", $file);
                $iframe_src = $relative_path;
                $html_file_found = true;
                break;
            }
        }

        if (!$html_file_found) {
            $iframe_src = '';
        }
    }
} else {
    $iframe_src = '';
}
?>

<!DOCTYPE html>
<html lang="bg">

<head>
    <meta charset="UTF-8">
    <title>Преглед на архив</title>
</head>

<body>

    <h1>Преглед на архив</h1>

    <?php if ($iframe_src && file_exists(__DIR__ . "/$iframe_src")): ?>
        <iframe src="<?php echo htmlspecialchars($iframe_src); ?>" width="100%" height="800px"></iframe>
    <?php else: ?>
        <p><strong>Грешка: Не беше намерен HTML файл за показване.</strong></p>
    <?php endif; ?>

    <p><a href="history.php">⬅️ Обратно към историята</a></p>

</body>

</html>
