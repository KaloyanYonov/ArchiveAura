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

        $wget_path = 'C:/xampp/wget/wget.exe';
        // $wget_path = '/bin/wget';

        $wget_options = "--no-parent --convert-links --adjust-extension --page-requisites";

        if (!empty($_POST['single_page'])) {
            $wget_options .= " --level=1";
        }

        if (!empty($_POST['use_cookie']) && !empty($_POST['cookie_value'])) {
            $cookie_value = trim($_POST['cookie_value']);
            $wget_options .= " " . escapeshellarg("--header=Cookie: $cookie_value");
        }

        $cmd = "\"$wget_path\" $wget_options -P " . escapeshellarg($archive_subdir) . " " . $url;

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

        if (isset($iframe_src) && file_exists(__DIR__ . "/$iframe_src")) {
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

                $hash = hash_file('sha256', __DIR__ . "/$iframe_src");

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

$slugified = isset($url_input) ? slugify_url($url_input) : 'capture';
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
    <div class="floating-toggle" id="toggleContainer">
        <button id="toggleBar" class="btn">⬆️ Скрий лентата</button>
    </div>

    <div class="toolbar" id="topbar">
        <?php if ($user_id != 1): ?>
            <span class="greeting">👋 Здравей, <?php echo htmlspecialchars($username); ?></span>
            <a href="logout.php" class="btn">🚪 Изход</a>
        <?php else: ?>
            <a href="login.php" class="btn">🔐 Вход</a>
            <a href="register.php" class="btn">📝 Регистрация</a>
        <?php endif; ?>

        <a class="btn" onclick="openCalendar()">📅 Календар</a>
        <a id="dark-mode" class="btn">🌗 Тъмен режим</a>

        <div class="form-wrap">
            <form method="post" action="archive.php" onsubmit="return validateURL();">
                <div class="input-row">
                    <input type="text" name="url" id="url" placeholder="Въведи URL за архивиране" required>
                    <button type="submit" class="btn">📥 Архивирай</button>
                </div>

                <div class="options">
                    <label><input type="checkbox" name="single_page" id="single_page"> Само до дълбочина 1</label><br>
                    <label><input type="checkbox" name="use_cookie" id="use_cookie"> Използвай cookie</label>
                    <input type="text" name="cookie_value" id="cookie_value" placeholder="Cookie: ключ=стойност"
                        style="display:none; margin-top: 6px;">
                </div>

                <?php if (!empty($message)): ?>
                    <div class="feedback-msg"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>
            </form>
        </div>


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

        <?php
        $stmt = $pdo->prepare("
            SELECT p.url, c.saved_path, c.captured_at 
            FROM captures c
            JOIN pages p ON c.page_id = p.id
            WHERE c.user_id = ?
            ORDER BY c.captured_at DESC
        ");
        $stmt->execute([$user_id]);
        $captures = $stmt->fetchAll();
        ?>

        <?php if ($captures): ?>
            <div class="archive-list">
                <h2>Предишни архиви</h2>
                <ul>
                    <?php foreach ($captures as $cap): ?>
                        <li>
                            <a href="<?php echo htmlspecialchars($cap['saved_path']); ?>" target="_blank">
                                <?php echo htmlspecialchars($cap['url']); ?>
                            </a>
                            <small>(<?php echo date("d.m.Y H:i:s", strtotime($cap['captured_at'])); ?>)</small>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

    </div>

    <div id="calendarModal">
        <div id="calendarBox">
            <button class="btn" id="close-btn" onclick="closeCalendar()">✖️ Затвори</button>
            <div id="calendarContent">Зареждане...</div>
        </div>
    </div>

    <?php if (isset($iframe_src)): ?>
        <button id="screenshotBtn" class="btn">📸 Изтегли като PNG</button>
        <canvas id="screenshotCanvas" style="display: none;"></canvas>


        <iframe src="<?php echo htmlspecialchars($iframe_src); ?>" width="100%" height="800px"
            data-filename="<?php echo htmlspecialchars($slugified); ?>">
        </iframe>
    <?php endif; ?>


    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const cookieCheckbox = document.getElementById("use_cookie");
            const cookieInput = document.getElementById("cookie_value");

            cookieCheckbox.addEventListener("change", () => {
                cookieInput.style.display = cookieCheckbox.checked ? "block" : "none";
            });
        });
    </script>
    <script src="../js/archive.js"></script>
    <script src="../js/containerLogic.js"></script>
    <script src="../js/topbarToggle.js"></script>
    <script src="../js/calendarModal.js"></script>
    <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
    <script src="../js/screenshot.js"></script>
</body>

</html>