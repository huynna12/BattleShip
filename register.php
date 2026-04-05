<?php
require_once "connect_db.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register – Battleship</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="site-header">
    <a href="index.php" class="logo">⚓ Battleship</a>
</header>

<div class="card">
    <h2>Create Account</h2>

    <form action="register.php" method="post" autocomplete="off">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary btn-full">Sign Up</button>
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $username = trim($_POST["username"] ?? "");
        $password = $_POST["password"] ?? "";

        if ($username === "" || $password === "") {
            echo "<div class='alert alert-error'>Please provide both a username and password.</div>";
        } else {
            $check = $conn->prepare("SELECT * FROM USERS WHERE username = ?");
            $check->bind_param("s", $username);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                echo "<div class='alert alert-error'>Username already exists — please choose another.</div>";
                $check->close();
            } else {
                $check->close();
                $sql  = "INSERT INTO USERS (username, password) VALUES (?, SHA2(?, 256))";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ss", $username, $password);
                $stmt->execute();

                if ($stmt->affected_rows === 1) {
                    echo "<div class='alert alert-success'>Account created! <a href='login.php'>Login here</a></div>";
                } else {
                    echo "<div class='alert alert-error'>Registration failed. Please try again.</div>";
                }
                $stmt->close();
            }
        }
        $conn->close();
    }
    ?>

    <p class="form-footer" style="margin-top:20px;">Already have an account? <a href="login.php">Login</a></p>
</div>

</body>
</html>
