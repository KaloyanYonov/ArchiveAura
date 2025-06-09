<?php
session_start();
require_once "db_config.php";

$user_id = $_SESSION['user_id'] ?? 1;

$iframe_src = '';

if (isset($_GET['capture_id']) && is_numeric($_GET['capture_id'])) {
    $capture_id = intval($_GET['capture_id']);

    // Load saved_path for this capture (only if belongs to current user or global)
    $stmt = $pdo->prepare("
        SELECT saved_path
        FROM captures
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$capture_id, $user_id]);
    $capture = $stmt->fetch();

    if ($capture) {
        $iframe_src = $capture['saved_path'];

        // Check if file exists
        if (!file_exists(__DIR__ . "/$iframe_src")) {
            // Try fallback → recursively find first .html file in same folder
            $base_dir = dirname(__DIR__ . "/$iframe_src");
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base_dir));
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
        // Invalid capture_id or user has no access
        $iframe_src = '';
    }
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
