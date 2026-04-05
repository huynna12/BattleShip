<?php
session_name("heidi_battleship");
session_start();
require_once "connect_db.php";

if (!isset($_SESSION["user_id"])) {
    echo "<p>You must be logged in. <a href='login.php'>Login</a></p>";
    exit;
}

$user_id = $_SESSION["user_id"];
$game_id = $_GET["game_id"] ?? "";

if (!ctype_digit($game_id)) {
    echo "<p>Invalid game. <a href='home.php?view=current'>Back</a></p>";
    exit;
}

$game_id = (int)$game_id;

$sql  = "SELECT player1_id, player2_id, status FROM GAMES WHERE game_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $game_id);
$stmt->execute();
$stmt->bind_result($p1, $p2, $status);
if (!$stmt->fetch()) {
    echo "<p>Game not found.</p>";
    exit;
}
$stmt->close();

if ($user_id != $p1 && $user_id != $p2) {
    echo "<p>You are not a player in this game.</p>";
    exit;
}

$sql  = "SELECT row_num, col_char, result FROM MOVES WHERE game_id = ? AND player_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $game_id, $user_id);
$stmt->execute();
$stmt->bind_result($r, $c, $res);

$moves = [];
while ($stmt->fetch()) {
    $moves["$c$r"] = $res;
}
$stmt->close();

$cols = range('A', 'J');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game <?php echo $game_id; ?> – Battleship</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="site-header">
    <a href="home.php" class="logo">⚓ Battleship</a>
    <div class="header-right">
        <a href="home.php?view=current">Back to Games</a>
    </div>
</header>

<div class="page-wrapper">
    <h2 class="section-title">Game #<?php echo $game_id; ?></h2>
    <p style="color:#a0b8d0; margin-bottom:20px;">Click a cell to fire.</p>

    <div class="board-wrapper">
        <table class="game-board">
            <tr>
                <th></th>
                <?php foreach ($cols as $col_char) { echo "<th>{$col_char}</th>"; } ?>
            </tr>
            <?php for ($row = 1; $row <= 10; $row++): ?>
            <tr>
                <th><?php echo $row; ?></th>
                <?php foreach ($cols as $col_char):
                    $key = $col_char . $row;
                ?>
                <td>
                    <?php if (isset($moves[$key])): ?>
                        <div class="<?php echo $moves[$key] == 1 ? 'cell-hit' : 'cell-miss'; ?>">
                            <?php echo $moves[$key] == 1 ? '●' : '×'; ?>
                        </div>
                    <?php else: ?>
                        <form method="post" action="make_move.php" style="margin:0;">
                            <input type="hidden" name="game_id" value="<?php echo $game_id; ?>">
                            <input type="hidden" name="row" value="<?php echo $row; ?>">
                            <input type="hidden" name="col" value="<?php echo $col_char; ?>">
                            <button type="submit" class="cell-btn"></button>
                        </form>
                    <?php endif; ?>
                </td>
                <?php endforeach; ?>
            </tr>
            <?php endfor; ?>
        </table>
    </div>
</div>

</body>
</html>
