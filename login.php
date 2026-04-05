<?php
session_name("heidi_battleship");
session_start();
require_once "connect_db.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – Battleship</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="site-header">
    <a href="index.php" class="logo">⚓ Battleship</a>
</header>

<div class="card">
    <h2>Login</h2>

    <form action="login.php" method="post">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary btn-full">Login</button>
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = trim($_POST["username"] ?? "");
        $password = $_POST["password"] ?? "";

        $sql = "SELECT username, user_id FROM USERS
                WHERE username = ? AND password = SHA2(?, 256)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $stmt->bind_result($uname, $uid);

        if ($stmt->fetch()) {
            $_SESSION["username"] = $uname;
            $_SESSION["user_id"]  = $uid;
            echo "<div class='alert alert-success'>Login successful. <a href='home.php'>Continue</a></div>";
        } else {
            echo "<div class='alert alert-error'>Invalid username or password.</div>";
        }
        $stmt->close();
    }
    ?>

    <p class="form-footer" style="margin-top:20px;">Don't have an account? <a href="register.php">Register</a></p>
</div>

</body>
</html>
