<?php
session_start();
require 'db_config.php';

$message = '';

if (isset($_POST['email']) && isset($_POST['password'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (email, password_hash) VALUES (?, ?)");
        $stmt->execute([$email, $password_hash]);
        $message = "Успешна регистрация! Вече можете да влезете.";
    } catch (PDOException $e) {
        $message = "Грешка при регистрация: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="bg">

<head>
    <meta charset="UTF-8">
    <title>Регистрация</title>
</head>

<body>

    <h1>Регистрация</h1>

    <form method="post" action="register.php" onsubmit="return validateRegister();">
        <label for="email">Email:</label>
        <input name="email" id="email" ><br>

        <label for="password">Парола:</label>
        <input type="password" name="password" id="password" ><br>

        <button type="submit">Регистрирай ме</button>
    </form>

    <p id="register-error"></p>

    <p><strong><?php echo htmlspecialchars($message); ?></strong></p>

    <p><a href="../index.php">⬅️ Начална страница</a></p>

    <script src="../js/register.js"></script>


</body>

</html>