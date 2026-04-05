<?php
session_name("heidi_battleship");
session_start();
require_once "connect_db.php";
?>
 
<!DOCTYPE html>
<html lang="en" xml:lang="en">
<head>
    <meta charset="utf-8">
    <title>User Login</title>
</head>
<body>

<h2>Login</h2>

<form action="login.php" method="post">
    <label for="username">Username:</label>
    <input type="text" id="username" name="username" required>
    <br><br>

    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required>
    <br><br>

    <input type="submit" value="Login">
</form>

<p>Don't have an account? <a href="register.php">Register</a></p>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    // check if the user exists
    $sql = "SELECT username, user_id FROM USERS
            WHERE username = ? AND password = SHA2(?, 256)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $stmt->bind_result($uname, $uid);

    // if there is a row
    if ($stmt->fetch()) {
        // save username in session
        $_SESSION["username"] = $uname;
        $_SESSION["user_id"] = $uid;
        
        echo "<p style='color:green;'>Login successful. <a href='home.php'>Continue</a></p>";
    } else {
        echo "<p style='color:red;'>Invalid username or password, please try again.</p>";
    }

    $stmt->close();
}
?>

</body>
</html>
