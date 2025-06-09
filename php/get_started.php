<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: archive.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <title>Изберете действие</title>
    <link rel="stylesheet" href="../styles/choose_style.css">
    <link rel="stylesheet" href="../styles/global.css">
</head>
<body>

<div class="container">
    <h1>🔐 Добре дошли!</h1>

    <div class="button-group">
        <a href="login.php" class="btn">Вход</a>
        <a href="register.php" class="btn">Регистрация</a>
        <a href="../index.php" class="link">⬅️ Обратно към началната страница</a>
    </div>
</div>

</body>
</html>
