<?php session_start(); ?>

<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="bg">

<head>
    <meta charset="UTF-8">
    <title>Моят профил</title>
    <link rel="stylesheet" href="../styles/profile_style.css">
</head>

<body>

    <div class="container">
        <h1>👤 Моят профил</h1>

        <p class="greeting">Здравей, <strong><?php echo htmlspecialchars($_SESSION['email']); ?></strong></p>

        <div class="button-group">
            <a href="history.php" class="button">📜 История на архивите</a>
            <a href="archive.php" class="button">⬅️ Назад към Архивиране</a>
            <a href="logout.php" class="button logout">🚪 Изход</a>
        </div>
    </div>

</body>

</html>
