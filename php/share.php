<?php
session_start();
header('Content-Type: application/json');
require_once "db_config.php";

$user_id = $_SESSION['user_id'] ?? 1;
if ($user_id == 1) {
    echo json_encode(['error' => 'Само влезнали потребители могат да споделят.']);
    exit;
}

$capture_id = $_POST['capture_id'] ?? null;
$target_email = $_POST['email'] ?? null;
$make_public = isset($_POST['make_public']);

if (!$capture_id) {
    echo json_encode(['error' => 'Липсва ID на архива.']);
    exit;
}

$stmt = $pdo->prepare("SELECT user_id FROM captures WHERE id = ?");
$stmt->execute([$capture_id]);
$row = $stmt->fetch();

if (!$row || $row['user_id'] != $user_id) {
    echo json_encode(['error' => 'Нямате права за този архив.']);
    exit;
}

$response = [];

if ($make_public) {
    $update = $pdo->prepare("UPDATE captures SET is_public = TRUE WHERE id = ?");
    $update->execute([$capture_id]);
    $response['public'] = true;
}

if ($target_email) {
    $user_stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $user_stmt->execute([$target_email]);
    $recipient = $user_stmt->fetch();

    if (!$recipient) {
        echo json_encode(['error' => 'Потребителят не е намерен.']);
        exit;
    }

    $check = $pdo->prepare("SELECT id FROM shared_captures WHERE capture_id = ? AND shared_with = ?");
    $check->execute([$capture_id, $recipient['id']]);

    if (!$check->fetch()) {
        $insert = $pdo->prepare("INSERT INTO shared_captures (capture_id, shared_by, shared_with) VALUES (?, ?, ?)");
        $insert->execute([$capture_id, $user_id, $recipient['id']]);
        $response['shared'] = $target_email;
    } else {
        $response['message'] = 'Вече е споделено с този потребител.';
    }
}

echo json_encode($response);