<?php
session_name("heidi_battleship");
session_start();
$username = $_SESSION["username"] ?? "Player";
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logged Out – Battleship</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="site-header">
    <a href="index.php" class="logo">⚓ Battleship</a>
</header>

<div class="card" style="text-align:center;">
    <h2>See you later, <?php echo htmlspecialchars($username); ?>!</h2>
    <p style="color:#a0b8d0; margin: 16px 0 24px;">You have been logged out.</p>
    <a href="login.php" class="btn btn-primary">Login Again</a>
    <p class="form-footer" style="margin-top:16px;">New here? <a href="register.php">Create an account</a></p>
</div>

</body>
</html>
