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
        $message = "✅ Успешна регистрация! Вече можете да влезете.";
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // SQLSTATE code for integrity constraint violation
            $message = "⚠️ Този имейл вече е регистриран.";
        } else {
            $message = "❌ Възникна грешка при регистрацията. Моля, опитайте отново.";
        }
    }

}
?>

<!DOCTYPE html>
<html lang="bg">

<head>
    <meta charset="UTF-8">
    <title>Регистрация</title>
    <link rel="stylesheet" href="../styles/form_styles.css">
    <link rel="stylesheet" href="../styles/global.css">

</head>

<body>

    <div class="form-container">
        <h1>📝 Регистрация</h1>

        <form method="post" action="register.php" onsubmit="return validateRegister();">
            <label for="email">Email:</label>
            <input name="email" id="email" type="email" required>

            <label for="password">Парола:</label>
            <input type="password" name="password" id="password" required>

            <button type="submit">Регистрирай ме</button>
        </form>

        <p id="register-error" class="error"></p>
        <p class="feedback"><?php echo htmlspecialchars($message); ?></p>
        <p><a href="archive.php" class="btn">⬅️ Обратно</a></p>
    </div>

    <script src="../js/register.js"></script>

</body>

</html>