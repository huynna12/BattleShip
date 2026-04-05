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
$game_id = $_GET["game_id"] ?? "";

if (!ctype_digit($game_id)) {
    echo "<p>Invalid game.</p>";
    echo "<p><a href='home.php?view=current'>Back to games</a></p>";
    exit;
}

$game_id = (int)$game_id;

// check if the user belongs to the game
$sql = "SELECT player1_id, player2_id, status
        FROM GAMES WHERE game_id = ?";
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

// load move
$sql = "SELECT row_num, col_char, result
        FROM MOVES
        WHERE game_id = ? AND player_id = ?";
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
<html>
<head>
    <meta charset="utf-8">
    <title>Game <?php echo $game_id; ?></title>
</head>
<body>

<h1>Game <?php echo $game_id; ?></h1>
<p>Click a cell to fire.</p>

// 60px x 60px board 
<table border="1" cellpadding="0" cellspacing="0"
       style="table-layout:fixed; width:630px; border-collapse:collapse;">

    <tr>
        <th style="width:60px; height:60px;"></th>
        <?php foreach ($cols as $col_char) echo "<th style='width:60px; height:60px; text-align:center; font-size:20px;'>$col_char</th>"; ?>
    </tr>

    <?php
    for ($row = 1; $row <= 10; $row++) {
        echo "<tr>";
        echo "<th style='width:60px; height:60px; text-align:center; font-size:20px;'>$row</th>";

        foreach ($cols as $col_char) {
            $key = $col_char . $row;

            echo "<td style='width:60px; height:60px; padding:0; margin:0;'>";

            if (isset($moves[$key])) {
                // 1 = hit = O, 0 = miss = X
                $symbol = ($moves[$key] == 1) ? "O" : "X";

                echo "<div style='width:60px; height:60px;
                                 line-height:60px;
                                 text-align:center;
                                 font-size:28px;
                                 font-weight:bold;'>
                        $symbol
                      </div>";
            } else {
                echo "<form method='post' action='make_move.php'
                            style='margin:0; padding:0;'>";
                echo "<input type='hidden' name='game_id' value='$game_id'>";
                echo "<input type='hidden' name='row' value='$row'>";
                echo "<input type='hidden' name='col' value='$col_char'>";

                echo "<button type='submit'
                            style='width:60px; height:60px;
                                   padding:0; margin:0;
                                   border:1px solid #ccc;
                                   background-color:#eef;
                                   cursor:pointer;'>
                      </button>";

                echo "</form>";
            }

            echo "</td>";
        }

        echo "</tr>";
    }
    ?>
</table>

<hr>
<p><a href="home.php?view=current">Back to current games</a></p>

</body>
</html>
