<?php
session_start();
require_once "db_config.php";

$archives_dir = __DIR__ . '/../archives';
if (!is_dir($archives_dir)) {
    mkdir($archives_dir, 0777, true);
}
$archives_dir = realpath($archives_dir) . '/';

$user_id = $_SESSION['user_id'] ?? 1;
$message = '';
$new_capture = false;

$username = 'Потребител';
if (isset($_SESSION['email'])) {
    $username = explode('@', $_SESSION['email'])[0];
}

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

        $safe_path = trim($path, '/') ?: 'index';
        $page_file = $safe_path . ".html";
        $page_path = "../archives/$timestamp/$host/$page_file";
        $full_page_path = __DIR__ . "/../archives/$timestamp/$host/$page_file";

        if (!file_exists($full_page_path)) {
            $base_dir = __DIR__ . "/../archives/$timestamp/$host";
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base_dir));
            foreach ($iterator as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'html') {
                    $relative_path = str_replace(realpath(__DIR__ . "/..") . DIRECTORY_SEPARATOR, "../", $file);
                    $iframe_src = $relative_path;
                    $message = "Архивирането беше успешно! (fallback)";
                    break;
                }
            }
        } else {
            $iframe_src = $page_path;
            $message = "Архивирането беше успешно!";
        }

        if (isset($iframe_src) && file_exists($full_page_path)) {
            try {
                $stmt = $pdo->prepare("SELECT id FROM pages WHERE url = ?");
                $stmt->execute([$url_input]);
                $page = $stmt->fetch();

                if ($page) {
                    $page_id = $page['id'];
                    $query = ($user_id == 1)
                        ? "UPDATE pages SET last_capture = NOW(), total_captures = total_captures + 1 WHERE id = ?"
                        : "UPDATE pages SET last_capture = NOW() WHERE id = ?";
                    $pdo->prepare($query)->execute([$page_id]);
                } else {
                    $pdo->prepare("INSERT INTO pages (url, first_capture, last_capture, total_captures) VALUES (?, NOW(), NOW(), ?)")
                        ->execute([$url_input, ($user_id == 1 ? 1 : 0)]);
                    $page_id = $pdo->lastInsertId();
                }

                $hash = hash_file('sha256', $full_page_path);

                $stmt = $pdo->prepare("SELECT id FROM captures WHERE page_id = ? AND user_id = ? AND content_hash = ?");
                $stmt->execute([$page_id, $user_id, $hash]);
                $existing = $stmt->fetch();

                if ($existing) {
                    $message = "ℹ️ Вече съществува архив с тази страница.";
                    $new_capture = false;
                } else {
                    $stmt = $pdo->prepare("INSERT INTO captures (page_id, user_id, saved_path, captured_at, content_hash) VALUES (?, ?, ?, NOW(), ?)");
                    $stmt->execute([$page_id, $user_id, $iframe_src, $hash]);
                    $new_capture = true;
                }

            } catch (PDOException $e) {
                $message = "❌ Грешка при записване в базата: " . $e->getMessage();
            }
        }
    } else {
        $message = "Моля въведете валиден URL (започващ с http:// или https://)";
    }
}

$stats = ["first" => null, "last" => null, "count" => 0];
if ($new_capture) {
    $stats_stmt = $pdo->prepare("SELECT MIN(captured_at) AS first, MAX(captured_at) AS last, COUNT(*) AS count FROM captures WHERE user_id = ?");
    $stats_stmt->execute([$user_id]);
    if ($row = $stats_stmt->fetch()) {
        $stats['first'] = $row['first'];
        $stats['last'] = $row['last'];
        $stats['count'] = $row['count'];
    }
}
?>

<!DOCTYPE html>
<html lang="bg">

<head>
    <meta charset="UTF-8">
    <title>Архивиране</title>
    <link rel="stylesheet" href="../styles/archive_style.css">
    <link rel="stylesheet" href="../styles/global.css">
</head>

<body>
    <div id="toggleContainer" class="floating-toggle">
        <button id="toggleBar" class="btn">⬆️ Скрий лентата</button>
    </div>

    <div class="topbar" id="topbar">

        <div class="toolbar">
            <?php if ($user_id != 1): ?>
                <span class="greeting">👋 Здравей, <?php echo htmlspecialchars($username); ?></span>
                <a href="logout.php" class="btn">🚪 Изход</a>
            <?php else: ?>
                <a href="login.php" class="btn">🔐 Вход</a>
                <a href="register.php" class="btn">📝 Регистрация</a>
            <?php endif; ?>
            <button class="btn" onclick="openCalendar()">📅 Календар</button>
            <button id="dark-mode" class="btn">🌗 Тъмен режим</button>
        </div>

        <div class="form-wrap">
            <form method="post" action="archive.php" onsubmit="return validateURL();">
                <input type="text" name="url" id="url" placeholder="Въведи URL за архивиране" required>
                <button type="submit" class="btn">📥 Архивирай</button>
            </form>

            <?php if (!empty($message)): ?>
                <div class="feedback-msg"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <?php
            $stats_stmt = $pdo->prepare("SELECT MIN(captured_at) AS first, MAX(captured_at) AS last, COUNT(*) AS count FROM captures WHERE user_id = ?");
            $stats_stmt->execute([$user_id]);
            $row = $stats_stmt->fetch();

            if ($row && $row['count'] > 0): ?>
                <div class="stats-box">
                    <span>Брой архиви: <strong><?php echo $row['count']; ?></strong></span>
                    <span><?php echo date("d.m.Y", strtotime($row['first'])); ?> –
                        <?php echo date("d.m.Y", strtotime($row['last'])); ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    </div>

    <div id="calendarModal">
        <div id="calendarBox">
            <button class="btn" onclick="closeCalendar()">✖️ Затвори</button>
            <div id="calendarContent">Зареждане...</div>
        </div>
    </div>

    <?php if (isset($iframe_src)): ?>
        <iframe src="<?php echo htmlspecialchars($iframe_src); ?>" width="100%" height="800px"></iframe>
    <?php endif; ?>

    <script src="../js/archive.js"></script>
    <script src="../js/containerLogic.js"></script>
    <script src="../js/topbarToggle.js"></script>
    <script src="../js/calendarModal.js"></script>
</body>

</html>