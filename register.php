<?php
require_once "connect_db.php";
?>
 
<!DOCTYPE html>
<html lang="en" xml:lang="en">
<head>
    <meta charset="utf-8">
    <title>User Registration</title>
</head>
<body>

<h2>Create an Account</h2>

<form action="register.php" method="post" autocomplete="off">
    <label for="username">Username:</label>
    <input type="text" id="username" name="username" required>
    <br><br>

    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required>
    <br><br>

    <input type="submit" value="Register">
</form>

<p>Already have an account? <a href="login.php">Login</a></p>

<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($username === "" || $password === "") {
        echo "<p style='color:red;'>Please provide both a username and password.</p>";
    } else {
        // check if username already exists 
        $check = $conn->prepare("SELECT * FROM USERS WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            echo "<p style='color:red;'>Username already exists — please choose another.</p>";
            $check->close();
        } else {
            $check->close();

            // insert the user
            $sql = "INSERT INTO USERS (username, password) VALUES (?, SHA2(?, 256))";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $username, $password);

            $stmt->execute();
            
            // check result without executing again
            if ($stmt->affected_rows === 1) {
                echo "<p style='color:green;'>Registration successful. <a href='login.php'>Login here</a></p>";
            } else {
                echo "<p style='color:red;'>Registration failed. Please try again.</p>";
            }
            $stmt->close();
        }
    }

    $conn->close();
}
?>

</body>
</html>
