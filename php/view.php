<?php
session_start();
require_once "db_config.php";

$user_id = $_SESSION['user_id'] ?? 1;

$iframe_src = '';
$page_title_slug = 'capture';

$capture_id = null;
$original_url = '';

if (isset($_GET['capture_id'])) {
    // Support both capture_id=14 or capture_id=14/https://...
    $raw = $_GET['capture_id'];
    $parts = explode('/', $raw, 2);
    $capture_id = intval($parts[0]);
    $original_url = isset($parts[1]) ? urldecode($parts[1]) : '';

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
    <h1>📄 Преглед на архив</h1>

    <?php if ($iframe_src && file_exists(__DIR__ . "/$iframe_src")): ?>
        <div class="frame-wrapper">
            <iframe src="<?php echo htmlspecialchars($iframe_src); ?>" width="100%" height="800px"></iframe>
        </div>
    <?php else: ?>
        <p class="error-msg"><strong>❌ Грешка:</strong> Не беше намерен HTML файл за показване.</p>
    <?php endif; ?>

    <button id="screenshotBtn" class="btn">📸 Изтегли като PNG</button>
    <canvas id="screenshotCanvas" style="display: none;"></canvas>
    <a href="../php/archive.php">⬅️Назад</a>
</div>


<script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
<script src="../js/screenshot.js"></script>
</body>
</html>