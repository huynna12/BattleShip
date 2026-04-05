<?php
// views/current.php
// Shows all games for the logged-in user that are in "placing" or "active" status.

// Assumes $conn (mysqli) and $user_id are already defined.

echo "<h2>Current Games</h2>";

// Fetch current games (status 1 = placing, 2 = active)
$sql = "
    SELECT g.game_id,
           g.status,
           u1.username AS p1_name,
           u2.username AS p2_name
    FROM GAMES g
    JOIN USERS u1 ON g.player1_id = u1.user_id
    JOIN USERS u2 ON g.player2_id = u2.user_id
    WHERE (g.player1_id = ? OR g.player2_id = ?)
      AND g.status IN (1, 2)
    ORDER BY g.game_id DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$stmt->bind_result($game_id, $status, $p1_name, $p2_name);

echo "<table border='1'>";
echo "<tr>
        <th>Game</th>
        <th>Player 1</th>
        <th>Player 2</th>
        <th>Status</th>
        <th>Open</th>
      </tr>";

$empty = true;

while ($stmt->fetch()) {
    $empty = false;

    // Decide label + target page based on status
    switch ($status) {
        case 1: // placing ships
            $status_text = "Placing Ships";
            $open_href   = "place_ships.php?game_id={$game_id}";
            break;

        case 2: // active
            $status_text = "Active";
            $open_href   = "game.php?game_id={$game_id}";
            break;

        default:
            // Should not happen because of the WHERE clause
            $status_text = "Unknown";
            $open_href   = "#";
            break;
    }

    echo "<tr>";
    echo "<td>{$game_id}</td>";
    echo "<td>{$p1_name}</td>";
    echo "<td>{$p2_name}</td>";
    echo "<td>{$status_text}</td>";
    echo "<td><a href='{$open_href}'>Open</a></td>";
    echo "</tr>";
}

if ($empty) {
    echo "<tr><td colspan='5'>No current games.</td></tr>";
}

echo "</table>";

$stmt->close();
