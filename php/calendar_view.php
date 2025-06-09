<?php
session_start();
require_once "db_config.php";

$user_id = $_SESSION['user_id'] ?? 1;
$year = isset($_GET['year']) ? intval($_GET['year']) : date("Y");

// Подготвяме заявка за всички архиви през годината
$stmt = $pdo->prepare("
    SELECT id, DATE(captured_at) AS capture_date, TIME(captured_at) AS capture_time
    FROM captures
    WHERE user_id = ? AND YEAR(captured_at) = ?
    ORDER BY captured_at ASC
");
$stmt->execute([$user_id, $year]);
$results = $stmt->fetchAll();

// Групиране по дата
$calendar_data = [];
foreach ($results as $row) {
    $calendar_data[$row['capture_date']][] = [
        'id' => $row['id'],
        'time' => $row['capture_time']
    ];
}
?>

<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <title>Календар на архивите: <?php echo $year; ?></title>
    <link rel="stylesheet" href="../styles/calendar_style.css">
</head>
<body>

<h1>📅 Архиви за <?php echo $year; ?></h1>

<!-- Превключване на година -->
<p class="year-switch">
    <a href="?year=<?php echo $year - 1; ?>">⬅️ <?php echo $year - 1; ?></a>
    |
    <a href="?year=<?php echo $year + 1; ?>"><?php echo $year + 1; ?> ➡️</a>
</p>

<div class="year-container">
<?php
for ($month = 1; $month <= 12; $month++) {
    $first_day = mktime(0, 0, 0, $month, 1, $year);
    $days_in_month = date("t", $first_day);
    $month_name = strftime('%B', $first_day);
    $start_day_of_week = date("w", $first_day);

    echo "<div class='month'>";
    echo "<h2>$month_name</h2>";
    echo "<table class='calendar'>";
    echo "<tr><th>Нед</th><th>Пон</th><th>Вт</th><th>Ср</th><th>Чет</th><th>Пет</th><th>Съб</th></tr><tr>";

    $day = 1;
    $cell = 0;

    for ($i = 0; $i < $start_day_of_week; $i++, $cell++) {
        echo "<td></td>";
    }

    while ($day <= $days_in_month) {
        $date_str = sprintf("%04d-%02d-%02d", $year, $month, $day);
        $is_today = ($date_str == date("Y-m-d"));
        $has_captures = isset($calendar_data[$date_str]);

        $class = "";
        if ($is_today) $class .= " today";
        if ($has_captures) $class .= " active-day";

        echo "<td class='$class'>";
        echo "<strong>$day</strong><br>";
        if ($has_captures) {
            echo "<a href='?year=$year&day=$day&month=$month'>📌 ".count($calendar_data[$date_str])."</a>";
        }
        echo "</td>";

        $day++;
        $cell++;

        if ($cell % 7 == 0 && $day <= $days_in_month) echo "</tr><tr>";
    }

    while ($cell % 7 != 0) {
        echo "<td></td>";
        $cell++;
    }

    echo "</tr></table></div>";
}
?>
</div>

<?php if (isset($_GET['day'], $_GET['month'])):
    $d = intval($_GET['day']);
    $m = intval($_GET['month']);
    $selected_date = sprintf("%04d-%02d-%02d", $year, $m, $d);
    if (isset($calendar_data[$selected_date])):
?>

    <h2>📍 Архиви за <?php echo strftime('%e %B %Y', strtotime($selected_date)); ?></h2>
    <ul>
        <?php foreach ($calendar_data[$selected_date] as $entry): ?>
            <li>🕒 <a href="view.php?capture_id=<?php echo $entry['id']; ?>">
                <?php echo htmlspecialchars($entry['time']); ?>
            </a></li>
        <?php endforeach; ?>
    </ul>

<?php else: ?>
    <p>❌ Няма архиви за избраната дата.</p>
<?php endif; endif; ?>

<p><a href="index.php">⬅️ Обратно</a></p>

</body>
</html>
