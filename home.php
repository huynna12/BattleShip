<?php
session_name("heidi_battleship");
session_start();
require_once "connect_db.php";

if (!isset($_SESSION["user_id"], $_SESSION["username"])) {
    echo "<p>You must be logged in to access this page.</p>";
    echo "<p><a href='login.php'>Login</a></p>";
    exit;
}

$username = $_SESSION["username"];
$user_id = $_SESSION["user_id"];

$view = $_GET["view"] ?? "sent";
?>
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Home</title></head>
<body>

<h1>Hello, <?php echo $username; ?>!</h1>

<div style="display:flex; gap:20px;">
    <a href="home.php?view=sent">Invite Sent</a>
    <a href="home.php?view=received">Invite Received</a>
    <a href="home.php?view=current">Current Games</a>
    <a href="home.php?view=history">History</a>
</div>

<hr>

<?php
$allowed = ["sent", "received", "current", "history"];

if (in_array($view, $allowed)) {
    include "views/" . $view . ".php";
} else {
    echo "<p>Invalid view</p>";
}
?>

<p><a href="logout.php">Logout</a></p>

</body>
</html>
