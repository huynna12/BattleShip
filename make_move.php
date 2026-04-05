<?php
session_name("heidi_battleship");
session_start();
require_once "connect_db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$game_id = $_POST["game_id"] ?? "";
$row     = $_POST["row"]     ?? "";
$col     = $_POST["col"]     ?? "";

if (!ctype_digit($game_id)) {
    echo "<p>Invalid game.</p>";
    exit;
}
$game_id = (int)$game_id;

if (!ctype_digit($row) || $row < 1 || $row > 10) {
    echo "<p>Invalid row.</p>";
    exit;
}

if (!ctype_alpha($col) || strlen($col) !== 1) {
    echo "<p>Invalid column.</p>";
    exit;
}
$col = strtoupper($col);

/* --- Verify it's this player's turn --- */
$sql  = "SELECT player1_id, player2_id, cur_turn, status FROM GAMES WHERE game_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $game_id);
$stmt->execute();
$stmt->bind_result($p1_id, $p2_id, $cur_turn, $status);
$stmt->fetch();
$stmt->close();

if ((int)$status !== 2) {
    header("Location: game.php?game_id=$game_id");
    exit;
}

if ((int)$cur_turn !== (int)$user_id) {
    header("Location: game.php?game_id=$game_id");
    exit;
}

$opp_id = ((int)$user_id === (int)$p1_id) ? $p2_id : $p1_id;

/* --- Determine hit or miss --- */
$sql  = "SELECT 1 FROM SHIP_CELLS sc
         JOIN SHIPS s ON sc.ship_id = s.ship_id
         WHERE s.game_id = ? AND s.player_id = ? AND sc.row_num = ? AND sc.col_char = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiis", $game_id, $opp_id, $row, $col);
$stmt->execute();
$stmt->store_result();
$is_hit = ($stmt->num_rows > 0) ? 1 : 0;
$stmt->close();

/* --- Insert move --- */
$sql  = "INSERT IGNORE INTO MOVES (game_id, player_id, row_num, col_char, result)
         VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiisi", $game_id, $user_id, $row, $col, $is_hit);
$stmt->execute();
$stmt->close();

/* --- Check if all opponent ships are sunk --- */
$sql  = "SELECT
            (SELECT COUNT(*) FROM SHIP_CELLS sc
             JOIN SHIPS s ON sc.ship_id = s.ship_id
             WHERE s.game_id = ? AND s.player_id = ?) AS total,
            (SELECT COUNT(*) FROM MOVES m
             JOIN SHIP_CELLS sc ON m.row_num = sc.row_num AND m.col_char = sc.col_char
             JOIN SHIPS s ON sc.ship_id = s.ship_id
             WHERE m.game_id = ? AND m.player_id = ? AND s.player_id = ? AND s.game_id = ?) AS hits";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiiiii", $game_id, $opp_id, $game_id, $user_id, $opp_id, $game_id);
$stmt->execute();
$stmt->bind_result($total, $hits);
$stmt->fetch();
$stmt->close();

if ($total > 0 && $hits >= $total) {
    // All opponent ships sunk — current player wins
    $sql  = "UPDATE GAMES SET status = 3, winner_id = ?, cur_turn = NULL WHERE game_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $game_id);
    $stmt->execute();
    $stmt->close();
} elseif ($is_hit === 0) {
    // Miss — switch turn to opponent
    $sql  = "UPDATE GAMES SET cur_turn = ? WHERE game_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $opp_id, $game_id);
    $stmt->execute();
    $stmt->close();
}
// Hit but no win — stay on same player's turn

header("Location: game.php?game_id=$game_id");
exit;
