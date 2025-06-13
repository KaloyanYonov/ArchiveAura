<?php
session_start();
require_once "db_config.php";

$user_id = $_SESSION['user_id'] ?? 1;
$year = isset($_GET['year']) ? intval($_GET['year']) : date("Y");
$month = isset($_GET['month']) ? intval($_GET['month']) : date("n");


$stmt = $pdo->prepare("
    SELECT captures.id, DATE(captures.captured_at) AS capture_date, 
           TIME(captures.captured_at) AS capture_time, pages.url
    FROM captures
    JOIN pages ON captures.page_id = pages.id
    WHERE captures.user_id = ? AND YEAR(captures.captured_at) = ? AND MONTH(captures.captured_at) = ?
    ORDER BY captures.captured_at ASC
");

$stmt->execute([$user_id, $year, $month]);
$results = $stmt->fetchAll();

$calendar_data = [];
foreach ($results as $row) {
    $calendar_data[$row['capture_date']][] = [
        'id' => $row['id'],
        'time' => $row['capture_time'],
        'url' => $row['url']
    ];

}

$first_day = mktime(0, 0, 0, $month, 1, $year);
$days_in_month = date("t", $first_day);
$start_day_of_week = date("N", $first_day); // 1 (Mon) to 7 (Sun)
$month_name = strftime('%B', $first_day);
$today = date("Y-m-d");
?>

<div class="calendar-header">
    <button onclick="loadCalendar(<?php echo $year - 1; ?>, <?php echo $month; ?>)">« <?php echo $year - 1; ?></button>
    <button onclick="loadCalendar(<?php echo $year; ?>, <?php echo $month - 1; ?>)">⬅️</button>
    <strong><?php echo $month_name . ' ' . $year; ?></strong>
    <button onclick="loadCalendar(<?php echo $year; ?>, <?php echo $month + 1; ?>)">➡️</button>
    <button onclick="loadCalendar(<?php echo $year + 1; ?>, <?php echo $month; ?>)"><?php echo $year + 1; ?> »</button>
</div>

<table class="calendar">
    <tr>
        <th>Пон</th>
        <th>Вт</th>
        <th>Ср</th>
        <th>Чет</th>
        <th>Пет</th>
        <th>Съб</th>
        <th>Нед</th>
    </tr>
    <tr>
        <?php
        $day = 1;
        $cell = 0;
        for ($i = 1; $i < $start_day_of_week; $i++, $cell++)
            echo "<td></td>";

        while ($day <= $days_in_month) {
            $date_str = sprintf("%04d-%02d-%02d", $year, $month, $day);
            $is_today = $date_str == $today;
            $has_captures = isset($calendar_data[$date_str]);

            $class = $is_today ? "today" : "";
            $class .= $has_captures ? " active-day" : "";

            echo "<td class='$class'><strong>$day</strong>";
            if ($has_captures) {
                echo "<ul>";
                foreach ($calendar_data[$date_str] as $entry) {
                    $encoded_url = urlencode($entry['url']);
                    echo "<li><a href='view.php?capture_id={$entry['id']}/{$encoded_url}' target='_blank'>🕒 {$entry['time']}</a></li>";
                }
                echo "</ul>";
            }


            $day++;
            $cell++;
            if ($cell % 7 == 0 && $day <= $days_in_month)
                echo "</tr><tr>";
        }
        while ($cell % 7 != 0) {
            echo "<td></td>";
            $cell++;
        }
        ?>
    </tr>
</table>