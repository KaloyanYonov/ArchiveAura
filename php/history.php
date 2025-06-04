<?php
// пътя до папката
$archives_dir = realpath(__DIR__ . '/../archives') . '/';

// списък с архивите
$archives = array_filter(glob($archives_dir . '*'), 'is_dir');

// сортираме 
rsort($archives);
?>

<!DOCTYPE html>
<html lang="bg">

<head>
    <meta charset="UTF-8">
    <title>История на архивите</title>
</head>

<body>

    <h1>История на архивите</h1>

    <ul>
        <?php foreach ($archives as $archive_path): ?>
            <?php
                $timestamp = basename($archive_path);
                $date = date('Y-m-d H:i:s', $timestamp);


                $domain_dirs = array_filter(glob($archive_path . '/*'), 'is_dir');
                $domain = $domain_dirs ? basename(reset($domain_dirs)) : 'Неизвестен домейн';

                $view_link = "view.php?archive=$timestamp&domain=$domain";
            ?>
            <li>
                [<?php echo htmlspecialchars($timestamp); ?>] (<?php echo htmlspecialchars($date); ?>)
                → <?php echo htmlspecialchars($domain); ?>
                → <a href="<?php echo $view_link; ?>">Преглед</a>
            </li>
        <?php endforeach; ?>
    </ul>

    <p><a href="../index.php">⬅️ Обратно към началната страница</a></p>

</body>

</html>
