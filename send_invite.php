<?php
session_name("heidi_battleship");
session_start();
require_once "connect_db.php";

// Make sure user is logged in
if (!isset($_SESSION["user_id"], $_SESSION["username"])) {
    echo "<p>You must be logged in to send an invite.</p>";
    echo "<p><a href='login.php'>Login</a></p>";
    exit;
}

$user_id = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get opponent id from form
    $opponent_id = $_POST["opponent_id"] ?? "";

    // Basic validation: must be a number and not you
    if (!ctype_digit($opponent_id)) {
        echo "<p>Invalid opponent.</p>";
        echo "<p><a href='home.php?view=sent'>Back</a></p>";
        exit;
    }

    $opponent_id = (int)$opponent_id;

    if ($opponent_id === (int)$user_id) {
        echo "<p>You cannot invite yourself.</p>";
        echo "<p><a href='home.php?view=sent'>Back</a></p>";
        exit;
    }

    // Optional: ensure opponent exists
    $sql = "SELECT user_id FROM USERS WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $opponent_id);
    $stmt->execute();
    $stmt->bind_result($found_id);
    if (!$stmt->fetch()) {
        $stmt->close();
        echo "<p>Opponent not found.</p>";
        echo "<p><a href='home.php?view=sent'>Back</a></p>";
        exit;
    }
    $stmt->close();

    // Insert a new game as a pending invite
    // status 0 = pending, cur_turn and winner_id are NULL
    $sql = "INSERT INTO GAMES (player1_id, player2_id, status, cur_turn, winner_id)
            VALUES (?, ?, 0, NULL, NULL)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $opponent_id);

    if ($stmt->execute()) {
        $stmt->close();
        // Go back to "Invite Sent" tab
        header("Location: home.php?view=sent");
        exit;
    } else {
        $error = $stmt->error;
        $stmt->close();
        echo "<p>Error sending invite: $error</p>";
        echo "<p><a href='home.php?view=sent'>Back</a></p>";
        exit;
    }

} else {
    echo "<p>Invalid request method.</p>";
    echo "<p><a href='home.php?view=sent'>Back</a></p>";
}
?>
