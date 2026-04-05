<?php
session_name("heidi_battleship");
session_start();
require_once "connect_db.php";

header("Content-Type: application/json");

if (!isset($_SESSION["user_id"])) {
    echo json_encode(["success" => false, "message" => "Not logged in."]);
    exit;
}

$user_id = $_SESSION["user_id"];
$action  = $_POST["action"]  ?? "place";
$game_id = $_POST["game_id"] ?? "";

if (!ctype_digit((string)$game_id)) {
    echo json_encode(["success" => false, "message" => "Invalid game."]);
    exit;
}
$game_id = (int)$game_id;

// Verify user is in this game and it's in placing stage
$sql  = "SELECT player1_id, player2_id, status FROM GAMES WHERE game_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $game_id);
$stmt->execute();
$stmt->bind_result($p1_id, $p2_id, $status);
if (!$stmt->fetch()) {
    echo json_encode(["success" => false, "message" => "Game not found."]);
    exit;
}
$stmt->close();

if ((int)$user_id !== (int)$p1_id && (int)$user_id !== (int)$p2_id) {
    echo json_encode(["success" => false, "message" => "Not your game."]);
    exit;
}
if ((int)$status !== 1) {
    echo json_encode(["success" => false, "message" => "Not in placing stage."]);
    exit;
}

// ── Helper: return all occupied cells for this player ──
function getOccupied(mysqli $conn, int $game_id, int $user_id): array {
    $sql  = "SELECT sc.row_num, sc.col_char
             FROM SHIP_CELLS sc
             JOIN SHIPS s ON sc.ship_id = s.ship_id
             WHERE s.game_id = ? AND s.player_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $game_id, $user_id);
    $stmt->execute();
    $stmt->bind_result($r, $c);
    $cells = [];
    while ($stmt->fetch()) {
        $cells[] = "$r-$c";
    }
    $stmt->close();
    return $cells;
}

function countPlaced(mysqli $conn, int $game_id, int $user_id): int {
    $sql  = "SELECT COUNT(DISTINCT sc.ship_id)
             FROM SHIP_CELLS sc
             JOIN SHIPS s ON sc.ship_id = s.ship_id
             WHERE s.game_id = ? AND s.player_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $game_id, $user_id);
    $stmt->execute();
    $stmt->bind_result($cnt);
    $stmt->fetch();
    $stmt->close();
    return (int)$cnt;
}

// ── Action: ready ──
if ($action === "ready") {
    $placed = countPlaced($conn, $game_id, $user_id);
    if ($placed < 5) {
        echo json_encode(["success" => false, "message" => "Place all 5 ships first."]);
        exit;
    }
    $opp_id  = ((int)$user_id === (int)$p1_id) ? $p2_id : $p1_id;
    $opp_placed = countPlaced($conn, $game_id, $opp_id);

    if ($opp_placed >= 5) {
        $sql  = "UPDATE GAMES SET status = 2, cur_turn = ? WHERE game_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $p1_id, $game_id);
        $stmt->execute();
        $stmt->close();
        echo json_encode(["success" => true, "started" => true, "message" => "Game started!"]);
    } else {
        echo json_encode(["success" => true, "started" => false, "message" => "Waiting for opponent to place their ships."]);
    }
    exit;
}

// ── Action: place ──
$ship_id     = $_POST["ship_id"]     ?? "";
$orientation = $_POST["orientation"] ?? "H";
$start       = $_POST["start"]       ?? "";

if (!ctype_digit((string)$ship_id) || !preg_match('/^\d+-[A-J]$/', $start)) {
    echo json_encode(["success" => false, "message" => "Invalid input."]);
    exit;
}
$ship_id = (int)$ship_id;
[$sr, $sc_char] = explode("-", $start);
$row      = (int)$sr;
$col_char = strtoupper($sc_char);

// Verify ship belongs to this player
$sql  = "SELECT length FROM SHIPS WHERE ship_id = ? AND game_id = ? AND player_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $ship_id, $game_id, $user_id);
$stmt->execute();
$stmt->bind_result($length);
if (!$stmt->fetch()) {
    echo json_encode(["success" => false, "message" => "Ship not found."]);
    exit;
}
$stmt->close();

// Compute cells
$cells = [];
for ($i = 0; $i < $length; $i++) {
    if ($orientation === "H") {
        $c = chr(ord($col_char) + $i);
        if ($c > 'J') { $cells = []; break; }
        $cells[] = [$row, $c];
    } else {
        $r = $row + $i;
        if ($r > 10) { $cells = []; break; }
        $cells[] = [$r, $col_char];
    }
}
if (empty($cells)) {
    echo json_encode(["success" => false, "message" => "Ship goes off the board."]);
    exit;
}

// Check overlap
$sql   = "SELECT COUNT(*) FROM SHIP_CELLS sc
          JOIN SHIPS s ON sc.ship_id = s.ship_id
          WHERE s.game_id = ? AND s.player_id = ? AND sc.row_num = ? AND sc.col_char = ? AND sc.ship_id <> ?";
$check = $conn->prepare($sql);
foreach ($cells as [$r, $c]) {
    $check->bind_param("iiisi", $game_id, $user_id, $r, $c, $ship_id);
    $check->execute();
    $check->bind_result($cnt);
    $check->fetch();
    if ($cnt > 0) {
        $check->close();
        echo json_encode(["success" => false, "message" => "Ships cannot overlap."]);
        exit;
    }
}
$check->close();

// Clear old cells and insert new
$sql  = "DELETE FROM SHIP_CELLS WHERE ship_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $ship_id);
$stmt->execute();
$stmt->close();

$sql  = "INSERT INTO SHIP_CELLS (ship_id, row_num, col_char) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
foreach ($cells as [$r, $c]) {
    $stmt->bind_param("iis", $ship_id, $r, $c);
    $stmt->execute();
}
$stmt->close();

$occupied     = getOccupied($conn, $game_id, $user_id);
$ships_placed = countPlaced($conn, $game_id, $user_id);

echo json_encode([
    "success"      => true,
    "message"      => "Ship placed.",
    "occupied"     => $occupied,
    "ships_placed" => $ships_placed,
]);
