<?php
session_start();
require_once "db_config.php";

$user_id = $_SESSION['user_id'] ?? 1;

// Get selected month/year
$year = isset($_GET['year']) ? intval($_GET['year']) : date("Y");
$month = isset($_GET['month']) ? intval($_GET['month']) : date("n");

// First day of the month
$first_day = mktime(0, 0, 0, $month, 1, $year);
$days_in_month = date("t", $first_day);
$month_name = date("F", $first_day);
$start_day_of_week = date("w", $first_day); // 0 = Sunday

// Get all captures for this month
$stmt = $pdo->prepare("
    SELECT id, DATE(captured_at) AS capture_date, TIME(captured_at) AS capture_time
    FROM captures
    WHERE user_id = ?
      AND YEAR(captured_at) = ?
      AND MONTH(captured_at) = ?
    ORDER BY captured_at ASC
");
$stmt->execute([$user_id, $year, $month]);
$results = $stmt->fetchAll();

// Group by date
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
    <title>Календар на архивите: <?php echo "$month_name $year"; ?></title>
    <style>
        table.calendar {
            border-collapse: collapse;
            width: 100%;
        }
        table.calendar th, table.calendar td {
            border: 1px solid #ccc;
            width: 14.28%;
            height: 80px;
            vertical-align: top;
            padding: 5px;
        }
        .active-day {
            background-color: #e0f0ff;
        }
        .today {
            border: 2px solid #000;
        }
    </style>
</head>
<body>

<h1>📅 Архиви за <?php echo "$month_name $year"; ?></h1>

<!-- Month switcher -->
<p>
    <a href="?month=<?php echo ($month == 1 ? 12 : $month - 1); ?>&year=<?php echo ($month == 1 ? $year - 1 : $year); ?>">⬅️ Предишен месец</a> |
    <a href="?month=<?php echo ($month == 12 ? 1 : $month + 1); ?>&year=<?php echo ($month == 12 ? $year + 1 : $year); ?>">Следващ месец ➡️</a>
</p>

<table class="calendar">
    <tr>
        <th>Нед</th><th>Пон</th><th>Вт</th><th>Ср</th><th>Чет</th><th>Пет</th><th>Съб</th>
    </tr>

    <tr>
        <?php
        $day = 1;
        $cell = 0;

        // Fill empty cells before first day
        for ($i = 0; $i < $start_day_of_week; $i++) {
            echo "<td></td>";
            $cell++;
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
                echo "<a href='?month=$month&year=$year&day=$day'>📌 ".count($calendar_data[$date_str])." архиви</a>";
            }
            echo "</td>";

            $day++;
            $cell++;

            if ($cell % 7 == 0 && $day <= $days_in_month) {
                echo "</tr><tr>";
            }
        }

        // Fill remaining cells
        while ($cell % 7 != 0) {
            echo "<td></td>";
            $cell++;
        }
        ?>
    </tr>
</table>

<!-- Display captures for selected day -->
<?php if (isset($_GET['day'])):
    $selected_day = intval($_GET['day']);
    $selected_date = sprintf("%04d-%02d-%02d", $year, $month, $selected_day);
    if (isset($calendar_data[$selected_date])):
?>

    <h2>📍 Архиви за <?php echo date("F j, Y", strtotime($selected_date)); ?></h2>
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
