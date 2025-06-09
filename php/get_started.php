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
</head>

<body>

    <a href="login.php">
        <button>Вход</button>
    </a>

    <a href="register.php">
        <button>Регистрация</button>
    </a>

    <p><a href="../index.php">⬅️ Обратно към началната страница</a></p>

</body>

</html>
