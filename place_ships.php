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

if (!ctype_digit((string)$game_id)) {
    echo "<p>Invalid game.</p>";
    exit;
}
$game_id = (int)$game_id;

$sql  = "SELECT player1_id, player2_id, status FROM GAMES WHERE game_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $game_id);
$stmt->execute();
$stmt->bind_result($p1_id, $p2_id, $status);
if (!$stmt->fetch()) {
    echo "<p>Game not found.</p>";
    exit;
}
$stmt->close();

if ((int)$user_id !== (int)$p1_id && (int)$user_id !== (int)$p2_id) {
    echo "<p>You are not part of this game.</p>";
    exit;
}
if ((int)$status !== 1) {
    echo "<p>This game is not in ship-placement stage. <a href='game.php?game_id=$game_id'>Go to game</a></p>";
    exit;
}

// Ensure ships exist for this player
$sql  = "SELECT COUNT(*) FROM SHIPS WHERE game_id = ? AND player_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $game_id, $user_id);
$stmt->execute();
$stmt->bind_result($ship_count);
$stmt->fetch();
$stmt->close();

if ((int)$ship_count === 0) {
    $lengths = [5, 4, 3, 3, 2];
    $sql     = "INSERT INTO SHIPS (game_id, player_id, ship_num, length) VALUES (?, ?, ?, ?)";
    $stmt    = $conn->prepare($sql);
    foreach ($lengths as $idx => $len) {
        $ship_num = $idx + 1;
        $stmt->bind_param("iiii", $game_id, $user_id, $ship_num, $len);
        $stmt->execute();
    }
    $stmt->close();
}

// Load ships
$ships = [];
$sql   = "SELECT ship_id, ship_num, length FROM SHIPS WHERE game_id = ? AND player_id = ? ORDER BY ship_num";
$stmt  = $conn->prepare($sql);
$stmt->bind_param("ii", $game_id, $user_id);
$stmt->execute();
$stmt->bind_result($sid, $snum, $slen);
while ($stmt->fetch()) {
    $ships[] = ["ship_id" => $sid, "ship_num" => $snum, "length" => $slen];
}
$stmt->close();

// Load occupied cells
$occupied = [];
$sql      = "SELECT sc.row_num, sc.col_char FROM SHIP_CELLS sc JOIN SHIPS s ON sc.ship_id = s.ship_id WHERE s.game_id = ? AND s.player_id = ?";
$stmt     = $conn->prepare($sql);
$stmt->bind_param("ii", $game_id, $user_id);
$stmt->execute();
$stmt->bind_result($r, $c);
while ($stmt->fetch()) {
    $occupied[] = "$r-$c";
}
$stmt->close();

// Count ships placed
$sql  = "SELECT COUNT(DISTINCT sc.ship_id) FROM SHIP_CELLS sc JOIN SHIPS s ON sc.ship_id = s.ship_id WHERE s.game_id = ? AND s.player_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $game_id, $user_id);
$stmt->execute();
$stmt->bind_result($ships_placed);
$stmt->fetch();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Place Ships – Battleship</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .place-layout { display: flex; gap: 32px; flex-wrap: wrap; align-items: flex-start; margin-top: 24px; }
        .place-controls { min-width: 200px; }
        .place-controls .form-group { margin-bottom: 16px; }
        .place-controls select {
            width: 100%; padding: 8px 10px;
            background-color: #0a1628; border: 1px solid #1e4d8c;
            border-radius: 4px; color: #e0e8f0; font-size: 0.95rem;
        }
        .place-controls select:focus { outline: none; border-color: #4fa3e0; }
        .ship-progress { color: #a0b8d0; font-size: 0.9rem; margin-bottom: 16px; }

        .place-board { border-collapse: collapse; }
        .place-board th {
            width: 52px; height: 52px; text-align: center;
            color: #4fa3e0; font-size: 1rem; background: #0d1f3c;
        }
        .place-board td { width: 52px; height: 52px; padding: 0; border: 1px solid #1e4d8c; }
        .cell-empty {
            width: 52px; height: 52px; border: none; cursor: pointer;
            background-color: #1a3a5c; transition: background 0.15s; display: block;
        }
        .cell-empty:hover { background-color: #2a5a8c; }
        .cell-ship {
            width: 52px; height: 52px; background-color: #2d6a9f;
            display: flex; align-items: center; justify-content: center;
            color: #e0e8f0; font-size: 1rem;
        }

        .ready-box {
            margin-top: 24px; padding: 16px;
            background: #0d1f3c; border: 1px solid #1e4d8c; border-radius: 8px;
        }
        #msg { margin-top: 12px; min-height: 24px; font-size: 0.9rem; }
        #ready-section { display: <?php echo $ships_placed >= 5 ? 'block' : 'none'; ?>; }
    </style>
</head>
<body>

<header class="site-header">
    <a href="home.php" class="logo">⚓ Battleship</a>
    <div class="header-right">
        <a href="home.php?view=current">Back to Games</a>
    </div>
</header>

<div class="page-wrapper">
    <h2 class="section-title">Place Your Ships — Game #<?php echo $game_id; ?></h2>

    <div class="place-layout">
        <div class="place-controls">

            <div class="form-group">
                <label for="ship_id">Ship</label>
                <select id="ship_id">
                    <?php foreach ($ships as $s): ?>
                        <option value="<?php echo $s['ship_id']; ?>">
                            Ship <?php echo $s['ship_num']; ?> (length <?php echo $s['length']; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="orientation">Orientation</label>
                <select id="orientation">
                    <option value="H">Horizontal</option>
                    <option value="V">Vertical</option>
                </select>
            </div>

            <p style="color:#a0b8d0; font-size:0.9rem; margin-bottom:16px;">
                Click a cell on the board to place the selected ship.
            </p>

            <p class="ship-progress">
                <span id="placed-count"><?php echo $ships_placed; ?></span> / 5 ships placed
            </p>

            <div id="msg"></div>

            <div id="ready-section" class="ready-box">
                <p style="color:#a0b8d0; margin-bottom:12px;">All ships placed! Confirm when ready.</p>
                <button class="btn btn-primary" onclick="confirmReady()">I'm Ready</button>
            </div>
        </div>

        <table class="place-board" id="board">
            <tr>
                <th scope="col"></th>
                <?php for ($c = 0; $c < 10; $c++): ?>
                    <th scope="col"><?php echo chr(ord('A') + $c); ?></th>
                <?php endfor; ?>
            </tr>
            <?php for ($row = 1; $row <= 10; $row++): ?>
                <tr>
                    <th scope="row"><?php echo $row; ?></th>
                    <?php for ($c = 0; $c < 10; $c++):
                        $colChar = chr(ord('A') + $c);
                        $key     = "$row-$colChar";
                        $isOccupied = in_array($key, $occupied);
                    ?>
                        <td>
                            <?php if ($isOccupied): ?>
                                <div class="cell-ship" data-key="<?php echo $key; ?>">■</div>
                            <?php else: ?>
                                <button class="cell-empty"
                                        data-row="<?php echo $row; ?>"
                                        data-col="<?php echo $colChar; ?>"
                                        onclick="placeShip(<?php echo $row; ?>, '<?php echo $colChar; ?>')">
                                </button>
                            <?php endif; ?>
                        </td>
                    <?php endfor; ?>
                </tr>
            <?php endfor; ?>
        </table>
    </div>
</div>

<script>
const GAME_ID = <?php echo $game_id; ?>;

function showMsg(text, isError) {
    const el = document.getElementById('msg');
    el.textContent = text;
    el.style.color = isError ? '#e05a4f' : '#5dd88a';
}

function updateBoard(occupied) {
    const cells = document.querySelectorAll('#board td');
    cells.forEach(td => {
        const btn = td.querySelector('button');
        const div = td.querySelector('div');
        const row = btn ? btn.dataset.row : (div ? div.dataset.key.split('-')[0] : null);
        const col = btn ? btn.dataset.col : (div ? div.dataset.key.split('-')[1] : null);
        if (!row || !col) { return; }
        const key = `${row}-${col}`;
        if (occupied.includes(key)) {
            td.innerHTML = `<div class="cell-ship" data-key="${key}">■</div>`;
        } else {
            td.innerHTML = `<button class="cell-empty" data-row="${row}" data-col="${col}" onclick="placeShip(${row}, '${col}')"></button>`;
        }
    });
}

function placeShip(row, col) {
    const shipId      = document.getElementById('ship_id').value;
    const orientation = document.getElementById('orientation').value;

    const body = new URLSearchParams({
        action: 'place', game_id: GAME_ID,
        ship_id: shipId, orientation, start: `${row}-${col}`
    });

    fetch('place_ship_ajax.php', { method: 'POST', body })
        .then(r => r.json())
        .then(data => {
            showMsg(data.message, !data.success);
            if (data.success) {
                updateBoard(data.occupied);
                document.getElementById('placed-count').textContent = data.ships_placed;
                document.getElementById('ready-section').style.display =
                    data.ships_placed >= 5 ? 'block' : 'none';
            }
        })
        .catch(() => showMsg('Network error. Try again.', true));
}

let polling = null;

function confirmReady() {
    const body = new URLSearchParams({ action: 'ready', game_id: GAME_ID });

    fetch('place_ship_ajax.php', { method: 'POST', body })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.started) {
                window.location.href = `game.php?game_id=${GAME_ID}`;
            } else {
                showMsg(data.message, !data.success);
                if (data.success && !data.started) {
                    // Poll every 5 seconds until opponent is ready
                    polling = setInterval(pollReady, 5000);
                }
            }
        })
        .catch(() => showMsg('Network error. Try again.', true));
}

function pollReady() {
    const body = new URLSearchParams({ action: 'ready', game_id: GAME_ID });
    fetch('place_ship_ajax.php', { method: 'POST', body })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.started) {
                clearInterval(polling);
                window.location.href = `game.php?game_id=${GAME_ID}`;
            }
        });
}
</script>

</body>
</html>
