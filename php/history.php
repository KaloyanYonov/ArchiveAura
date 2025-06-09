<?php
session_start();
require_once "db_config.php";

$user_id = $_SESSION['user_id'] ?? 1;

// Query captures for this user
$stmt = $pdo->prepare("
    SELECT captures.id AS capture_id, pages.url, captures.saved_path, captures.captured_at
    FROM captures
    JOIN pages ON captures.page_id = pages.id
    WHERE captures.user_id = ?
    ORDER BY captures.captured_at DESC
");

$stmt->execute([$user_id]);
$captures = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../styles/history.css">
    <link rel="stylesheet" href="../styles/global.css">

    <title>История на архивите</title>
</head>
<body>

    <h1>История на архивите</h1>
    <p><a href="calendar_view.php">🔁 Превключи към изглед на календар</a></p>

    <?php if (empty($captures)): ?>
        <p>Все още няма архивирани страници.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($captures as $capture): ?>
                <?php
                    $timestamp = strtotime($capture['captured_at']);
                    $date = date('Y-m-d H:i:s', $timestamp);

                    // Extract domain from URL
                    $domain = parse_url($capture['url'], PHP_URL_HOST) ?? 'Неизвестен домейн';

                    // Link to view.php by capture_id
                    $view_link = "view.php?capture_id=" . urlencode($capture['capture_id']);
                ?>
                <li>
                    [<?php echo htmlspecialchars($timestamp); ?>] (<?php echo htmlspecialchars($date); ?>)
                    → <?php echo htmlspecialchars($domain); ?>
                    → <a href="<?php echo $view_link; ?>">Преглед</a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <p><a href="../index.php">⬅️ Обратно към началната страница</a></p>

</body>
</html>
