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
</head>

<body>

    <h1>Моят профил</h1>

    <p>Здравей, <?php echo htmlspecialchars($_SESSION['email']); ?></p>

    <p>
        <a href="history.php">
            <button>История на архивите</button>
        </a>
    </p>

    <p>
        <a href="logout.php">
            <button>Изход</button>
        </a>
    </p>

    <p><a href="archive.php">Назад към Архивиране</a></p>

</body>

</html>
