<?php
session_start();
require_once "db_config.php";

// ✅ Ensure archives/ directory exists
$archives_dir = __DIR__ . '/../archives';
if (!is_dir($archives_dir)) {
    mkdir($archives_dir, 0777, true);
}
$archives_dir = realpath($archives_dir) . '/';

$user_id = $_SESSION['user_id'] ?? 1;
$message = '';

if (isset($_POST['url']) && !empty($_POST['url'])) {
    $url_input = trim($_POST['url']);

    if (preg_match('/^https?:\/\//', $url_input)) {
        $url = escapeshellarg($url_input);

        $timestamp = time();
        $archive_subdir = $archives_dir . $timestamp;
        mkdir($archive_subdir, 0777, true);

        $wget_path = '/bin/wget';
        $cmd = "\"$wget_path\" --mirror --convert-links --adjust-extension --page-requisites --no-parent -P " . escapeshellarg($archive_subdir) . " " . $url;

        exec($cmd . " 2>&1", $output, $return_var);

        $host = parse_url($url_input, PHP_URL_HOST);
        $path = parse_url($url_input, PHP_URL_PATH);

        if ($path == '' || $path == '/') {
            $page_file = "index.html";
            $page_path = "../archives/$timestamp/$host/$page_file";
        } else {
            $safe_path = ltrim($path, '/');
            $safe_path = rtrim($safe_path, '/');
            $page_file = $safe_path . ".html";
            $page_path = "../archives/$timestamp/$host/$safe_path.html";
        }

        $full_page_path = __DIR__ . "/../archives/$timestamp/$host/" . $page_file;

        if (file_exists($full_page_path)) {
            $iframe_src = $page_path;
            $message = "Архивирането беше успешно!";
        } else {
            // ✅ Fallback to first .html found recursively
            $base_dir = __DIR__ . "/../archives/$timestamp/$host";
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base_dir));
            $html_file_found = false;

            foreach ($iterator as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'html') {
                    $relative_path = str_replace(realpath(__DIR__ . "/..") . DIRECTORY_SEPARATOR, "../", $file);
                    $iframe_src = $relative_path;
                    $html_file_found = true;
                    break;
                }
            }

            if ($html_file_found) {
                $message = "Архивирането беше успешно! (fallback)";
            } else {
                $message = "Грешка: Не беше намерен HTML файл. Може би сайтът не позволява архивиране с wget.";
            }
        }

        // ✅ DB logic (only if something was saved)
        if (isset($iframe_src) && !empty($iframe_src)) {
            try {
                $stmt = $pdo->prepare("SELECT id FROM pages WHERE url = ?");
                $stmt->execute([trim($_POST['url'])]);
                $page = $stmt->fetch();

                if ($page) {
                    $page_id = $page['id'];

                    if ($user_id == 1) {
                        $pdo->prepare("UPDATE pages SET last_capture = NOW(), total_captures = total_captures + 1 WHERE id = ?")
                            ->execute([$page_id]);
                    } else {
                        $pdo->prepare("UPDATE pages SET last_capture = NOW() WHERE id = ?")
                            ->execute([$page_id]);
                    }
                } else {
                    $pdo->prepare("INSERT INTO pages (url, first_capture, last_capture, total_captures) VALUES (?, NOW(), NOW(), ?)")
                        ->execute([trim($_POST['url']), ($user_id == 1 ? 1 : 0)]);
                    $page_id = $pdo->lastInsertId();
                }

                $stmt = $pdo->prepare("INSERT INTO captures (page_id, user_id, saved_path, captured_at) VALUES (?, ?, ?, NOW())");
                $stmt->execute([$page_id, $user_id, $iframe_src]);

            } catch (PDOException $e) {
                $message = "Грешка при записване в базата: " . $e->getMessage();
            }
        }

    } else {
        $message = "Моля въведете валиден URL (започващ с http:// или https://)";
    }
}
?>

<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <title>Архивиране</title>
</head>
<body>
    <button id="hideBtn">X</button>

    <div id="form-container">
        <h1>Архивирай страница</h1>

        <p><a href="profile.php"><button>Профил</button></a></p>
        <button id="dark-mode">Switch to Dark mode</button>

        <form method="post" action="archive.php" onsubmit="return validateURL();">
            <label for="url">Въведете URL:</label>
            <input type="text" name="url" id="url" required>
            <button type="submit">Архивирай</button>
        </form>
    </div>

    <p id="url-error"></p>

    <script src="../js/archive.js"></script>
    <script src="../js/containerFunc.js"></script>

    <?php if (!empty($message)): ?>
        <p><strong><?php echo htmlspecialchars($message); ?></strong></p>
    <?php endif; ?>

    <?php if (isset($iframe_src)): ?>
        <h2>Резултат:</h2>
        <iframe src="<?php echo htmlspecialchars($iframe_src); ?>" width="100%" height="800px"></iframe>
    <?php endif; ?>

    <p><a href="../index.php">⬅️ Обратно към началната страница</a></p>
</body>
</html>
