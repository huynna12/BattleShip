<?php
// Invites Sent view

echo "<h2>Invites Sent</h2>";

/* ---------------------------
   1. List other users to challenge
   --------------------------- */
$sql = "SELECT user_id, username
        FROM USERS
        WHERE user_id <> ?
        ORDER BY username";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($opp_id, $opp_name);

echo "<h3>Challenge a user</h3>";
echo "<table border='1'>";
echo "<tr><th>User</th><th>Action</th></tr>";

while ($stmt->fetch()) {
    echo "<tr>";
    echo "<td>$opp_name</td>";
    echo "<td>
            <form method='post' action='send_invite.php'>
                <input type='hidden' name='opponent_id' value='$opp_id'>
                <input type='submit' value='Challenge'>
            </form>
          </td>";
    echo "</tr>";
}
echo "</table>";
$stmt->close();

/* ---------------------------
   2. Pending invites you sent
   --------------------------- */
echo "<h3>Pending invites</h3>";

$sql = "SELECT g.game_id, u.username
        FROM GAMES g
        JOIN USERS u ON g.player2_id = u.user_id
        WHERE g.player1_id = ? AND g.status = 0";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($game_id, $opp_name);

echo "<table border='1'>";
echo "<tr><th>Game</th><th>Opponent</th></tr>";

$empty = true;
while ($stmt->fetch()) {
    $empty = false;
    echo "<tr><td>$game_id</td><td>$opp_name</td></tr>";
}
if ($empty) {
    echo "<tr><td colspan='2'>No invites.</td></tr>";
}
echo "</table>";

$stmt->close();
