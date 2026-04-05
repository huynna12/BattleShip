<?php
session_name("heidi_battleship");
session_start();
require_once "connect_db.php";

// Must be logged in
if (!isset($_SESSION["user_id"], $_SESSION["username"])) {
    echo "<p>You must be logged in to accept an invite.</p>";
    echo "<p><a href='login.php'>Login</a></p>";
    exit;
}

$user_id = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $game_id = $_POST["game_id"] ?? "";

    if (!ctype_digit($game_id)) {
        echo "<p>Invalid game.</p>";
        echo "<p><a href='home.php?view=received'>Back</a></p>";
        exit;
    }

    $game_id = (int)$game_id;

    // Fetch the game and verify:
    //  - it exists
    //  - current user is player2
    //  - status is pending (0)
    $sql = "SELECT player1_id, player2_id, status
            FROM GAMES
            WHERE game_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $game_id);
    $stmt->execute();
    $stmt->bind_result($p1_id, $p2_id, $status);

    if (!$stmt->fetch()) {
        $stmt->close();
        echo "<p>Game not found.</p>";
        echo "<p><a href='home.php?view=received'>Back</a></p>";
        exit;
    }
    $stmt->close();

    if ((int)$p2_id !== (int)$user_id) {
        echo "<p>You are not the invited player for this game.</p>";
        echo "<p><a href='home.php?view=received'>Back</a></p>";
        exit;
    }

    if ((int)$status !== 0) {
        echo "<p>This game is no longer pending.</p>";
        echo "<p><a href='home.php?view=received'>Back</a></p>";
        exit;
    }

    // Accept the invite: move to placing ships (status = 1)
    $sql = "UPDATE GAMES SET status = 1 WHERE game_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $game_id);

    if ($stmt->execute()) {
        $stmt->close();
        // Later you can redirect to place ships or directly to game.php
        header("Location: place_ships.php?game_id=$game_id");
        exit;
    } else {
        $error = $stmt->error;
        $stmt->close();
        echo "<p>Error accepting invite: $error</p>";
        echo "<p><a href='home.php?view=received'>Back</a></p>";
        exit;
    }

} else {
    echo "<p>Invalid request method.</p>";
    echo "<p><a href='home.php?view=received'>Back</a></p>";
}
