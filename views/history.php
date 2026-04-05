<?php
// Finished (History) Games view

echo "<h2>Finished Games</h2>";

$sql = "SELECT g.game_id,
               g.winner_id,
               u1.username AS p1_name,
               u2.username AS p2_name
        FROM GAMES g
        JOIN USERS u1 ON g.player1_id = u1.user_id
        JOIN USERS u2 ON g.player2_id = u2.user_id
        WHERE (g.player1_id = ? OR g.player2_id = ?)
          AND g.status = 3
        ORDER BY g.game_id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$stmt->bind_result($game_id, $winner_id, $p1_name, $p2_name);

echo "<table border='1'>";
echo "<tr><th>Game</th><th>Player 1</th><th>Player 2</th><th>Result</th></tr>";

$empty = true;
while ($stmt->fetch()) {
    $empty = false;

    if ($winner_id === null) {
        $result = "Finished (no winner)";
    } elseif ($winner_id == $user_id) {
        $result = "You won";
    } else {
        $result = "You lost";
    }

    echo "<tr>";
    echo "<td>$game_id</td>";
    echo "<td>$p1_name</td>";
    echo "<td>$p2_name</td>";
    echo "<td>$result</td>";
    echo "</tr>";
}

if ($empty) {
    echo "<tr><td colspan='4'>No finished games.</td></tr>";
}
echo "</table>";

$stmt->close();
