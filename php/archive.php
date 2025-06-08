<?php
$archives_dir = realpath(__DIR__ . '/../archives') . '/';

$message = '';

if (isset($_POST['url']) && !empty($_POST['url'])) {
    $url_input = trim($_POST['url']);

    // дали е http или htps
    if (preg_match('/^https?:\/\//', $url_input)) {
        $url = escapeshellarg($url_input);

        // create dir с някакъв timestamp
        $timestamp = time();
        $archive_subdir = $archives_dir . $timestamp;
        mkdir($archive_subdir, 0777, true);

        // wget Windows PATH , Доро внимавай тук
        $wget_path = 'C:/xampp/wget/wget.exe';

        $cmd = "\"$wget_path\" --mirror --convert-links --adjust-extension --page-requisites --no-parent -P " . escapeshellarg($archive_subdir) . " " . $url;

        // За debug :
        // echo "<pre>Executing command: $cmd</pre>";

        exec($cmd . " 2>&1", $output, $return_var);

        // Debug
        // echo "<pre>Output:\n" . implode("\n", $output) . "\nReturn code: $return_var</pre>";

        // Проверяваме дали index.html е създаден
        $host = parse_url($url_input, PHP_URL_HOST);
        $path = parse_url($url_input, PHP_URL_PATH);

        // Ако няма path търсим index.html
        if ($path == '' || $path == '/') {
            $page_file = "index.html";
            $page_path = "../archives/$timestamp/$host/$page_file";
        } else {
            // Правим path-а на .html файл
            $safe_path = ltrim($path, '/'); // махаме /
            $safe_path = rtrim($safe_path, '/'); // махаме / в края
            $page_file = $safe_path . ".html";
            $page_path = "../archives/$timestamp/$host/$safe_path.html";
        }

        if (file_exists(__DIR__ . "/../archives/$timestamp/$host/" . $page_file)) {
            $iframe_src = $page_path;
            $message = "Архивирането беше успешно!";
        } else {
            $message = "Грешка: Не беше намерен $page_file. Може би сайтът не позволява архивиране с wget.";
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
    <button id = "hideBtn">X</button>

    <div id="form-container">

        <h1>Архивирай страница</h1>

        <p>
            <a href="profile.php">
                <button>Профил</button>
            </a>
        </p>

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