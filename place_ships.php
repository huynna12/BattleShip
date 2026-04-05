<?php
session_name("heidi_battleship");
session_start();
require_once "connect_db.php";

if (!isset($_SESSION["user_id"])) {
    echo "<p>You must be logged in.</p>";
    echo "<p><a href='login.php'>Login</a></p>";
    exit;
}

$user_id = $_SESSION["user_id"];
$game_id = $_GET["game_id"] ?? ($_POST["game_id"] ?? "");

if (!ctype_digit((string)$game_id)) {
    echo "<p>Invalid game.</p>";
    exit;
}
$game_id = (int)$game_id;

/* --- Make sure user is in this game and status = 1 (placing) --- */
$sql = "SELECT player1_id, player2_id, status
        FROM GAMES
        WHERE game_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $game_id);
$stmt->execute();
$stmt->bind_result($p1_id, $p2_id, $status);
if (!$stmt->fetch()) {
    echo "<p>Game not found.</p>";
    exit;
}
$stmt->close();

if ($user_id !== $p1_id && $user_id !== $p2_id) {
    echo "<p>You are not part of this game.</p>";
    exit;
}
if ($status != 1) {
    echo "<p>This game is not in ship-placement stage.</p>";
    echo "<p><a href='game.php?game_id=$game_id'>Go to game</a></p>";
    exit;
}

/* --- Ensure ships exist for this player (5,4,3,3,2) --- */
$sql = "SELECT COUNT(*) FROM SHIPS WHERE game_id = ? AND player_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $game_id, $user_id);
$stmt->execute();
$stmt->bind_result($ship_count);
$stmt->fetch();
$stmt->close();

if ($ship_count == 0) {
    $lengths = [5,4,3,3,2];
    $sql = "INSERT INTO SHIPS (game_id, player_id, ship_num, length)
            VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    foreach ($lengths as $idx => $len) {
        $ship_num = $idx + 1;
        $stmt->bind_param("iiii", $game_id, $user_id, $ship_num, $len);
        $stmt->execute();
    }
    $stmt->close();
}

/* --- Handle placing a ship (POST) --- */
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["start"])) {
    $ship_id = $_POST["ship_id"] ?? "";
    $orientation = $_POST["orientation"] ?? "H";
    $start = $_POST["start"];      // "row-colLetter", e.g. "3-C"
    $game_id_post = $_POST["game_id"] ?? "";
    
    if (!ctype_digit((string)$ship_id) || !ctype_digit((string)$game_id_post)) {
        $message = "Invalid input.";
    } else {
        $ship_id = (int)$ship_id;
        $game_id = (int)$game_id_post;

        // Parse start cell
        list($sr, $sc) = explode("-", $start);
        $row = (int)$sr;
        $col_char = strtoupper($sc);

        if ($row < 1 || $row > 10 || $col_char < 'A' || $col_char > 'J') {
            $message = "Invalid starting cell.";
        } else {
            // Get ship length and check ownership
            $sql = "SELECT length
                    FROM SHIPS
                    WHERE ship_id = ? AND game_id = ? AND player_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iii", $ship_id, $game_id, $user_id);
            $stmt->execute();
            $stmt->bind_result($length);
            if (!$stmt->fetch()) {
                $message = "Ship not found.";
            }
            $stmt->close();

            if ($message === "") {
                // Compute all cells for this ship
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
                    $message = "Ship goes off the board.";
                } else {
                    // Check overlap with other ships for this player in this game
                    $overlap = false;
                    $sql = "SELECT COUNT(*)
                            FROM SHIP_CELLS sc
                            JOIN SHIPS s ON sc.ship_id = s.ship_id
                            WHERE s.game_id = ?
                              AND s.player_id = ?
                              AND sc.row_num = ?
                              AND sc.col_char = ?
                              AND sc.ship_id <> ?";
                    $check = $conn->prepare($sql);

                    foreach ($cells as [$r, $c]) {
                        $check->bind_param("iiisi", $game_id, $user_id, $r, $c, $ship_id);
                        $check->execute();
                        $check->bind_result($cnt);
                        $check->fetch();
                        if ($cnt > 0) {
                            $overlap = true;
                            break;
                        }
                    }
                    $check->close();

                    if ($overlap) {
                        $message = "Ships cannot overlap.";
                    } else {
                        // Clear old cells for this ship, then insert new ones
                        $sql = "DELETE FROM SHIP_CELLS WHERE ship_id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $ship_id);
                        $stmt->execute();
                        $stmt->close();

                        $sql = "INSERT INTO SHIP_CELLS (ship_id, row_num, col_char)
                                VALUES (?, ?, ?)";
                        $stmt = $conn->prepare($sql);
                        foreach ($cells as [$r, $c]) {
                            $stmt->bind_param("iis", $ship_id, $r, $c);
                            $stmt->execute();
                        }
                        $stmt->close();
                        $message = "Ship placed.";
                    }
                }
            }
        }
    }
}

/* --- Fetch ships and placed cells for display --- */
$ships = [];
$sql = "SELECT ship_id, ship_num, length
        FROM SHIPS
        WHERE game_id = ? AND player_id = ?
        ORDER BY ship_num";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $game_id, $user_id);
$stmt->execute();
$stmt->bind_result($sid, $snum, $slen);
while ($stmt->fetch()) {
    $ships[] = ["ship_id" => $sid, "ship_num" => $snum, "length" => $slen];
}
$stmt->close();

$occupied = [];
$sql = "SELECT sc.row_num, sc.col_char
        FROM SHIP_CELLS sc
        JOIN SHIPS s ON sc.ship_id = s.ship_id
        WHERE s.game_id = ? AND s.player_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $game_id, $user_id);
$stmt->execute();
$stmt->bind_result($r, $c);
while ($stmt->fetch()) {
    $occupied["$r-$c"] = true;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Place Ships</title>
</head>
<body>
<h2>Place Your Ships (Game <?php echo htmlspecialchars($game_id); ?>)</h2>

<?php
if ($message !== "") {
    echo "<p><strong>$message</strong></p>";
}
?>

<form method="post">
    <input type="hidden" name="game_id" value="<?php echo $game_id; ?>">

    <label for="ship_id">Ship:</label>
    <select name="ship_id" id="ship_id">
        <?php
        foreach ($ships as $s) {
            $label = "Ship {$s["ship_num"]} (length {$s["length"]})";
            echo "<option value=\"{$s["ship_id"]}\">$label</option>";
        }
        ?>
    </select>

    <label for="orientation">Orientation:</label>
    <select name="orientation" id="orientation">
        <option value="H">Horizontal</option>
        <option value="V">Vertical</option>
    </select>

    <p>Click a cell on the board to choose the starting position.</p>

    <table border="1" cellspacing="0" cellpadding="4">
        <tr>
            <th></th>
            <?php for ($c = 0; $c < 10; $c++): ?>
                <th><?php echo chr(ord('A') + $c); ?></th>
            <?php endfor; ?>
        </tr>
        <?php for ($row = 1; $row <= 10; $row++): ?>
            <tr>
                <th><?php echo $row; ?></th>
                <?php for ($c = 0; $c < 10; $c++):
                    $colChar = chr(ord('A') + $c);
                    $key = "$row-$colChar";
                    $hasShip = isset($occupied[$key]);
                    ?>
                    <td style="text-align:center; width:30px; height:30px;">
                        <button type="submit" name="start" value="<?php echo $row . '-' . $colChar; ?>"
                                style="width:100%; height:100%;<?php echo $hasShip ? 'background-color:#cccccc;' : ''; ?>">
                            <?php echo $hasShip ? "■" : "&nbsp;"; ?>
                        </button>
                    </td>
                <?php endfor; ?>
            </tr>
        <?php endfor; ?>
    </table>
</form>

<p><a href="home.php?view=current">Back to current games</a></p>
</body>
</html>
