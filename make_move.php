<?php
session_name("heidi_battleship");
session_start();
require_once "connect_db.php";

if (!isset($_SESSION["user_id"])) {
    echo "<p>You must be logged in.</p>";
    exit;
}

$user_id = $_SESSION["user_id"];

$game_id = $_POST["game_id"] ?? "";
$row     = $_POST["row"] ?? "";
$col     = $_POST["col"] ?? "";

if (!ctype_digit($game_id)) {
    echo "<p>Invalid game.</p>";
    exit;
}
$game_id = (int)$game_id;

if (!ctype_digit($row) || $row < 1 || $row > 10) {
    echo "<p>Invalid row.</p>";
    exit;
}

if (!ctype_alpha($col) || strlen($col) != 1) {
    echo "<p>Invalid column.</p>";
    exit;
}

$col = strtoupper($col);

/* ----------------------------------------------------
   Determine HIT or MISS
   A hit means: opponent has a ship at (row, col)
   ---------------------------------------------------- */
$sql = "SELECT 1 FROM SHIP_CELLS
        WHERE game_id = ? AND player_id <> ? AND row_num = ? AND col_char = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iiis", $game_id, $user_id, $row, $col);
$stmt->execute();
$stmt->store_result();

$is_hit = ($stmt->num_rows > 0) ? 1 : 0;
$stmt->close();

/* ----------------------------------------------------
   Insert move (if not already fired at the same spot)
   ---------------------------------------------------- */
$sql = "INSERT IGNORE INTO MOVES (game_id, player_id, row_num, col_char, result)
        VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiisi", $game_id, $user_id, $row, $col, $is_hit);
$stmt->execute();
$stmt->close();

/* ----------------------------------------------------
   Redirect back to game board
   ---------------------------------------------------- */
header("Location: game.php?game_id=" . $game_id);
exit;
?>
