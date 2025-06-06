<?php
session_start();
require 'db_config.php';

$message = '';

if (isset($_POST['email']) && isset($_POST['password'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        header("Location: ../index.php");
        exit;
    } else {
        $message = "Грешен email или парола!";
    }
}
?>

<!DOCTYPE html>
<html lang="bg">

<head>
    <meta charset="UTF-8">
    <title>Вход</title>
</head>

<body>

    <h1>Вход</h1>

    <form method="post" action="login.php" onsubmit="return validateLogin();">
        <label for="email">Email:</label>
        <input name="email" id="email"><br>

        <label for="password">Парола:</label>
        <input type="password" name="password" id="password"><br>

        <button type="submit">Влез</button>
    </form>

    <p id="login-error"></p>


    <p><strong><?php echo htmlspecialchars($message); ?></strong></p>

    <p><a href="../index.php">⬅️ Начална страница</a></p>

    <script src="../js/login.js"></script>


</body>

</html>