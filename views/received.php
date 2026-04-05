<?php
// Invites Received view

echo "<h2>Invites Received</h2>";

$sql = "SELECT g.game_id, u.username
        FROM GAMES g
        JOIN USERS u ON g.player1_id = u.user_id
        WHERE g.player2_id = ? AND g.status = 0
        ORDER BY g.game_id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($game_id, $challenger);

echo "<table border='1'>";
echo "<tr><th>Game</th><th>Challenger</th><th>Action</th></tr>";

$empty = true;
while ($stmt->fetch()) {
    $empty = false;
    echo "<tr>";
    echo "<td>$game_id</td>";
    echo "<td>$challenger</td>";
    echo "<td>
            <form method='post' action='accept_invite.php'>
                <input type='hidden' name='game_id' value='$game_id'>
                <input type='submit' value='Accept'>
            </form>
          </td>";
    echo "</tr>";
}

if ($empty) {
    echo "<tr><td colspan='3'>No invites received.</td></tr>";
}
echo "</table>";

$stmt->close();
