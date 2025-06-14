<?php
session_start();
require_once "db_config.php";

$user_id = $_SESSION['user_id'] ?? 1;

$iframe_src = '';
$page_title_slug = 'capture';

$capture_id = null;
$original_url = '';

if (isset($_GET['capture_id'])) {
    $raw = $_GET['capture_id'];
    $parts = explode('/', $raw, 2);
    $capture_id = intval($parts[0]);
    $original_url = isset($parts[1]) ? urldecode($parts[1]) : '';

    if (empty($original_url) && isset($capture_id)) {
        $stmt = $pdo->prepare("
        SELECT pages.url FROM captures
        JOIN pages ON captures.page_id = pages.id
        WHERE captures.id = ? AND captures.user_id = ?
        LIMIT 1
    ");
        $stmt->execute([$capture_id, $user_id]);
        $result = $stmt->fetch();
        if ($result) {
            $original_url = $result['url'];
        }
    }


    if ($original_url) {
        $page_title_slug = $original_url;
    }

    $stmt = $pdo->prepare("SELECT saved_path FROM captures WHERE id = ? AND user_id = ?");
    $stmt->execute([$capture_id, $user_id]);
    $capture = $stmt->fetch();

    if ($capture) {
        $iframe_src = $capture['saved_path'];

        if (!file_exists(__DIR__ . "/$iframe_src")) {
            $base_dir = dirname(__DIR__ . "/$iframe_src");
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base_dir));
            foreach ($iterator as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'html') {
                    $relative_path = str_replace(realpath(__DIR__ . "/..") . DIRECTORY_SEPARATOR, "../", $file);
                    $iframe_src = $relative_path;
                    break;
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="bg">

<head>
    <meta charset="UTF-8">
    <title>Преглед на архив</title>
    <link rel="stylesheet" href="../styles/view_style.css">
    <link rel="stylesheet" href="../styles/global.css">
</head>

<body>
    <div class="container">
        <h2>📄 Преглед на архив</h2>

        <a href="../php/archive.php">⬅️Назад</a>
        <button id="screenshotBtn" class="btn">📸 Изтегли като PNG</button>
        <canvas id="screenshotCanvas" style="display: none;"></canvas>

        <?php if ($iframe_src && file_exists(__DIR__ . "/$iframe_src")): ?>
            <div class="frame-wrapper">
                <?php
                function slugify_url($url)
                {
                    $parsed = parse_url($url);
                    $host = $parsed['host'] ?? 'unknown';
                    $path = $parsed['path'] ?? '';
                    $slug = $host . $path;
                    $slug = str_replace(['/', '\\'], '_', $slug);
                    $slug = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $slug);
                    return $slug;
                }
                $slugified = $original_url ? slugify_url($original_url) : 'capture';
                ?>
                <iframe src="<?php echo htmlspecialchars($iframe_src); ?>" width="100%" height="800px"
                    data-filename="<?php echo htmlspecialchars($slugified); ?>">
                </iframe>
            </div>
        <?php else: ?>

            <p class="error-msg"><strong>❌ Грешка:</strong> Не беше намерен HTML файл за показване.</p>
        <?php endif; ?>
    </div>


    <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
    <script src="../js/screenshot.js"></script>
</body>

</html>