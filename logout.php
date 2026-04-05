<?php
session_start();

// if user not logged in, block access
if (!isset($_SESSION["username"])) {
    echo "<p>You must be logged in to access this page.</p>";
    echo "<p><a href='login.php'>Go to login</a></p>";
    exit;
}

$username = $_SESSION["username"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Welcome</title>
</head>
<body>

<h1>Bye <?php echo htmlspecialchars($username); ?>!</h1>
<p>Getting back ?</p>
<a href="login.php">Login</a>
<p>Or if you do not have an account yet</p>
<a href="register.php">Register</a>
</body>
</html>

<?php
session_destroy();
?>