<?php
session_name("heidi_battleship");
session_start();
require_once "connect_db.php";

if (!isset($_SESSION["user_id"], $_SESSION["username"])) {
    echo "<p>You must be logged in. <a href='login.php'>Login</a></p>";
    exit;
}

$username = $_SESSION["username"];
$user_id  = $_SESSION["user_id"];
$view     = $_GET["view"] ?? "sent";
$allowed  = ["sent", "received", "current", "history"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home – Battleship</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="site-header">
    <a href="home.php" class="logo">⚓ Battleship</a>
    <div class="header-right">
        <span>Hello, <?php echo htmlspecialchars($username); ?></span>
        <a href="logout.php">Logout</a>
    </div>
</header>

<div class="page-wrapper">
    <?php
    function navLink(string $label, string $key, string $current): void {
        $active = ($current === $key) ? ' class="active"' : '';
        echo "<a href='home.php?view={$key}'{$active}>{$label}</a>";
    }
    ?>
    <nav class="nav-tabs">
        <?php
        navLink('Invites Sent',     'sent',     $view);
        navLink('Invites Received', 'received', $view);
        navLink('Current Games',    'current',  $view);
        navLink('History',          'history',  $view);
        ?>
    </nav>

    <?php
    if (in_array($view, $allowed)) {
        include_once "views/" . $view . ".php";
    } else {
        echo "<p>Invalid view.</p>";
    }
    ?>
</div>

</body>
</html>
